<?php
namespace App\Pages\Controllers;

class oops
{
    public function index()
    {
        ViewerPlace::render('error' , ['error_code' => '404', 'error_msg' => 'Parece que algo inesperado aconteceu.', 'error_mensage' => 'Parece que a página que você está procurando não existe, clique <a href="/home">aqui</a> para retornar a página principal.']);
    }
}