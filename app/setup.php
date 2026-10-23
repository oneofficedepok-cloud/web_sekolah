<?php
declare(strict_types=1);

/* First-run screen: create the first admin account, then fill the site with starter content. */

if (val('SELECT COUNT(*) FROM users')) redirect('admin');

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_check();
    $name = post('name');
    $username = strtolower(post('username'));
    $pass = (string)($_POST['password'] ?? '');
    if ($name === '' || !preg_match('/^[a-z0-9._-]{3,40}$/', $username)) {
        $error = 'Isi nama, dan nama pengguna 3–40 karakter (huruf kecil, angka, titik, strip).';
    } elseif (strlen($pass) < 8) {
        $error = 'Kata sandi minimal 8 karakter.';
    } elseif ($pass !== (string)($_POST['password2'] ?? '')) {
        $error = 'Ulangi kata sandi dengan benar.';
    } else {
        $uid = insert('users', ['name' => $name, 'username' => $username, 'password' => password_hash($pass, PASSWORD_DEFAULT),
            'role' => 'admin', 'created_at' => now(), 'last_login' => now()]);
        if (!val('SELECT COUNT(*) FROM pages') && !empty($_POST['seed'])) {
            require APP . '/seed.php';
            seed_content($uid);
        } elseif (!val('SELECT COUNT(*) FROM pages')) {
            insert('pages', ['title' => 'Beranda', 'slug' => 'beranda', 'blocks' => json_encode([['type' => 'page_header', 'title' => setting('site_name', 'Beranda'), 'subtitle' => '']]),
                'status' => 'published', 'is_home' => 1, 'meta_description' => '', 'created_at' => now(), 'updated_at' => now()]);
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = $uid;
        flash('Selamat datang! Website sudah siap. Mulai ubah isi dari menu di samping.');
        redirect('admin');
    }
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Pemasangan · Website Madrasah</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>?v=1">
</head>
<body class="auth">
<form class="auth-card" method="post" action="<?= e(url('setup')) ?>">
  <?= csrf_field() ?>
  <span class="logo-mark big">MC</span>
  <h1>Pemasangan website</h1>
  <p class="muted">Buat akun admin pertama. Akun ini bisa menambah pengguna lain nanti.</p>
  <?php if ($error): ?><div class="flash error" role="alert"><?= e($error) ?></div><?php endif ?>
  <label>Nama lengkap<input name="name" value="<?= e(post('name')) ?>" required autofocus></label>
  <label>Nama pengguna<input name="username" value="<?= e(post('username')) ?>" autocomplete="username" required pattern="[a-zA-Z0-9._\-]{3,40}"></label>
  <label>Kata sandi (minimal 8 karakter)<input type="password" name="password" autocomplete="new-password" minlength="8" required></label>
  <label>Ulangi kata sandi<input type="password" name="password2" autocomplete="new-password" minlength="8" required></label>
  <label class="check"><input type="checkbox" name="seed" value="1" checked> Isi dengan konten contoh sesuai desain (halaman, menu, berita, galeri)</label>
  <button class="btn block" type="submit">Pasang dan masuk</button>
</form>
</body>
</html>
