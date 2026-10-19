<form class="panel form" method="post" action="<?= e(url('admin/pages/new')) ?>">
  <?= csrf_field() ?>
  <div class="grid2">
    <label>Judul halaman<input name="title" required placeholder="Contoh: Ekstrakurikuler" data-slug-source></label>
    <label>Alamat (opsional)<input name="slug" placeholder="otomatis dari judul" data-slug-target></label>
  </div>

  <fieldset>
    <legend>Mulai dari</legend>
    <div class="tpl-grid">
      <?php $first = !$dup; foreach ($templates as $k => $t): ?>
      <label class="tpl">
        <input type="radio" name="source" value="<?= e($k) ?>"<?= $first ? ' checked' : '' ?>>
        <span><strong><?= e($t['label']) ?></strong><small><?= e($t['desc']) ?></small><em><?= count($t['blocks']) ?> blok</em></span>
      </label>
      <?php $first = false; endforeach ?>
    </div>
    <label class="tpl dup">
      <input type="radio" name="source" value="dup"<?= $dup ? ' checked' : '' ?> data-dup-radio>
      <span><strong>Duplikat halaman yang sudah ada</strong><small>Salin seluruh susunan dan isi blok dari halaman lain, lalu ubah seperlunya.</small>
        <select name="dup_page" data-dup-select aria-label="Halaman yang diduplikat">
          <?php foreach ($pages as $p): ?><option value="page:<?= (int)$p['id'] ?>"<?= $dup === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['title']) ?></option><?php endforeach ?>
        </select>
      </span>
    </label>
  </fieldset>

  <fieldset>
    <legend>Menu</legend>
    <label class="check"><input type="checkbox" name="add_menu" value="1" checked> Tambahkan ke menu utama</label>
    <label>Letakkan di bawah menu<select name="menu_parent"><option value="0">— Menu utama (paling atas) —</option>
      <?php foreach ($parents as $m): ?><option value="<?= (int)$m['id'] ?>">Submenu dari: <?= e($m['label']) ?></option><?php endforeach ?></select></label>
  </fieldset>
  <button class="btn" type="submit">Buat halaman</button>
</form>
