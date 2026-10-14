<?php
require_once __DIR__ . '/Traductor.php'; // Incluir el traductor

class DB {
    private static ?PDO $pdo = null;
    static function pdo(): PDO {
        if (!self::$pdo) {
            $c = cfg('db');
            self::$pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}", $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }
    
    // Modificamos run() para que traduzca el SQL antes de ejecutarlo
    static function run($sql, $p = []) { 
        $sql = Traductor::traducir($sql); // <-- Aquí ocurre la magia
        $s = self::pdo()->prepare($sql); 
        $s->execute($p); 
        return $s; 
    }
    
    static function all($sql, $p = []) { return self::run($sql, $p)->fetchAll(); }
    static function one($sql, $p = []) { $r = self::run($sql, $p)->fetch(); return $r ?: null; }
    static function val($sql, $p = []) { $v = self::run($sql, $p)->fetchColumn(); return $v === false ? null : $v; }
    static function exec($sql, $p = []) { return self::run($sql, $p)->rowCount(); }
    static function insert($sql, $p = []) { self::run($sql, $p); return (int)self::pdo()->lastInsertId(); }
}