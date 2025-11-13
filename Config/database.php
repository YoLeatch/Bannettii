<?php
return [
    'default' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'seu_banco_ecommerce',
        'user'     => 'seu_usuario_app',
        'password' => 'sua_senha_app'
    ],
    'read_only' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'seu_banco_ecommerce',
        'user'     => 'usuario_relatorios',
        'password' => 'senha_segura_read_only'
    ],
];