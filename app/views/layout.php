<?php $u = Auth::user(); $self = trim($_GET['url'] ?? '', '/'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') ? $title . ' · ' : '') . e(cfg('app_name')) ?></title>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header class="nav">
  <a class="brand" href="<?= url() ?>">📚 <?= e(cfg('app_name')) ?></a>
  <button class="burger" onclick="document.getElementById('menu').classList.toggle('open')" aria-label="Menú">☰</button>
  <nav id="menu">
    <a href="<?= url() ?>">Inicio</a>
    <a href="<?= url('catalogo') ?>">Catálogo</a>
    <?php if (!$u): ?>
      <a href="<?= url('auth/login') ?>">Login</a>
      <a href="<?= url('auth/register') ?>" class="btn-nav">Registro</a>
    <?php else: ?>
      <a href="<?= url('reservas') ?>">Mis reservas</a>
      <a href="<?= url('prestamos') ?>">Mis préstamos</a>
      <a href="<?= url('multas') ?>">Mis multas</a>
      <?php if (Auth::isStaff()): ?>
        <span class="drop"><a href="#">Gestión ▾</a><div>
          <a href="<?= url('admin') ?>">Panel</a>
          <a href="<?= url('prestamos/gestionar') ?>">Préstamos</a>
          <a href="<?= url('reservas/gestionar') ?>">Reservas</a>
          <a href="<?= url('multas/gestionar') ?>">Multas</a>
          <a href="<?= url('libros/nuevo') ?>">Nuevo libro</a>
          <a href="<?= url('admin/stock') ?>">Stock</a>
          <?php if (Auth::is('administrador')): ?>
            <a href="<?= url('admin/usuarios') ?>">Usuarios</a>
            <a href="<?= url('admin/categorias') ?>">Categorías</a>
            <a href="<?= url('admin/autores') ?>">Autores</a>
            <a href="<?= url('admin/reportes') ?>">Reportes</a>
            <a href="<?= url('admin/configuracion') ?>">Configuración</a>
            <a href="<?= url('admin/bitacora') ?>">Bitácora</a>
          <?php endif; ?>
        </div></span>
      <?php endif; ?>
      <a href="<?= url('auth/profile') ?>" class="user">👤 <?= e($u['nombres']) ?> <small>(<?= e($u['rol']) ?>)</small></a>
      <form method="post" action="<?= url('auth/logout') ?>" class="inline"><?= csrf_field() ?><button class="btn-nav" type="submit">Salir</button></form>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
  <?php foreach (flash() as [$t, $m]): ?><div class="alert a-<?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
  <?= $content ?>
</main>
<footer class="foot">© <?= date('Y') ?> <?= e(cfg('app_name')) ?> · Sistema de biblioteca</footer>
<script>
// RF-51: cuenta regresiva actualizada en vivo
function fmt(s){ if(s<=0) return 'Vencida'; var h=Math.floor(s/3600), m=Math.floor(s%3600/60); return 'Expira en '+(h>0? h+' h '+m+' min' : m+' min '+(s%60)+' s'); }
document.querySelectorAll('.countdown').forEach(function(el){
  var s=parseInt(el.dataset.secs,10); el.textContent=fmt(s);
  setInterval(function(){ s--; el.textContent=fmt(s); if(s<=0) el.classList.add('expired'); },1000);
});
document.querySelectorAll('form[data-confirm]').forEach(function(f){ f.addEventListener('submit',function(e){ if(!confirm(f.dataset.confirm)) e.preventDefault(); }); });
</script>
</body>
</html>
