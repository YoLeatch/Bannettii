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
        echo ViewerPlace::render('index'); 
    }
}