<?php $p = $post ?? ['id' => 0, 'title' => '', 'slug' => '', 'category_id' => null, 'cover' => '', 'excerpt' => '', 'body' => '', 'status' => 'published', 'featured' => 0, 'published_at' => now()]; ?>
<form method="post" class="editor" action="<?= e(url('admin/posts/edit/' . (int)$p['id'])) ?>" data-dirty-guard>
  <?= csrf_field() ?>
  <div class="editor-main">
    <div class="panel form">
      <label>Judul<input name="title" value="<?= e($p['title']) ?>" required data-slug-source></label>
      <label>Ringkasan (tampil di daftar berita)<textarea name="excerpt" rows="2" maxlength="400"><?= e((string)$p['excerpt']) ?></textarea></label>
      <div class="field"><span class="label">Isi berita</span>
        <div class="rte-wrap" data-rte><textarea name="body" hidden><?= e((string)$p['body']) ?></textarea></div>
      </div>
    </div>
  </div>
  <aside class="editor-side">
    <div class="panel form sticky">
      <label>Status<select name="status">
        <option value="published"<?= $p['status'] === 'published' ? ' selected' : '' ?>>Terbit</option>
        <option value="draft"<?= $p['status'] !== 'published' ? ' selected' : '' ?>>Draf</option>
      </select></label>
      <label>Tanggal terbit<input type="datetime-local" name="published_at" value="<?= e(str_replace(' ', 'T', substr((string)$p['published_at'], 0, 16))) ?>"></label>
      <label>Kategori<select name="category_id"><option value="">— Tanpa kategori —</option>
        <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"<?= (int)$p['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach ?>
      </select></label>
      <div class="field"><span class="label">Foto sampul</span><div class="img-field" data-img-field><input type="hidden" name="cover" value="<?= e((string)$p['cover']) ?>"></div></div>
      <label class="check"><input type="checkbox" name="featured" value="1"<?= $p['featured'] ? ' checked' : '' ?>> Jadikan berita utama</label>
      <label>Alamat<input name="slug" value="<?= e($p['slug']) ?>" placeholder="otomatis dari judul" data-slug-target></label>
      <button class="btn block" type="submit">Simpan</button>
      <?php if ($p['id']): ?><a class="btn ghost block" href="<?= e(url('berita/' . $p['slug'])) ?>" target="_blank" rel="noopener">Lihat ↗</a><?php endif ?>
    </div>
  </aside>
</form>
