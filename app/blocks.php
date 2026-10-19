<?php
declare(strict_types=1);

/*
 * Page builder blocks. Each block type declares its admin fields (rendered by assets/js/admin.js)
 * and a PHP renderer for the public site. Block data is stored as JSON in pages.blocks.
 */

const BG_OPTIONS = ['white' => 'Putih', 'soft' => 'Hijau muda', 'dark' => 'Hijau tua'];
const ICON_OPTIONS = ['clipboard' => 'Formulir', 'laptop' => 'Laptop', 'test' => 'Ujian', 'medal' => 'Medali', 'calendar' => 'Kalender',
    'book' => 'Buku', 'star' => 'Bintang', 'heart' => 'Hati', 'flask' => 'Sains', 'lang' => 'Bahasa', 'users' => 'Orang',
    'user' => 'Profil', 'image' => 'Gambar', 'file' => 'Dokumen', 'pin' => 'Lokasi', 'phone' => 'Telepon', 'mail' => 'Email',
    'wa' => 'WhatsApp', 'clock' => 'Jam', 'home' => 'Rumah', 'check' => 'Centang', 'map' => 'Peta'];

function heading_fields(): array
{
    return [
        ['name' => 'eyebrow', 'label' => 'Label kecil di atas judul', 'type' => 'text'],
        ['name' => 'title', 'label' => 'Judul bagian', 'type' => 'text'],
        ['name' => 'intro', 'label' => 'Teks pengantar', 'type' => 'textarea'],
        ['name' => 'link_label', 'label' => 'Tautan di kanan judul (teks)', 'type' => 'text'],
        ['name' => 'link_url', 'label' => 'Tautan di kanan judul (alamat)', 'type' => 'url'],
    ];
}

function block_types(): array
{
    $h = heading_fields();
    $btn = fn($n, $l) => [
        ['name' => $n . '_label', 'label' => $l . ' (teks)', 'type' => 'text'],
        ['name' => $n . '_url', 'label' => $l . ' (alamat)', 'type' => 'url'],
    ];
    return [
        'hero' => ['label' => 'Banner utama (hero)', 'group' => 'Pembuka', 'fields' => [
            ['name' => 'eyebrow', 'label' => 'Label kecil', 'type' => 'text'],
            ['name' => 'title', 'label' => 'Judul besar', 'type' => 'textarea'],
            ['name' => 'subtitle', 'label' => 'Sub judul', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Foto', 'type' => 'image'],
            ...$btn('btn1', 'Tombol utama'), ...$btn('btn2', 'Tombol kedua'),
            ['name' => 'badges', 'label' => 'Keunggulan singkat', 'type' => 'list', 'fields' => [
                ['name' => 'icon', 'label' => 'Ikon', 'type' => 'select', 'options' => ICON_OPTIONS],
                ['name' => 'text', 'label' => 'Teks', 'type' => 'text']]],
            ['name' => 'card_label', 'label' => 'Kartu melayang: label', 'type' => 'text'],
            ['name' => 'card_text', 'label' => 'Kartu melayang: isi', 'type' => 'text'],
        ]],
        'page_header' => ['label' => 'Judul halaman', 'group' => 'Pembuka', 'fields' => [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'subtitle', 'label' => 'Sub judul', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Foto (opsional)', 'type' => 'image'],
        ]],
        'subnav' => ['label' => 'Navigasi bagian (pintasan)', 'group' => 'Pembuka', 'fields' => [
            ['name' => 'items', 'label' => 'Pintasan', 'type' => 'list', 'fields' => [
                ['name' => 'label', 'label' => 'Teks', 'type' => 'text'],
                ['name' => 'url', 'label' => 'Tujuan (contoh: #sejarah)', 'type' => 'url']]],
        ]],
        'quick_links' => ['label' => 'Layanan cepat', 'group' => 'Pembuka', 'fields' => [
            ['name' => 'items', 'label' => 'Layanan', 'type' => 'list', 'fields' => [
                ['name' => 'icon', 'label' => 'Ikon', 'type' => 'select', 'options' => ICON_OPTIONS],
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Keterangan', 'type' => 'text'],
                ['name' => 'url', 'label' => 'Tautan', 'type' => 'url']]],
        ]],
        'text_image' => ['label' => 'Teks + gambar', 'group' => 'Konten', 'fields' => [
            ...array_slice($h, 0, 2),
            ['name' => 'body', 'label' => 'Isi', 'type' => 'richtext'],
            ['name' => 'image', 'label' => 'Gambar', 'type' => 'image'],
            ['name' => 'image_side', 'label' => 'Posisi gambar', 'type' => 'select', 'options' => ['right' => 'Kanan', 'left' => 'Kiri', 'none' => 'Tanpa gambar']],
            ['name' => 'quote_mark', 'label' => 'Tampilkan tanda kutip emas di gambar', 'type' => 'checkbox'],
            ['name' => 'sign_name', 'label' => 'Nama (tanda tangan)', 'type' => 'text'],
            ['name' => 'sign_role', 'label' => 'Jabatan', 'type' => 'text'],
            ...$btn('btn', 'Tombol'),
        ]],
        'cards' => ['label' => 'Kartu (program, fasilitas, dll)', 'group' => 'Konten', 'fields' => [
            ...$h,
            ['name' => 'style', 'label' => 'Gaya kartu', 'type' => 'select', 'options' => ['image' => 'Dengan foto', 'icon' => 'Dengan ikon', 'feature' => 'Program unggulan (kartu pertama hijau)']],
            ['name' => 'columns', 'label' => 'Jumlah kolom', 'type' => 'select', 'options' => ['3' => '3 kolom', '4' => '4 kolom', '2' => '2 kolom']],
            ['name' => 'items', 'label' => 'Kartu', 'type' => 'list', 'fields' => [
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image'],
                ['name' => 'icon', 'label' => 'Ikon', 'type' => 'select', 'options' => ICON_OPTIONS],
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Keterangan', 'type' => 'textarea'],
                ['name' => 'meta', 'label' => 'Info tambahan (jadwal, pembina, dll)', 'type' => 'text'],
                ['name' => 'url', 'label' => 'Tautan (opsional)', 'type' => 'url']]],
        ]],
        'stats' => ['label' => 'Angka statistik', 'group' => 'Konten', 'fields' => [
            ['name' => 'items', 'label' => 'Angka', 'type' => 'list', 'fields' => [
                ['name' => 'value', 'label' => 'Angka', 'type' => 'text'],
                ['name' => 'label', 'label' => 'Keterangan', 'type' => 'text']]],
        ]],
        'steps' => ['label' => 'Langkah bernomor', 'group' => 'Konten', 'fields' => [
            ...$h,
            ['name' => 'items', 'label' => 'Langkah', 'type' => 'list', 'fields' => [
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Keterangan', 'type' => 'textarea']]],
        ]],
        'timeline' => ['label' => 'Linimasa (sejarah)', 'group' => 'Konten', 'fields' => [
            ...$h,
            ['name' => 'items', 'label' => 'Peristiwa', 'type' => 'list', 'fields' => [
                ['name' => 'year', 'label' => 'Tahun', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Peristiwa', 'type' => 'textarea']]],
        ]],
        'vision' => ['label' => 'Visi & misi', 'group' => 'Konten', 'fields' => [
            ...array_slice($h, 0, 2),
            ['name' => 'vision', 'label' => 'Visi', 'type' => 'textarea'],
            ['name' => 'missions', 'label' => 'Misi', 'type' => 'list', 'fields' => [['name' => 'text', 'label' => 'Misi', 'type' => 'textarea']]],
            ['name' => 'goals', 'label' => 'Tujuan', 'type' => 'list', 'fields' => [['name' => 'text', 'label' => 'Tujuan', 'type' => 'textarea']]],
        ]],
        'org_chart' => ['label' => 'Struktur organisasi', 'group' => 'Konten', 'fields' => [
            ...array_slice($h, 0, 2),
            ['name' => 'items', 'label' => 'Jabatan', 'type' => 'list', 'fields' => [
                ['name' => 'level', 'label' => 'Tingkat', 'type' => 'select', 'options' => ['1' => 'Tingkat 1 (puncak)', '2' => 'Tingkat 2', '3' => 'Tingkat 3']],
                ['name' => 'role', 'label' => 'Jabatan', 'type' => 'text'],
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
                ['name' => 'highlight', 'label' => 'Sorot (warna hijau)', 'type' => 'checkbox']]],
        ]],
        'faq' => ['label' => 'Tanya jawab / akordeon', 'group' => 'Konten', 'fields' => [
            ...array_slice($h, 0, 3),
            ['name' => 'items', 'label' => 'Pertanyaan', 'type' => 'list', 'fields' => [
                ['name' => 'q', 'label' => 'Pertanyaan / judul', 'type' => 'text'],
                ['name' => 'a', 'label' => 'Jawaban / isi', 'type' => 'textarea']]],
        ]],
        'table' => ['label' => 'Tabel', 'group' => 'Konten', 'fields' => [
            ...$h,
            ['name' => 'header', 'label' => 'Judul kolom (pisahkan dengan |)', 'type' => 'text'],
            ['name' => 'rows', 'label' => 'Isi tabel: satu baris per baris, kolom dipisah |', 'type' => 'textarea', 'rows' => 8],
            ['name' => 'note', 'label' => 'Catatan di bawah tabel', 'type' => 'text'],
        ]],
        'testimonials' => ['label' => 'Testimoni', 'group' => 'Konten', 'fields' => [
            ...$h,
            ['name' => 'items', 'label' => 'Testimoni', 'type' => 'list', 'fields' => [
                ['name' => 'quote', 'label' => 'Kutipan', 'type' => 'textarea'],
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
                ['name' => 'role', 'label' => 'Keterangan', 'type' => 'text']]],
        ]],
        'quote' => ['label' => 'Kutipan / hadits', 'group' => 'Konten', 'fields' => [
            ['name' => 'arabic', 'label' => 'Teks Arab', 'type' => 'textarea'],
            ['name' => 'text', 'label' => 'Terjemahan', 'type' => 'textarea'],
            ['name' => 'source', 'label' => 'Sumber', 'type' => 'text'],
        ]],
        'cta' => ['label' => 'Ajakan (call to action)', 'group' => 'Konten', 'fields' => [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Teks', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Foto (opsional, tampil di kiri)', 'type' => 'image'],
            ...$btn('btn1', 'Tombol utama'), ...$btn('btn2', 'Tombol kedua'),
        ]],
        'richtext' => ['label' => 'Teks bebas', 'group' => 'Konten', 'fields' => [
            ...array_slice($h, 0, 2),
            ['name' => 'body', 'label' => 'Isi', 'type' => 'richtext'],
            ['name' => 'narrow', 'label' => 'Lebar baca (kolom sempit)', 'type' => 'checkbox'],
        ]],
        'gallery' => ['label' => 'Galeri foto', 'group' => 'Media', 'fields' => [
            ...$h,
            ['name' => 'album', 'label' => 'Ambil foto dari album (kosongkan untuk pilih manual)', 'type' => 'select', 'options' => 'albums'],
            ['name' => 'limit', 'label' => 'Jumlah foto dari album', 'type' => 'number'],
            ['name' => 'items', 'label' => 'Foto pilihan manual', 'type' => 'list', 'fields' => [
                ['name' => 'image', 'label' => 'Foto', 'type' => 'image'],
                ['name' => 'caption', 'label' => 'Keterangan', 'type' => 'text'],
                ['name' => 'tall', 'label' => 'Foto tinggi (2 baris)', 'type' => 'checkbox'],
                ['name' => 'video', 'label' => 'Tautan video YouTube (opsional)', 'type' => 'url']]],
        ]],
        'albums' => ['label' => 'Daftar album', 'group' => 'Media', 'fields' => [...$h, ['name' => 'limit', 'label' => 'Jumlah album (0 = semua)', 'type' => 'number']]],
        'videos' => ['label' => 'Daftar video', 'group' => 'Media', 'fields' => [...$h, ['name' => 'limit', 'label' => 'Jumlah video (0 = semua)', 'type' => 'number']]],
        'posts' => ['label' => 'Berita / postingan', 'group' => 'Data', 'fields' => [
            ...$h,
            ['name' => 'category', 'label' => 'Kategori', 'type' => 'select', 'options' => 'categories'],
            ['name' => 'limit', 'label' => 'Jumlah berita', 'type' => 'number'],
            ['name' => 'mode', 'label' => 'Tampilan', 'type' => 'select', 'options' => ['grid' => 'Kartu + agenda di samping', 'cards' => 'Kartu saja', 'full' => 'Halaman berita lengkap (cari, filter, halaman)']],
        ]],
        'agenda' => ['label' => 'Agenda', 'group' => 'Data', 'fields' => [...$h, ['name' => 'limit', 'label' => 'Jumlah agenda', 'type' => 'number']]],
        'staff' => ['label' => 'Guru & staf', 'group' => 'Data', 'fields' => [...$h, ['name' => 'group', 'label' => 'Kelompok', 'type' => 'select', 'options' => 'staff_groups'], ['name' => 'filter', 'label' => 'Tampilkan filter kelompok', 'type' => 'checkbox']]],
        'downloads' => ['label' => 'Unduhan', 'group' => 'Data', 'fields' => [...$h, ['name' => 'category', 'label' => 'Kategori', 'type' => 'select', 'options' => 'download_categories']]],
        'contact_info' => ['label' => 'Info kontak', 'group' => 'Formulir', 'fields' => [['name' => 'note', 'label' => 'Diambil dari Pengaturan', 'type' => 'note']]],
        'contact_form' => ['label' => 'Formulir pesan + peta', 'group' => 'Formulir', 'fields' => [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'subjects', 'label' => 'Pilihan keperluan (pisahkan dengan |)', 'type' => 'text'],
            ['name' => 'show_map', 'label' => 'Tampilkan peta di samping', 'type' => 'checkbox'],
        ]],
        'ppdb_form' => ['label' => 'Formulir pendaftaran PPDB', 'group' => 'Formulir', 'fields' => [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'intro', 'label' => 'Keterangan', 'type' => 'textarea'],
            ['name' => 'closed', 'label' => 'Pendaftaran ditutup', 'type' => 'checkbox'],
            ['name' => 'closed_text', 'label' => 'Pesan saat ditutup', 'type' => 'text'],
        ]],
        'ppdb_status' => ['label' => 'Cek status pendaftaran', 'group' => 'Formulir', 'fields' => [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'intro', 'label' => 'Keterangan', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Foto (opsional)', 'type' => 'image'],
        ]],
        'countdown' => ['label' => 'Kuota & hitung mundur', 'group' => 'Formulir', 'fields' => [
            ...array_slice($h, 0, 3),
            ['name' => 'label', 'label' => 'Label kartu', 'type' => 'text'],
            ['name' => 'quota', 'label' => 'Kuota kursi', 'type' => 'number'],
            ['name' => 'deadline', 'label' => 'Batas waktu (tanggal & jam)', 'type' => 'datetime'],
            ...$btn('btn1', 'Tombol utama'), ...$btn('btn2', 'Tombol kedua'),
        ]],
    ];
}

/** Common fields every block gets in the editor. */
function block_common_fields(): array
{
    return [
        ['name' => '_anchor', 'label' => 'ID bagian (untuk pintasan menu, contoh: sejarah)', 'type' => 'text'],
        ['name' => '_bg', 'label' => 'Warna latar', 'type' => 'select', 'options' => BG_OPTIONS],
        ['name' => '_hidden', 'label' => 'Sembunyikan bagian ini', 'type' => 'checkbox'],
    ];
}

/** Schema for the admin editor, with dynamic option lists resolved. */
function block_schema(): array
{
    $dyn = [
        'categories' => ['' => 'Semua kategori'] + array_column(rows('SELECT id, name FROM categories ORDER BY name'), 'name', 'id'),
        'albums' => ['' => '— Pilih manual —'] + array_column(rows('SELECT id, title FROM albums ORDER BY event_date DESC, id DESC'), 'title', 'id'),
        'staff_groups' => ['' => 'Semua'] + array_combine($g = array_column(rows("SELECT DISTINCT group_name FROM staff WHERE group_name <> '' ORDER BY group_name"), 'group_name'), $g),
        'download_categories' => ['' => 'Semua'] + array_combine($d = array_column(rows("SELECT DISTINCT category FROM downloads WHERE category <> '' ORDER BY category"), 'category'), $d),
    ];
    $fix = function (array $fields) use (&$fix, $dyn) {
        foreach ($fields as &$f) {
            if (($f['type'] === 'select') && is_string($f['options'])) $f['options'] = $dyn[$f['options']] ?? [];
            if ($f['type'] === 'list') $f['fields'] = $fix($f['fields']);
            if ($f['type'] === 'select') {
                // keep key order in JS by sending a list of pairs
                $f['options'] = array_map(fn($k, $v) => [(string)$k, (string)$v], array_keys($f['options']), $f['options']);
            }
        }
        return $fields;
    };
    $out = [];
    foreach (block_types() as $type => $def) {
        $out[$type] = ['label' => $def['label'], 'group' => $def['group'], 'fields' => $fix($def['fields'])];
    }
    return ['types' => $out, 'common' => $fix(block_common_fields())];
}

/** Clean block JSON coming from the editor: known types only, rich text sanitised. */
function sanitize_blocks(string $json): array
{
    $data = json_decode($json, true);
    if (!is_array($data)) return [];
    $types = block_types();
    $common = block_common_fields();
    $cleanFields = function (array $fields, array $vals) use (&$cleanFields): array {
        $out = [];
        foreach ($fields as $f) {
            $n = $f['name'];
            $v = $vals[$n] ?? null;
            switch ($f['type']) {
                case 'list':
                    $out[$n] = [];
                    foreach ((array)$v as $item) if (is_array($item)) $out[$n][] = $cleanFields($f['fields'], $item);
                    break;
                case 'richtext':
                    $out[$n] = clean_html(is_string($v) ? $v : '');
                    break;
                case 'checkbox':
                    $out[$n] = !empty($v);
                    break;
                case 'note':
                    break;
                default:
                    $out[$n] = is_scalar($v) ? mb_substr(trim((string)$v), 0, 5000) : '';
            }
        }
        return $out;
    };
    $blocks = [];
    foreach ($data as $b) {
        if (!is_array($b) || !isset($types[$b['type'] ?? ''])) continue;
        $clean = $cleanFields(array_merge($types[$b['type']]['fields'], $common), $b);
        $a = trim((string)($clean['_anchor'] ?? ''));
        $clean['_anchor'] = $a === '' ? '' : slugify($a);
        $blocks[] = ['type' => $b['type']] + $clean;
    }
    return $blocks;
}

/* ===================== Rendering ===================== */

function render_blocks(array $blocks, array $page): string
{
    $html = '';
    $types = block_types();
    foreach ($blocks as $i => $b) {
        $type = $b['type'] ?? '';
        if (!isset($types[$type]) || !empty($b['_hidden'])) continue;
        $fn = 'blk_' . $type;
        if (function_exists($fn)) $html .= $fn($b, $page, $i) . "\n";
    }
    return $html;
}

function b(array $b, string $k): string
{
    return (string)($b[$k] ?? '');
}

function sec_open(array $b, string $class = '', string $defaultBg = 'white'): string
{
    $bg = $b['_bg'] ?? '' ?: $defaultBg;
    $id = $b['_anchor'] ?? '' ? ' id="' . e($b['_anchor']) . '"' : '';
    return '<section' . $id . ' class="blk bg-' . e($bg) . ' ' . e($class) . '">';
}

function sec_head(array $b, string $right = ''): string
{
    $title = b($b, 'title');
    $eyebrow = b($b, 'eyebrow');
    $intro = b($b, 'intro');
    $link = b($b, 'link_label');
    if ($title === '' && $eyebrow === '' && $intro === '' && $right === '') return '';
    $h = '<div class="sec-head"><div class="sec-head-text">';
    if ($eyebrow !== '') $h .= '<span class="eyebrow">' . e($eyebrow) . '</span>';
    if ($title !== '') $h .= '<h2>' . e($title) . '</h2>';
    if ($intro !== '') $h .= '<p class="intro">' . nl2br(e($intro)) . '</p>';
    $h .= '</div>';
    if ($link !== '') $h .= '<a class="link-arrow" href="' . e(url(b($b, 'link_url') ?: '#')) . '">' . e($link) . icon('arrow') . '</a>';
    return $h . $right . '</div>';
}

function btn(string $label, string $href, string $class = 'btn-primary', string $icon = ''): string
{
    if ($label === '') return '';
    return '<a class="btn ' . $class . '" href="' . e(url($href ?: '#')) . '">' . ($icon ? icon($icon) : '') . e($label) . ($class === 'btn-primary' || $class === 'btn-gold' ? icon('arrow') : '') . '</a>';
}

function img_tag(string $src, string $alt = '', string $class = ''): string
{
    if ($src === '') return '<div class="img-ph ' . e($class) . '">' . icon('image') . '</div>';
    return '<img src="' . e(media($src)) . '" alt="' . e($alt) . '" class="' . e($class) . '" loading="lazy">';
}

function geo_pattern(string $id): string
{
    return '<svg class="geo" aria-hidden="true"><defs><pattern id="' . $id . '" width="64" height="64" patternUnits="userSpaceOnUse"><g fill="none" stroke="#E9C978" stroke-width="1"><rect x="16" y="16" width="32" height="32"/><rect x="16" y="16" width="32" height="32" transform="rotate(45 32 32)"/><circle cx="32" cy="32" r="8"/></g></pattern></defs><rect width="100%" height="100%" fill="url(#' . $id . ')"/></svg>';
}

function blk_hero(array $b): string
{
    $badges = '';
    foreach ($b['badges'] ?? [] as $x) {
        $badges .= '<span>' . icon($x['icon'] ?: 'star') . e($x['text']) . '</span>';
    }
    $card = b($b, 'card_label') !== '' ? '<div class="hero-card"><span class="hero-card-ic">' . icon('calendar') . '</span><span><small>' . e(b($b, 'card_label')) . '</small><strong>' . e(b($b, 'card_text')) . '</strong></span></div>' : '';
    return sec_open($b, 'hero', 'dark') . geo_pattern('geoHero' . mt_rand()) . '<div class="wrap hero-grid"><div class="hero-text">'
        . (b($b, 'eyebrow') ? '<span class="hero-eyebrow">' . e(b($b, 'eyebrow')) . '</span>' : '')
        . '<h1>' . nl2br(e(b($b, 'title'))) . '</h1>'
        . (b($b, 'subtitle') ? '<p class="lead">' . nl2br(e(b($b, 'subtitle'))) . '</p>' : '')
        . '<div class="btn-row">' . btn(b($b, 'btn1_label'), b($b, 'btn1_url'), 'btn-gold') . btn(b($b, 'btn2_label'), b($b, 'btn2_url'), 'btn-ghost-light') . '</div>'
        . ($badges ? '<div class="hero-badges">' . $badges . '</div>' : '')
        . '</div><div class="hero-media">' . img_tag(b($b, 'image'), b($b, 'title'), 'hero-img') . $card . '</div></div></section>';
}

function blk_page_header(array $b, array $page): string
{
    $title = b($b, 'title') ?: $page['title'];
    $hasImg = b($b, 'image') !== '';
    return sec_open($b, 'page-header' . ($hasImg ? '' : ' no-img'), 'dark') . geo_pattern('geoPage' . mt_rand()) . '<div class="wrap ph-grid"><div class="ph-text">'
        . '<nav class="crumb" aria-label="Breadcrumb"><a href="' . e(url('')) . '">Beranda</a> / ' . e($page['title']) . '</nav>'
        . '<h1>' . e($title) . '</h1>' . (b($b, 'subtitle') ? '<p class="lead">' . nl2br(e(b($b, 'subtitle'))) . '</p>' : '')
        . '</div>' . ($hasImg ? '<div class="ph-media">' . img_tag(b($b, 'image'), $title, 'ph-img') . '</div>' : '') . '</div></section>';
}

function blk_subnav(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $x) $h .= '<a href="' . e(url($x['url'] ?: '#')) . '">' . e($x['label']) . '</a>';
    return '<div class="subnav"><nav class="wrap" aria-label="Bagian halaman">' . $h . '</nav></div>';
}

function blk_quick_links(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $x) {
        $h .= '<a class="ql-item" href="' . e(url($x['url'] ?: '#')) . '"><span class="ic-box">' . icon($x['icon'] ?: 'star') . '</span><strong>' . e($x['title']) . '</strong><small>' . e($x['text']) . '</small></a>';
    }
    return '<section class="quick-links"' . (b($b, '_anchor') ? ' id="' . e(b($b, '_anchor')) . '"' : '') . '><div class="wrap"><div class="ql-box">' . $h . '</div></div></section>';
}

function blk_text_image(array $b): string
{
    $side = b($b, 'image_side') ?: 'right';
    $media = '';
    if ($side !== 'none' && b($b, 'image') !== '') {
        $media = '<div class="ti-media">' . img_tag(b($b, 'image'), b($b, 'title') ?: b($b, 'sign_name'), 'ti-img') . (!empty($b['quote_mark']) ? '<span class="quote-mark" aria-hidden="true">&#8220;</span>' : '') . '</div>';
    }
    $sign = b($b, 'sign_name') ? '<div class="sign"><span class="sign-line"></span><span><strong>' . e(b($b, 'sign_name')) . '</strong><small>' . e(b($b, 'sign_role')) . '</small></span></div>' : '';
    $text = '<div class="ti-text">' . (b($b, 'eyebrow') ? '<span class="eyebrow">' . e(b($b, 'eyebrow')) . '</span>' : '')
        . (b($b, 'title') ? '<h2>' . e(b($b, 'title')) . '</h2>' : '') . '<div class="prose">' . b($b, 'body') . '</div>' . $sign
        . (b($b, 'btn_label') ? '<div>' . '<a class="link-arrow" href="' . e(url(b($b, 'btn_url') ?: '#')) . '">' . e(b($b, 'btn_label')) . icon('arrow') . '</a></div>' : '') . '</div>';
    return sec_open($b, 'text-image') . '<div class="wrap ti-grid ' . ($side === 'left' ? 'img-left' : '') . ($media ? '' : ' no-media') . '">'
        . ($side === 'left' ? $media . $text : $text . $media) . '</div></section>';
}

function blk_cards(array $b): string
{
    $style = b($b, 'style') ?: 'image';
    $cols = (int)(b($b, 'columns') ?: 3);
    $h = '';
    foreach ($b['items'] ?? [] as $i => $x) {
        $tag = $x['url'] ? 'a' : 'article';
        $href = $x['url'] ? ' href="' . e(url($x['url'])) . '"' : '';
        $dark = $style === 'feature' && $i === 0 ? ' card-dark' : '';
        $h .= "<$tag class=\"card card-$style$dark\"$href>";
        if (($style === 'image' || $style === 'feature') && $x['image']) $h .= img_tag($x['image'], $x['title'], 'card-img');
        $h .= '<div class="card-body">';
        if ($style === 'icon' || ($style === 'feature' && !$x['image'])) $h .= '<span class="ic-box">' . icon($x['icon'] ?: 'star') . '</span>';
        $h .= '<h3>' . e($x['title']) . '</h3>' . ($x['text'] ? '<p>' . nl2br(e($x['text'])) . '</p>' : '') . ($x['meta'] ? '<small class="meta">' . e($x['meta']) . '</small>' : '');
        $h .= "</div></$tag>";
    }
    return sec_open($b, 'cards-sec') . '<div class="wrap stack">' . sec_head($b) . '<div class="grid cols-' . $cols . '">' . $h . '</div></div></section>';
}

function blk_stats(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $x) $h .= '<div class="stat"><strong>' . e($x['value']) . '</strong><span>' . e($x['label']) . '</span></div>';
    return sec_open($b, 'stats-sec', 'soft') . '<div class="wrap stats">' . $h . '</div></section>';
}

function blk_steps(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $i => $x) $h .= '<li class="step"><span class="step-no">' . ($i + 1) . '</span><h3>' . e($x['title']) . '</h3><p>' . nl2br(e($x['text'])) . '</p></li>';
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<ol class="steps">' . $h . '</ol></div></section>';
}

function blk_timeline(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $x) $h .= '<li><span class="tl-year">' . e($x['year']) . '</span><span>' . nl2br(e($x['text'])) . '</span></li>';
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<ol class="timeline">' . $h . '</ol></div></section>';
}

function blk_vision(array $b): string
{
    $m = '';
    foreach ($b['missions'] ?? [] as $i => $x) $m .= '<li><span class="num">' . ($i + 1) . '</span><span>' . e($x['text']) . '</span></li>';
    $g = '';
    foreach ($b['goals'] ?? [] as $i => $x) $g .= '<div class="goal"><small>TUJUAN ' . ($i + 1) . '</small><span>' . e($x['text']) . '</span></div>';
    return sec_open($b, '', 'soft') . '<div class="wrap stack">' . sec_head($b) . '<div class="vision-grid"><div class="vision-card"><span class="eyebrow gold">VISI</span><p>"' . e(b($b, 'vision')) . '"</p></div>'
        . '<div class="mission-card"><span class="eyebrow">MISI</span><ol class="missions">' . $m . '</ol></div></div>' . ($g ? '<div class="grid cols-3">' . $g . '</div>' : '') . '</div></section>';
}

function blk_org_chart(array $b): string
{
    $levels = [1 => '', 2 => '', 3 => ''];
    foreach ($b['items'] ?? [] as $x) {
        $lv = (int)($x['level'] ?: 3);
        $levels[$lv] .= '<div class="org-box' . (!empty($x['highlight']) ? ' hl' : '') . '"><strong>' . e($x['role']) . '</strong><small>' . e($x['name']) . '</small></div>';
    }
    $h = '';
    foreach ($levels as $lv => $boxes) {
        if ($boxes === '') continue;
        if ($h !== '') $h .= '<div class="org-line" aria-hidden="true"></div>';
        $h .= '<div class="org-row lv' . $lv . '">' . $boxes . '</div>';
    }
    return sec_open($b, '', 'soft') . '<div class="wrap stack">' . sec_head($b) . '<div class="org">' . $h . '</div></div></section>';
}

function blk_faq(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $i => $x) $h .= '<details' . ($i === 0 ? ' open' : '') . '><summary>' . e($x['q']) . '</summary><p>' . nl2br(e($x['a'])) . '</p></details>';
    return sec_open($b) . '<div class="wrap faq-grid"><div>' . sec_head($b) . '</div><div class="faq-list">' . $h . '</div></div></section>';
}

function blk_table(array $b): string
{
    $head = array_filter(array_map('trim', explode('|', b($b, 'header'))), fn($x) => $x !== '');
    $h = '<thead><tr>' . implode('', array_map(fn($c) => '<th>' . e($c) . '</th>', $head)) . '</tr></thead><tbody>';
    foreach (preg_split('/\r?\n/', b($b, 'rows')) as $line) {
        if (trim($line) === '') continue;
        $h .= '<tr>' . implode('', array_map(fn($c) => '<td>' . e(trim($c)) . '</td>', explode('|', $line))) . '</tr>';
    }
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<div class="table-box"><table>' . ($head ? $h : str_replace('<thead><tr></tr></thead>', '', $h)) . '</tbody></table></div>'
        . (b($b, 'note') ? '<p class="note">' . e(b($b, 'note')) . '</p>' : '') . '</div></section>';
}

function blk_testimonials(array $b): string
{
    $h = '';
    foreach ($b['items'] ?? [] as $x) $h .= '<figure class="testi"><span class="testi-mark" aria-hidden="true">&#8220;</span><blockquote>' . nl2br(e($x['quote'])) . '</blockquote><figcaption><strong>' . e($x['name']) . '</strong><small>' . e($x['role']) . '</small></figcaption></figure>';
    return sec_open($b, '', 'soft') . '<div class="wrap stack">' . sec_head($b) . '<div class="grid cols-3">' . $h . '</div></div></section>';
}

function blk_quote(array $b): string
{
    return sec_open($b, 'quote-sec', 'dark') . '<div class="wrap quote">' . (b($b, 'arabic') ? '<p lang="ar" dir="rtl" class="arabic">' . e(b($b, 'arabic')) . '</p>' : '')
        . '<p class="quote-text">' . e(b($b, 'text')) . '</p>' . (b($b, 'source') ? '<span class="quote-src">' . e(b($b, 'source')) . '</span>' : '') . '</div></section>';
}

function blk_cta(array $b): string
{
    if (b($b, 'image') !== '') {
        return sec_open($b) . '<div class="wrap"><div class="cta-split">' . img_tag(b($b, 'image'), b($b, 'title'), 'cta-img') . '<div class="cta-split-text"><h2>' . e(b($b, 'title')) . '</h2><p>' . nl2br(e(b($b, 'text'))) . '</p><div class="btn-row">'
            . btn(b($b, 'btn1_label'), b($b, 'btn1_url'), 'btn-gold') . btn(b($b, 'btn2_label'), b($b, 'btn2_url'), 'btn-ghost-light') . '</div></div></div></div></section>';
    }
    return sec_open($b) . '<div class="wrap"><div class="cta"><svg class="cta-geo" aria-hidden="true" viewBox="0 0 320 320"><g fill="none" stroke="#0A3528" stroke-width="1.5"><rect x="80" y="80" width="160" height="160"/><rect x="80" y="80" width="160" height="160" transform="rotate(45 160 160)"/><circle cx="160" cy="160" r="50"/></g></svg><div class="cta-text"><h2>' . e(b($b, 'title')) . '</h2><p>' . nl2br(e(b($b, 'text'))) . '</p></div><div class="btn-row">'
        . btn(b($b, 'btn1_label'), b($b, 'btn1_url'), 'btn-dark') . btn(b($b, 'btn2_label'), b($b, 'btn2_url'), 'btn-outline-dark', 'wa') . '</div></div></div></section>';
}

function blk_richtext(array $b): string
{
    return sec_open($b) . '<div class="wrap stack' . (!empty($b['narrow']) ? ' narrow' : '') . '">' . sec_head($b) . '<div class="prose">' . b($b, 'body') . '</div></div></section>';
}

function blk_gallery(array $b): string
{
    $items = [];
    if (b($b, 'album') !== '') {
        $limit = max(1, (int)(b($b, 'limit') ?: 8));
        foreach (rows('SELECT file, caption FROM photos WHERE album_id = ? ORDER BY sort, id LIMIT ' . $limit, [(int)b($b, 'album')]) as $i => $p) {
            $items[] = ['image' => $p['file'], 'caption' => (string)$p['caption'], 'tall' => $i === 0 || $i === 3, 'video' => ''];
        }
    } else {
        $items = $b['items'] ?? [];
    }
    $h = '';
    foreach ($items as $x) {
        $vid = youtube_id((string)($x['video'] ?? ''));
        $cls = 'g-item' . (!empty($x['tall']) ? ' tall' : '');
        if ($vid) {
            $h .= '<a class="' . $cls . ' is-video" href="https://www.youtube.com/watch?v=' . e($vid) . '" target="_blank" rel="noopener">' . img_tag($x['image'], $x['caption']) . '<span class="play">' . icon('play', 'ic') . '</span>' . ($x['caption'] ? '<span class="g-cap">' . e($x['caption']) . '</span>' : '') . '</a>';
        } else {
            $h .= '<a class="' . $cls . '" href="' . e(media($x['image'])) . '" data-lightbox="g" data-caption="' . e($x['caption']) . '">' . img_tag($x['image'], $x['caption']) . '</a>';
        }
    }
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<div class="masonry">' . $h . '</div></div></section>';
}

function blk_albums(array $b): string
{
    $limit = (int)b($b, 'limit');
    $list = rows('SELECT a.*, (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS n, (SELECT file FROM photos p WHERE p.album_id = a.id ORDER BY sort, id LIMIT 1) AS first FROM albums a ORDER BY event_date DESC, id DESC' . ($limit > 0 ? ' LIMIT ' . $limit : ''));
    $h = '';
    foreach ($list as $a) {
        $h .= '<a class="album" href="' . e(url('galeri/' . $a['slug'])) . '"><span class="album-cover">' . img_tag($a['cover'] ?: (string)$a['first'], $a['title']) . '<span class="pill">' . (int)$a['n'] . ' foto</span></span><strong>' . e($a['title']) . '</strong><small>' . e(tgl($a['event_date'])) . '</small></a>';
    }
    if ($h === '') $h = '<p class="muted">Belum ada album.</p>';
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<div class="grid cols-3">' . $h . '</div></div></section>';
}

function blk_videos(array $b): string
{
    $limit = (int)b($b, 'limit');
    $h = '';
    foreach (rows('SELECT * FROM videos ORDER BY sort, id DESC' . ($limit > 0 ? ' LIMIT ' . $limit : '')) as $v) {
        $id = youtube_id($v['youtube']);
        if (!$id) continue;
        $h .= '<a class="video" href="https://www.youtube.com/watch?v=' . e($id) . '" target="_blank" rel="noopener" data-youtube="' . e($id) . '"><span class="video-thumb"><img src="https://i.ytimg.com/vi/' . e($id) . '/hqdefault.jpg" alt="" loading="lazy"><span class="play">' . icon('play') . '</span></span><strong>' . e($v['title']) . '</strong></a>';
    }
    if ($h === '') $h = '<p class="muted">Belum ada video.</p>';
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<div class="grid cols-3">' . $h . '</div></div></section>';
}

function post_card(array $p): string
{
    return '<article class="post-card"><a href="' . e(url('berita/' . $p['slug'])) . '" class="post-img">' . img_tag((string)$p['cover'], $p['title']) . '</a><div class="post-body"><span class="post-meta"><span class="cat">' . e((string)($p['cat_name'] ?? 'Berita')) . '</span><span>' . e(tgl($p['published_at'])) . '</span></span>'
        . '<h3><a href="' . e(url('berita/' . $p['slug'])) . '">' . e($p['title']) . '</a></h3><a class="more" href="' . e(url('berita/' . $p['slug'])) . '">Baca selengkapnya</a></div></article>';
}

function agenda_list(int $limit): string
{
    $h = '';
    $list = rows('SELECT * FROM agenda WHERE event_date >= ? ORDER BY event_date LIMIT ' . $limit, [date('Y-m-d')]);
    if (!$list) $list = rows('SELECT * FROM agenda ORDER BY event_date DESC LIMIT ' . $limit);
    foreach ($list as $i => $a) {
        $h .= '<div class="agenda-item"><span class="date-box' . ($i === 0 ? ' active' : '') . '"><strong>' . e(date('d', strtotime($a['event_date']))) . '</strong><small>' . e(bulan_singkat($a['event_date'])) . '</small></span><span><strong>' . e($a['title']) . '</strong><small>' . e(trim($a['time_text'] . ' · ' . $a['place'], ' ·')) . '</small></span></div>';
    }
    return $h ?: '<p class="muted">Belum ada agenda.</p>';
}

function blk_posts(array $b): string
{
    $mode = b($b, 'mode') ?: 'grid';
    $limit = max(1, (int)(b($b, 'limit') ?: 3));
    if ($mode === 'full') return posts_full($b, $limit);
    $params = [now()];
    $where = "p.status = 'published' AND p.published_at <= ?";
    if (b($b, 'category') !== '') {
        $where .= ' AND p.category_id = ?';
        $params[] = (int)b($b, 'category');
    }
    $list = rows("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE $where ORDER BY p.published_at DESC LIMIT $limit", $params);
    $cards = implode('', array_map('post_card', $list)) ?: '<p class="muted">Belum ada berita.</p>';
    if ($mode === 'cards') {
        return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . '<div class="grid cols-3">' . $cards . '</div></div></section>';
    }
    return sec_open($b, '', 'soft') . '<div class="wrap news-layout"><div class="stack">' . sec_head($b) . '<div class="grid cols-3">' . $cards . '</div></div>'
        . '<aside class="side-card"><h2>Agenda madrasah</h2>' . agenda_list(3) . '<a class="btn btn-outline" href="' . e(url('agenda')) . '">Lihat semua agenda</a></aside></div></section>';
}

function posts_full(array $b, int $perPage): string
{
    $perPage = max(6, $perPage);
    $cat = (string)($_GET['kategori'] ?? '');
    $search = trim((string)($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['hal'] ?? 1));
    $params = [now()];
    $where = "p.status = 'published' AND p.published_at <= ?";
    if ($cat !== '') {
        $where .= ' AND c.slug = ?';
        $params[] = $cat;
    }
    if ($search !== '') {
        $where .= ' AND (p.title LIKE ? OR p.body LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $total = (int)val("SELECT COUNT(*) FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE $where", $params);
    $pages = max(1, (int)ceil($total / $perPage));
    $off = ($page - 1) * $perPage;

    $featured = null;
    if ($page === 1 && $cat === '' && $search === '') {
        $featured = row("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status = 'published' AND p.published_at <= ? AND p.featured = 1 ORDER BY p.published_at DESC LIMIT 1", [now()]);
    }
    $list = rows("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE $where" . ($featured ? ' AND p.id <> ' . (int)$featured['id'] : '') . " ORDER BY p.published_at DESC LIMIT $perPage OFFSET $off", $params);

    $self = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $chips = '<a class="chip' . ($cat === '' ? ' on' : '') . '" href="' . e($self) . '">Semua</a>';
    foreach (rows('SELECT name, slug FROM categories ORDER BY name') as $c) {
        $chips .= '<a class="chip' . ($cat === $c['slug'] ? ' on' : '') . '" href="' . e($self . '?kategori=' . urlencode($c['slug'])) . '">' . e($c['name']) . '</a>';
    }
    $pager = '';
    if ($pages > 1) {
        for ($i = 1; $i <= $pages; $i++) {
            $qs = http_build_query(array_filter(['kategori' => $cat, 'q' => $search, 'hal' => $i > 1 ? $i : null]));
            $pager .= '<a class="pg' . ($i === $page ? ' on' : '') . '"' . ($i === $page ? ' aria-current="page"' : '') . ' href="' . e($self . ($qs ? '?' . $qs : '')) . '">' . $i . '</a>';
        }
        $pager = '<nav class="pager" aria-label="Halaman berita">' . $pager . '</nav>';
    }
    $feat = '';
    if ($featured) {
        $u = e(url('berita/' . $featured['slug']));
        $feat = '<article class="featured"><a href="' . $u . '" class="featured-img">' . img_tag((string)$featured['cover'], $featured['title']) . '</a><div class="featured-body"><span class="post-meta"><span class="pill-dark">Utama</span><span>' . e((string)$featured['cat_name']) . ' · ' . e(tgl($featured['published_at'])) . '</span></span><h2><a href="' . $u . '">' . e($featured['title']) . '</a></h2><p>' . e($featured['excerpt'] ?: excerpt((string)$featured['body'])) . '</p><a class="btn btn-primary" href="' . $u . '">Baca berita' . icon('arrow') . '</a></div></article>';
    }
    $popular = '';
    foreach (rows("SELECT title, slug, published_at FROM posts WHERE status = 'published' AND published_at <= ? ORDER BY views DESC, published_at DESC LIMIT 4", [now()]) as $i => $p) {
        $popular .= '<li><span class="rank">0' . ($i + 1) . '</span><span><a href="' . e(url('berita/' . $p['slug'])) . '">' . e($p['title']) . '</a><small>' . e(tgl($p['published_at'])) . '</small></span></li>';
    }
    $wa = setting('whatsapp_channel');
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b)
        . '<form class="search-inline" role="search" method="get" action="' . e($self) . '"><label class="sr-only" for="q-news">Cari berita</label><input id="q-news" type="search" name="q" value="' . e($search) . '" placeholder="Cari berita..."><button class="btn btn-primary" type="submit">' . icon('search') . 'Cari</button></form>'
        . $feat . '<div class="news-layout"><div class="stack"><div class="chips">' . $chips . '</div>'
        . ($list ? '<div class="grid cols-3">' . implode('', array_map('post_card', $list)) . '</div>' : '<p class="muted">Tidak ada berita yang cocok.</p>') . $pager . '</div>'
        . '<aside class="stack-sm"><div class="side-card"><h2>Terpopuler</h2><ol class="popular">' . $popular . '</ol></div><div class="side-card"><h2>Agenda terdekat</h2>' . agenda_list(3) . '</div>'
        . ($wa ? '<div class="side-card gold"><h2>Dapatkan pengumuman</h2><p>Info penting madrasah langsung ke WhatsApp orang tua.</p><a class="btn btn-dark" href="' . e($wa) . '" target="_blank" rel="noopener">Gabung saluran WA</a></div>' : '')
        . '</aside></div></div></section>';
}

function blk_agenda(array $b): string
{
    $limit = max(1, (int)(b($b, 'limit') ?: 6));
    $h = '';
    foreach (rows('SELECT * FROM agenda WHERE event_date >= ? ORDER BY event_date LIMIT ' . $limit, [date('Y-m-d')]) as $a) {
        $h .= '<div class="agenda-card"><span class="date-box active"><strong>' . e(date('d', strtotime($a['event_date']))) . '</strong><small>' . e(bulan_singkat($a['event_date'])) . '</small></span><div><h3>' . e($a['title']) . '</h3><small>' . e(tgl($a['event_date'], true)) . ($a['time_text'] ? ' · ' . e($a['time_text']) : '') . ($a['place'] ? ' · ' . e($a['place']) : '') . '</small>' . ($a['description'] ? '<p>' . e($a['description']) . '</p>' : '') . '</div></div>';
    }
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b) . ($h ? '<div class="grid cols-2">' . $h . '</div>' : '<p class="muted">Belum ada agenda mendatang.</p>') . '</div></section>';
}

function blk_staff(array $b): string
{
    $g = b($b, 'group');
    $list = $g !== '' ? rows('SELECT * FROM staff WHERE group_name = ? ORDER BY sort, name', [$g]) : rows('SELECT * FROM staff ORDER BY sort, name');
    $groups = array_values(array_unique(array_filter(array_column($list, 'group_name'))));
    $chips = '';
    if (!empty($b['filter']) && count($groups) > 1) {
        $chips = '<div class="chips" role="group" aria-label="Filter kelompok" data-filter-group><button type="button" class="chip on" data-filter="">Semua</button>';
        foreach ($groups as $x) $chips .= '<button type="button" class="chip" data-filter="' . e($x) . '">' . e($x) . '</button>';
        $chips .= '</div>';
    }
    $h = '';
    foreach ($list as $s) {
        $h .= '<div class="person" data-group="' . e((string)$s['group_name']) . '">' . ($s['photo'] ? img_tag($s['photo'], $s['name'], 'avatar') : '<span class="avatar ph">' . icon('user') . '</span>')
            . '<strong>' . e($s['name']) . '</strong><small>' . e(trim((string)$s['position'] . ($s['subject'] ? ' · ' . $s['subject'] : ''), ' ·')) . '</small></div>';
    }
    return sec_open($b) . '<div class="wrap stack">' . sec_head($b, $chips) . '<div class="grid cols-4" data-filter-target>' . ($h ?: '<p class="muted">Belum ada data guru.</p>') . '</div></div></section>';
}

function blk_downloads(array $b): string
{
    $c = b($b, 'category');
    $list = $c !== '' ? rows('SELECT * FROM downloads WHERE category = ? ORDER BY created_at DESC', [$c]) : rows('SELECT * FROM downloads ORDER BY created_at DESC');
    $h = '';
    foreach ($list as $d) {
        $ext = strtoupper(pathinfo($d['file'], PATHINFO_EXTENSION));
        $h .= '<div class="dl-row"><span class="dl-name"><span class="ic-box">' . icon('file') . '</span><span><strong>' . e($d['title']) . '</strong><small>' . e($ext . ' · ' . human_size((int)$d['size'])) . '</small></span></span><a class="btn btn-outline" href="' . e(media($d['file'])) . '" download>' . icon('download') . 'Unduh</a></div>';
    }
    return sec_open($b, '', 'soft') . '<div class="wrap stack">' . sec_head($b) . '<div class="dl-box">' . ($h ?: '<p class="muted">Belum ada dokumen.</p>') . '</div></div></section>';
}

function contact_items(): string
{
    $wa = setting('whatsapp');
    $waLink = $wa ? 'https://wa.me/' . preg_replace('/\D/', '', preg_replace('/^0/', '62', $wa)) : '#';
    return '<div class="contact-card"><span class="ic-box">' . icon('pin') . '</span><strong>Alamat</strong><span>' . nl2br(e(setting('address', '[Alamat madrasah]'))) . '</span></div>'
        . '<div class="contact-card"><span class="ic-box">' . icon('wa') . '</span><strong>WhatsApp & telepon</strong><a href="' . e($waLink) . '" target="_blank" rel="noopener">WA ' . e($wa ?: '-') . '</a><span>Telp. ' . e(setting('phone', '-')) . '</span></div>'
        . '<div class="contact-card"><span class="ic-box">' . icon('mail') . '</span><strong>Email</strong><a href="mailto:' . e(setting('email')) . '">' . e(setting('email', '-')) . '</a></div>'
        . '<div class="contact-card"><span class="ic-box">' . icon('clock') . '</span><strong>Jam layanan</strong><span>' . nl2br(e(setting('office_hours', '-'))) . '</span></div>';
}

function blk_contact_info(array $b): string
{
    return '<section class="blk contact-info"' . (b($b, '_anchor') ? ' id="' . e(b($b, '_anchor')) . '"' : '') . '><div class="wrap grid cols-4 lift">' . contact_items() . '</div></section>';
}

function map_embed(): string
{
    $m = setting('map_embed');
    if ($m && preg_match('#^https://(www\.)?google\.com/maps/embed#', $m)) {
        return '<iframe class="map" src="' . e($m) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi madrasah" allowfullscreen></iframe>';
    }
    return '<div class="map map-ph">' . icon('map') . '<span>Peta lokasi (atur di Pengaturan)</span></div>';
}

function blk_contact_form(array $b): string
{
    $subjects = array_filter(array_map('trim', explode('|', b($b, 'subjects') ?: 'Informasi PPDB|Administrasi & keuangan|Kerja sama|Kritik & saran')));
    $opts = implode('', array_map(fn($s) => '<option>' . e($s) . '</option>', $subjects));
    $sent = isset($_GET['terkirim']) ? '<p class="alert ok" role="status">Terima kasih, pesan Anda sudah kami terima.</p>' : '';
    $form = '<form class="form-card" method="post" action="' . e(url('kirim-pesan')) . '">' . csrf_field() . '<input type="hidden" name="back" value="' . e(strtok($_SERVER['REQUEST_URI'] ?? '/', '?')) . '">'
        . '<div class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<h2>' . e(b($b, 'title') ?: 'Kirim pesan') . '</h2>' . $sent
        . '<div class="grid cols-2 tight"><label>Nama lengkap<input name="name" required maxlength="120"></label><label>Nomor WhatsApp<input name="phone" type="tel" maxlength="30"></label></div>'
        . '<label>Email<input name="email" type="email" maxlength="120"></label>'
        . '<label>Keperluan<select name="subject">' . $opts . '</select></label>'
        . '<label>Pesan<textarea name="message" rows="5" required maxlength="3000"></textarea></label>'
        . '<button class="btn btn-primary" type="submit">Kirim pesan</button></form>';
    $map = !empty($b['show_map']) ? '<div class="map-col">' . map_embed() . (setting('map_link') ? '<a class="btn btn-outline" href="' . e(setting('map_link')) . '" target="_blank" rel="noopener">Buka petunjuk arah' . icon('arrow') . '</a>' : '') . '</div>' : '';
    return sec_open($b) . '<div class="wrap form-layout">' . $form . $map . '</div></section>';
}

function blk_ppdb_form(array $b): string
{
    $title = e(b($b, 'title') ?: 'Formulir pendaftaran');
    if (!empty($b['closed'])) {
        return sec_open($b) . '<div class="wrap"><div class="form-card center"><h2>' . $title . '</h2><p>' . e(b($b, 'closed_text') ?: 'Pendaftaran sedang ditutup.') . '</p></div></div></section>';
    }
    $done = '';
    if (!empty($_SESSION['ppdb_done'])) {
        $done = '<div class="alert ok" role="status">Pendaftaran berhasil. Nomor pendaftaran Anda: <strong>' . e($_SESSION['ppdb_done']) . '</strong>. Simpan nomor ini untuk cek status.</div>';
        unset($_SESSION['ppdb_done']);
    }
    $err = '';
    if (!empty($_SESSION['ppdb_error'])) {
        $err = '<div class="alert err" role="alert">' . e($_SESSION['ppdb_error']) . '</div>';
        unset($_SESSION['ppdb_error']);
    }
    return sec_open($b) . '<div class="wrap"><form class="form-card" method="post" action="' . e(url('ppdb/daftar')) . '">' . csrf_field() . '<input type="hidden" name="back" value="' . e(strtok($_SERVER['REQUEST_URI'] ?? '/', '?')) . '">'
        . '<div class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<h2>' . $title . '</h2>' . (b($b, 'intro') ? '<p class="muted">' . e(b($b, 'intro')) . '</p>' : '') . $done . $err
        . '<div class="grid cols-2 tight">'
        . '<label>Nama lengkap calon siswa<input name="name" required maxlength="120" placeholder="Sesuai akta kelahiran"></label>'
        . '<label>NISN<input name="nisn" inputmode="numeric" maxlength="10" placeholder="10 digit"></label>'
        . '<label>Tempat lahir<input name="birth_place" maxlength="80"></label>'
        . '<label>Tanggal lahir<input name="birth_date" type="date" required></label>'
        . '<label>Jenis kelamin<select name="gender"><option>Laki-laki</option><option>Perempuan</option></select></label>'
        . '<label>Asal sekolah<input name="school_origin" maxlength="120" placeholder="SD/MI asal"></label>'
        . '<label>Nama orang tua/wali<input name="parent_name" required maxlength="120"></label>'
        . '<label>Nomor WhatsApp orang tua<input name="phone" type="tel" required maxlength="20" placeholder="08xx"></label>'
        . '<label>Email (opsional)<input name="email" type="email" maxlength="120"></label>'
        . '</div><label>Alamat lengkap<textarea name="address" rows="3" maxlength="500"></textarea></label>'
        . '<div class="form-foot"><span class="muted">Nomor pendaftaran tampil setelah formulir dikirim.</span><button class="btn btn-primary" type="submit">Kirim pendaftaran</button></div></form></div></section>';
}

function blk_ppdb_status(array $b): string
{
    $result = '';
    if (isset($_GET['no'])) {
        $r = row('SELECT reg_no, name, status, notes FROM ppdb WHERE reg_no = ? AND birth_date = ?', [trim((string)$_GET['no']), (string)($_GET['lahir'] ?? '')]);
        $labels = ['baru' => 'Menunggu verifikasi', 'verifikasi' => 'Berkas terverifikasi', 'diterima' => 'DITERIMA', 'ditolak' => 'Belum diterima', 'cadangan' => 'Cadangan'];
        $result = $r ? '<div class="alert ok" role="status"><strong>' . e($r['name']) . '</strong> (' . e($r['reg_no']) . '): ' . e($labels[$r['status']] ?? $r['status']) . ($r['notes'] ? '<br>' . e($r['notes']) : '') . '</div>'
            : '<div class="alert err" role="alert">Data tidak ditemukan. Periksa nomor pendaftaran dan tanggal lahir.</div>';
    }
    $self = e(strtok($_SERVER['REQUEST_URI'] ?? '', '?'));
    $form = '<h2>' . e(b($b, 'title') ?: 'Cek status pendaftaran') . '</h2>' . (b($b, 'intro') ? '<p>' . e(b($b, 'intro')) . '</p>' : '') . $result
        . '<form class="inline-form" method="get" action="' . $self . '#' . e(b($b, '_anchor')) . '"><label>Nomor pendaftaran<input name="no" required value="' . e((string)($_GET['no'] ?? '')) . '"></label><label>Tanggal lahir<input type="date" name="lahir" required value="' . e((string)($_GET['lahir'] ?? '')) . '"></label><button class="btn btn-gold" type="submit">Cek</button></form>';
    return sec_open($b) . '<div class="wrap"><div class="cta-split">' . (b($b, 'image') ? img_tag(b($b, 'image'), '', 'cta-img') : '') . '<div class="cta-split-text">' . $form . '</div></div></div></section>';
}

function blk_countdown(array $b): string
{
    $quota = (int)b($b, 'quota');
    $filled = (int)val('SELECT COUNT(*) FROM ppdb');
    $pct = $quota > 0 ? min(100, (int)round($filled * 100 / $quota)) : 0;
    $deadline = b($b, 'deadline');
    return sec_open($b, 'countdown-sec', 'soft') . '<div class="wrap cd-grid"><div class="stack-sm">' . sec_head($b)
        . '<div class="btn-row">' . btn(b($b, 'btn1_label'), b($b, 'btn1_url')) . btn(b($b, 'btn2_label'), b($b, 'btn2_url'), 'btn-outline', 'download') . '</div></div>'
        . '<div class="cd-card"><span class="eyebrow gold">' . e(b($b, 'label') ?: 'KUOTA') . '</span><div class="cd-quota"><strong>' . $filled . '</strong><span>dari ' . $quota . ' kursi terisi</span></div>'
        . '<div class="bar"><span style="width:' . $pct . '%"></span></div>'
        . ($deadline ? '<div class="cd-timer" data-deadline="' . e($deadline) . '"><div><strong data-d>00</strong><small>Hari</small></div><div><strong data-h>00</strong><small>Jam</small></div><div><strong data-m>00</strong><small>Menit</small></div><div><strong data-s>00</strong><small>Detik</small></div></div><small class="cd-note">Ditutup ' . e(tgl(substr($deadline, 0, 10), true)) . '</small>' : '')
        . '</div></div></section>';
}
