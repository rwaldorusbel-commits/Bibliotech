<?php function fe($err, $k) { return isset($err[$k]) ? '<small class="err">' . e($err[$k]) . '</small>' : ''; } ?>
<div class="card narrow">
  <h2>Crear cuenta</h2>
  <form method="post" novalidate><?= csrf_field() ?>
    <div class="row2">
      <label>Nombres<input name="nombres" value="<?= old('nombres') ?>" required><?= fe($err, 'nombres') ?></label>
      <label>Apellidos<input name="apellidos" value="<?= old('apellidos') ?>" required><?= fe($err, 'apellidos') ?></label>
    </div>
    <label>Documento de identidad<input name="documento" value="<?= old('documento') ?>" required><?= fe($err, 'documento') ?></label>
    <label>Correo electrónico<input type="email" name="email" value="<?= old('email') ?>" required><?= fe($err, 'email') ?></label>
    <label>Teléfono<input name="telefono" value="<?= old('telefono') ?>" required><?= fe($err, 'telefono') ?></label>
    <div class="row2">
      <label>Contraseña<input type="password" name="password" required><?= fe($err, 'password') ?></label>
      <label>Repite la contraseña<input type="password" name="password2" required><?= fe($err, 'password2') ?></label>
    </div>
    <button class="btn">Registrarme</button>
  </form>
  <p class="muted">¿Ya tienes cuenta? <a href="<?= url('auth/login') ?>">Inicia sesión</a></p>
</div>
