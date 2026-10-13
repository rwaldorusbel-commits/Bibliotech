<div class="card narrow"><h2>Parámetros generales</h2>
<form method="post"><?= csrf_field() ?>
<?php foreach ($settings as $s): ?><label><?= e($s['descripcion']) ?><input name="<?= e($s['clave']) ?>" type="number" step="0.01" min="0" value="<?= e($s['valor']) ?>"></label><?php endforeach; ?>
<button class="btn">Guardar</button></form></div>
