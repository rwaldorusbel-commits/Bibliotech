<?php
// Datos de demostración: php database/seed.php
require __DIR__ . '/../app/bootstrap.php';
$users = [
  ['Admin', 'Sistema', '10000001', 'admin@biblioteca.test', '900000001', 'administrador'],
  ['Bety', 'Bibliotecaria', '10000002', 'biblio@biblioteca.test', '900000002', 'bibliotecario'],
  ['Ana', 'Estudiante', '10000003', 'estudiante@biblioteca.test', '900000003', 'estudiante'],
];
foreach ($users as $u) {
    DB::exec('INSERT IGNORE INTO users(nombres,apellidos,documento,email,telefono,password_hash,rol) VALUES(?,?,?,?,?,?,?)',
        [$u[0], $u[1], $u[2], $u[3], $u[4], password_hash('Clave1234', PASSWORD_DEFAULT), $u[5]]);
}
foreach (['Novela', 'Ciencia', 'Historia', 'Tecnología', 'Poesía'] as $c) DB::exec('INSERT IGNORE INTO categories(nombre) VALUES(?)', [$c]);
foreach (['Gabriel García Márquez', 'Mario Vargas Llosa', 'Carl Sagan', 'Ricardo Palma', 'Robert C. Martin', 'César Vallejo'] as $a) DB::exec('INSERT IGNORE INTO authors(nombre) VALUES(?)', [$a]);
$cat = fn($n) => DB::val('SELECT id FROM categories WHERE nombre=?', [$n]);
$aut = fn($n) => DB::val('SELECT id FROM authors WHERE nombre=?', [$n]);
$books = [
  ['Cien años de soledad', 'Gabriel García Márquez', 'Novela', 'Sudamericana', 1967, '9780307474728', 'La saga de la familia Buendía en Macondo.', 3],
  ['La ciudad y los perros', 'Mario Vargas Llosa', 'Novela', 'Seix Barral', 1963, '9788432217104', 'Vida cadete en el Colegio Militar Leoncio Prado.', 2],
  ['Cosmos', 'Carl Sagan', 'Ciencia', 'Planeta', 1980, '9788408123521', 'Un viaje por el universo y la historia de la ciencia.', 2],
  ['Tradiciones peruanas', 'Ricardo Palma', 'Historia', 'Peisa', 1872, '9789972402111', 'Relatos breves de la historia virreinal del Perú.', 1],
  ['Clean Code', 'Robert C. Martin', 'Tecnología', 'Prentice Hall', 2008, '9780132350884', 'Manual de artesanía de software ágil.', 4],
  ['Trilce', 'César Vallejo', 'Poesía', 'Talleres Penitenciaría', 1922, '9788437600000', 'Obra cumbre de la vanguardia poética.', 1],
  ['El amor en los tiempos del cólera', 'Gabriel García Márquez', 'Novela', 'Oveja Negra', 1985, '9780307387349', 'Una historia de amor que desafía el tiempo.', 2],
  ['Conversación en La Catedral', 'Mario Vargas Llosa', 'Novela', 'Seix Barral', 1969, '9788432217111', 'Retrato del Perú durante el ochenio de Odría.', 2],
];
foreach ($books as $b) {
    if (DB::val('SELECT id FROM books WHERE isbn=?', [$b[5]])) continue;
    Book::save(0, ['titulo' => $b[0], 'author_id' => $aut($b[1]), 'category_id' => $cat($b[2]), 'editorial' => $b[3], 'anio' => $b[4], 'isbn' => $b[5], 'descripcion' => $b[6], 'stock_min' => 1, 'copies' => $b[7], 'portada' => null]);
}
echo "Listo. Usuarios (clave: Clave1234): admin@biblioteca.test, biblio@biblioteca.test, estudiante@biblioteca.test\n";
