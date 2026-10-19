<?php
declare(strict_types=1);

require APP . '/templates.php';
require APP . '/crud.php';

function admin_route(string $path, string $method): void
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Robots-Tag: noindex');
    $parts = $path === '' ? [] : explode('/', $path);
    $section = $parts[0] ?? '';
    $action = $parts[1] ?? '';
    $id = isset($parts[2]) ? (int)$parts[2] : 0;

    if ($section === 'login') { admin_login($method); return; }
    if ($section === 'logout') { if ($method === 'POST') { csrf_check(); logout(); } redirect('admin/login'); }

    require_login();
    if ($method === 'POST') csrf_check();

    match ($section) {
        '' => admin_dashboard(),
        'pages' => admin_pages($action, $id, $method),
        'menus' => admin_menus($action, $id, $method),
        'posts' => admin_posts($action, $id, $method),
        'albums' => admin_albums($action, $id, $method),
        'messages' => admin_messages($action, $id, $method),
        'ppdb' => admin_ppdb($action, $id, $method),
        'media' => admin_media($action, $id, $method),
        'settings' => admin_settings($method),
        'users' => admin_users($action, $id, $method),
        'profile' => admin_profile($method),
        default => isset(crud_defs()[$section]) ? admin_crud($section, $action, $id, $method) : admin_404(),
    };
}

function admin_404(): void
{
    http_response_code(404);
    admin_layout('Tidak ditemukan', '<div class="panel"><p>Halaman admin ini tidak ada.</p></div>');
}

function admin_layout(string $title, string $content, string $active = '', array $opts = []): void
{
    view('admin/layout', ['title' => $title, 'content' => $content, 'active' => $active, 'actions' => $opts['actions'] ?? '', 'wide' => $opts['wide'] ?? false]);
}

/* ---------- Login ---------- */
function admin_login(string $method): void
{
    if (current_user()) redirect('admin');
    $error = '';
    if ($method === 'POST') {
        csrf_check();
        if (too_many_attempts()) {
            $error = 'Terlalu banyak percobaan. Tunggu 15 menit lalu coba lagi.';
        } elseif (attempt_login(post('username'), (string)($_POST['password'] ?? ''))) {
            redirect('admin');
        } else {
            $error = 'Nama pengguna atau kata sandi salah.';
        }
    }
    view('admin/login', ['error' => $error]);
}

/* ---------- Dashboard ---------- */
function admin_dashboard(): void
{
    $stats = [
        ['Halaman', (int)val('SELECT COUNT(*) FROM pages'), 'admin/pages'],
        ['Berita', (int)val('SELECT COUNT(*) FROM posts'), 'admin/posts'],
        ['Album foto', (int)val('SELECT COUNT(*) FROM albums'), 'admin/albums'],
        ['Pesan belum dibaca', (int)val('SELECT COUNT(*) FROM messages WHERE is_read = 0'), 'admin/messages'],
        ['Pendaftar PPDB', (int)val('SELECT COUNT(*) FROM ppdb'), 'admin/ppdb'],
        ['Pendaftar baru', (int)val("SELECT COUNT(*) FROM ppdb WHERE status = 'baru'"), 'admin/ppdb?status=baru'],
    ];
    $h = '<div class="stat-grid">';
    foreach ($stats as [$l, $n, $u]) $h .= '<a class="stat-card" href="' . e(url($u)) . '"><strong>' . $n . '</strong><span>' . e($l) . '</span></a>';
    $h .= '</div><div class="two-col"><div class="panel"><div class="panel-head"><h2>Berita terbaru</h2><a class="btn sm" href="' . e(url('admin/posts/edit/0')) . '">+ Tulis berita</a></div><ul class="simple-list">';
    foreach (rows('SELECT id, title, status, published_at FROM posts ORDER BY created_at DESC LIMIT 6') as $p) {
        $h .= '<li><a href="' . e(url('admin/posts/edit/' . $p['id'])) . '">' . e($p['title']) . '</a><small>' . e(tgl($p['published_at'])) . ($p['status'] !== 'published' ? ' · draf' : '') . '</small></li>';
    }
    $h .= '</ul></div><div class="panel"><div class="panel-head"><h2>Pesan masuk</h2><a href="' . e(url('admin/messages')) . '">Lihat semua</a></div><ul class="simple-list">';
    foreach (rows('SELECT id, name, subject, is_read, created_at FROM messages ORDER BY created_at DESC LIMIT 6') as $m) {
        $h .= '<li><a href="' . e(url('admin/messages/view/' . $m['id'])) . '">' . ($m['is_read'] ? '' : '<span class="dot"></span>') . e($m['name']) . ' · ' . e((string)$m['subject']) . '</a><small>' . e(tgl($m['created_at'])) . '</small></li>';
    }
    $h .= '</ul></div></div><div class="panel"><h2>Pintasan</h2><div class="shortcut-row">'
        . '<a class="btn" href="' . e(url('admin/pages/new')) . '">+ Halaman baru</a>'
        . '<a class="btn ghost" href="' . e(url('admin/menus')) . '">Atur menu</a>'
        . '<a class="btn ghost" href="' . e(url('admin/albums/edit/0')) . '">+ Album foto</a>'
        . '<a class="btn ghost" href="' . e(url('admin/agenda/edit/0')) . '">+ Agenda</a>'
        . '<a class="btn ghost" href="' . e(url('admin/settings')) . '">Pengaturan situs</a>'
        . '<a class="btn ghost" href="' . e(url('')) . '" target="_blank">Lihat website ↗</a></div></div>';
    admin_layout('Dasbor', $h, 'dashboard');
}

/* ---------- Pages ---------- */
function admin_pages(string $action, int $id, string $method): void
{
    if ($action === 'new') {
        if ($method === 'POST') {
            $title = post('title') ?: 'Halaman baru';
            $src = post('source') === 'dup' ? post('dup_page') : post('source');
            $blocks = [];
            if (str_starts_with($src, 'page:')) {
                $orig = row('SELECT blocks FROM pages WHERE id = ?', [(int)substr($src, 5)]);
                $blocks = $orig ? (json_decode((string)$orig['blocks'], true) ?: []) : [];
            } else {
                $blocks = page_templates()[$src]['blocks'] ?? page_templates()['kosong']['blocks'];
                foreach ($blocks as &$tb) if (($tb['type'] ?? '') === 'page_header' && ($tb['title'] ?? '') === '') $tb['title'] = $title;
                unset($tb);
            }
            $blocks = sanitize_blocks(json_encode($blocks));
            $slug = unique_slug('pages', slugify(post('slug') ?: $title));
            $pid = insert('pages', ['title' => $title, 'slug' => $slug, 'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                'status' => 'draft', 'is_home' => 0, 'meta_description' => '', 'created_at' => now(), 'updated_at' => now()]);
            if (post('add_menu') === '1') {
                $parent = (int)post('menu_parent');
                insert('menus', ['parent_id' => $parent ?: null, 'label' => $title, 'type' => 'page', 'page_id' => $pid, 'url' => '',
                    'sort' => (int)val('SELECT COALESCE(MAX(sort), 0) + 1 FROM menus WHERE location = ?', ['main']), 'visible' => 1, 'new_tab' => 0, 'location' => 'main']);
            }
            flash('Halaman dibuat sebagai draf. Ubah isinya, lalu terbitkan.');
            redirect('admin/pages/edit/' . $pid);
        }
        admin_layout('Halaman baru', render_to_string('admin/page_new', [
            'templates' => page_templates(),
            'pages' => rows('SELECT id, title FROM pages ORDER BY title'),
            'parents' => rows("SELECT id, label FROM menus WHERE (parent_id IS NULL OR parent_id = 0) AND location = 'main' ORDER BY sort"),
            'dup' => (int)($_GET['dari'] ?? 0),
        ]), 'pages');
        return;
    }
    if ($action === 'edit') {
        $page = row('SELECT * FROM pages WHERE id = ?', [$id]);
        if (!$page) admin_404();
        if ($method === 'POST') {
            $blocks = sanitize_blocks((string)($_POST['blocks'] ?? '[]'));
            $slug = $page['is_home'] ? $page['slug'] : unique_slug('pages', slugify(post('slug') ?: post('title')), $id);
            if (in_array($slug, ['admin', 'setup', 'berita', 'galeri', 'cari', 'assets', 'uploads', 'agenda'], true) && $slug !== $page['slug']) {
                // reserved paths (berita/galeri are allowed only for the list pages created at install)
                $slug = unique_slug('pages', $slug . '-1', $id);
            }
            update('pages', $id, ['title' => post('title') ?: 'Tanpa judul', 'slug' => $slug, 'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                'status' => post('status') === 'published' ? 'published' : 'draft', 'meta_description' => mb_substr(post('meta_description'), 0, 300), 'updated_at' => now()]);
            flash('Halaman disimpan.');
            redirect('admin/pages/edit/' . $id);
        }
        admin_layout('Ubah halaman', render_to_string('admin/page_edit', ['page' => $page, 'schema' => block_schema()]), 'pages', ['wide' => true]);
        return;
    }
    if ($method === 'POST' && $action === 'delete') {
        $p = row('SELECT * FROM pages WHERE id = ?', [$id]);
        if ($p && !$p['is_home']) {
            q('DELETE FROM pages WHERE id = ?', [$id]);
            q('DELETE FROM menus WHERE page_id = ?', [$id]);
            flash('Halaman dihapus beserta tautan menunya.');
        } else {
            flash('Halaman beranda tidak bisa dihapus.', 'error');
        }
        redirect('admin/pages');
    }
    if ($method === 'POST' && $action === 'home') {
        q('UPDATE pages SET is_home = 0');
        q("UPDATE pages SET is_home = 1, status = 'published' WHERE id = ?", [$id]);
        flash('Beranda diganti.');
        redirect('admin/pages');
    }
    $list = rows('SELECT p.*, (SELECT COUNT(*) FROM menus m WHERE m.page_id = p.id) AS in_menu FROM pages p ORDER BY p.is_home DESC, p.title');
    admin_layout('Halaman', render_to_string('admin/pages', ['list' => $list]), 'pages', ['actions' => '<a class="btn" href="' . e(url('admin/pages/new')) . '">+ Halaman baru</a>']);
}

/* ---------- Menus ---------- */
function admin_menus(string $action, int $id, string $method): void
{
    if ($method === 'POST' && $action === 'save') {
        $type = in_array(post('type'), ['page', 'url', 'category', 'album'], true) ? post('type') : 'url';
        $loc = in_array(post('location'), ['main', 'top', 'footer'], true) ? post('location') : 'main';
        $parent = (int)post('parent_id');
        if ($parent === $id) $parent = 0;
        $data = ['label' => post('label') ?: 'Menu', 'type' => $type, 'page_id' => $type === 'page' ? ((int)post('page_id') ?: null) : null,
            'url' => match ($type) { 'url' => post('url'), 'category' => post('category'), 'album' => post('album'), default => '' },
            'parent_id' => $parent ?: null, 'location' => $loc, 'visible' => post('visible') === '1' ? 1 : 0, 'new_tab' => post('new_tab') === '1' ? 1 : 0];
        if ($id) {
            update('menus', $id, $data);
            if ($parent) q('UPDATE menus SET parent_id = NULL WHERE parent_id = ?', [$id]); // only two levels
        } else {
            $data['sort'] = (int)val('SELECT COALESCE(MAX(sort), 0) + 1 FROM menus WHERE location = ?', [$loc]);
            insert('menus', $data);
        }
        flash('Menu disimpan.');
        redirect('admin/menus?lokasi=' . $loc);
    }
    if ($method === 'POST' && $action === 'delete') {
        q('UPDATE menus SET parent_id = NULL WHERE parent_id = ?', [$id]);
        q('DELETE FROM menus WHERE id = ?', [$id]);
        flash('Menu dihapus.');
        redirect('admin/menus?lokasi=' . post('location', 'main'));
    }
    if ($method === 'POST' && $action === 'move') {
        $m = row('SELECT * FROM menus WHERE id = ?', [$id]);
        if ($m) {
            $sib = rows('SELECT id FROM menus WHERE location = ? AND ' . ($m['parent_id'] ? 'parent_id = ' . (int)$m['parent_id'] : '(parent_id IS NULL OR parent_id = 0)') . ' ORDER BY sort, id', [$m['location']]);
            $ids = array_column($sib, 'id');
            $i = array_search($m['id'], $ids);
            $j = post('dir') === 'up' ? $i - 1 : $i + 1;
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            }
            foreach ($ids as $k => $mid) q('UPDATE menus SET sort = ? WHERE id = ?', [$k + 1, $mid]);
        }
        redirect('admin/menus?lokasi=' . ($m['location'] ?? 'main'));
    }
    $loc = in_array($_GET['lokasi'] ?? 'main', ['main', 'top', 'footer'], true) ? ($_GET['lokasi'] ?? 'main') : 'main';
    $all = rows('SELECT m.*, p.title AS page_title FROM menus m LEFT JOIN pages p ON p.id = m.page_id WHERE m.location = ? ORDER BY m.sort, m.id', [$loc]);
    $edit = $action === 'edit' ? row('SELECT * FROM menus WHERE id = ?', [$id]) : null;
    admin_layout('Menu', render_to_string('admin/menus', [
        'all' => $all, 'loc' => $loc, 'edit' => $edit,
        'pages' => rows('SELECT id, title, status FROM pages ORDER BY title'),
        'categories' => rows('SELECT name, slug FROM categories ORDER BY name'),
        'albums' => rows('SELECT title, slug FROM albums ORDER BY title'),
    ]), 'menus');
}

/* ---------- Posts ---------- */
function admin_posts(string $action, int $id, string $method): void
{
    if ($action === 'edit') {
        $post = $id ? row('SELECT * FROM posts WHERE id = ?', [$id]) : null;
        if ($id && !$post) admin_404();
        if ($method === 'POST') {
            $title = post('title') ?: 'Tanpa judul';
            $pub = post('published_at') ? date('Y-m-d H:i:s', strtotime(post('published_at')) ?: time()) : now();
            $data = ['title' => $title, 'slug' => unique_slug('posts', slugify(post('slug') ?: $title), $id), 'category_id' => (int)post('category_id') ?: null,
                'cover' => post('cover'), 'excerpt' => mb_substr(post('excerpt'), 0, 400), 'body' => clean_html((string)($_POST['body'] ?? '')),
                'status' => post('status') === 'published' ? 'published' : 'draft', 'featured' => post('featured') === '1' ? 1 : 0,
                'published_at' => $pub, 'updated_at' => now()];
            if ($id) {
                update('posts', $id, $data);
            } else {
                $data += ['created_at' => now(), 'author_id' => current_user()['id'], 'views' => 0];
                $id = insert('posts', $data);
            }
            flash('Berita disimpan.');
            redirect('admin/posts/edit/' . $id);
        }
        admin_layout($id ? 'Ubah berita' : 'Tulis berita', render_to_string('admin/post_edit', ['post' => $post, 'categories' => rows('SELECT * FROM categories ORDER BY name')]), 'posts');
        return;
    }
    if ($method === 'POST' && $action === 'delete') {
        q('DELETE FROM posts WHERE id = ?', [$id]);
        flash('Berita dihapus.');
        redirect('admin/posts');
    }
    $where = '1=1';
    $params = [];
    if (!empty($_GET['q'])) { $where .= ' AND p.title LIKE ?'; $params[] = '%' . $_GET['q'] . '%'; }
    if (!empty($_GET['kategori'])) { $where .= ' AND p.category_id = ?'; $params[] = (int)$_GET['kategori']; }
    $list = rows("SELECT p.*, c.name AS cat FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE $where ORDER BY p.published_at DESC LIMIT 300", $params);
    admin_layout('Berita', render_to_string('admin/posts', ['list' => $list, 'categories' => rows('SELECT * FROM categories ORDER BY name')]), 'posts',
        ['actions' => '<a class="btn ghost" href="' . e(url('admin/categories')) . '">Kategori</a><a class="btn" href="' . e(url('admin/posts/edit/0')) . '">+ Tulis berita</a>']);
}

/* ---------- Albums ---------- */
function admin_albums(string $action, int $id, string $method): void
{
    if ($action === 'edit') {
        $album = $id ? row('SELECT * FROM albums WHERE id = ?', [$id]) : null;
        if ($id && !$album) admin_404();
        if ($method === 'POST') {
            $title = post('title') ?: 'Album';
            $data = ['title' => $title, 'slug' => unique_slug('albums', slugify(post('slug') ?: $title), $id), 'description' => post('description'),
                'cover' => post('cover'), 'event_date' => post('event_date') ?: date('Y-m-d')];
            if ($id) update('albums', $id, $data);
            else $id = insert('albums', $data + ['created_at' => now()]);
            $errors = [];
            $sort = (int)val('SELECT COALESCE(MAX(sort), 0) FROM photos WHERE album_id = ?', [$id]);
            foreach (files_list('photos') as $f) {
                if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
                try {
                    $r = store_upload($f, true);
                    insert('photos', ['album_id' => $id, 'file' => $r['path'], 'caption' => '', 'sort' => ++$sort]);
                } catch (RuntimeException $ex) {
                    $errors[] = $f['name'] . ': ' . $ex->getMessage();
                }
            }
            foreach ((array)($_POST['caption'] ?? []) as $pid => $cap) {
                q('UPDATE photos SET caption = ?, sort = ? WHERE id = ? AND album_id = ?', [mb_substr((string)$cap, 0, 250), (int)($_POST['psort'][$pid] ?? 0), (int)$pid, $id]);
            }
            foreach ((array)($_POST['remove'] ?? []) as $pid) {
                $ph = row('SELECT file FROM photos WHERE id = ? AND album_id = ?', [(int)$pid, $id]);
                if ($ph) { q('DELETE FROM photos WHERE id = ?', [(int)$pid]); delete_upload($ph['file']); }
            }
            flash($errors ? 'Album disimpan, tapi beberapa foto gagal: ' . implode('; ', $errors) : 'Album disimpan.', $errors ? 'error' : 'success');
            redirect('admin/albums/edit/' . $id);
        }
        $photos = $id ? rows('SELECT * FROM photos WHERE album_id = ? ORDER BY sort, id', [$id]) : [];
        admin_layout($id ? 'Ubah album' : 'Album baru', render_to_string('admin/album_edit', ['album' => $album, 'photos' => $photos]), 'albums');
        return;
    }
    if ($method === 'POST' && $action === 'delete') {
        foreach (rows('SELECT file FROM photos WHERE album_id = ?', [$id]) as $ph) delete_upload($ph['file']);
        q('DELETE FROM photos WHERE album_id = ?', [$id]);
        q('DELETE FROM albums WHERE id = ?', [$id]);
        flash('Album dihapus.');
        redirect('admin/albums');
    }
    $list = rows('SELECT a.*, (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS n, (SELECT file FROM photos p WHERE p.album_id = a.id ORDER BY sort, id LIMIT 1) AS first FROM albums a ORDER BY event_date DESC, id DESC');
    admin_layout('Galeri foto', render_to_string('admin/albums', ['list' => $list]), 'albums',
        ['actions' => '<a class="btn ghost" href="' . e(url('admin/videos')) . '">Video</a><a class="btn" href="' . e(url('admin/albums/edit/0')) . '">+ Album baru</a>']);
}

/* ---------- Messages ---------- */
function admin_messages(string $action, int $id, string $method): void
{
    if ($method === 'POST' && $action === 'delete') {
        q('DELETE FROM messages WHERE id = ?', [$id]);
        flash('Pesan dihapus.');
        redirect('admin/messages');
    }
    if ($action === 'view') {
        $m = row('SELECT * FROM messages WHERE id = ?', [$id]);
        if (!$m) admin_404();
        q('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
        $wa = $m['phone'] ? 'https://wa.me/' . preg_replace('/\D/', '', preg_replace('/^0/', '62', (string)$m['phone'])) : '';
        $h = '<div class="panel"><dl class="dl">'
            . '<dt>Nama</dt><dd>' . e($m['name']) . '</dd><dt>WhatsApp</dt><dd>' . e((string)$m['phone']) . ($wa ? ' · <a href="' . e($wa) . '" target="_blank" rel="noopener">Balas via WA</a>' : '') . '</dd>'
            . '<dt>Email</dt><dd>' . ($m['email'] ? '<a href="mailto:' . e($m['email']) . '">' . e($m['email']) . '</a>' : '-') . '</dd>'
            . '<dt>Keperluan</dt><dd>' . e((string)$m['subject']) . '</dd><dt>Waktu</dt><dd>' . e(tgl($m['created_at'], true)) . ' ' . e(substr($m['created_at'], 11, 5)) . '</dd></dl>'
            . '<div class="msg-body">' . nl2br(e((string)$m['message'])) . '</div>'
            . '<form method="post" action="' . e(url('admin/messages/delete/' . $id)) . '" data-confirm="Hapus pesan ini?">' . csrf_field() . '<button class="btn danger">Hapus pesan</button></form></div>';
        admin_layout('Pesan dari ' . $m['name'], $h, 'messages', ['actions' => '<a class="btn ghost" href="' . e(url('admin/messages')) . '">← Kembali</a>']);
        return;
    }
    $list = rows('SELECT * FROM messages ORDER BY created_at DESC LIMIT 500');
    $h = '<div class="panel"><table class="tbl"><thead><tr><th>Nama</th><th>Keperluan</th><th>Pesan</th><th>Waktu</th></tr></thead><tbody>';
    foreach ($list as $m) {
        $h .= '<tr class="' . ($m['is_read'] ? '' : 'unread') . '"><td><a href="' . e(url('admin/messages/view/' . $m['id'])) . '">' . e($m['name']) . '</a></td><td>' . e((string)$m['subject']) . '</td><td>' . e(excerpt((string)$m['message'], 80)) . '</td><td>' . e(tgl($m['created_at'])) . '</td></tr>';
    }
    if (!$list) $h .= '<tr><td colspan="4" class="muted">Belum ada pesan.</td></tr>';
    admin_layout('Pesan masuk', $h . '</tbody></table></div>', 'messages');
}

/* ---------- PPDB ---------- */
const PPDB_STATUS = ['baru' => 'Baru', 'verifikasi' => 'Terverifikasi', 'diterima' => 'Diterima', 'cadangan' => 'Cadangan', 'ditolak' => 'Tidak diterima'];

function admin_ppdb(string $action, int $id, string $method): void
{
    if ($action === 'export') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pendaftar-ppdb-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        $cols = ['reg_no', 'name', 'nisn', 'birth_place', 'birth_date', 'gender', 'school_origin', 'parent_name', 'phone', 'email', 'address', 'status', 'notes', 'created_at'];
        fputcsv($out, ['No. daftar', 'Nama', 'NISN', 'Tempat lahir', 'Tanggal lahir', 'Jenis kelamin', 'Asal sekolah', 'Orang tua', 'WhatsApp', 'Email', 'Alamat', 'Status', 'Catatan', 'Waktu daftar']);
        foreach (rows('SELECT * FROM ppdb ORDER BY created_at') as $r) {
            fputcsv($out, array_map(fn($c) => preg_match('/^[=+\-@]/', (string)$r[$c]) ? "'" . $r[$c] : $r[$c], $cols));
        }
        exit;
    }
    if ($method === 'POST' && $action === 'delete') {
        q('DELETE FROM ppdb WHERE id = ?', [$id]);
        flash('Data pendaftar dihapus.');
        redirect('admin/ppdb');
    }
    if ($action === 'view') {
        $r = row('SELECT * FROM ppdb WHERE id = ?', [$id]);
        if (!$r) admin_404();
        if ($method === 'POST') {
            update('ppdb', $id, ['status' => array_key_exists(post('status'), PPDB_STATUS) ? post('status') : 'baru', 'notes' => mb_substr(post('notes'), 0, 1000)]);
            flash('Status pendaftar diperbarui.');
            redirect('admin/ppdb/view/' . $id);
        }
        $h = '<div class="two-col"><div class="panel"><dl class="dl">';
        foreach (['reg_no' => 'No. pendaftaran', 'name' => 'Nama', 'nisn' => 'NISN', 'birth_place' => 'Tempat lahir', 'birth_date' => 'Tanggal lahir', 'gender' => 'Jenis kelamin',
            'school_origin' => 'Asal sekolah', 'parent_name' => 'Orang tua/wali', 'phone' => 'WhatsApp', 'email' => 'Email', 'address' => 'Alamat', 'created_at' => 'Waktu daftar'] as $k => $l) {
            $h .= '<dt>' . $l . '</dt><dd>' . nl2br(e((string)$r[$k])) . '</dd>';
        }
        $h .= '</dl></div><form class="panel form" method="post">' . csrf_field() . '<h2>Status seleksi</h2><label>Status<select name="status">';
        foreach (PPDB_STATUS as $k => $l) $h .= '<option value="' . $k . '"' . ($r['status'] === $k ? ' selected' : '') . '>' . $l . '</option>';
        $h .= '</select></label><label>Catatan untuk pendaftar (tampil saat cek status)<textarea name="notes" rows="4">' . e((string)$r['notes']) . '</textarea></label><button class="btn">Simpan status</button></form></div>'
            . '<form method="post" action="' . e(url('admin/ppdb/delete/' . $id)) . '" data-confirm="Hapus data pendaftar ini?">' . csrf_field() . '<button class="btn danger">Hapus pendaftar</button></form>';
        admin_layout($r['name'], $h, 'ppdb', ['actions' => '<a class="btn ghost" href="' . e(url('admin/ppdb')) . '">← Kembali</a>']);
        return;
    }
    $st = (string)($_GET['status'] ?? '');
    $list = $st !== '' ? rows('SELECT * FROM ppdb WHERE status = ? ORDER BY created_at DESC', [$st]) : rows('SELECT * FROM ppdb ORDER BY created_at DESC');
    $chips = '<div class="chips"><a class="chip' . ($st === '' ? ' on' : '') . '" href="' . e(url('admin/ppdb')) . '">Semua (' . (int)val('SELECT COUNT(*) FROM ppdb') . ')</a>';
    foreach (PPDB_STATUS as $k => $l) $chips .= '<a class="chip' . ($st === $k ? ' on' : '') . '" href="' . e(url('admin/ppdb?status=' . $k)) . '">' . $l . ' (' . (int)val('SELECT COUNT(*) FROM ppdb WHERE status = ?', [$k]) . ')</a>';
    $h = $chips . '</div><div class="panel"><table class="tbl"><thead><tr><th>No. daftar</th><th>Nama</th><th>Asal sekolah</th><th>WhatsApp</th><th>Status</th><th>Waktu</th></tr></thead><tbody>';
    foreach ($list as $r) {
        $h .= '<tr><td>' . e($r['reg_no']) . '</td><td><a href="' . e(url('admin/ppdb/view/' . $r['id'])) . '">' . e($r['name']) . '</a></td><td>' . e((string)$r['school_origin']) . '</td><td>' . e((string)$r['phone']) . '</td><td><span class="badge st-' . e($r['status']) . '">' . e(PPDB_STATUS[$r['status']] ?? $r['status']) . '</span></td><td>' . e(tgl($r['created_at'])) . '</td></tr>';
    }
    if (!$list) $h .= '<tr><td colspan="6" class="muted">Belum ada pendaftar.</td></tr>';
    admin_layout('Pendaftar PPDB', $h . '</tbody></table></div>', 'ppdb', ['actions' => '<a class="btn ghost" href="' . e(url('admin/ppdb/export')) . '">Unduh Excel (CSV)</a>']);
}

/* ---------- Media ---------- */
function admin_media(string $action, int $id, string $method): void
{
    if ($action === 'upload' && $method === 'POST') {
        $out = [];
        $err = [];
        foreach (files_list('file') as $f) {
            try {
                $r = store_upload($f, ($_POST['images_only'] ?? '1') === '1');
                $out[] = ['path' => $r['path'], 'url' => media($r['path'])];
            } catch (RuntimeException $ex) {
                $err[] = $f['name'] . ': ' . $ex->getMessage();
            }
        }
        if (($_POST['ajax'] ?? '') === '1') json_out(['files' => $out, 'errors' => $err], $out ? 200 : 422);
        flash($err ? implode('; ', $err) : count($out) . ' file diunggah.', $err ? 'error' : 'success');
        redirect('admin/media');
    }
    if ($action === 'list') {
        $list = rows("SELECT id, file, original, width, height FROM media WHERE mime LIKE 'image/%' ORDER BY id DESC LIMIT 300");
        json_out(array_map(fn($m) => $m + ['url' => media($m['file'])], $list));
    }
    if ($action === 'delete' && $method === 'POST') {
        $m = row('SELECT file FROM media WHERE id = ?', [$id]);
        if ($m) delete_upload($m['file']);
        flash('File dihapus. Pastikan file itu tidak lagi dipakai di halaman.');
        redirect('admin/media');
    }
    $list = rows('SELECT * FROM media ORDER BY id DESC LIMIT 500');
    admin_layout('Media', render_to_string('admin/media', ['list' => $list]), 'media');
}

/* ---------- Settings ---------- */
function settings_fields(): array
{
    return [
        'Identitas' => [
            ['site_name', 'Nama madrasah', 'text'], ['site_city', 'Kota (tampil setelah nama)', 'text'], ['tagline', 'Tagline di bawah logo', 'text'],
            ['logo', 'Logo', 'image'], ['favicon', 'Ikon tab browser (favicon)', 'image'],
            ['npsn', 'NPSN', 'text'], ['nsm', 'NSM', 'text'], ['footer_about', 'Teks singkat di footer', 'textarea'],
        ],
        'Kontak' => [
            ['address', 'Alamat lengkap', 'textarea'], ['phone', 'Telepon', 'text'], ['whatsapp', 'Nomor WhatsApp (contoh 0812...)', 'text'],
            ['email', 'Email', 'text'], ['office_hours', 'Jam layanan', 'textarea'],
            ['map_embed', 'Alamat embed Google Maps (https://www.google.com/maps/embed?...)', 'text'], ['map_link', 'Tautan petunjuk arah Google Maps', 'text'],
        ],
        'Media sosial' => [
            ['instagram', 'Instagram (URL)', 'text'], ['youtube', 'YouTube (URL)', 'text'], ['facebook', 'Facebook (URL)', 'text'], ['tiktok', 'TikTok (URL)', 'text'],
            ['whatsapp_channel', 'Saluran WhatsApp (URL)', 'text'],
        ],
        'Tampilan' => [
            ['cta_label', 'Tombol di header (teks)', 'text'], ['cta_url', 'Tombol di header (tautan)', 'text'],
            ['primary_color', 'Warna utama (kosongkan untuk hijau bawaan #0E4D3A)', 'color'],
            ['meta_description', 'Deskripsi situs untuk Google', 'textarea'],
        ],
    ];
}

function admin_settings(string $method): void
{
    require_admin();
    if ($method === 'POST') {
        foreach (settings_fields() as $fields) {
            foreach ($fields as [$k]) {
                if (isset($_POST[$k])) set_setting($k, mb_substr(trim((string)$_POST[$k]), 0, 3000));
            }
        }
        flash('Pengaturan disimpan.');
        redirect('admin/settings');
    }
    $vals = array_column(rows('SELECT k, v FROM settings'), 'v', 'k');
    admin_layout('Pengaturan situs', render_to_string('admin/settings', ['groups' => settings_fields(), 'vals' => $vals]), 'settings');
}

/* ---------- Users ---------- */
function admin_users(string $action, int $id, string $method): void
{
    require_admin();
    if ($method === 'POST' && $action === 'save') {
        $username = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', post('username')));
        $pass = (string)($_POST['password'] ?? '');
        if ($username === '' || ($id === 0 && strlen($pass) < 8)) {
            flash('Isi nama pengguna dan kata sandi minimal 8 karakter.', 'error');
            redirect('admin/users');
        }
        if (val('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$username, $id])) {
            flash('Nama pengguna sudah dipakai.', 'error');
            redirect('admin/users');
        }
        $data = ['name' => post('name') ?: $username, 'username' => $username, 'role' => post('role') === 'admin' ? 'admin' : 'editor'];
        if ($pass !== '') {
            if (strlen($pass) < 8) { flash('Kata sandi minimal 8 karakter.', 'error'); redirect('admin/users'); }
            $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
        }
        if ($id) {
            if ($id === (int)current_user()['id']) $data['role'] = 'admin';
            update('users', $id, $data);
        } else {
            insert('users', $data + ['created_at' => now()]);
        }
        flash('Pengguna disimpan.');
        redirect('admin/users');
    }
    if ($method === 'POST' && $action === 'delete') {
        if ($id === (int)current_user()['id']) flash('Tidak bisa menghapus akun sendiri.', 'error');
        else { q('DELETE FROM users WHERE id = ?', [$id]); flash('Pengguna dihapus.'); }
        redirect('admin/users');
    }
    $edit = $action === 'edit' ? row('SELECT * FROM users WHERE id = ?', [$id]) : null;
    admin_layout('Pengguna', render_to_string('admin/users', ['list' => rows('SELECT * FROM users ORDER BY name'), 'edit' => $edit]), 'users');
}

function admin_profile(string $method): void
{
    $u = current_user();
    if ($method === 'POST') {
        $full = row('SELECT * FROM users WHERE id = ?', [$u['id']]);
        $new = (string)($_POST['new_password'] ?? '');
        if (!password_verify((string)($_POST['current_password'] ?? ''), $full['password'])) {
            flash('Kata sandi saat ini salah.', 'error');
        } elseif (strlen($new) < 8) {
            flash('Kata sandi baru minimal 8 karakter.', 'error');
        } else {
            update('users', (int)$u['id'], ['password' => password_hash($new, PASSWORD_DEFAULT), 'name' => post('name') ?: $u['name']]);
            flash('Profil dan kata sandi diperbarui.');
        }
        redirect('admin/profile');
    }
    $h = '<form class="panel form narrow" method="post">' . csrf_field()
        . '<label>Nama tampilan<input name="name" value="' . e($u['name']) . '"></label>'
        . '<label>Kata sandi saat ini<input type="password" name="current_password" required autocomplete="current-password"></label>'
        . '<label>Kata sandi baru (minimal 8 karakter)<input type="password" name="new_password" required minlength="8" autocomplete="new-password"></label>'
        . '<button class="btn">Simpan</button></form>';
    admin_layout('Profil saya', $h, 'profile');
}
