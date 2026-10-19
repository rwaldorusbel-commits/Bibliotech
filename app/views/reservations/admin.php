<style>
.r-head { margin-bottom:20px; }
.r-head h1 { color:var(--primary); font-size:1.8rem; line-height:1.2; }
.r-head p { color:var(--text-light); font-size:.9rem; }
.r-filter { display:flex; gap:10px; align-items:center; flex-wrap:wrap; background:#fff; padding:16px 20px; border-radius:12px; box-shadow:0 2px 12px rgba(107,45,92,.08); margin-bottom:18px; }
.r-filter select { padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:.9rem; min-width:200px; }
.r-count { color:var(--text-light); font-size:.88rem; margin-left:auto; }
.r-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.r-table th { background:var(--primary); color:#fff; font-size:.78rem; text-transform:uppercase; letter-spacing:.6px; padding:14px 16px; text-align:left; }
.r-table td { padding:14px 16px; border-bottom:1px solid #f0eaee; font-size:.9rem; vertical-align:middle; }
.r-table tr:last-child td { border-bottom:none; }
.r-table tbody tr:hover { background:#fbf7fa; }
.r-id { color:var(--text-light); font-weight:bold; }
.r-user strong { display:block; }
.r-user small { color:var(--text-light); }
.r-date { white-space:nowrap; color:var(--text-light); }
.r-empty { text-align:center; color:var(--text-light); padding:40px; background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.tag { display:inline-block; padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:bold; }
.tag-pendiente { background:#FFF3CD; color:#856404; }
.tag-confirmada { background:#DCE8F9; color:#1E4B8F; }
.tag-recogida { background:#D4EDDA; color:#155724; }
.tag-vencida { background:#FBE3D0; color:#9A4A12; }
.tag-cancelada { background:#F8D7DA; color:#721C24; }
.tag-otro { background:#E2E3E5; color:#383D41; }
.r-table .btn { padding:7px 14px; font-size:.82rem; }
</style>

<div class="r-head">
  <h1>Gestión de reservas</h1>
  <p>Control de todas las reservas del sistema</p>
</div>

<form class="r-filter" method="get" action="<?= url('reservas/admin') ?>">
  <select name="estado">
    <option value="">Todos los estados</option>
    <option value="pendiente" <?= ($estado ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
    <option value="confirmada" <?= ($estado ?? '') === 'confirmada' ? 'selected' : '' ?>>Confirmadas</option>
    <option value="recogida" <?= ($estado ?? '') === 'recogida' ? 'selected' : '' ?>>Recogidas</option>
    <option value="vencida" <?= ($estado ?? '') === 'vencida' ? 'selected' : '' ?>>Vencidas</option>
    <option value="cancelada" <?= ($estado ?? '') === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
  </select>
  <button class="btn" type="submit">Filtrar</button>
  <span class="r-count"><?= count($reservations) ?> reserva(s)</span>
</form>

<?php if (!$reservations): ?>
  <div class="r-empty">No hay reservas con esos criterios.</div>
<?php else: ?>
  <table class="r-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Libro</th>
        <th>Estado</th>
        <th>Reservado</th>
        <th>Expira</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td class="r-id">#<?= (int)$r['id'] ?></td>
          <td class="r-user">
            <strong><?= e($r['usuario'] ?? '—') ?></strong>
            <small>Doc. <?= e($r['documento'] ?? '—') ?></small>
          </td>
          <td><?= e($r['titulo']) ?></td>
          <td>
            <?php $est = $r['estado'] ?: 'otro'; ?>
            <span class="tag tag-<?= e($est) ?>"><?= e($r['estado'] ? ucfirst($r['estado']) : 'Sin estado') ?></span>
          </td>
          <td class="r-date"><?= fdt($r['fecha_reserva']) ?></td>
          <td class="r-date"><?= fdt($r['fecha_expiracion']) ?></td>
          <td>
            <?php if ($r['estado'] === 'pendiente'): ?>
              <form method="post" action="<?= url('reservas/confirmar/' . $r['id']) ?>" class="inline">
                <?= csrf_field() ?>
                <button class="btn" type="submit">Confirmar</button>
              </form>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>