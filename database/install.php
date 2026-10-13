<?php
// Instalador: crea la base (si no existe), las tablas y los datos de prueba.
// Uso: php database/install.php
require __DIR__ . '/../app/bootstrap.php';

$c = cfg('db');
try {
    $pdo = new PDO("mysql:host={$c['host']};charset={$c['charset']}", $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$c['name']}`");
    echo "Base de datos '{$c['name']}' lista.\n";

    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b[^;]*;/mi', '', $sql);
    $sql = preg_replace('/CREATE TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql);
    $sql = preg_replace('/INSERT INTO settings/i', 'INSERT IGNORE INTO settings', $sql);

    $n = 0;
    foreach (array_filter(array_map('trim', explode(";\n", $sql))) as $stmt) {
        $stmt = rtrim($stmt, ';');
        if ($stmt === '') continue;
        $pdo->exec($stmt);
        $n++;
    }
    echo "Sentencias ejecutadas: $n\n";
} catch (PDOException $e) {
    exit("ERROR: " . $e->getMessage() . "\n");
}

require __DIR__ . '/seed.php';