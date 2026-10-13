<div class="between"><h1>Usuarios</h1>
<form method="get" class="inline-form"><input name="q" placeholder="Buscar..." value="<?= e($_GET['q'] ?? '') ?>"><button class="btn btn-sm">Buscar</button></form></div>
<div class="card"><table class="tbl"><thead><tr><th>Nombre</th><th>Documento</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($users as $x): ?>
<tr><td><?= e($x['nombres'] . ' ' . $x['apellidos']) ?></td><td><?= e($x['documento']) ?></td><td><?= e($x['email']) ?></td><td><?= e($x['rol']) ?></td><td><?= badge($x['estado']) ?></td>
<td><?php if ($x['id'] != Auth::user()['id']): ?>
<form method="post" action="<?= url('admin/usuario-estado/' . $x['id']) ?>" class="inline-form"><?= csrf_field() ?>
  <select name="rol"><?php foreach (['estudiante', 'bibliotecario', 'administrador'] as $r): ?><option <?= $x['rol'] === $r ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select>
  <select name="estado"><?php foreach (['activo', 'suspendido', 'inactivo'] as $s): ?><option <?= $x['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
  <button class="btn btn-sm btn-alt">Guardar</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div>
