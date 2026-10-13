<h1>Reservas activas</h1>
<div class="card"><table class="tbl"><thead><tr><th>Usuario</th><th>Documento</th><th>Libro</th><th>Estado</th><th>Vence</th><th></th></tr></thead><tbody>
<?php foreach ($reservations as $r): ?>
<tr><td><?= e($r['usuario']) ?></td><td><?= e($r['documento']) ?></td><td><?= e($r['titulo']) ?></td><td><?= badge($r['estado']) ?></td>
<td><span class="countdown" data-secs="<?= max(0, (int)$r['secs_left']) ?>"></span></td>
<td class="actions">
  <?php if ($r['estado'] === 'pendiente'): ?><form method="post" action="<?= url('reservas/confirmar/' . $r['id']) ?>" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-alt">Confirmar</button></form><?php endif; ?>
  <a class="btn btn-sm" href="<?= url('prestamos/nuevo?reserva=' . $r['id']) ?>">Entregar</a>
</td></tr>
<?php endforeach; if (!$reservations): ?><tr><td colspan="6" class="center">No hay reservas activas.</td></tr><?php endif; ?>
</tbody></table></div>
