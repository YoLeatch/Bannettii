<?php

namespace Core;

use App\Pages\Controllers\oops;

class ViewerPlace {
    public static function render($view, $data = []) {
        $viewFile = __DIR__ . '/../App/Pages/Views/' . $view . '.html';

        if (file_exists($viewFile)) {
            $page = file_get_contents($viewFile);
            $keys = array_keys($data);
            $values = array_values($data);
            $search = [];

            foreach ($keys as $key) {
                $search[] = '{' . $key . '}';
            }

            return str_replace($search, $values, $page);
        } else {
            echo oops::index();
        }   
    }
}

