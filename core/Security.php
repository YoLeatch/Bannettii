<?php

namespace Core;

class Security {
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new \Exception('CSRF token validation failed');
        }
        return true;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', 
            $base64UrlHeader . "." . $base64UrlPayload, 
            getenv('JWT_SECRET'), 
            true
        );
        
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validateJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[2]));
        $expectedSignature = hash_hmac('sha256', 
            $parts[0] . "." . $parts[1], 
            getenv('JWT_SECRET'), 
            true
        );

        return hash_equals($signature, $expectedSignature);
    }

    public static function rateLimit($key, $limit = 60, $period = 60) {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        
        $current = $redis->get($key);
        if (!$current) {
            $redis->setex($key, $period, 1);
            return true;
        }
        
        if ($current >= $limit) {
            return false;
        }
        
        $redis->incr($key);
        return true;
    }
} 