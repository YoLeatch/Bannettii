<?php

// As variáveis já foram carregadas pelo autoload.php via Core\DotEnv->load()

return [
    'default' => [
        'driver'   => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'] ?? 'bennettii',
        'user'     => $_ENV['DB_USER'] ?? 'default',
        'password' => $_ENV['DB_PASSWORD'] ?? 'u*!v2aSN#;^9sNR_'
    ],
    'read_only' => [
        'driver'   => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'] ?? 'bennettii',
        'user'     => $_ENV['DB_USER'] ?? 'read_only',
        'password' => $_ENV['DB_PASSWORD'] ?? '95kw2hT{UiJ[+d[9'
    ],
];
