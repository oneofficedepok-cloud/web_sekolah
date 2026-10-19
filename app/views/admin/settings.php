<form method="post" class="settings" action="<?= e(url('admin/settings')) ?>" data-dirty-guard>
  <?= csrf_field() ?>
  <?php foreach ($groups as $g => $fields): ?>
  <div class="panel form">
    <h2><?= e($g) ?></h2>
    <div class="grid2">
    <?php foreach ($fields as [$k, $label, $type]): $v = (string)($vals[$k] ?? ''); ?>
      <?php if ($type === 'image'): ?>
        <div class="field"><span class="label"><?= e($label) ?></span><div class="img-field" data-img-field><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"></div></div>
      <?php elseif ($type === 'textarea'): ?>
        <label><?= e($label) ?><textarea name="<?= e($k) ?>" rows="3"><?= e($v) ?></textarea></label>
      <?php elseif ($type === 'color'): ?>
        <label><?= e($label) ?><span class="color-row"><input type="color" value="<?= e($v ?: '#0E4D3A') ?>" data-color-for="<?= e($k) ?>"><input name="<?= e($k) ?>" value="<?= e($v) ?>" placeholder="#0E4D3A"></span></label>
      <?php else: ?>
        <label><?= e($label) ?><input name="<?= e($k) ?>" value="<?= e($v) ?>"></label>
      <?php endif ?>
    <?php endforeach ?>
    </div>
  </div>
  <?php endforeach ?>
  <div class="sticky-save"><button class="btn" type="submit">Simpan pengaturan</button></div>
</form>
