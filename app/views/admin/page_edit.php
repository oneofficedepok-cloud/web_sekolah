<form method="post" class="editor" id="page-form" action="<?= e(url('admin/pages/edit/' . $page['id'])) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="blocks" id="blocks-json">
  <div class="editor-main">
    <div class="panel form">
      <label>Judul halaman<input name="title" value="<?= e($page['title']) ?>" required></label>
    </div>
    <div class="blocks-bar">
      <h2>Isi halaman</h2>
      <span class="muted">Klik judul blok untuk membuka. Seret ⠿ atau pakai tombol ↑ ↓ untuk mengurutkan.</span>
      <button type="button" class="link-btn" data-collapse-all>Tutup semua</button>
    </div>
    <div id="blocks" class="blocks"></div>
    <div class="add-block">
      <button type="button" class="btn" data-add-block>+ Tambah blok</button>
    </div>
  </div>
  <aside class="editor-side">
    <div class="panel form sticky">
      <label>Status<select name="status">
        <option value="published"<?= $page['status'] === 'published' ? ' selected' : '' ?>>Terbit (tampil untuk umum)</option>
        <option value="draft"<?= $page['status'] !== 'published' ? ' selected' : '' ?>>Draf (hanya admin)</option>
      </select></label>
      <?php if (!$page['is_home']): ?>
      <label>Alamat halaman<span class="prefix-input"><span><?= e(url('')) ?></span><input name="slug" value="<?= e($page['slug']) ?>"></span></label>
      <?php else: ?>
      <input type="hidden" name="slug" value="<?= e($page['slug']) ?>"><p class="muted">Ini halaman beranda.</p>
      <?php endif ?>
      <label>Deskripsi untuk Google<textarea name="meta_description" rows="3" maxlength="300"><?= e((string)$page['meta_description']) ?></textarea></label>
      <button class="btn block" type="submit">Simpan</button>
      <a class="btn ghost block" href="<?= e(url($page['is_home'] ? '' : $page['slug'])) ?>" target="_blank" rel="noopener">Pratinjau ↗</a>
      <a class="btn ghost block" href="<?= e(url('admin/pages/new?dari=' . $page['id'])) ?>">Duplikat halaman</a>
      <p class="muted small">Simpan dulu sebelum pratinjau. Perubahan yang belum disimpan akan diingatkan saat menutup halaman.</p>
    </div>
  </aside>
</form>
<div class="modal" id="block-picker" hidden role="dialog" aria-modal="true" aria-labelledby="bp-title">
  <div class="modal-box">
    <div class="modal-head"><h2 id="bp-title">Pilih jenis blok</h2><button type="button" class="link-btn" data-close>Tutup</button></div>
    <div class="picker" data-picker></div>
  </div>
</div>
<script>
window.BLOCK_SCHEMA = <?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
window.PAGE_BLOCKS = <?= json_encode(json_decode((string)$page['blocks'], true) ?: [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
