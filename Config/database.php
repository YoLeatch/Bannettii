<?php
require_once __DIR__ . '/../Core/Environment.php';

use Core\Environment;

// Carregar variáveis de ambiente
Environment::load();

return [
    'default' => [
        'driver'   => Environment::get('DB_DRIVER', 'mysql'),
        'host'     => Environment::get('DB_HOST', 'localhost'),
        'port'     => (int)Environment::get('DB_PORT', 3306),
        'dbname'   => Environment::get('DB_NAME', 'bennettii'),
        'user'     => Environment::get('DB_USER', 'root'),
        'password' => Environment::get('DB_PASSWORD', '')
    ],
    'read_only' => [
        'driver'   => Environment::get('DB_DRIVER', 'mysql'),
        'host'     => Environment::get('DB_HOST', 'localhost'),
        'port'     => (int)Environment::get('DB_PORT', 3306),
        'dbname'   => Environment::get('DB_NAME', 'bennettii'),
        'user'     => Environment::get('DB_USER', 'root'),
        'password' => Environment::get('DB_PASSWORD', '')
    ],
];
