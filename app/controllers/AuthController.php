<?php
class AuthController extends Controller {

    // RF-03
    function login() {
        // Si ya está logueado, redirigir según el rol
        if (Auth::check()) {
            $u = Auth::user();
            if (in_array($u['rol'], ['administrador', 'bibliotecario'])) {
                redirect('admin');
            }
            redirect('catalogo');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $u = User::byEmail(trim($_POST['email'] ?? ''));
            if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
                if ($u['estado'] === 'inactivo') {
                    $error = 'Tu cuenta está desactivada. Contacta al administrador.';
                } else {
                    Auth::login($u['id']);
                    Audit::log('login', $u['email'], $u['id']);
                    flash('ok', 'Bienvenido, ' . $u['nombres'] . '.');

                    // ✅ Redirigir según el rol
                    if (in_array($u['rol'], ['administrador', 'bibliotecario'])) {
                        redirect('admin');
                    }
                    redirect('catalogo');
                }
            } else {
                $error = 'Correo o contraseña incorrectos.';
                usleep(400000);
            }
        }
        $this->render('auth/login', compact('error'), 'Iniciar sesión');
    }

    // RF-01, RF-02, RF-04
    function register() {
        if (Auth::check()) redirect('catalogo');
        $err = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $d = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
            $err = User::validateRegistration($d);
            if (!$err) {
                try {
                    $id = User::create($d);
                    Audit::log('registro', $d['email'], $id);
                    Auth::login($id);
                    flash('ok', '¡Cuenta creada! Ya puedes reservar libros.');
                    redirect('catalogo');
                } catch (PDOException $e) {
                    $err['email'] = 'El correo o documento ya está registrado.';
                }
            }
        }
        $this->render('auth/register', compact('err'), 'Registro');
    }

    // RF-05
    function logout() {
        $this->post();
        Audit::log('logout');
        Auth::logout();
        session_start();
        flash('ok', 'Sesión cerrada.');
        redirect('');
    }

    // RF-06
    function forgot() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $u = User::byEmail(trim($_POST['email'] ?? ''));
            if ($u) {
                $t = User::createReset($u['id']);
                $link = (isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']) : '') . url('auth/reset/' . $t);
                Mailer::send($u['email'], 'Restablecer contraseña', "Hola {$u['nombres']}, usa este enlace (válido 1 hora) para crear una nueva contraseña:\n$link");
            }
            flash('ok', 'Si el correo existe, enviamos un enlace para restablecer la contraseña.');
            redirect('auth/login');
        }
        $this->render('auth/forgot', [], 'Recuperar contraseña');
    }

    function reset($token = '') {
        $r = User::findReset($token);
        if (!$r) {
            flash('error', 'El enlace no es válido o expiró.');
            redirect('auth/forgot');
        }
        $err = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $p = $_POST['password'] ?? '';
            if (strlen($p) < 8 || !preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) {
                $err = 'Mínimo 8 caracteres con letras y números.';
            } elseif ($p !== ($_POST['password2'] ?? '')) {
                $err = 'Las contraseñas no coinciden.';
            } else {
                User::setPassword($r['user_id'], $p);
                User::useReset($r['id']);
                Audit::log('password_reset', '', $r['user_id']);
                flash('ok', 'Contraseña actualizada. Ya puedes iniciar sesión.');
                redirect('auth/login');
            }
        }
        $this->render('auth/reset', compact('err', 'token'), 'Nueva contraseña');
    }

    // RF-08
    function profile() {
        $this->requireLogin();
        $u = Auth::user();
        $err = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $n = trim($_POST['nombres'] ?? '');
            $a = trim($_POST['apellidos'] ?? '');
            $t = trim($_POST['telefono'] ?? '');
            if (mb_strlen($n) < 2) $err['nombres'] = 'Obligatorio.';
            if (mb_strlen($a) < 2) $err['apellidos'] = 'Obligatorio.';
            if (!preg_match('/^[0-9+ ]{6,20}$/', $t)) $err['telefono'] = 'Teléfono inválido.';

            $np = $_POST['new_password'] ?? '';
            if ($np !== '') {
                if (!password_verify($_POST['current_password'] ?? '', $u['password_hash'])) {
                    $err['current_password'] = 'Contraseña actual incorrecta.';
                } elseif (strlen($np) < 8 || !preg_match('/[A-Za-z]/', $np) || !preg_match('/\d/', $np)) {
                    $err['new_password'] = 'Mínimo 8 caracteres con letras y números.';
                }
            }
            if (!$err) {
                User::updateProfile($u['id'], $n, $a, $t);
                if ($np !== '') User::setPassword($u['id'], $np);
                Audit::log('perfil_actualizado');
                flash('ok', 'Datos actualizados.');
                redirect('auth/profile');
            }
        }
        $this->render('auth/profile', compact('u', 'err'), 'Mi perfil');
    }
}