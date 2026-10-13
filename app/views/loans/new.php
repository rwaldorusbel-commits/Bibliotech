<div class="card narrow"><h2>Registrar préstamo</h2>
  <?php if ($res): ?><div class="alert a-ok">Entrega de la reserva #<?= (int)$res['id'] ?>: <strong><?= e($res['titulo']) ?></strong></div><?php endif; ?>
  <form method="post" action="<?= url('prestamos/registrar') ?>"><?= csrf_field() ?>
    <input type="hidden" name="reservation_id" value="<?= (int)($res['id'] ?? 0) ?>">
    <label>Documento del usuario<input name="documento" value="<?= e($res['documento'] ?? '') ?>" required></label>
    <label>Libro<select name="book_id" required>
      <?php foreach ($books as $b): $sel = $res ? $res['book_id'] == $b['id'] : false; if ($res && !$sel) continue; ?>
        <option value="<?= $b['id'] ?>" <?= $sel ? 'selected' : '' ?>><?= e($b['titulo']) ?> (<?= (int)$b['copies_available'] ?> disp.)</option>
      <?php endforeach; ?></select></label>
    <p class="muted">La fecha de devolución se calcula con los días de préstamo configurados (<?= (int)Setting::get('dias_prestamo', 7) ?> días).</p>
    <button class="btn">Registrar</button>
  </form>
</div>
