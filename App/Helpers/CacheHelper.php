<?php

namespace App\Helpers;

class CacheHelper {
    public static function isCached($key): bool {
        $cacheFile = __DIR__ . '/../../Core/cache/' . md5($key) . '.cache';
        return file_exists($cacheFile);
    }

    public static function getCache($key): ?string {
        $cacheFile = __DIR__ . '/../../Core/cache/' . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            return require $cacheFile;
        }
        return null;
    }

    public static function setCache($key, $data): void {
        $cacheFile = __DIR__ . '/../../Core/cache/' . md5($key) . '.cache';
        $data = '<?php return ' . var_export($data, true) . ';';
        file_put_contents($cacheFile, $data);
    }
}