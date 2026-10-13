<div class="card narrow"><h2>Recuperar contraseña</h2>
  <form method="post"><?= csrf_field() ?>
    <label>Correo electrónico<input type="email" name="email" required autofocus></label>
    <button class="btn">Enviar enlace</button>
  </form>
  <p class="muted">En desarrollo, los correos se guardan en <code>storage/mail.log</code>.</p>
</div>
