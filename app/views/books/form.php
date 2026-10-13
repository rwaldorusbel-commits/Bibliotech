<?php $v = fn($k, $d = '') => e($b[$k] ?? $d); ?>
<div class="card narrow wide"><h2><?= $b ? 'Editar libro' : 'Nuevo libro' ?></h2>
  <form method="post" action="<?= url('libros/guardar') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($b['id'] ?? 0) ?>">
    <label>Título<input name="titulo" value="<?= $v('titulo') ?>" required></label>
    <div class="row2">
      <label>Autor<select name="author_id"><option value="">—</option><?php foreach ($authors as $a): ?><option value="<?= $a['id'] ?>" <?= ($b['author_id'] ?? 0) == $a['id'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option><?php endforeach; ?></select></label>
      <label>Categoría<select name="category_id"><option value="">—</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($b['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option><?php endforeach; ?></select></label>
    </div>
    <div class="row2">
      <label>Editorial<input name="editorial" value="<?= $v('editorial') ?>"></label>
      <label>Año<input name="anio" maxlength="4" value="<?= $v('anio') ?>"></label>
    </div>
    <div class="row2">
      <label>ISBN<input name="isbn" value="<?= $v('isbn') ?>"></label>
      <label>Stock mínimo (alerta)<input type="number" min="0" name="stock_min" value="<?= $v('stock_min', 1) ?>"></label>
    </div>
    <?php if (!$b): ?><label>Cantidad de ejemplares<input type="number" min="1" name="copies" value="1"></label><?php endif; ?>
    <label>Descripción<textarea name="descripcion" rows="4"><?= $v('descripcion') ?></textarea></label>
    <div class="row2">
      <label>Portada (archivo JPG/PNG/WEBP, máx. 2 MB)<input type="file" name="portada" accept="image/*"></label>
      <label>…o URL de portada<input name="portada_url" placeholder="https://..."></label>
    </div>
    <button class="btn">Guardar</button>
  </form>
</div>
