<?php
class Setting {
    private static $c = null;
    static function all() { return DB::all('SELECT * FROM settings ORDER BY clave'); }
    static function get($k, $d = null) {
        if (self::$c === null) self::$c = array_column(DB::all('SELECT clave, valor FROM settings'), 'valor', 'clave');
        return self::$c[$k] ?? $d;
    }
    static function set($k, $v) { DB::exec('UPDATE settings SET valor=? WHERE clave=?', [$v, $k]); self::$c = null; }
}
