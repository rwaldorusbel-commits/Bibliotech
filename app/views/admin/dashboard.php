<h1>Panel de control</h1>
<div class="grid4">
  <div class="card stat"><span><?= $stats['prestados'] ?></span>Libros prestados</div>
  <div class="card stat"><span><?= $stats['reservas'] ?></span>Reservas activas</div>
  <div class="card stat"><span><?= $stats['usuarios'] ?></span>Usuarios registrados</div>
  <div class="card stat"><span><?= money($stats['multas']) ?></span>Multas pendientes</div>
</div>
<div class="grid2">
  <div class="card"><h3>⚠️ Alertas de stock bajo</h3>
    <?php if (!$low): ?><p class="muted">Sin alertas.</p><?php endif; ?>
    <ul><?php foreach ($low as $b): ?><li><a href="<?= url('catalogo/ver/' . $b['id']) ?>"><?= e($b['titulo']) ?></a> — <?= (int)$b['copies_available'] ?>/<?= (int)$b['copies_total'] ?> (mínimo <?= (int)$b['stock_min'] ?>)</li><?php endforeach; ?></ul></div>
  <div class="card"><h3>⏰ Préstamos vencidos</h3>
    <?php if (!$overdue): ?><p class="muted">Ninguno.</p><?php endif; ?>
    <ul><?php foreach ($overdue as $o): ?><li><?= e($o['usuario']) ?> — <?= e($o['titulo']) ?> (<?= (int)$o['dias'] ?> d)</li><?php endforeach; ?></ul></div>
</div>
