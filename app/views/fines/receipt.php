<div class="card narrow receipt">
  <h2>Comprobante de pago</h2>
  <table class="kv">
    <tr><th>N.º</th><td><?= e($f['comprobante']) ?></td></tr>
    <tr><th>Fecha</th><td><?= fdt($f['paid_at']) ?></td></tr>
    <tr><th>Usuario</th><td><?= e($f['usuario']) ?> (<?= e($f['documento']) ?>)</td></tr>
    <tr><th>Libro</th><td><?= e($f['titulo'] ?? '—') ?></td></tr>
    <tr><th>Concepto</th><td><?= ['retraso' => 'Multa por retraso (' . (int)$f['dias_retraso'] . ' días)', 'dano' => 'Multa por daño', 'perdida' => 'Multa por pérdida'][$f['motivo']] ?></td></tr>
    <tr><th>Monto</th><td><strong><?= money($f['monto']) ?></strong></td></tr>
    <tr><th>Cobrado por</th><td><?= e($f['cobrador'] ?? '—') ?></td></tr>
  </table>
  <button class="btn noprint" onclick="window.print()">Imprimir</button>
</div>
