<div class="card-grid">
<?php foreach ($list as $a): ?>
  <a class="album-card" href="<?= e(url('admin/albums/edit/' . $a['id'])) ?>">
    <span class="album-thumb"><?php $c = $a['cover'] ?: $a['first']; if ($c): ?><img src="<?= e(media($c)) ?>" alt=""><?php endif ?></span>
    <strong><?= e($a['title']) ?></strong>
    <small><?= (int)$a['n'] ?> foto · <?= e(tgl($a['event_date'])) ?></small>
  </a>
<?php endforeach ?>
<?php if (!$list): ?><p class="muted">Belum ada album. Klik "+ Album baru".</p><?php endif ?>
</div>
