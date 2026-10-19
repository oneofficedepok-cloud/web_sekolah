<?php
$locs = ['main' => 'Menu utama', 'top' => 'Bilah atas', 'footer' => 'Footer (tautan cepat)'];
$tops = array_values(array_filter($all, fn($m) => !$m['parent_id']));
$kids = [];
foreach ($all as $m) if ($m['parent_id']) $kids[(int)$m['parent_id']][] = $m;
$e = $edit ?? ['id' => 0, 'label' => '', 'type' => 'page', 'page_id' => null, 'url' => '', 'parent_id' => null, 'location' => $loc, 'visible' => 1, 'new_tab' => 0];
$target = function ($m) {
    return match ($m['type']) {
        'page' => 'Halaman: ' . ($m['page_title'] ?? '(hilang)'),
        'category' => 'Kategori berita: ' . $m['url'],
        'album' => 'Album: ' . $m['url'],
        default => $m['url'],
    };
};
$rowHtml = function ($m, bool $child) use ($target, $loc) {
    ?>
    <li class="menu-row<?= $child ? ' child' : '' ?><?= $m['visible'] ? '' : ' hidden-item' ?>">
      <span class="menu-label"><strong><?= e($m['label']) ?></strong><small><?= e($target($m)) ?><?= $m['visible'] ? '' : ' · disembunyikan' ?></small></span>
      <span class="actions">
        <form method="post" action="<?= e(url('admin/menus/move/' . $m['id'])) ?>"><?= csrf_field() ?><input type="hidden" name="dir" value="up"><button class="btn sm ghost" aria-label="Naikkan <?= e($m['label']) ?>">↑</button></form>
        <form method="post" action="<?= e(url('admin/menus/move/' . $m['id'])) ?>"><?= csrf_field() ?><input type="hidden" name="dir" value="down"><button class="btn sm ghost" aria-label="Turunkan <?= e($m['label']) ?>">↓</button></form>
        <a class="btn sm ghost" href="<?= e(url('admin/menus/edit/' . $m['id'] . '?lokasi=' . $loc)) ?>">Ubah</a>
        <form method="post" action="<?= e(url('admin/menus/delete/' . $m['id'])) ?>" data-confirm="Hapus menu &quot;<?= e($m['label']) ?>&quot;? Halamannya tidak ikut terhapus."><?= csrf_field() ?><input type="hidden" name="location" value="<?= e($loc) ?>"><button class="btn sm danger">Hapus</button></form>
      </span>
    </li>
    <?php
};
?>
<div class="chips">
  <?php foreach ($locs as $k => $l): ?><a class="chip<?= $loc === $k ? ' on' : '' ?>" href="<?= e(url('admin/menus?lokasi=' . $k)) ?>"><?= e($l) ?></a><?php endforeach ?>
</div>
<div class="two-col wide-left">
  <div class="panel">
    <h2><?= e($locs[$loc]) ?></h2>
    <ul class="menu-tree">
      <?php foreach ($tops as $m): $rowHtml($m, false); foreach ($kids[(int)$m['id']] ?? [] as $c) $rowHtml($c, true); endforeach ?>
      <?php if (!$tops): ?><li class="muted">Belum ada menu di sini.</li><?php endif ?>
    </ul>
    <p class="muted small">Menu bertingkat dua: menu utama dan submenunya. Submenu tampil sebagai daftar tarik-turun.</p>
  </div>
  <form class="panel form" method="post" action="<?= e(url('admin/menus/save/' . (int)$e['id'])) ?>" data-menu-form>
    <?= csrf_field() ?>
    <h2><?= $e['id'] ? 'Ubah menu' : 'Tambah menu' ?></h2>
    <label>Teks menu<input name="label" value="<?= e($e['label']) ?>" required></label>
    <label>Menuju ke<select name="type" data-menu-type>
      <option value="page"<?= $e['type'] === 'page' ? ' selected' : '' ?>>Halaman di website ini</option>
      <option value="category"<?= $e['type'] === 'category' ? ' selected' : '' ?>>Kategori berita</option>
      <option value="album"<?= $e['type'] === 'album' ? ' selected' : '' ?>>Album foto</option>
      <option value="url"<?= $e['type'] === 'url' ? ' selected' : '' ?>>Tautan bebas / bagian halaman</option>
    </select></label>
    <label data-for="page">Halaman<select name="page_id">
      <?php foreach ($pages as $p): ?><option value="<?= (int)$p['id'] ?>"<?= (int)$e['page_id'] === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['title']) ?><?= $p['status'] !== 'published' ? ' (draf)' : '' ?></option><?php endforeach ?>
    </select><a class="small" href="<?= e(url('admin/pages/new')) ?>">+ Buat halaman baru</a></label>
    <label data-for="category">Kategori<select name="category">
      <?php foreach ($categories as $c): ?><option value="<?= e($c['slug']) ?>"<?= $e['url'] === $c['slug'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach ?>
    </select></label>
    <label data-for="album">Album<select name="album">
      <?php foreach ($albums as $a): ?><option value="<?= e($a['slug']) ?>"<?= $e['url'] === $a['slug'] ? ' selected' : '' ?>><?= e($a['title']) ?></option><?php endforeach ?>
    </select></label>
    <label data-for="url">Alamat<input name="url" value="<?= $e['type'] === 'url' ? e($e['url']) : '' ?>" placeholder="https://... atau /profil#sejarah"></label>
    <label>Lokasi<select name="location">
      <?php foreach ($locs as $k => $l): ?><option value="<?= $k ?>"<?= $e['location'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach ?>
    </select></label>
    <label>Induk (untuk submenu)<select name="parent_id"><option value="0">— Tidak ada (menu teratas) —</option>
      <?php foreach ($tops as $m): if ((int)$m['id'] === (int)$e['id']) continue; ?><option value="<?= (int)$m['id'] ?>"<?= (int)$e['parent_id'] === (int)$m['id'] ? ' selected' : '' ?>><?= e($m['label']) ?></option><?php endforeach ?>
    </select></label>
    <label class="check"><input type="checkbox" name="visible" value="1"<?= $e['visible'] ? ' checked' : '' ?>> Tampilkan</label>
    <label class="check"><input type="checkbox" name="new_tab" value="1"<?= $e['new_tab'] ? ' checked' : '' ?>> Buka di tab baru</label>
    <div class="row-btns"><button class="btn" type="submit">Simpan menu</button><?php if ($e['id']): ?><a class="btn ghost" href="<?= e(url('admin/menus?lokasi=' . $loc)) ?>">Batal</a><?php endif ?></div>
  </form>
</div>
