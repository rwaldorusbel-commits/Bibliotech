<?php $u = Auth::user(); $self = trim($_GET['url'] ?? '', '/'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') ? $title . ' · ' : '') . e(cfg('app_name')) ?></title>
<style>
:root {
    --primary: #6B2D5C;
    --primary-dark: #4A1F40;
    --accent: #D4A017;
    --bg: #FDF8E4;
    --card-bg: #FFFFFF;
    --text: #2E2E2E;
    --text-light: #666666;
    --sidebar-w: 260px;
}
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
body { background-color: var(--bg); color: var(--text); line-height: 1.6; }

/* ===== LAYOUT GENERAL ===== */
.layout { display: flex; min-height: 100vh; }
.main-area { flex: 1; transition: margin-left 0.3s ease; }
body.has-sidebar .main-area { margin-left: var(--sidebar-w); }

/* ===== NAV SUPERIOR ===== */
.nav { background-color: var(--primary); padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.nav .brand { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 8px; }
.nav nav { display: flex; align-items: center; gap: 25px; }
.nav nav a { color: white; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: opacity 0.2s; }
.nav nav a:hover { opacity: 0.8; }
.nav nav .btn-nav { background-color: var(--accent); color: white; padding: 8px 20px; border-radius: 20px; font-weight: bold; }
.nav nav .btn-nav:hover { background-color: #B88A0F; opacity: 1; }
.nav nav .user { color: white; font-weight: bold; }
.burger { display: none; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
.container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

/* ===== SIDEBAR FIJO (para admin/bibliotecario) ===== */
.sidebar-fixed {
    position: fixed;
    top: 0; left: 0;
    width: var(--sidebar-w);
    height: 100vh;
    background: #2E2E2E;
    color: white;
    z-index: 100;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.15);
}
.sidebar-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 22px 24px;
    background: var(--primary);
    color: white;
    font-size: 1.3rem;
    font-weight: bold;
    text-decoration: none;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.sidebar-user {
    padding: 15px 24px;
    background: rgba(255,255,255,0.05);
    font-size: 0.85rem;
    color: #B0B0B0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sidebar-user strong { color: white; display: block; font-size: 0.95rem; }
.sidebar-section {
    padding: 15px 24px 8px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #808080;
    font-weight: bold;
}
.sidebar-nav { display: flex; flex-direction: column; padding: 5px 0; }
.sidebar-nav a {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 24px;
    color: #E0E0E0;
    text-decoration: none;
    font-size: 0.92rem;
    transition: background 0.15s, padding-left 0.15s, border-left-color 0.15s;
    border-left: 3px solid transparent;
}
.sidebar-nav a:hover {
    background: rgba(212, 160, 23, 0.15);
    padding-left: 30px;
    border-left-color: var(--accent);
    color: white;
}
.sidebar-nav a .icon { font-size: 1.1rem; width: 24px; text-align: center; }

/* Botón cerrar sidebar en móvil */
.sidebar-close-btn {
    display: none;
    position: absolute;
    top: 15px; right: 15px;
    background: none; border: none; color: white;
    font-size: 1.3rem; cursor: pointer;
}

/* ===== COMPONENTES COMUNES ===== */
.hero { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: 80px 40px; border-radius: 12px; text-align: center; margin-bottom: 50px; }
.hero h1 { font-size: 3rem; margin-bottom: 15px; }
.hero p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 30px; }
.hero .btn-hero { background-color: var(--accent); color: white; padding: 15px 35px; border-radius: 30px; text-decoration: none; font-size: 1.1rem; font-weight: bold; display: inline-block; transition: transform 0.2s; }
.hero .btn-hero:hover { transform: scale(1.05); }
.section-title { text-align: center; font-size: 2rem; color: var(--primary); margin-bottom: 40px; font-weight: bold; }
.features { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-bottom: 50px; }
.feature-card { background: white; padding: 40px 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(107, 45, 92, 0.08); border: 1px solid rgba(107, 45, 92, 0.1); transition: transform 0.2s; }
.feature-card:hover { transform: translateY(-5px); }
.feature-icon { width: 70px; height: 70px; background: var(--accent); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
.feature-card h3 { color: var(--primary); margin-bottom: 10px; font-size: 1.3rem; }
.feature-card p { color: var(--text-light); font-size: 0.95rem; }
.stats { text-align: center; color: var(--text-light); font-size: 1.1rem; margin-top: 30px; }
.foot { text-align: center; padding: 30px; color: var(--text-light); font-size: 0.9rem; margin-top: 50px; }
.alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
.a-ok { background: #D4EDDA; color: #155724; border: 1px solid #C3E6CB; }
.a-error { background: #F8D7DA; color: #721C24; border: 1px solid #F5C6CB; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
th { background: var(--primary); color: white; }
tr:hover { background: #f9f9f9; }
.btn { background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
.btn:hover { background: var(--primary-dark); }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--primary); }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }

/* ===== Catálogo ===== */
.books { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
.book { display: flex; flex-direction: column; background: white; border-radius: 12px; padding: 15px; text-decoration: none; color: var(--text); box-shadow: 0 2px 8px rgba(107, 45, 92, 0.08); border: 1px solid rgba(107, 45, 92, 0.1); transition: transform 0.2s, box-shadow 0.2s; }
.book:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(107, 45, 92, 0.15); }
.book img { width: 100%; height: 220px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; background: var(--bg); }
.book strong { color: var(--primary); font-size: 1rem; display: block; margin-bottom: 5px; line-height: 1.3; }
.book small { color: var(--text-light); font-size: 0.85rem; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; margin-top: 8px; }
.b-disponible { background: #D4EDDA; color: #155724; }
.b-reservado { background: #FFF3CD; color: #856404; }
.b-prestado { background: #F8D7DA; color: #721C24; }
.b-nodisponible { background: #E2E3E5; color: #383D41; }

.filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding: 20px; margin-bottom: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(107, 45, 92, 0.08); }
.filters input, .filters select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; color: var(--text); }
.filters input:not([type="checkbox"]) { flex: 1; min-width: 200px; }
.filters .chk { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; color: var(--text); }
.muted { color: var(--text-light); font-size: 0.9rem; margin: 15px 0; }
.center { text-align: center; }
.card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(107, 45, 92, 0.08); }

/* Grids para el dashboard */
.grid4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 10px; }
.grid2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 10px; }
.stat { text-align: center; padding: 25px 15px; color: var(--text-light); font-size: 0.95rem; }
.stat span { display: block; font-size: 2.2rem; font-weight: bold; color: var(--primary); margin-bottom: 5px; }

/* ===== LOGIN PROFESIONAL ===== */
.login-wrap { min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
.login-card { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(107, 45, 92, 0.15), 0 4px 12px rgba(0,0,0,0.05); padding: 50px 45px 40px; max-width: 420px; width: 100%; text-align: center; border-top: 5px solid var(--primary); animation: loginFadeIn 0.4s ease-out; }
@keyframes loginFadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.login-logo { width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(107, 45, 92, 0.3); }
.login-title { color: var(--primary); font-size: 2rem; font-weight: bold; margin-bottom: 5px; }
.login-sub { color: var(--text-light); font-size: 0.95rem; margin-bottom: 5px; }
.login-hint { color: var(--text-light); font-size: 0.85rem; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
.login-form { text-align: left; }
.login-field { margin-bottom: 20px; }
.login-field label { display: block; font-weight: bold; color: var(--primary); font-size: 0.9rem; margin-bottom: 8px; }
.login-field input { width: 100%; padding: 14px 16px; border: 2px solid #e5e5e5; border-radius: 10px; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s; background: #fafafa; }
.login-field input:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(107, 45, 92, 0.1); }
.login-btn { width: 100%; padding: 15px; font-size: 1.1rem; font-weight: bold; border-radius: 10px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; cursor: pointer; margin-top: 10px; transition: transform 0.2s, box-shadow 0.2s; }
.login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(107, 45, 92, 0.3); background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%); }
.login-links { margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9rem; }
.login-links a { color: var(--primary); text-decoration: none; font-weight: 500; transition: color 0.2s; }
.login-links a:hover { color: var(--accent); text-decoration: underline; }
.login-links .sep { color: var(--text-light); margin: 0 10px; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .burger { display: block; }
    .nav { padding: 0 20px; }
    .nav nav { display: none; position: absolute; top: 70px; left: 0; right: 0; background: var(--primary); flex-direction: column; padding: 20px; gap: 15px; z-index: 200; }
    .nav nav.open { display: flex; }

    .sidebar-fixed { left: -100%; transition: left 0.3s ease; }
    .sidebar-fixed.open { left: 0; }
    body.has-sidebar .main-area { margin-left: 0; }
    .sidebar-close-btn { display: block; }

    .login-card { padding: 35px 25px 30px; }
    .login-title { font-size: 1.6rem; }
    .login-logo { width: 65px; height: 65px; font-size: 2rem; }
}
</style>
</head>
<body class="<?= Auth::isStaff() ? 'has-sidebar' : '' ?>">

<?php if (Auth::isStaff()): ?>
<!-- ===== SIDEBAR FIJO (solo admin/bibliotecario) ===== -->
<aside class="sidebar-fixed" id="sidebar">
  <button class="sidebar-close-btn" onclick="document.getElementById('sidebar').classList.remove('open')">✕</button>
  
  <a href="<?= url('admin') ?>" class="sidebar-brand">📚 BiblioTech</a>
  
  <div class="sidebar-user">
    <strong><?= e($u['nombre']) ?></strong>
    <?= e($u['rol']) ?>
  </div>

  <div class="sidebar-section">Administración</div>
  <nav class="sidebar-nav">
    <a href="<?= url('admin') ?>"><span class="icon">📊</span><span>Panel</span></a>
    <a href="<?= url('prestamos/gestionar') ?>"><span class="icon">📖</span><span>Préstamos</span></a>
    <a href="<?= url('reservas/gestionar') ?>"><span class="icon">📅</span><span>Reservas activas</span></a>
    <a href="<?= url('reservas/admin') ?>"><span class="icon">📋</span><span>Gestión de Reservas</span></a>
    <a href="<?= url('multas/gestionar') ?>"><span class="icon">💰</span><span>Multas</span></a>
    <a href="<?= url('libros/nuevo') ?>"><span class="icon">➕</span><span>Nuevo libro</span></a>
    <a href="<?= url('admin/stock') ?>"><span class="icon">📦</span><span>Stock</span></a>
  </nav>

  <?php if (Auth::is('administrador')): ?>
    <div class="sidebar-section">Configuración</div>
    <nav class="sidebar-nav">
      <a href="<?= url('admin/usuarios') ?>"><span class="icon">👥</span><span>Usuarios</span></a>
      <a href="<?= url('admin/categorias') ?>"><span class="icon">🏷️</span><span>Categorías</span></a>
      <a href="<?= url('admin/autores') ?>"><span class="icon">✍️</span><span>Autores</span></a>
      <a href="<?= url('admin/reportes') ?>"><span class="icon">📈</span><span>Reportes</span></a>
      <a href="<?= url('admin/configuracion') ?>"><span class="icon">⚙️</span><span>Configuración</span></a>
      <a href="<?= url('admin/bitacora') ?>"><span class="icon">📋</span><span>Bitácora</span></a>
    </nav>
  <?php endif; ?>
</aside>
<?php endif; ?>

<!-- ===== ÁREA PRINCIPAL ===== -->
<div class="main-area">

  <!-- NAV SUPERIOR -->
  <header class="nav">
    <?php if (Auth::isStaff()): ?>
      <button class="burger" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menú">☰</button>
    <?php endif; ?>
    
    <a class="brand" href="<?= url() ?>">📚 <?= e(cfg('app_name')) ?></a>

    <nav id="menu">
      <a href="<?= url() ?>">Inicio</a>
      <a href="<?= url('catalogo') ?>">Catálogo</a>
      <?php if (!$u): ?>
        <a href="<?= url('auth/login') ?>">Login</a>
        <a href="<?= url('auth/register') ?>" class="btn-nav">Registro</a>
      <?php else: ?>
        <a href="<?= url('auth/profile') ?>" class="user">👤 <?= e($u['nombre']) ?> <small>(<?= e($u['rol']) ?>)</small></a>
        <form method="post" action="<?= url('auth/logout') ?>" class="inline"><?= csrf_field() ?><button class="btn-nav" type="submit">Salir</button></form>
      <?php endif; ?>
    </nav>
  </header>

  <!-- CONTENIDO -->
  <main class="container">
    <?php foreach (flash() as [$t, $m]): ?><div class="alert a-<?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
    <?= $content ?>
  </main>

  <footer class="foot">© <?= date('Y') ?> <?= e(cfg('app_name')) ?> · Sistema de biblioteca</footer>

</div>

<script>
function fmt(s){ if(s<=0) return 'Vencida'; var h=Math.floor(s/3600), m=Math.floor(s%3600/60); return 'Expira en '+(h>0? h+' h '+m+' min' : m+' min '+(s%60)+' s'); }
document.querySelectorAll('.countdown').forEach(function(el){
  var s=parseInt(el.dataset.secs,10); el.textContent=fmt(s);
  setInterval(function(){ s--; el.textContent=fmt(s); if(s<=0) el.classList.add('expired'); },1000);
});
document.querySelectorAll('form[data-confirm]').forEach(function(f){ f.addEventListener('submit',function(e){ if(!confirm(f.dataset.confirm)) e.preventDefault(); }); });
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') document.getElementById('sidebar')?.classList.remove('open'); });
</script>
</body>
</html>