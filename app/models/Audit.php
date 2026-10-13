<?php
class Audit {
    static function log($accion, $detalle = '', $uid = null) {
        $uid = $uid ?? (Auth::check() ? Auth::user()['id'] : null);
        DB::exec('INSERT INTO audit_log(user_id,accion,detalle,ip) VALUES(?,?,?,?)', [$uid, $accion, $detalle, $_SERVER['REMOTE_ADDR'] ?? 'cli']);
    }
    static function recent($n = 200) {
        return DB::all('SELECT a.*, CONCAT(u.nombres," ",u.apellidos) usuario FROM audit_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT ' . (int)$n);
    }
}
