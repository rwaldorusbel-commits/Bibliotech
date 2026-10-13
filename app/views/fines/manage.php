<h1>Gestión de multas</h1>
<form class="filters card" method="get"><select name="estado" onchange="this.form.submit()"><?php foreach (['pendiente', 'pagada', 'condonada', 'anulada', ''] as $s): ?><option value="<?= $s ?>" <?= $estado === $s ? 'selected' : '' ?>><?= $s ? ucfirst($s) : 'Todas' ?></option><?php endforeach; ?></select></form>
<div class="card"><table class="tbl"><thead><tr><th>Usuario</th><th>Libro</th><th>Motivo</th><th>Monto</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($fines as $f): ?>
<tr><td><?= e($f['usuario']) ?><br><small class="muted"><?= e($f['documento']) ?></small></td><td><?= e($f['titulo'] ?? '—') ?></td>
<td><?= ['retraso' => 'Retraso (' . (int)$f['dias_retraso'] . ' d)', 'dano' => 'Daño', 'perdida' => 'Pérdida'][$f['motivo']] ?></td><td><?= money($f['monto']) ?></td>
<td><?= badge($f['estado']) ?><?= $f['justificacion'] ? '<br><small class="muted">' . e($f['justificacion']) . '</small>' : '' ?></td>
<td class="actions">
<?php if ($f['estado'] === 'pendiente'): ?>
  <form method="post" action="<?= url('multas/pagar/' . $f['id']) ?>" class="inline" data-confirm="¿Registrar el pago de <?= money($f['monto']) ?>?"><?= csrf_field() ?><button class="btn btn-sm">Registrar pago</button></form>
  <?php if (Auth::is('administrador')): ?>
  <form method="post" action="<?= url('multas/anular/' . $f['id']) ?>" class="inline-form"><?= csrf_field() ?>
    <select name="tipo"><option value="condonada">Condonar</option><option value="anulada">Anular</option></select>
    <input name="justificacion" placeholder="Justificación" required minlength="5">
    <button class="btn btn-sm btn-alt">OK</button></form>
  <?php endif; ?>
<?php elseif ($f['estado'] === 'pagada'): ?><a href="<?= url('multas/comprobante/' . $f['id']) ?>">Comprobante</a><?php endif; ?>
</td></tr>
<?php endforeach; if (!$fines): ?><tr><td colspan="6" class="center">Sin multas en este estado.</td></tr><?php endif; ?>
</tbody></table></div>
