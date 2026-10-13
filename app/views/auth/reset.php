<div class="card narrow"><h2>Nueva contraseña</h2>
  <?php if ($err): ?><div class="alert a-error"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <label>Nueva contraseña<input type="password" name="password" required></label>
    <label>Repite la contraseña<input type="password" name="password2" required></label>
    <button class="btn">Guardar</button>
  </form>
</div>
