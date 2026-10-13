<div class="card narrow">
  <h2>Iniciar sesión</h2>
  <?php if ($error): ?><div class="alert a-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label>Correo electrónico<input type="email" name="email" value="<?= old('email') ?>" required autofocus></label>
    <label>Contraseña<input type="password" name="password" required></label>
    <button class="btn">Entrar</button>
  </form>
  <p class="muted"><a href="<?= url('auth/forgot') ?>">¿Olvidaste tu contraseña?</a> · <a href="<?= url('auth/register') ?>">Crear cuenta</a></p>
</div>
