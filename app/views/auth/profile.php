<?php function pe($err, $k) { return isset($err[$k]) ? '<small class="err">' . e($err[$k]) . '</small>' : ''; } ?>
<div class="card narrow"><h2>Mis datos</h2>
  <form method="post"><?= csrf_field() ?>
    <div class="row2">
      <label>Nombres<input name="nombres" value="<?= old('nombres', $u['nombres']) ?>"><?= pe($err, 'nombres') ?></label>
      <label>Apellidos<input name="apellidos" value="<?= old('apellidos', $u['apellidos']) ?>"><?= pe($err, 'apellidos') ?></label>
    </div>
    <label>Documento<input value="<?= e($u['documento']) ?>" disabled></label>
    <label>Correo<input value="<?= e($u['email']) ?>" disabled></label>
    <label>Teléfono<input name="telefono" value="<?= old('telefono', $u['telefono']) ?>"><?= pe($err, 'telefono') ?></label>
    <h4>Cambiar contraseña (opcional)</h4>
    <div class="row2">
      <label>Contraseña actual<input type="password" name="current_password"><?= pe($err, 'current_password') ?></label>
      <label>Nueva contraseña<input type="password" name="new_password"><?= pe($err, 'new_password') ?></label>
    </div>
    <button class="btn">Guardar cambios</button>
  </form>
</div>
