<?php
$campos = [
  'dias_prestamo'    => ['Días de préstamo',            'días',  'Plazo para devolver un libro.'],
  'max_prestamos'    => ['Máximo de préstamos',         'libros','Préstamos simultáneos por usuario.'],
  'horas_reserva'    => ['Vigencia de reserva',         'horas', 'Tiempo que se guarda un libro reservado.'],
  'max_reservas'     => ['Máximo de reservas',          'libros','Reservas activas por usuario.'],
  'multa_por_dia'    => ['Multa por día de atraso',     'S/',    'Monto cobrado por cada día vencido.'],
  'dias_gracia'      => ['Días de gracia',              'días',  'Días de atraso sin multa.'],
  'max_renovaciones' => ['Renovaciones permitidas',     'veces', 'Cuántas veces se puede renovar un préstamo.'],
  'dias_renovacion'  => ['Días por renovación',         'días',  'Extensión de cada renovación.'],
];
?>
<div class="card">
  <h1 style="color:var(--primary);margin-bottom:5px">⚙️ Configuración del sistema</h1>
  <p class="muted" style="margin-top:0">Parámetros que controlan préstamos, reservas y multas.</p>

  <form method="post">
    <?= csrf_field() ?>
    <div class="grid2">
      <?php foreach ($settings as $s):
        $info = $campos[$s['clave']] ?? [ucwords(str_replace('_', ' ', $s['clave'])), '', ''];
      ?>
        <div class="card" style="border:1px solid #eee;box-shadow:none">
          <label style="font-weight:bold;color:var(--primary)"><?= e($info[0]) ?></label>
          <div style="display:flex;align-items:center;gap:8px;margin:8px 0">
            <input type="number" min="0" step="any"
                   name="<?= e($s['clave']) ?>" value="<?= e($s['valor']) ?>"
                   style="flex:1;padding:10px;border:1px solid #ddd;border-radius:6px">
            <span class="muted" style="margin:0"><?= e($info[1]) ?></span>
          </div>
          <small class="muted"><?= e($info[2]) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:20px">
      <button class="btn" type="submit">💾 Guardar cambios</button>
    </div>
  </form>
</div>