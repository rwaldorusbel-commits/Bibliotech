<h1>Gestión de <?= e($label) ?></h1>
<div class="card narrow wide">
<form method="post" class="inline-form"><?= csrf_field() ?><input name="nombre" placeholder="Nuevo nombre" required><button class="btn">Agregar</button></form>
<table class="tbl"><thead><tr><th>Nombre</th><th>Libros</th><th></th></tr></thead><tbody>
<?php foreach ($items as $i): ?>
<tr><td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="editar_id" value="<?= $i['id'] ?>"><input name="nombre" value="<?= e($i['nombre']) ?>"><button class="btn btn-sm btn-alt">Renombrar</button></form></td>
<td><?= (int)$i['usos'] ?></td>
<td><form method="post" class="inline" data-confirm="¿Eliminar? Los libros asociados quedarán sin <?= $route === 'autores' ? 'autor' : 'categoría' ?>."><?= csrf_field() ?><button class="btn btn-sm btn-danger" name="borrar" value="<?= $i['id'] ?>">Eliminar</button></form></td></tr>
<?php endforeach; ?></tbody></table></div>
