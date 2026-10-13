<h1>Control de stock</h1>
<?php if ($low): ?><div class="alert a-error">⚠️ <?= count($low) ?> libro(s) con stock en el nivel mínimo o por debajo: <?= e(implode(', ', array_column($low, 'titulo'))) ?></div><?php endif; ?>
<div class="card"><h3>Historial de movimientos</h3><table class="tbl"><thead><tr><th>Fecha</th><th>Libro</th><th>Tipo</th><th>Cant.</th><th>Usuario</th><th>Nota</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= fdt($r['created_at']) ?></td><td><?= e($r['titulo']) ?></td><td><?= e($r['tipo']) ?></td><td><?= (int)$r['cantidad'] ?></td><td><?= e($r['usuario'] ?? 'Sistema') ?></td><td><?= e($r['nota']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
