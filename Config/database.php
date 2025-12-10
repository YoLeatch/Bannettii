<?php

// As variáveis já foram carregadas pelo autoload.php via Core\DotEnv->load()

return [
    'default' => [
        'driver'   => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'] ?? 'u607845901_bennettii_db',
        'user'     => $_ENV['DB_USER'] ?? 'u607845901_bennettii',
        'password' => $_ENV['DB_PASSWORD'] ?? 'admin@Bennettii1'
    ],
    'read_only' => [
        'driver'   => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'] ?? 'u607845901_bennettii_db',
        'user'     => $_ENV['DB_USER'] ?? 'u607845901_bennettii',
        'password' => $_ENV['DB_PASSWORD'] ?? 'admin@Bennettii1'
    ],
];
