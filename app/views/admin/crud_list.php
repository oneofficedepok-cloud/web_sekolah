<div class="panel">
<table class="tbl">
<thead><tr><?php foreach ($d['cols'] as $c => $l): ?><th><?= e($l) ?></th><?php endforeach ?><th class="right">Aksi</th></tr></thead>
<tbody>
<?php foreach ($list as $r): ?>
<tr>
  <?php $first = true; foreach ($d['cols'] as $c => $l): $v = (string)$r[$c]; ?>
    <td<?= in_array($c, ['photo'], true) ? ' class="thumb-cell"' : '' ?>><?php
      if ($c === 'photo') echo $v ? '<img src="' . e(media($v)) . '" alt="">' : '';
      elseif ($c === 'event_date') echo e(tgl($v, true));
      elseif ($c === 'file') echo '<a href="' . e(media($v)) . '" target="_blank" rel="noopener">' . e(basename($v)) . '</a>';
      elseif ($c === 'youtube') echo '<a href="' . e($v) . '" target="_blank" rel="noopener">' . e(mb_strimwidth($v, 0, 40, '…')) . '</a>';
      elseif ($first) echo '<a href="' . e(url('admin/' . $key . '/edit/' . $r['id'])) . '"><strong>' . e($v) . '</strong></a>';
      else echo e($v);
      if ($c !== 'photo') $first = false; ?></td>
  <?php endforeach ?>
  <td class="right actions"><a class="btn sm ghost" href="<?= e(url('admin/' . $key . '/edit/' . $r['id'])) ?>">Ubah</a>
    <form method="post" action="<?= e(url('admin/' . $key . '/delete/' . $r['id'])) ?>" data-confirm="Hapus data ini?"><?= csrf_field() ?><button class="btn sm danger">Hapus</button></form></td>
</tr>
<?php endforeach ?>
<?php if (!$list): ?><tr><td colspan="<?= count($d['cols']) + 1 ?>" class="muted">Belum ada data.</td></tr><?php endif ?>
</tbody>
</table>
</div>
