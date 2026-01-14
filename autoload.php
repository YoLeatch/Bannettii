<?php

function autoload($className) {
    $namespaceMap = [
        'App\\' => __DIR__ . '/App/',
        'Core\\' => __DIR__ . '/Core/',
        'Helpers\\' => __DIR__ . '/App/Helpers/'
    ];

    foreach ($namespaceMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($className, $len);

        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
}

spl_autoload_register('autoload');

// Carrega as variáveis de ambiente
try {
    // Ajuste o caminho se o .env não estiver na raiz do projeto (onde está o autoload.php)
    $dotenv = new Core\DotEnv(__DIR__ . '/.env');
    $dotenv->load();
} catch (\Exception $e) {
    // Em produção, talvez você não queira travar se o .env não existir (se usar vars de servidor)
    // Mas para dev é bom saber
    // error_log("Erro ao carregar .env: " . $e->getMessage());
}
