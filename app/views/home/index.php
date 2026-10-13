<section class="hero">
  <h1>Tu biblioteca, ahora en línea</h1>
  <p>Consulta el catálogo, reserva tus libros, revisa tus préstamos y multas desde cualquier lugar.</p>
  <a class="btn btn-lg" href="<?= url(Auth::check() ? 'catalogo' : 'auth/register') ?>">Comenzar Ahora</a>
</section>
<section class="grid3">
  <div class="card"><h3>🔎 Catálogo inteligente</h3><p>Busca por título, autor, categoría, ISBN o palabra clave y filtra por disponibilidad.</p></div>
  <div class="card"><h3>⏱️ Reserva en línea</h3><p>Reserva y recoge tu libro en un plazo de <?= (int)Setting::get('plazo_recojo_horas', 48) ?> horas.</p></div>
  <div class="card"><h3>🔔 Avisos por correo</h3><p>Recordatorios de vencimiento y aviso cuando un libro vuelva a estar disponible.</p></div>
</section>
<p class="muted center"><?= (int)$stats['titulos'] ?> títulos · <?= (int)$stats['libros'] ?> ejemplares</p>
