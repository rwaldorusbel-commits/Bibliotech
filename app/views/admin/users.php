<style>
.u-head { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
.u-head h1 { color:var(--primary); font-size:1.8rem; line-height:1.2; }
.u-head p { color:var(--text-light); font-size:.9rem; }
.u-search { display:flex; gap:8px; }
.u-search input { padding:10px 14px; border:1px solid #ddd; border-radius:8px; min-width:260px; font-size:.9rem; }
.u-search input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(107,45,92,.12); }
.u-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(107,45,92,.08); }
.u-table th { background:var(--primary); color:#fff; font-size:.78rem; text-transform:uppercase; letter-spacing:.6px; padding:14px 16px; }
.u-table td { padding:14px 16px; border-bottom:1px solid #f0eaee; vertical-align:middle; font-size:.92rem; }
.u-table tr:last-child td { border-bottom:none; }
.u-table tbody tr:hover { background:#fbf7fa; }
.u-user { display:flex; align-items:center; gap:12px; }
.u-avatar { width:38px; height:38px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:.85rem; flex-shrink:0; }
.u-user strong { display:block; color:var(--text); }
.u-user small { color:var(--text-light); }
.tag { display:inline-block; padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:bold; text-transform:capitalize; }
.tag-administrador { background:#EADCF0; color:#4A1F40; }
.tag-bibliotecario { background:#DCE8F9; color:#1E4B8F; }
.tag-estudiante { background:#E8F0E4; color:#2F5D1E; }
.tag-activo { background:#D4EDDA; color:#155724; }
.tag-suspendido { background:#FFF3CD; color:#856404; }
.tag-inactivo, .tag-bloqueado { background:#F8D7DA; color:#721C24; }
.u-actions { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.u-actions select { padding:7px 8px; border:1px solid #ddd; border-radius:6px; font-size:.82rem; background:#fff; }
.u-actions .btn { padding:7px 14px; font-size:.82rem; }
.u-you { color:var(--text-light); font-size:.8rem; font-style:italic; }
</style>

<div class="u-head">
  <div>
    <h1>Usuarios</h1>
    <p><?= count($users) ?> usuario<?= count($users) === 1 ? '' : 's' ?> encontrado<?= count($users) === 1 ? '' : 's' ?></p>
  </div>
  <form method="get" class="u-search">
    <input name="q" placeholder="Buscar por nombre, correo o documento" value="<?= e($_GET['q'] ?? '') ?>">
    <button class="btn" type="submit">Buscar</button>
  </form>
</div>

<table class="u-table">
  <thead>
    <tr><th>Usuario</th><th>Documento</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
  </thead>
  <tbody>
  <?php foreach ($users as $x):
    $nombre = trim($x['nombres'] . ' ' . $x['apellidos']);
    $ini = mb_strtoupper(mb_substr($nombre, 0, 1));
  ?>
    <tr>
      <td>
        <div class="u-user">
          <div class="u-avatar"><?= e($ini) ?></div>
          <div><strong><?= e($nombre) ?></strong><small><?= e($x['email']) ?></small></div>
        </div>
      </td>
      <td><?= e($x['documento']) ?></td>
      <td><span class="tag tag-<?= e($x['rol']) ?>"><?= e($x['rol']) ?></span></td>
      <td><span class="tag tag-<?= e($x['estado']) ?>"><?= e($x['estado']) ?></span></td>
      <td>
        <?php if ($x['id'] != Auth::user()['id']): ?>
          <form method="post" action="<?= url('admin/usuario-estado/' . $x['id']) ?>" class="u-actions">
            <?= csrf_field() ?>
            <select name="rol">
              <?php foreach (['estudiante', 'bibliotecario', 'administrador'] as $r): ?>
                <option <?= $x['rol'] === $r ? 'selected' : '' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
            <select name="estado">
              <?php foreach (['activo', 'suspendido', 'inactivo'] as $s): ?>
                <option <?= $x['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Guardar</button>
          </form>
        <?php else: ?>
          <span class="u-you">Tu cuenta</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>