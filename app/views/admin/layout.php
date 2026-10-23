<?php
/** @var string $title @var string $content @var string $active @var string $actions @var bool $wide */
$u = current_user();
$unread = (int)val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$newPpdb = (int)val("SELECT COUNT(*) FROM ppdb WHERE status = 'baru'");
$nav = [
    'Utama' => [['dashboard', 'Dasbor', 'admin', 'home']],
    'Konten' => [
        ['pages', 'Halaman', 'admin/pages', 'file'], ['menus', 'Menu', 'admin/menus', 'menu'], ['posts', 'Berita', 'admin/posts', 'book'],
        ['albums', 'Galeri foto', 'admin/albums', 'image'], ['videos', 'Video', 'admin/videos', 'youtube'], ['agenda', 'Agenda', 'admin/agenda', 'calendar'],
        ['staff', 'Guru & staf', 'admin/staff', 'users'], ['downloads', 'Unduhan', 'admin/downloads', 'download'], ['media', 'Media', 'admin/media', 'image'],
    ],
    'Layanan' => [['messages', 'Pesan masuk', 'admin/messages', 'mail', $unread], ['ppdb', 'Pendaftar PPDB', 'admin/ppdb', 'clipboard', $newPpdb]],
    'Sistem' => array_merge(is_admin() ? [['settings', 'Pengaturan', 'admin/settings', 'star'], ['users', 'Pengguna', 'admin/users', 'user']] : [], [['profile', 'Profil saya', 'admin/profile', 'user']]),
];
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($title) ?> · Admin <?= e(setting('site_name', 'MTs Contoh')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>?v=1">
</head>
<body class="admin">
<aside class="side" id="side">
  <a class="side-brand" href="<?= e(url('admin')) ?>"><span class="logo-mark">MC</span><span><strong><?= e(setting('site_name', 'MTs Contoh')) ?></strong><small>Panel admin</small></span></a>
  <nav aria-label="Menu admin">
    <?php foreach ($nav as $group => $items): ?>
      <span class="side-group"><?= e($group) ?></span>
      <?php foreach ($items as $it): ?>
        <a href="<?= e(url($it[2])) ?>" class="<?= $active === $it[0] ? 'on' : '' ?>"<?= $active === $it[0] ? ' aria-current="page"' : '' ?>><?= icon($it[3]) ?><span><?= e($it[1]) ?></span><?php if (!empty($it[4])): ?><em class="count"><?= (int)$it[4] ?></em><?php endif ?></a>
      <?php endforeach ?>
    <?php endforeach ?>
  </nav>
  <div class="side-foot">
    <span><?= e($u['name']) ?><small><?= $u['role'] === 'admin' ? 'Admin' : 'Editor' ?></small></span>
    <form method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?><button class="link-btn" type="submit">Keluar</button></form>
  </div>
</aside>
<div class="main">
  <header class="topbar">
    <button class="menu-btn" type="button" aria-controls="side" aria-expanded="false" aria-label="Menu"><?= icon('menu') ?></button>
    <h1><?= e($title) ?></h1>
    <div class="top-actions"><?= $actions ?><a class="btn ghost" href="<?= e(url('')) ?>" target="_blank" rel="noopener">Lihat website ↗</a></div>
  </header>
  <div class="content<?= $wide ? ' wide' : '' ?>">
    <?php foreach (flashes() as $f): ?><div class="flash <?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div><?php endforeach ?>
    <?= $content ?>
  </div>
</div>
<div class="modal" id="media-modal" hidden role="dialog" aria-modal="true" aria-labelledby="mm-title">
  <div class="modal-box">
    <div class="modal-head"><h2 id="mm-title">Pilih gambar</h2><button type="button" class="link-btn" data-close>Tutup</button></div>
    <div class="modal-tools">
      <label class="btn">Unggah gambar baru<input type="file" accept="image/*" multiple hidden data-upload></label>
      <input type="search" placeholder="Cari nama file..." data-search>
      <span class="muted" data-status></span>
    </div>
    <div class="media-grid" data-grid></div>
  </div>
</div>
<script>window.CMS = {base: <?= json_encode(base_path()) ?>, csrf: <?= json_encode(csrf_token()) ?>};</script>
<script src="<?= e(url('assets/js/admin.js')) ?>?v=1"></script>
</body>
</html>
