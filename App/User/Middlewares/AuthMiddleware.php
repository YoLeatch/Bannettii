<?php

namespace App\User\Middlewares;

use App\User\UserModel;
use Core\Security;

class AuthMiddleware {
    public static function handle($admin = false): bool {
        session_start();
        $isAuthenticated = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && ((isset($_SESSION['logged_in']) && !empty($_SESSION['logged_in']))));

        if (!$isAuthenticated) {
            if(!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])){
                return false;
            }
            $token = Security::getJWTPayload($_COOKIE['remember_token']);

            if (!($token && isset($token['user_uid']))) {
                setcookie('remember_token', '', time() - 3600, "/");
                return false;
            }
            $userUId = $token['user_uid'];
            $user = UserModel::findByData('uid', $userUId);

            if (!$user) {
                return false;
            }
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['logged_in'] = true;
            
            return true;
        }
            return true;
        }


    public static function logout(): void {
        session_start();
        session_unset();
        session_destroy();
        if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, "/");
        }
    }
}