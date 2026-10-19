<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require APP . '/site.php';

$path = trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
$base = trim(base_path(), '/');
if ($base !== '' && str_starts_with($path, $base)) $path = trim(substr($path, strlen($base)), '/');
if ($path === 'index.php') $path = '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// First run: no users yet → setup screen.
if (!val('SELECT COUNT(*) FROM users') && $path !== 'setup') redirect('setup');

if ($path === 'setup') {
    require APP . '/setup.php';
    exit;
}
if ($path === 'admin' || str_starts_with($path, 'admin/')) {
    require APP . '/admin.php';
    admin_route(substr($path, 6) ?: '', $method);
    exit;
}

if ($method === 'POST' && $path === 'kirim-pesan') site_contact_submit();
if ($method === 'POST' && $path === 'ppdb/daftar') site_ppdb_submit();

if ($path === '') {
    $page = row("SELECT * FROM pages WHERE is_home = 1 AND status = 'published' LIMIT 1") ?? row("SELECT * FROM pages WHERE status = 'published' ORDER BY id LIMIT 1");
    $page ? site_page($page) : site_404();
} elseif (preg_match('#^berita/([a-z0-9-]+)$#', $path, $m)) {
    site_post($m[1]);
} elseif (preg_match('#^galeri/([a-z0-9-]+)$#', $path, $m)) {
    site_album($m[1]);
} elseif ($path === 'cari') {
    site_search();
} elseif ($path === 'sitemap.xml') {
    site_sitemap();
} elseif ($path === 'agenda' && !val("SELECT COUNT(*) FROM pages WHERE slug = 'agenda'")) {
    site_agenda();
} elseif (preg_match('#^[a-z0-9-]+$#', $path)) {
    $page = row('SELECT * FROM pages WHERE slug = ?', [$path]);
    $preview = $page && $page['status'] !== 'published' && current_user();
    ($page && ($page['status'] === 'published' || $preview)) ? site_page($page) : site_404();
} else {
    site_404();
}
