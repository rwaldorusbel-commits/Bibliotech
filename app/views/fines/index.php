<h1>Catálogo de libros</h1>
<form class="filters card" method="get" action="<?= url('catalogo') ?>">
  <input name="q" placeholder="Buscar por título, autor, categoría, ISBN o palabra clave" value="<?= e($f['q'] ?? '') ?>">
  <select name="categoria">
    <option value="">Categoría</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ($f['categoria'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="autor">
    <option value="">Autor</option>
    <?php foreach ($authors as $a): ?>
      <option value="<?= $a['id'] ?>" <?= ($f['autor'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="anio">
    <option value="">Año</option>
    <?php foreach ($years as $y): ?>
      <option <?= ($f['anio'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
    <?php endforeach; ?>
  </select>
  <select name="orden">
    <?php foreach (['titulo' => 'Ordenar: título', 'autor' => 'Autor', 'anio' => 'Año (reciente)', 'disp' => 'Disponibilidad'] as $k => $v): ?>
      <option value="<?= $k ?>" <?= ($f['orden'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <label class="chk"><input type="checkbox" name="disp" value="1" <?= !empty($f['disp']) ? 'checked' : '' ?>> Solo disponibles</label>
  <button class="btn">Buscar</button>
</form>

<p class="muted"><?= $total ?> resultado(s)</p>

<?php if (!$books): ?>
  <div class="card center">No se encontraron libros con esos criterios.</div>
<?php endif; ?>

<div class="books">
<?php foreach ($books as $b): 
  [$cls, $lbl] = Book::status($b); 
  $disp = (int)($b['stock_disponible'] ?? $b['copies_available'] ?? 0);
  $tot = (int)($b['stock_total'] ?? $b['copies_total'] ?? 0);
?>
  <a class="book" href="<?= url('catalogo/ver/' . $b['id']) ?>">
    <img src="<?= e(cover_url($b)) ?>" alt="Portada de <?= e($b['titulo']) ?>" loading="lazy">
    <div>
      <strong><?= e($b['titulo']) ?></strong><br>
      <small><?= e($b['autor'] ?? 'Autor desconocido') ?></small><br>
      <span class="badge b-<?= $cls ?>"><?= $lbl ?></span> 
      <small><?= $disp ?>/<?= $tot ?></small>
    </div>
  </a>
<?php endforeach; ?>
</div>

<?= pager($total, $page, $per, 'catalogo', $f) ?>