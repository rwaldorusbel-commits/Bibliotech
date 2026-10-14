<h1>Gestión de reservas</h1>

<form class="filters card" method="get" action="<?= url('reservas/admin') ?>">
  <select name="estado">
    <option value="">Todos los estados</option>
    <option value="pendiente" <?= ($estado ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
    <option value="confirmada" <?= ($estado ?? '') === 'confirmada' ? 'selected' : '' ?>>Confirmadas</option>
    <option value="recogida" <?= ($estado ?? '') === 'recogida' ? 'selected' : '' ?>>Recogidas</option>
    <option value="vencida" <?= ($estado ?? '') === 'vencida' ? 'selected' : '' ?>>Vencidas</option>
    <option value="cancelada" <?= ($estado ?? '') === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
  </select>
  <button class="btn">Filtrar</button>
</form>

<p class="muted"><?= count($reservations) ?> reserva(s)</p>

<?php if (!$reservations): ?>
  <div class="card center">No hay reservas con esos criterios.</div>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Documento</th>
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
          <td>#<?= (int)$r['id'] ?></td>
          <td><?= e($r['usuario'] ?? '—') ?></td>
          <td><?= e($r['documento'] ?? '—') ?></td>
          <td><?= e($r['titulo']) ?></td>
          <td><span class="badge b-<?= e($r['estado']) ?>"><?= e(ucfirst($r['estado'])) ?></span></td>
          <td><?= fdt($r['fecha_reserva']) ?></td>
          <td><?= fdt($r['fecha_expiracion']) ?></td>
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