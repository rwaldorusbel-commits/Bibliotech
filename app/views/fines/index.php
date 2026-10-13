<h1>Mis multas</h1>
<div class="card total">Total pendiente: <strong><?= money($total) ?></strong></div>
<div class="card"><table class="tbl"><thead><tr><th>Libro</th><th>Motivo</th><th>Días de retraso</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($fines as $f): ?>
<tr><td><?= e($f['titulo'] ?? '—') ?></td><td><?= ['retraso' => 'Retraso', 'dano' => 'Daño', 'perdida' => 'Pérdida'][$f['motivo']] ?></td><td><?= (int)$f['dias_retraso'] ?></td><td><?= money($f['monto']) ?></td><td><?= badge($f['estado']) ?></td>
<td><?php if ($f['estado'] === 'pagada'): ?><a href="<?= url('multas/comprobante/' . $f['id']) ?>">Comprobante</a><?php endif; ?></td></tr>
<?php endforeach; if (!$fines): ?><tr><td colspan="6" class="center">No tienes multas. ¡Buen trabajo!</td></tr><?php endif; ?>
</tbody></table></div>
