<form class="panel form" method="post" enctype="multipart/form-data" action="<?= e(url('admin/media/upload')) ?>">
  <?= csrf_field() ?><input type="hidden" name="images_only" value="0">
  <label class="drop">Unggah gambar atau dokumen (JPG, PNG, WEBP, PDF, DOCX, XLSX, ...)<input type="file" name="file[]" multiple required></label>
  <button class="btn">Unggah</button>
</form>
<div class="media-lib">
<?php foreach ($list as $m): $img = str_starts_with((string)$m['mime'], 'image/'); ?>
  <div class="media-item">
    <a href="<?= e(media($m['file'])) ?>" target="_blank" rel="noopener" class="media-thumb"><?= $img ? '<img src="' . e(media($m['file'])) . '" alt="" loading="lazy">' : '<span class="file-ic">' . e(strtoupper(pathinfo($m['file'], PATHINFO_EXTENSION))) . '</span>' ?></a>
    <small title="<?= e((string)$m['original']) ?>"><?= e(mb_strimwidth((string)$m['original'], 0, 28, '…')) ?></small>
    <small class="muted"><?= e(human_size((int)$m['size'])) ?><?= $m['width'] ? ' · ' . (int)$m['width'] . '×' . (int)$m['height'] : '' ?></small>
    <div class="row-btns">
      <button type="button" class="btn sm ghost" data-copy="<?= e(media($m['file'])) ?>">Salin alamat</button>
      <form method="post" action="<?= e(url('admin/media/delete/' . $m['id'])) ?>" data-confirm="Hapus file ini? Halaman yang memakainya akan kehilangan gambar."><?= csrf_field() ?><button class="btn sm danger">Hapus</button></form>
    </div>
  </div>
<?php endforeach ?>
</div>
