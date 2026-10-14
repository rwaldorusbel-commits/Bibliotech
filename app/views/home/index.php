<?php 
// Aseguramos que $stats exista para evitar errores
$stats = $stats ?? ['titulos' => 0, 'libros' => 0]; 
?>

<section class="hero">
  <h1>Plataforma BiblioTech</h1>
  <p>Solución tecnológica desarrollada sobre entorno LAMP.</p>
  <a href="<?= url('catalogo') ?>" class="btn-hero">Comenzar Ahora</a>
</section>

<h2 class="section-title">Características</h2>

<div class="features">
  <div class="feature-card">
    <div class="feature-icon">📖</div>
    <h3>Reserva online</h3>
    <p>Reserva tus libros desde cualquier dispositivo.</p>
  </div>
  <div class="feature-card">
    <div class="feature-icon">📊</div>
    <h3>Control de stock</h3>
    <p>Consulta disponibilidad en tiempo real.</p>
  </div>
  <div class="feature-card">
    <div class="feature-icon">💰</div>
    <h3>Gestión de multas</h3>
    <p>Cálculo automático de moras por retraso.</p>
  </div>
</div>

<p class="stats"><?= $stats['titulos'] ?> títulos · <?= $stats['libros'] ?> ejemplares</p>