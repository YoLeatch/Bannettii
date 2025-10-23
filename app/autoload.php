<?php

function autoload($className) {
    $namespaceMap = [
        'App\\' => __DIR__ . '/app/',
        'Core\\' => __DIR__ . '/core/'
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