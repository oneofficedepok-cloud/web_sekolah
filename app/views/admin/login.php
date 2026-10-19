<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Masuk · Admin <?= e(setting('site_name', 'MTs Al-Hidayah')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>?v=1">
</head>
<body class="auth">
<form class="auth-card" method="post" action="<?= e(url('admin/login')) ?>">
  <?= csrf_field() ?>
  <span class="logo-mark big">AH</span>
  <h1>Masuk panel admin</h1>
  <p class="muted"><?= e(setting('site_name', 'MTs Al-Hidayah')) ?></p>
  <?php if ($error): ?><div class="flash error" role="alert"><?= e($error) ?></div><?php endif ?>
  <label>Nama pengguna<input name="username" autocomplete="username" required autofocus></label>
  <label>Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
  <button class="btn block" type="submit">Masuk</button>
  <a class="muted small" href="<?= e(url('')) ?>">← Kembali ke website</a>
</form>
</body>
</html>
