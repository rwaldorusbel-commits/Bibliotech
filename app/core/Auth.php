<?php
class Auth {
    private static $u = false;
    static function user() {
        if (self::$u === false) {
            self::$u = null;
            if (!empty($_SESSION['uid'])) {
                $u = User::find((int)$_SESSION['uid']);
                if ($u && $u['estado'] !== 'inactivo') self::$u = $u; else unset($_SESSION['uid']);
            }
        }
        return self::$u;
    }
    static function check() { return self::user() !== null; }
    static function is(...$roles) { return self::check() && in_array(self::user()['rol'], $roles, true); }
    static function isStaff() { return self::is('bibliotecario', 'administrador'); }
    static function login($id) { session_regenerate_id(true); $_SESSION['uid'] = $id; self::$u = false; }
    static function logout() { $_SESSION = []; session_destroy(); self::$u = false; }
}
