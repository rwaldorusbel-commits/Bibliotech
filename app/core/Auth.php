<?php
class Auth {
    // Verifica si hay un usuario logueado
    static function check() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // Devuelve los datos del usuario actual
    static function user() {
        if (!self::check()) return null;
        return User::find($_SESSION['user_id']);
    }

    // Inicia sesión con el ID del usuario
    // (el AuthController ya validó la contraseña antes de llamar a este método)
    static function login($userId) {
        $_SESSION['user_id'] = $userId;
    }

    // Cierra la sesión
    static function logout() {
        unset($_SESSION['user_id']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // Verifica si el usuario tiene un rol específico
    static function is($rol) {
        $u = self::user();
        return $u && $u['rol'] === $rol;
    }

    // Verifica si el usuario es staff (admin o bibliotecario)
    static function isStaff() {
        $u = self::user();
        return $u && in_array($u['rol'], ['administrador', 'bibliotecario']);
    }
}