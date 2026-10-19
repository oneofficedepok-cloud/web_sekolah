<div class="panel">
<p class="muted">Setiap halaman tersusun dari blok yang bisa diubah, ditambah, diurutkan, atau disembunyikan. Pakai <strong>Duplikat</strong> untuk membuat halaman baru dengan susunan yang sama.</p>
<table class="tbl">
<thead><tr><th>Judul</th><th>Alamat</th><th>Status</th><th>Di menu</th><th>Diubah</th><th class="right">Aksi</th></tr></thead>
<tbody>
<?php foreach ($list as $p): ?>
<tr>
  <td><a href="<?= e(url('admin/pages/edit/' . $p['id'])) ?>"><strong><?= e($p['title']) ?></strong></a><?= $p['is_home'] ? ' <span class="badge st-diterima">Beranda</span>' : '' ?></td>
  <td><a href="<?= e(url($p['is_home'] ? '' : $p['slug'])) ?>" target="_blank" rel="noopener">/<?= e($p['is_home'] ? '' : $p['slug']) ?></a></td>
  <td><span class="badge <?= $p['status'] === 'published' ? 'st-diterima' : 'st-baru' ?>"><?= $p['status'] === 'published' ? 'Terbit' : 'Draf' ?></span></td>
  <td><?= $p['in_menu'] ? 'Ya' : '—' ?></td>
  <td><?= e(tgl($p['updated_at'])) ?></td>
  <td class="right actions">
    <a class="btn sm ghost" href="<?= e(url('admin/pages/edit/' . $p['id'])) ?>">Ubah</a>
    <a class="btn sm ghost" href="<?= e(url('admin/pages/new?dari=' . $p['id'])) ?>">Duplikat</a>
    <?php if (!$p['is_home']): ?>
    <form method="post" action="<?= e(url('admin/pages/home/' . $p['id'])) ?>" data-confirm="Jadikan halaman ini beranda?"><?= csrf_field() ?><button class="btn sm ghost">Jadikan beranda</button></form>
    <form method="post" action="<?= e(url('admin/pages/delete/' . $p['id'])) ?>" data-confirm="Hapus halaman &quot;<?= e($p['title']) ?>&quot;? Tautan menunya juga dihapus."><?= csrf_field() ?><button class="btn sm danger">Hapus</button></form>
    <?php endif ?>
  </td>
</tr>
<?php endforeach ?>
</tbody>
</table>
</div>
