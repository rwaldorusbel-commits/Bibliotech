<h1>Bitácora de acciones críticas</h1>
<div class="card"><table class="tbl"><thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= fdt($r['created_at']) ?></td><td><?= e($r['usuario'] ?? 'Sistema') ?></td><td><?= e($r['accion']) ?></td><td><?= e($r['detalle']) ?></td><td><?= e($r['ip']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
