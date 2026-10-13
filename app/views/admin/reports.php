<div class="between noprint"><h1>Reportes</h1>
<form method="get" class="inline-form"><input type="date" name="desde" value="<?= e($from) ?>"><input type="date" name="hasta" value="<?= e($to) ?>"><button class="btn btn-sm">Aplicar</button>
<button type="button" class="btn btn-sm btn-alt" onclick="window.print()">Imprimir / Guardar como PDF</button></form></div>
<p class="muted">Periodo: <?= fd($from) ?> a <?= fd($to) ?></p>
<?php foreach ($data as $key => [$title, $head, $rows]): ?>
<div class="card"><div class="between"><h3><?= e($title) ?></h3>
<a class="btn btn-sm btn-alt noprint" href="<?= url('admin/exportar/' . $key . '?desde=' . $from . '&hasta=' . $to) ?>">Exportar a Excel (CSV)</a></div>
<table class="tbl"><thead><tr><?php foreach ($head as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><?php foreach ($r as $c): ?><td><?= e($c) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="<?= count($head) ?>" class="center muted">Sin datos en el periodo.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endforeach; ?>
