<?php
return [
    'app_name' => 'BiblioTech',
    'base_url' => null, // null = autodetectar. Ej: '/biblioteca/public'
    'db' => ['host' => '127.0.0.1', 
    'name' => 'bdbibliotech', 'user' => 'root', 
    'pass' => '', 
    'charset' => 'utf8mb4'],
    'mail' => ['enabled' => false, 'from' => 'biblioteca@localhost'], // siempre se registra en storage/mail.log
    'per_page' => 8,
];
