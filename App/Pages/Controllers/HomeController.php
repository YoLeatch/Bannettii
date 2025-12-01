<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\ProductModel;

class HomeController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        echo $_SESSION['user_id'];
        echo $_SESSION['logged_in'];
        echo ViewerPlace::render('index');
    }
}