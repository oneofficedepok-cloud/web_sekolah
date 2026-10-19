<?php $a = $album ?? ['id' => 0, 'title' => '', 'slug' => '', 'description' => '', 'cover' => '', 'event_date' => date('Y-m-d')]; ?>
<form method="post" enctype="multipart/form-data" action="<?= e(url('admin/albums/edit/' . (int)$a['id'])) ?>" data-dirty-guard>
  <?= csrf_field() ?>
  <div class="two-col wide-left">
    <div class="panel form">
      <label>Nama album<input name="title" value="<?= e($a['title']) ?>" required data-slug-source></label>
      <div class="grid2">
        <label>Tanggal kegiatan<input type="date" name="event_date" value="<?= e((string)$a['event_date']) ?>"></label>
        <label>Alamat<input name="slug" value="<?= e($a['slug']) ?>" placeholder="otomatis" data-slug-target></label>
      </div>
      <label>Keterangan<textarea name="description" rows="2"><?= e((string)$a['description']) ?></textarea></label>
      <label class="drop">Tambah foto (bisa pilih banyak sekaligus)<input type="file" name="photos[]" accept="image/*" multiple data-preview></label>
      <div class="upload-preview" data-preview-out></div>
    </div>
    <div class="panel form">
      <div class="field"><span class="label">Sampul album (kosongkan untuk pakai foto pertama)</span><div class="img-field" data-img-field><input type="hidden" name="cover" value="<?= e((string)$a['cover']) ?>"></div></div>
      <button class="btn block" type="submit">Simpan album</button>
      <?php if ($a['id']): ?><a class="btn ghost block" href="<?= e(url('galeri/' . $a['slug'])) ?>" target="_blank" rel="noopener">Lihat album ↗</a><?php endif ?>
    </div>
  </div>
  <?php if ($photos): ?>
  <div class="panel">
    <h2>Foto di album (<?= count($photos) ?>)</h2>
    <p class="muted small">Ubah keterangan atau urutan, centang "hapus" untuk membuang foto, lalu klik Simpan album.</p>
    <div class="photo-grid">
      <?php foreach ($photos as $ph): ?>
      <div class="photo-item">
        <img src="<?= e(media($ph['file'])) ?>" alt="">
        <input name="caption[<?= (int)$ph['id'] ?>]" value="<?= e((string)$ph['caption']) ?>" placeholder="Keterangan" aria-label="Keterangan foto">
        <div class="photo-meta"><label class="inline">Urutan <input type="number" name="psort[<?= (int)$ph['id'] ?>]" value="<?= (int)$ph['sort'] ?>"></label><label class="check danger-text"><input type="checkbox" name="remove[]" value="<?= (int)$ph['id'] ?>"> hapus</label></div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
  <?php endif ?>
</form>
<?php if ($a['id']): ?>
<form method="post" action="<?= e(url('admin/albums/delete/' . $a['id'])) ?>" data-confirm="Hapus album ini beserta semua fotonya?"><?= csrf_field() ?><button class="btn danger">Hapus album</button></form>
<?php endif ?>
