<form class="panel form narrow" method="post" enctype="multipart/form-data" action="<?= e(url('admin/' . $key . '/edit/' . (int)($rec['id'] ?? 0))) ?>" data-dirty-guard>
  <?= csrf_field() ?>
  <?php foreach ($d['fields'] as $f): [$name, $label, $type] = $f; $v = (string)($rec[$name] ?? ''); $req = !empty($f[3]); ?>
    <?php if ($type === 'textarea'): ?>
      <label><?= e($label) ?><textarea name="<?= e($name) ?>" rows="4"<?= $req ? ' required' : '' ?>><?= e($v) ?></textarea></label>
    <?php elseif ($type === 'image'): ?>
      <div class="field"><span class="label"><?= e($label) ?></span><div class="img-field" data-img-field><input type="hidden" name="<?= e($name) ?>" value="<?= e($v) ?>"></div></div>
    <?php elseif ($type === 'file'): ?>
      <label><?= e($label) ?><?= $v ? ' (saat ini: <a href="' . e(media($v)) . '" target="_blank">' . e(basename($v)) . '</a>, pilih file baru untuk mengganti)' : '' ?><input type="file" name="<?= e($name) ?>"<?= $req && !$v ? ' required' : '' ?>></label>
    <?php else: ?>
      <label><?= e($label) ?><input type="<?= $type === 'date' ? 'date' : ($type === 'number' ? 'number' : 'text') ?>" name="<?= e($name) ?>" value="<?= e($v) ?>"<?= $req ? ' required' : '' ?>></label>
    <?php endif ?>
  <?php endforeach ?>
  <button class="btn" type="submit">Simpan</button>
</form>
<?php if ($rec): ?>
<form method="post" action="<?= e(url('admin/' . $key . '/delete/' . $rec['id'])) ?>" data-confirm="Hapus <?= e($d['one']) ?> ini?"><?= csrf_field() ?><button class="btn danger">Hapus <?= e($d['one']) ?></button></form>
<?php endif ?>
