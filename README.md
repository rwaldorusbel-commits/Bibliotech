# BiblioTech – Sistema de biblioteca (PHP puro, MVC)

PHP 8.1+ · MySQL/MariaDB · sin frameworks ni Composer.

## Instalación
1. Crear BD y tablas: `mysql -u root -p < database/schema.sql`
2. Ajustar credenciales en `app/config/config.php`
3. Datos de prueba: `php database/seed.php`
4. Ejecutar:
   - Servidor embebido: `php -S localhost:8000 -t public public/router.php`
   - Apache: apuntar el DocumentRoot a `public/` (usa `mod_rewrite`; `.htaccess` incluido). Si va en subcarpeta, se autodetecta la URL base.
5. Proceso programado (RF-19/23/29/59): `* * * * * php /ruta/biblioteca/cli/cron.php`
   (además, cada minuto se ejecuta un respaldo que vence reservas al recibir tráfico).

Usuarios demo (clave `Clave1234`): `admin@biblioteca.test`, `biblio@biblioteca.test`, `estudiante@biblioteca.test`.
Los correos se escriben en `storage/mail.log` (activar `mail.enabled` para enviar con `mail()`).

## Estructura MVC
```
public/index.php        Front controller (+ .htaccess, router.php, assets)
app/core/               Router, Controller, Database (PDO), Auth, Mailer, helpers
app/models/             User, Book, Reservation, Loan, Fine, Setting, Audit
app/controllers/        Home, Auth, Book, BookAdmin, Reservation, Loan, Fine, Admin
app/views/              Plantillas PHP (layout + una carpeta por módulo)
database/               schema.sql, seed.php
cli/cron.php            Tareas programadas
```
URLs: `/catalogo`, `/reservas`, `/prestamos`, `/multas`, `/auth/login`, `/admin`… (`/controlador/accion/parámetro`).

## Cobertura de requerimientos
| Módulo | RF | Dónde |
|---|---|---|
| Usuarios y acceso | 01-09 | AuthController, User, AdminController::usuarios |
| Catálogo | 10-16 | BookController, BookAdminController, Book, AdminController::categorias/autores |
| Reserva online | 17-23 | ReservationController, Reservation, Book (lista de espera), cron |
| Préstamos | 24-29 | LoanController, Loan |
| Stock | 30-35 | Book (take/give/addCopies/writeOff/movimientos), admin/stock, panel |
| Multas | 36-42 | Fine, Loan::giveBack, FineController |
| Reportes/Admin | 43-47 | AdminController (panel, reportes, bitácora, configuración) |
| Mis reservas | 48-59 | reservations/index.php (tarjetas, cuenta regresiva en vivo, filtros) |
| Mis multas | 60-62 | fines/index.php |
| Inicio y navegación | 63-64 | home/index.php, layout.php |

## Notas
- Seguridad: contraseñas con `password_hash`, PDO con sentencias preparadas, token CSRF en todos los POST, escape con `e()`, cierre de sesión por POST.
- RF-45: Excel se exporta como CSV (con BOM, abre directo en Excel). PDF: botón "Imprimir / Guardar como PDF" (hoja de estilos de impresión), ya que PHP puro no genera PDF sin librerías.
- Reglas: una reserva cancelable solo si está *pendiente*; el stock se descuenta al reservar y se repone al cancelar/vencer/devolver; ejemplares dañados/perdidos se dan de baja y generan multa.
