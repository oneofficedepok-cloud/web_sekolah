<?php $e = $edit ?? ['id' => 0, 'name' => '', 'username' => '', 'role' => 'editor']; ?>
<div class="two-col wide-left">
  <div class="panel">
    <table class="tbl">
      <thead><tr><th>Nama</th><th>Pengguna</th><th>Peran</th><th>Terakhir masuk</th><th class="right">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($list as $u): ?>
        <tr><td><?= e($u['name']) ?></td><td><?= e($u['username']) ?></td><td><?= $u['role'] === 'admin' ? 'Admin' : 'Editor' ?></td><td><?= e($u['last_login'] ? tgl($u['last_login']) : '—') ?></td>
        <td class="right actions"><a class="btn sm ghost" href="<?= e(url('admin/users/edit/' . $u['id'])) ?>">Ubah</a>
        <?php if ((int)$u['id'] !== (int)current_user()['id']): ?><form method="post" action="<?= e(url('admin/users/delete/' . $u['id'])) ?>" data-confirm="Hapus pengguna ini?"><?= csrf_field() ?><button class="btn sm danger">Hapus</button></form><?php endif ?></td></tr>
      <?php endforeach ?>
      </tbody>
    </table>
    <p class="muted small"><strong>Admin</strong> bisa mengatur semua hal termasuk pengaturan dan pengguna. <strong>Editor</strong> mengelola konten: halaman, menu, berita, galeri, agenda, pesan, dan PPDB.</p>
  </div>
  <form class="panel form" method="post" action="<?= e(url('admin/users/save/' . (int)$e['id'])) ?>">
    <?= csrf_field() ?>
    <h2><?= $e['id'] ? 'Ubah pengguna' : 'Tambah pengguna' ?></h2>
    <label>Nama<input name="name" value="<?= e($e['name']) ?>" required></label>
    <label>Nama pengguna (untuk masuk)<input name="username" value="<?= e($e['username']) ?>" required autocomplete="off"></label>
    <label><?= $e['id'] ? 'Kata sandi baru (kosongkan bila tidak diganti)' : 'Kata sandi (minimal 8 karakter)' ?><input type="password" name="password" <?= $e['id'] ? '' : 'required' ?> minlength="8" autocomplete="new-password"></label>
    <label>Peran<select name="role"><option value="editor"<?= $e['role'] === 'editor' ? ' selected' : '' ?>>Editor</option><option value="admin"<?= $e['role'] === 'admin' ? ' selected' : '' ?>>Admin</option></select></label>
    <div class="row-btns"><button class="btn">Simpan</button><?php if ($e['id']): ?><a class="btn ghost" href="<?= e(url('admin/users')) ?>">Batal</a><?php endif ?></div>
  </form>
</div>
