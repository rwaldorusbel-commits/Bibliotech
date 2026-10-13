<h1>Mis reservas</h1>
<form class="filters card" method="get" action="<?= url('reservas') ?>">
  <select name="estado"><option value="">Todos los estados</option><?php foreach (['pendiente', 'confirmada', 'recogida', 'vencida', 'cancelada'] as $s): ?><option value="<?= $s ?>" <?= $estado === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <select name="orden"><option value="desc" <?= $orden === 'desc' ? 'selected' : '' ?>>Más recientes primero</option><option value="asc" <?= $orden === 'asc' ? 'selected' : '' ?>>Más antiguas primero</option></select>
  <button class="btn">Filtrar</button>
</form>
<?php if (!$reservations): ?>
  <div class="card center"><p>Aún no tienes reservas.</p><a class="btn" href="<?= url('catalogo') ?>">Explorar el catálogo</a></div>
<?php endif; ?>
<div class="cards">
<?php foreach ($reservations as $r): ?>
  <div class="card rcard">
    <img src="<?= e(cover_url($r)) ?>" alt="Portada de <?= e($r['titulo']) ?>">
    <div>
      <h3><?= e($r['titulo']) ?></h3>
      <p class="muted"><?= e($r['autor'] ?? 'Autor desconocido') ?></p>
      <p><?= badge($r['estado']) ?></p>
      <?php if ($r['estado'] === 'pendiente'): ?><p class="countdown" data-secs="<?= max(0, (int)$r['secs_left']) ?>"></p><?php endif; ?>
      <p class="muted">Reservado el <?= fdt($r['created_at']) ?></p>
      <a class="btn btn-sm btn-alt" href="<?= url('catalogo/ver/' . $r['book_id']) ?>">Ver libro</a>
      <?php if ($r['estado'] === 'pendiente'): ?>
        <form method="post" action="<?= url('reservas/cancelar/' . $r['id']) ?>" class="inline" data-confirm="¿Seguro que deseas cancelar esta reserva?"><?= csrf_field() ?><button class="btn btn-sm btn-danger">Cancelar</button></form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
