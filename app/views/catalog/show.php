<?php [$cls, $lbl] = Book::status($b); ?>
<a href="<?= url('catalogo') ?>">← Volver al catálogo</a>
<div class="card detail">
  <img src="<?= e(cover_url($b)) ?>" alt="Portada">
  <div>
    <h1><?= e($b['titulo']) ?></h1>
    <p class="muted"><?= e($b['autor'] ?? 'Autor desconocido') ?></p>
    <table class="kv">
      <tr><th>Editorial</th><td><?= e($b['editorial'] ?: '—') ?></td></tr>
      <tr><th>Año</th><td><?= e($b['anio'] ?: '—') ?></td></tr>
      <tr><th>ISBN</th><td><?= e($b['isbn'] ?: '—') ?></td></tr>
      <tr><th>Categoría</th><td><?= e($b['categoria'] ?? '—') ?></td></tr>
      <tr><th>Ejemplares</th><td><span class="badge b-<?= $cls ?>"><?= $lbl ?></span> <?= (int)$b['copies_available'] ?> disponibles de <?= (int)$b['copies_total'] ?></td></tr>
    </table>
    <p><?= nl2br(e($b['descripcion'] ?: 'Sin descripción.')) ?></p>
    <?php if (!Auth::check()): ?>
      <a class="btn" href="<?= url('auth/login') ?>">Inicia sesión para reservar</a>
    <?php elseif ($b['copies_available'] > 0): ?>
      <form method="post" action="<?= url('reservas/crear/' . $b['id']) ?>" class="inline"><?= csrf_field() ?><button class="btn">Reservar</button></form>
    <?php elseif ($inWait): ?>
      <span class="badge b-confirmada">Estás en la lista de espera</span>
    <?php else: ?>
      <form method="post" action="<?= url('reservas/espera/' . $b['id']) ?>" class="inline"><?= csrf_field() ?><button class="btn btn-alt">Unirme a la lista de espera</button></form>
    <?php endif; ?>
  </div>
</div>
<?php if (Auth::isStaff()): ?>
<div class="card"><h3>Administrar libro</h3>
  <a class="btn btn-alt" href="<?= url('libros/editar/' . $b['id']) ?>">Editar</a>
  <form method="post" action="<?= url('libros/eliminar/' . $b['id']) ?>" class="inline" data-confirm="¿Dar de baja este libro del catálogo?"><?= csrf_field() ?><button class="btn btn-danger">Dar de baja</button></form>
  <form method="post" action="<?= url('libros/ejemplares/' . $b['id']) ?>" class="inline-form"><?= csrf_field() ?>
    <input type="number" name="cantidad" value="1" min="1" style="width:80px">
    <input name="nota" placeholder="Motivo (para bajas)">
    <button class="btn btn-alt" name="accion" value="alta">+ Ingresar ejemplares</button>
    <button class="btn btn-danger" name="accion" value="baja">− Dar de baja</button>
  </form>
</div>
<?php endif; ?>
