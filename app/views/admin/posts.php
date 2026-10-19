<form class="filters" method="get" action="<?= e(url('admin/posts')) ?>">
  <input type="search" name="q" value="<?= e((string)($_GET['q'] ?? '')) ?>" placeholder="Cari judul..." aria-label="Cari judul">
  <select name="kategori" aria-label="Kategori"><option value="">Semua kategori</option>
    <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"<?= (int)($_GET['kategori'] ?? 0) === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach ?>
  </select>
  <button class="btn ghost">Saring</button>
</form>
<div class="panel">
<table class="tbl">
<thead><tr><th></th><th>Judul</th><th>Kategori</th><th>Status</th><th>Tanggal</th><th>Dibaca</th><th class="right">Aksi</th></tr></thead>
<tbody>
<?php foreach ($list as $p): ?>
<tr>
  <td class="thumb-cell"><?= $p['cover'] ? '<img src="' . e(media($p['cover'])) . '" alt="">' : '' ?></td>
  <td><a href="<?= e(url('admin/posts/edit/' . $p['id'])) ?>"><strong><?= e($p['title']) ?></strong></a><?= $p['featured'] ? ' <span class="badge st-cadangan">Utama</span>' : '' ?></td>
  <td><?= e((string)$p['cat']) ?></td>
  <td><span class="badge <?= $p['status'] === 'published' ? ($p['published_at'] > now() ? 'st-verifikasi' : 'st-diterima') : 'st-baru' ?>"><?= $p['status'] === 'published' ? ($p['published_at'] > now() ? 'Terjadwal' : 'Terbit') : 'Draf' ?></span></td>
  <td><?= e(tgl($p['published_at'])) ?></td>
  <td><?= (int)$p['views'] ?></td>
  <td class="right actions">
    <a class="btn sm ghost" href="<?= e(url('berita/' . $p['slug'])) ?>" target="_blank" rel="noopener">Lihat</a>
    <form method="post" action="<?= e(url('admin/posts/delete/' . $p['id'])) ?>" data-confirm="Hapus berita ini?"><?= csrf_field() ?><button class="btn sm danger">Hapus</button></form>
  </td>
</tr>
<?php endforeach ?>
<?php if (!$list): ?><tr><td colspan="7" class="muted">Belum ada berita.</td></tr><?php endif ?>
</tbody>
</table>
</div>
