<?php
declare(strict_types=1);

/** Resolve a menu row into an href. */
function menu_href(array $m): string
{
    return match ($m['type']) {
        'page' => ($m['page_slug'] ?? null) !== null ? url($m['page_is_home'] ? '' : $m['page_slug']) : '#',
        'category' => url('berita') . '?kategori=' . urlencode((string)$m['url']),
        'album' => url('galeri/' . $m['url']),
        default => url((string)$m['url'] ?: '#'),
    };
}

function menu_tree(string $location = 'main'): array
{
    $list = rows('SELECT m.*, p.slug AS page_slug, p.is_home AS page_is_home FROM menus m LEFT JOIN pages p ON p.id = m.page_id WHERE m.location = ? AND m.visible = 1 ORDER BY m.sort, m.id', [$location]);
    $byParent = [];
    foreach ($list as $m) $byParent[(int)$m['parent_id']][] = $m;
    $build = function (int $pid) use (&$build, $byParent) {
        $out = [];
        foreach ($byParent[$pid] ?? [] as $m) {
            $m['href'] = menu_href($m);
            $m['children'] = $build((int)$m['id']);
            $out[] = $m;
        }
        return $out;
    };
    return $build(0);
}

function site_layout(string $title, string $content, array $opts = []): void
{
    $siteName = setting('site_name', 'MTs Contoh');
    $fullTitle = $title && $title !== $siteName ? $title . ' · ' . $siteName : trim($siteName . ' ' . setting('site_city', ''));
    view('site/layout', [
        'title' => $fullTitle,
        'description' => $opts['description'] ?? setting('meta_description'),
        'image' => $opts['image'] ?? '',
        'content' => $content,
        'activePath' => $opts['active'] ?? '',
    ]);
}

function site_page(array $page): void
{
    $blocks = json_decode((string)$page['blocks'], true) ?: [];
    $html = render_blocks($blocks, $page);
    if ($page['status'] !== 'published') {
        $html = '<div class="preview-bar">Pratinjau: halaman ini masih draf dan belum tampil untuk umum.</div>' . $html;
    }
    $img = '';
    foreach ($blocks as $bl) if (!empty($bl['image'])) { $img = $bl['image']; break; }
    site_layout($page['is_home'] ? '' : $page['title'], $html, ['description' => $page['meta_description'], 'image' => $img, 'active' => $page['is_home'] ? '' : $page['slug']]);
}

function page_header_html(string $title, string $crumbs, string $sub = ''): string
{
    return '<section class="blk bg-dark page-header no-img">' . geo_pattern('geoP') . '<div class="wrap ph-grid"><div class="ph-text"><nav class="crumb" aria-label="Breadcrumb"><a href="' . e(url('')) . '">Beranda</a>' . $crumbs . '</nav><h1>' . e($title) . '</h1>' . ($sub ? '<p class="lead">' . e($sub) . '</p>' : '') . '</div></div></section>';
}

function news_page_slug(): string
{
    return (string)(val("SELECT slug FROM pages WHERE blocks LIKE '%\"mode\":\"full\"%' AND status = 'published' LIMIT 1") ?: 'berita');
}

function site_post(string $slug): void
{
    $p = row("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author FROM posts p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN users u ON u.id = p.author_id WHERE p.slug = ?", [$slug]);
    if (!$p || ($p['status'] !== 'published' && !current_user()) || ($p['published_at'] > now() && !current_user())) site_404();
    $key = 'viewed_' . $p['id'];
    if (empty($_SESSION[$key])) {
        q('UPDATE posts SET views = views + 1 WHERE id = ?', [$p['id']]);
        $_SESSION[$key] = 1;
    }
    $newsSlug = news_page_slug();
    $related = rows("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status = 'published' AND p.published_at <= ? AND p.id <> ? ORDER BY (p.category_id = ?) DESC, p.published_at DESC LIMIT 3", [now(), $p['id'], (int)$p['category_id']]);
    $share = urlencode((isset($_SERVER['HTTP_HOST']) ? (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] : '') . url('berita/' . $p['slug']));
    $html = page_header_html($p['title'], ' / <a href="' . e(url($newsSlug)) . '">Berita</a>' . ($p['cat_name'] ? ' / <a href="' . e(url($newsSlug) . '?kategori=' . urlencode($p['cat_slug'])) . '">' . e($p['cat_name']) . '</a>' : ''))
        . '<article class="blk bg-white"><div class="wrap article">'
        . '<div class="article-meta">' . e(tgl($p['published_at'], true)) . ($p['author'] ? ' · ' . e($p['author']) : '') . ' · ' . (int)$p['views'] . 'x dibaca</div>'
        . ($p['cover'] ? '<figure class="article-cover">' . img_tag($p['cover'], $p['title']) . '</figure>' : '')
        . '<div class="prose">' . $p['body'] . '</div>'
        . '<div class="share"><span>Bagikan:</span><a class="chip" href="https://wa.me/?text=' . $share . '" target="_blank" rel="noopener">WhatsApp</a><a class="chip" href="https://www.facebook.com/sharer/sharer.php?u=' . $share . '" target="_blank" rel="noopener">Facebook</a></div>'
        . '</div></article>';
    if ($related) {
        $html .= '<section class="blk bg-soft"><div class="wrap stack"><div class="sec-head"><div class="sec-head-text"><span class="eyebrow">BACA JUGA</span><h2>Berita lainnya</h2></div></div><div class="grid cols-3">' . implode('', array_map('post_card', $related)) . '</div></div></section>';
    }
    site_layout($p['title'], $html, ['description' => $p['excerpt'] ?: excerpt((string)$p['body']), 'image' => (string)$p['cover'], 'active' => $newsSlug]);
}

function site_album(string $slug): void
{
    $a = row('SELECT * FROM albums WHERE slug = ?', [$slug]);
    if (!$a) site_404();
    $photos = rows('SELECT * FROM photos WHERE album_id = ? ORDER BY sort, id', [$a['id']]);
    $gal = (string)(val("SELECT slug FROM pages WHERE blocks LIKE '%\"type\":\"albums\"%' AND status = 'published' LIMIT 1") ?: 'galeri');
    $h = '';
    foreach ($photos as $i => $p) {
        $h .= '<a class="g-item' . ($i % 7 === 0 ? ' tall' : '') . '" href="' . e(media($p['file'])) . '" data-lightbox="album" data-caption="' . e((string)$p['caption']) . '">' . img_tag($p['file'], (string)$p['caption']) . '</a>';
    }
    $html = page_header_html($a['title'], ' / <a href="' . e(url($gal)) . '">Galeri</a>', trim(tgl($a['event_date']) . ($a['description'] ? ' · ' . $a['description'] : ''), ' ·'))
        . '<section class="blk bg-white"><div class="wrap stack"><div class="masonry">' . ($h ?: '<p class="muted">Belum ada foto di album ini.</p>') . '</div><a class="link-arrow" href="' . e(url($gal)) . '">Kembali ke galeri' . icon('arrow') . '</a></div></section>';
    site_layout($a['title'], $html, ['image' => $a['cover'] ?: ($photos[0]['file'] ?? ''), 'active' => $gal]);
}

function site_agenda(): void
{
    $html = page_header_html('Agenda madrasah', ' / Agenda') . blk_agenda(['limit' => 50, 'title' => 'Agenda mendatang', 'eyebrow' => 'AGENDA']);
    site_layout('Agenda', $html);
}

function site_search(): void
{
    $qs = trim((string)($_GET['q'] ?? ''));
    $html = page_header_html('Pencarian', ' / Cari');
    $html .= '<section class="blk bg-white"><div class="wrap stack narrow"><form class="search-inline" role="search" method="get" action="' . e(url('cari')) . '"><label class="sr-only" for="q-all">Kata kunci</label><input id="q-all" type="search" name="q" value="' . e($qs) . '" placeholder="Cari berita, halaman, atau album..." autofocus><button class="btn btn-primary" type="submit">' . icon('search') . 'Cari</button></form>';
    if (mb_strlen($qs) >= 2) {
        $like = '%' . $qs . '%';
        $res = [];
        foreach (rows("SELECT title, slug, blocks FROM pages WHERE status = 'published' AND (title LIKE ? OR blocks LIKE ?) LIMIT 10", [$like, $like]) as $p) $res[] = ['Halaman', $p['title'], url($p['slug']), ''];
        foreach (rows("SELECT title, slug, excerpt, body FROM posts WHERE status = 'published' AND published_at <= ? AND (title LIKE ? OR body LIKE ?) ORDER BY published_at DESC LIMIT 20", [now(), $like, $like]) as $p) $res[] = ['Berita', $p['title'], url('berita/' . $p['slug']), $p['excerpt'] ?: excerpt((string)$p['body'])];
        foreach (rows('SELECT title, slug FROM albums WHERE title LIKE ? LIMIT 10', [$like]) as $p) $res[] = ['Album', $p['title'], url('galeri/' . $p['slug']), ''];
        $html .= '<p class="muted">' . count($res) . ' hasil untuk "' . e($qs) . '"</p><ul class="results">';
        foreach ($res as [$type, $t, $u, $ex]) $html .= '<li><span class="pill-soft">' . e($type) . '</span><a href="' . e($u) . '">' . e($t) . '</a>' . ($ex ? '<p>' . e($ex) . '</p>' : '') . '</li>';
        $html .= '</ul>';
    }
    $html .= '</div></section>';
    site_layout('Pencarian', $html);
}

function site_sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $host = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (rows("SELECT slug, is_home, updated_at FROM pages WHERE status = 'published'") as $p) {
        echo '<url><loc>' . e($host . url($p['is_home'] ? '' : $p['slug'])) . '</loc><lastmod>' . substr((string)$p['updated_at'], 0, 10) . '</lastmod></url>';
    }
    foreach (rows("SELECT slug, updated_at FROM posts WHERE status = 'published' AND published_at <= ?", [now()]) as $p) {
        echo '<url><loc>' . e($host . url('berita/' . $p['slug'])) . '</loc><lastmod>' . substr((string)$p['updated_at'], 0, 10) . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
}

function site_404(): never
{
    http_response_code(404);
    $html = page_header_html('Halaman tidak ditemukan', ' / 404', 'Alamat yang Anda buka tidak tersedia atau sudah dipindahkan.')
        . '<section class="blk bg-white"><div class="wrap"><a class="btn btn-primary" href="' . e(url('')) . '">Kembali ke beranda' . icon('arrow') . '</a></div></section>';
    site_layout('Tidak ditemukan', $html);
    exit;
}

function back_url(): string
{
    $b = (string)($_POST['back'] ?? '/');
    return preg_match('#^/[A-Za-z0-9/_-]*$#', $b) ? $b : '/';
}

function site_contact_submit(): never
{
    csrf_check();
    $back = back_url();
    if (post('website') !== '') { header('Location: ' . $back . '?terkirim=1'); exit; }
    $name = mb_substr(post('name'), 0, 120);
    $msg = mb_substr(post('message'), 0, 3000);
    if ($name !== '' && $msg !== '') {
        $recent = (int)val('SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at > ?', [client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
        if ($recent < 10) {
            insert('messages', ['name' => $name, 'phone' => mb_substr(post('phone'), 0, 30), 'email' => mb_substr(post('email'), 0, 120),
                'subject' => mb_substr(post('subject'), 0, 120), 'message' => $msg, 'ip' => client_ip(), 'created_at' => now()]);
        }
    }
    header('Location: ' . $back . '?terkirim=1');
    exit;
}

function site_ppdb_submit(): never
{
    csrf_check();
    $back = back_url();
    if (post('website') !== '') { header('Location: ' . $back); exit; }
    $name = mb_substr(post('name'), 0, 120);
    $birth = post('birth_date');
    $phone = preg_replace('/[^0-9+]/', '', post('phone'));
    if ($name === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth) || strlen($phone) < 8 || post('parent_name') === '') {
        $_SESSION['ppdb_error'] = 'Lengkapi nama, tanggal lahir, nama orang tua, dan nomor WhatsApp yang benar.';
        header('Location: ' . $back . '#form');
        exit;
    }
    $recent = (int)val('SELECT COUNT(*) FROM ppdb WHERE phone = ? AND created_at > ?', [$phone, date('Y-m-d H:i:s', time() - 600)]);
    if ($recent > 3) {
        $_SESSION['ppdb_error'] = 'Terlalu banyak pendaftaran dari nomor ini. Coba lagi nanti.';
        header('Location: ' . $back . '#form');
        exit;
    }
    $year = date('Y');
    $seq = (int)val('SELECT COUNT(*) FROM ppdb WHERE reg_no LIKE ?', ["PPDB-$year-%"]) + 1;
    do {
        $reg = sprintf('PPDB-%s-%04d', $year, $seq++);
    } while (val('SELECT COUNT(*) FROM ppdb WHERE reg_no = ?', [$reg]));
    insert('ppdb', ['reg_no' => $reg, 'name' => $name, 'nisn' => preg_replace('/\D/', '', post('nisn')), 'birth_place' => mb_substr(post('birth_place'), 0, 80),
        'birth_date' => $birth, 'gender' => post('gender') === 'Perempuan' ? 'Perempuan' : 'Laki-laki', 'school_origin' => mb_substr(post('school_origin'), 0, 120),
        'parent_name' => mb_substr(post('parent_name'), 0, 120), 'phone' => $phone, 'email' => mb_substr(post('email'), 0, 120),
        'address' => mb_substr(post('address'), 0, 500), 'status' => 'baru', 'created_at' => now()]);
    $_SESSION['ppdb_done'] = $reg;
    header('Location: ' . $back . '#form');
    exit;
}
