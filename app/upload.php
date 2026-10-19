<?php
declare(strict_types=1);

const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const DOC_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'txt'];

/**
 * Store one uploaded file from $_FILES-style array. Returns relative path (uploads/2026/10/abc.jpg).
 * Images larger than 1920px are scaled down. Throws RuntimeException with a user-facing message.
 */
function store_upload(array $f, bool $imagesOnly = false): array
{
    global $CONFIG;
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'File terlalu besar.' : 'Gagal mengunggah file.');
    }
    $max = (int)($CONFIG['max_upload_mb'] ?? 8) * 1024 * 1024;
    if ($f['size'] > $max) throw new RuntimeException('File terlalu besar (maksimal ' . ($max / 1048576) . ' MB).');

    $ext = strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION));
    $isImage = in_array($ext, IMAGE_EXT, true);
    if ($imagesOnly && !$isImage) throw new RuntimeException('Hanya gambar JPG, PNG, WEBP, atau GIF.');
    if (!$isImage && !in_array($ext, DOC_EXT, true)) throw new RuntimeException('Jenis file tidak diizinkan.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) ?: 'application/octet-stream';
    $w = $h = null;
    if ($isImage) {
        $info = @getimagesize($f['tmp_name']);
        if (!$info || !str_starts_with($mime, 'image/')) throw new RuntimeException('File gambar tidak valid.');
        [$w, $h] = $info;
    }

    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $base = slugify(pathinfo((string)$f['name'], PATHINFO_FILENAME));
    $name = substr($base, 0, 40) . '-' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $dest = $dir . '/' . $name;

    $moved = is_uploaded_file($f['tmp_name']) ? move_uploaded_file($f['tmp_name'], $dest) : rename($f['tmp_name'], $dest);
    if (!$moved) throw new RuntimeException('Gagal menyimpan file.');

    if ($isImage && $w > 1920 && $ext !== 'gif') {
        [$w, $h] = resize_image($dest, $ext, 1920);
    }
    $rel = 'uploads/' . $sub . '/' . $name;
    $size = (int)filesize($dest);
    insert('media', ['file' => $rel, 'original' => mb_substr((string)$f['name'], 0, 250), 'mime' => $mime, 'size' => $size,
        'width' => $w, 'height' => $h, 'created_at' => now()]);
    return ['path' => $rel, 'size' => $size, 'is_image' => $isImage];
}

function resize_image(string $path, string $ext, int $maxW): array
{
    if (!function_exists('imagecreatefromjpeg')) return getimagesize($path) ?: [0, 0];
    $src = match ($ext) {
        'png' => imagecreatefrompng($path),
        'webp' => imagecreatefromwebp($path),
        default => imagecreatefromjpeg($path),
    };
    if (!$src) return getimagesize($path) ?: [0, 0];
    $w = imagesx($src);
    $h = imagesy($src);
    $nh = (int)round($h * $maxW / $w);
    $dst = imagecreatetruecolor($maxW, $nh);
    if ($ext === 'png' || $ext === 'webp') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxW, $nh, $w, $h);
    match ($ext) {
        'png' => imagepng($dst, $path, 6),
        'webp' => imagewebp($dst, $path, 82),
        default => imagejpeg($dst, $path, 82),
    };
    imagedestroy($src);
    imagedestroy($dst);
    return [$maxW, $nh];
}

/** Normalise a multi-file input ($_FILES['x'] with name[]) into a list. */
function files_list(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f) return [];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

function human_size(int $b): string
{
    if ($b >= 1048576) return number_format($b / 1048576, 1, ',', '.') . ' MB';
    return max(1, (int)round($b / 1024)) . ' KB';
}

/** Delete a file under uploads/ if no longer referenced elsewhere by the caller. */
function delete_upload(?string $rel): void
{
    if (!$rel || !str_starts_with($rel, 'uploads/') || str_contains($rel, '..')) return;
    $p = ROOT . '/' . $rel;
    if (is_file($p)) @unlink($p);
    q('DELETE FROM media WHERE file = ?', [$rel]);
}
