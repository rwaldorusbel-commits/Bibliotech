<h1>Mis préstamos</h1>
<div class="card"><table class="tbl"><thead><tr><th>Libro</th><th>Prestado</th><th>Devolver el</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($loans as $l): ?>
<tr><td><?= e($l['titulo']) ?><br><small class="muted"><?= e($l['autor']) ?></small></td><td><?= fd($l['loaned_at']) ?></td><td><?= fd($l['due_date']) ?></td>
<td><?= $l['returned_at'] ? 'Devuelto ' . fd($l['returned_at']) : ($l['vencido'] ? '<span class="badge b-vencida">Vencido</span>' : '<span class="badge b-confirmada">Activo</span>') ?></td>
<td><?php if (!$l['returned_at']): ?><form method="post" action="<?= url('prestamos/renovar/' . $l['id']) ?>" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-alt">Renovar</button></form> <small class="muted"><?= (int)$l['renovaciones'] ?> renovación(es)</small><?php endif; ?></td></tr>
<?php endforeach; if (!$loans): ?><tr><td colspan="5" class="center">Aún no tienes préstamos.</td></tr><?php endif; ?>
</tbody></table></div>
