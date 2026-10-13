<div class="between"><h1>Préstamos activos</h1><a class="btn" href="<?= url('prestamos/nuevo') ?>">+ Registrar préstamo</a></div>
<div class="card"><table class="tbl"><thead><tr><th>Usuario</th><th>Libro</th><th>Vence</th><th>Estado</th><th>Registrar devolución</th></tr></thead><tbody>
<?php foreach ($loans as $l): ?>
<tr><td><?= e($l['usuario']) ?><br><small class="muted"><?= e($l['documento']) ?></small></td><td><?= e($l['titulo']) ?></td><td><?= fd($l['due_date']) ?></td>
<td><?= $l['vencido'] ? '<span class="badge b-vencida">Vencido</span>' : '<span class="badge b-confirmada">En plazo</span>' ?></td>
<td><form method="post" action="<?= url('prestamos/devolver/' . $l['id']) ?>" class="inline-form" data-confirm="¿Registrar la devolución?"><?= csrf_field() ?>
  <select name="estado"><option value="bueno">Buen estado</option><option value="danado">Dañado</option><option value="perdido">Perdido</option></select>
  <button class="btn btn-sm">Devolver</button></form></td></tr>
<?php endforeach; if (!$loans): ?><tr><td colspan="5" class="center">No hay préstamos activos.</td></tr><?php endif; ?>
</tbody></table></div>
