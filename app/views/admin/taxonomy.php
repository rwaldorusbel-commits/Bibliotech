<style>
.t-head { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
.t-head h1 { color:var(--primary); font-size:1.8rem; line-height:1.2; }
.t-head p { color:var(--text-light); font-size:.9rem; }
.t-add { display:flex; gap:8px; background:#fff; padding:12px; border-radius:12px; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.t-add input { padding:10px 14px; border:1px solid #ddd; border-radius:8px; min-width:240px; font-size:.9rem; }
.t-add input:focus, .t-name:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(107,45,92,.12); }
.t-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.t-table th { background:var(--primary); color:#fff; font-size:.78rem; text-transform:uppercase; letter-spacing:.6px; padding:14px 16px; text-align:left; }
.t-table td { padding:12px 16px; border-bottom:1px solid #f0eaee; vertical-align:middle; font-size:.9rem; }
.t-table tr:last-child td { border-bottom:none; }
.t-table tbody tr:hover { background:#fbf7fa; }
.t-name { width:100%; max-width:380px; padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:.9rem; }
.t-count { display:inline-block; min-width:32px; text-align:center; background:#f4ecf2; color:var(--primary); font-weight:bold; border-radius:20px; padding:3px 10px; font-size:.82rem; }
.t-table .btn { padding:7px 14px; font-size:.82rem; }
.btn-danger { background:#B03A2E; }
.btn-danger:hover { background:#8B2D23; }
.t-actions { text-align:right; white-space:nowrap; }
.t-actions form { display:inline; }
.t-actions .btn { margin-left:6px; }
.t-empty { text-align:center; color:var(--text-light); padding:30px; }
</style>

<div class="t-head">
  <div>
    <h1>Gestión de <?= e($label) ?></h1>
    <p><?= count($items) ?> registro<?= count($items) === 1 ? '' : 's' ?></p>
  </div>
  <form method="post" class="t-add">
    <?= csrf_field() ?>
    <input name="nombre" placeholder="Nuevo nombre" required>
    <button class="btn" type="submit">➕ Agregar</button>
  </form>
</div>

<table class="t-table">
  <thead>
    <tr><th>Nombre</th><th>Libros</th><th class="t-actions">Acciones</th></tr>
  </thead>
  <tbody>
  <?php if (!$items): ?>
    <tr><td colspan="3" class="t-empty">Aún no hay registros.</td></tr>
  <?php endif; ?>
  <?php foreach ($items as $i): $fid = 'f' . (int)$i['id']; ?>
    <tr>
      <td>
        <!-- Formulario de actualizar: vacío, los controles se enlazan con form="" -->
        <form id="<?= $fid ?>" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="editar_id" value="<?= $i['id'] ?>">
        </form>
        <input class="t-name" form="<?= $fid ?>" name="nombre" value="<?= e($i['nombre']) ?>" required>
      </td>
      <td><span class="t-count"><?= (int)$i['usos'] ?></span></td>
      <td class="t-actions">
        <button class="btn" type="submit" form="<?= $fid ?>">✏️ Actualizar</button>
        <form method="post" data-confirm="¿Eliminar? Los libros asociados quedarán sin <?= $route === 'autores' ? 'autor' : 'categoría' ?>.">
          <?= csrf_field() ?>
          <button class="btn btn-danger" name="borrar" value="<?= $i['id'] ?>">🗑 Eliminar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

