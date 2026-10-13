<?php
// Proceso programado (RF-19, RF-23, RF-29, RF-59). Ejemplo de cron: * * * * * php /ruta/biblioteca/cli/cron.php
require __DIR__ . '/../app/bootstrap.php';
$v = Reservation::expireDue();
$a = Reservation::sendExpiryWarnings();
$r = Loan::sendReminders();
echo date('c') . " reservas vencidas: $v | avisos de reserva: $a | recordatorios de préstamo: $r\n";
