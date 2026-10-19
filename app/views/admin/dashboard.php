<style>
.d-head { margin-bottom:25px; }
.d-head h1 { color:var(--primary); font-size:1.8rem; line-height:1.2; }
.d-head p { color:var(--text-light); font-size:.9rem; }
.d-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px; margin-bottom:25px; }
.d-stat { background:#fff; border-radius:12px; padding:22px; display:flex; align-items:center; gap:16px; box-shadow:0 2px 12px rgba(107,45,92,.08); border-left:5px solid var(--primary); }
.d-stat.s2 { border-left-color:#D4A017; }
.d-stat.s3 { border-left-color:#1E4B8F; }
.d-stat.s4 { border-left-color:#B03A2E; }
.d-icon { width:50px; height:50px; border-radius:12px; background:#f4ecf2; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
.d-stat strong { display:block; font-size:1.9rem; color:var(--primary); line-height:1.1; }
.d-stat span { color:var(--text-light); font-size:.85rem; }
.d-panels { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:18px; margin-bottom:25px; }
.d-panel { background:#fff; border-radius:12px; padding:22px; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.d-panel h3 { color:var(--primary); font-size:1.05rem; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #f0eaee; }
.d-empty { background:#EAF6EC; color:#1E6B34; padding:12px 14px; border-radius:8px; font-size:.9rem; }
.d-list { list-style:none; }
.d-list li { display:flex; justify-content:space-between; gap:10px; padding:10px 0; border-bottom:1px solid #f5f0f4; font-size:.9rem; }
.d-list li:last-child { border-bottom:none; }
.d-list a { color:var(--text); text-decoration:none; font-weight:600; }
.d-list a:hover { color:var(--primary); }
.d-list small { display:block; color:var(--text-light); font-weight:normal; }
.d-badge { background:#F8D7DA; color:#721C24; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:bold; white-space:nowrap; align-self:center; }
.d-badge.amber { background:#FFF3CD; color:#856404; }
.d-quick { display:flex; gap:10px; flex-wrap:wrap; }
.d-quick a { background:#fff; color:var(--primary); border:1px solid #e3d3df; padding:10px 18px; border-radius:8px; text-decoration:none; font-size:.88rem; font-weight:600; transition:all .15s; }
.d-quick a:hover { background:var(--primary); color:#fff; }
</style>

<div class="d-head">
  <h1>Panel de control</h1>
  <p>Resumen general al <?= date('d/m/Y') ?></p>
</div>

<div class="d-stats">
  <div class="d-stat"><div class="d-icon">📖</div>
    <div><strong><?= $stats['prestados'] ?></strong><span>Libros prestados</span></div></div>
  <div class="d-stat s2"><div class="d-icon">📅</div>
    <div><strong><?= $stats['reservas'] ?></strong><span>Reservas activas</span></div></div>
  <div class="d-stat s3"><div class="d-icon">👥</div>
    <div><strong><?= $stats['usuarios'] ?></strong><span>Usuarios registrados</span></div></div>
  <div class="d-stat s4"><div class="d-icon">💰</div>
    <div><strong><?= money($stats['multas']) ?></strong><span>Multas pendientes</span></div></div>
</div>

<div class="d-panels">
  <div class="d-panel">
    <h3>⚠️ Alertas de stock bajo</h3>
    <?php if (!$low): ?>
      <div class="d-empty">✔ Sin alertas. El inventario está en orden.</div>
    <?php else: ?>
      <ul class="d-list">
        <?php foreach ($low as $b): ?>
          <li>
            <span>
              <a href="<?= url('catalogo/ver/' . $b['id']) ?>"><?= e($b['titulo']) ?></a>
              <small>Mínimo requerido: <?= (int)$b['stock_min'] ?></small>
            </span>
            <span class="d-badge amber"><?= (int)$b['copies_available'] ?>/<?= (int)$b['copies_total'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="d-panel">
    <h3>⏰ Préstamos vencidos</h3>
    <?php if (!$overdue): ?>
      <div class="d-empty">✔ Ningún préstamo vencido.</div>
    <?php else: ?>
      <ul class="d-list">
        <?php foreach ($overdue as $o): ?>
          <li>
            <span>
              <strong><?= e($o['titulo']) ?></strong>
              <small><?= e($o['usuario']) ?></small>
            </span>
            <span class="d-badge"><?= (int)$o['dias'] ?> d de atraso</span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<div class="d-panel">
  <h3>Accesos rápidos</h3>
  <div class="d-quick">
    <a href="<?= url('libros/nuevo') ?>">➕ Nuevo libro</a>
    <a href="<?= url('prestamos/gestionar') ?>">📖 Préstamos</a>
    <a href="<?= url('reservas/gestionar') ?>">📅 Reservas</a>
    <a href="<?= url('multas/gestionar') ?>">💰 Multas</a>
    <a href="<?= url('admin/usuarios') ?>">👥 Usuarios</a>
  </div>
</div>