<?php
return [
    'app_name' => 'BiblioTech',
    'base_url' => null, // null = autodetectar. Ej: '/biblioteca/public'
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'bibliotech', // Nombre de la BD de tu profesor
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ],
    'mail' => ['enabled' => false, 'from' => 'biblioteca@localhost'],
    'per_page' => 8,
];