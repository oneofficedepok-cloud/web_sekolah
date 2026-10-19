<?php
/** @var string $title @var string $description @var string $image @var string $content @var string $activePath */
$siteName = setting('site_name', 'MTs Al-Hidayah');
$tagline = setting('tagline', 'Madrasah Tsanawiyah · Depok');
$logo = setting('logo');
$main = menu_tree('main');
$top = menu_tree('top');
$footer = menu_tree('footer');
$wa = setting('whatsapp');
$waLink = $wa ? 'https://wa.me/' . preg_replace('/\D/', '', preg_replace('/^0/', '62', $wa)) : '';
$cur = '/' . trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$isActive = function (array $m) use ($cur, &$isActive): bool {
    $h = '/' . trim((string)parse_url($m['href'], PHP_URL_PATH), '/');
    if ($h === $cur && !str_contains($m['href'], '#')) return true;
    foreach ($m['children'] as $c) if ($isActive($c)) return true;
    return $h !== '/' && $h === $cur;
};
$logoSvg = '<svg width="52" height="52" viewBox="0 0 52 52" aria-hidden="true"><rect x="8" y="8" width="36" height="36" rx="4" fill="#0E4D3A"/><rect x="8" y="8" width="36" height="36" rx="4" fill="#0E4D3A" transform="rotate(45 26 26)"/><circle cx="26" cy="26" r="12" fill="none" stroke="#E9C978" stroke-width="1.5"/><text x="26" y="31" text-anchor="middle" font-family="Amiri, serif" font-size="15" font-weight="700" fill="#E9C978">AH</text></svg>';
$socials = ['instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'];
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($description): ?><meta name="description" content="<?= e($description) ?>"><?php endif ?>
<meta property="og:title" content="<?= e($title) ?>">
<?php if ($image): ?><meta property="og:image" content="<?= e(media($image)) ?>"><?php endif ?>
<?php if (setting('favicon')): ?><link rel="icon" href="<?= e(media(setting('favicon'))) ?>"><?php endif ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/site.css')) ?>?v=1">
<?php if (setting('primary_color')): ?><style>:root{--green:<?= e(setting('primary_color')) ?>}</style><?php endif ?>
</head>
<body>
<a class="skip" href="#konten">Lewati ke konten</a>
<div class="topbar">
  <div class="wrap topbar-in">
    <div class="topbar-info">
      <?php if (setting('address')): ?><span><?= icon('pin') ?><?= e(strtok(setting('address'), "\n")) ?></span><?php endif ?>
      <?php if (setting('email')): ?><a href="mailto:<?= e(setting('email')) ?>"><?= icon('mail') ?><?= e(setting('email')) ?></a><?php endif ?>
    </div>
    <div class="topbar-links">
      <?php foreach ($top as $m): ?><a href="<?= e($m['href']) ?>"<?= $m['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($m['label']) ?></a><?php endforeach ?>
      <a class="gold" href="<?= e(url('admin')) ?>">Masuk Admin</a>
    </div>
  </div>
</div>
<header class="site-header">
  <div class="wrap header-in">
    <a class="brand" href="<?= e(url('')) ?>">
      <?= $logo ? '<img src="' . e(media($logo)) . '" alt="" class="brand-logo">' : $logoSvg ?>
      <span><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="mainnav" aria-label="Buka menu"><?= icon('menu') ?></button>
    <nav id="mainnav" class="mainnav" aria-label="Menu utama">
      <ul>
      <?php foreach ($main as $m): $act = $isActive($m); ?>
        <li class="<?= $m['children'] ? 'has-sub' : '' ?>">
          <a href="<?= e($m['href']) ?>"<?= $act ? ' class="active" aria-current="page"' : '' ?><?= $m['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($m['label']) ?><?= $m['children'] ? icon('chev', 'ic chev') : '' ?></a>
          <?php if ($m['children']): ?>
          <button class="sub-toggle" type="button" aria-label="Buka submenu <?= e($m['label']) ?>" aria-expanded="false"><?= icon('chev') ?></button>
          <ul class="sub">
            <?php foreach ($m['children'] as $c): ?><li><a href="<?= e($c['href']) ?>"<?= $c['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($c['label']) ?></a></li><?php endforeach ?>
          </ul>
          <?php endif ?>
        </li>
      <?php endforeach ?>
      </ul>
    </nav>
    <div class="header-actions">
      <a class="icon-btn" href="<?= e(url('cari')) ?>" aria-label="Cari"><?= icon('search') ?></a>
      <?php if (setting('cta_label')): ?><a class="btn btn-primary btn-sm" href="<?= e(url(setting('cta_url', '#'))) ?>"><?= e(setting('cta_label')) ?><?= icon('arrow') ?></a><?php endif ?>
    </div>
  </div>
</header>
<main id="konten">
<?= $content ?>
</main>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="f-col">
      <span class="f-brand"><?= $logo ? '<img src="' . e(media($logo)) . '" alt="" class="brand-logo sm">' : str_replace(['52', '#0E4D3A'], ['44', '#1B6650'], $logoSvg) ?><strong><?= e($siteName . ' ' . setting('site_city', 'Depok')) ?></strong></span>
      <p><?= e(setting('footer_about')) ?></p>
      <div class="socials">
        <?php foreach ($socials as $k => $label): if (setting($k)): ?><a href="<?= e(setting($k)) ?>" target="_blank" rel="noopener" aria-label="<?= $label ?>"><?= icon($k) ?></a><?php endif; endforeach ?>
        <?php if ($waLink): ?><a href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= icon('wa') ?></a><?php endif ?>
      </div>
    </div>
    <div class="f-col">
      <strong class="f-title">Tautan cepat</strong>
      <?php foreach ($footer as $m): ?><a href="<?= e($m['href']) ?>"><?= e($m['label']) ?></a><?php endforeach ?>
    </div>
    <div class="f-col">
      <strong class="f-title">Kontak</strong>
      <span><?= nl2br(e(setting('address'))) ?></span>
      <?php if (setting('phone')): ?><span>Telp. <?= e(setting('phone')) ?></span><?php endif ?>
      <?php if ($wa): ?><span>WA <?= e($wa) ?></span><?php endif ?>
    </div>
    <div class="f-col">
      <strong class="f-title">Jam layanan</strong>
      <span><?= nl2br(e(setting('office_hours'))) ?></span>
    </div>
  </div>
  <div class="f-bottom"><div class="wrap f-bottom-in"><span>© <?= date('Y') ?> <?= e($siteName . ' ' . setting('site_city', 'Depok')) ?>. Hak cipta dilindungi.</span><span><?= e(trim((setting('npsn') ? 'NPSN ' . setting('npsn') : '') . (setting('nsm') ? ' · NSM ' . setting('nsm') : ''), ' ·')) ?></span></div></div>
</footer>
<?php if ($waLink): ?><a class="wa-float" href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="Chat WhatsApp"><?= icon('wa') ?></a><?php endif ?>
<div class="lightbox" hidden role="dialog" aria-modal="true" aria-label="Foto"><button type="button" class="lb-close" aria-label="Tutup"><?= icon('close') ?></button><button type="button" class="lb-prev" aria-label="Sebelumnya">‹</button><figure><img alt=""><figcaption></figcaption></figure><button type="button" class="lb-next" aria-label="Berikutnya">›</button></div>
<script src="<?= e(url('assets/js/site.js')) ?>?v=1" defer></script>
</body>
</html>
