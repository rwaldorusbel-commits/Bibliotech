<?php
// Traductor de tablas y columnas para adaptar el proyecto a la BD en español
class Traductor {
    // Mapeo de tablas (inglés => español)
    // NOTA: Se eliminó 'audit' => 'auditoria' porque la tabla real es audit_log
    public static $tablas = [
        'books' => 'libros',
        'users' => 'usuarios',
        'loans' => 'prestamos',
        'reservations' => 'reservas',
        'fines' => 'multas',
        'categories' => 'categorias',
        'authors' => 'autores',
        'settings' => 'configuraciones'
    ];

    // Mapeo de columnas (inglés => español)
    public static $columnas = [
        'id' => 'id',
        'name' => 'nombre',
        'email' => 'email',
        'password_hash' => 'password_hash',
        'role' => 'rol',
        'status' => 'estado',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'description' => 'descripcion',
        'title' => 'titulo',
        'author' => 'autor',
        'isbn' => 'isbn',
        'category_id' => 'id_categoria',
        'publisher' => 'editorial',
        'year' => 'anio',
        'cover_url' => 'portada_url',
        'synopsis' => 'sinopsis',
        'copies_total' => 'stock_total',
        'copies_available' => 'stock_disponible',
        'stock_total' => 'stock_total',
        'stock_available' => 'stock_disponible',
        'loan_date' => 'fecha_prestamo',
        'due_date' => 'fecha_devolucion_esperada',
        'return_date' => 'fecha_devolucion_real',
        'reservation_date' => 'fecha_reserva',
        'expiration_date' => 'fecha_expiracion',
        'amount' => 'monto',
        'generation_date' => 'fecha_generacion',
        'payment_date' => 'fecha_pago'
    ];

    public static function traducir($sql) {
        // Reemplazar nombres de tablas (siempre)
        foreach (self::$tablas as $en => $es) {
            $sql = preg_replace("/\b" . $en . "\b/i", $es, $sql);
        }

        // Reemplazar columnas SOLO si NO están entre comillas
        foreach (self::$columnas as $en => $es) {
            $sql = preg_replace("/(?<!['\"])\b" . $en . "\b(?!['\"])/i", $es, $sql);
        }

        return $sql;
    }
}