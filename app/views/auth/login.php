<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">📚</div>
    <h1 class="login-title">BiblioTech</h1>
    <p class="login-sub">Sistema de Gestión de Biblioteca</p>
    <p class="login-hint">Ingresa tus credenciales para continuar</p>
    <?php if (!empty($error)): ?>
      <div class="alert a-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= url('auth/login') ?>" class="login-form">
      <?= csrf_field() ?>
      <div class="login-field">
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" placeholder="tucorreo@bibliotech.test" value="<?= old('email') ?>" required autofocus>
      </div>
      <div class="login-field">
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" placeholder="••••••••" required>
      </div>
      <button class="btn login-btn" type="submit">Entrar</button>
    </form>
    <div class="login-links">
      <a href="<?= url('auth/forgot') ?>">¿Olvidaste tu contraseña?</a>
      <span class="sep">·</span>
      <a href="<?= url('auth/register') ?>">Crear cuenta</a>
    </div>
  </div>
</div>