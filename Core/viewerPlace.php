<?php
namespace Core;

class ViewerPlace {
    public static function render($view, $data = []) {
        $viewFile = __DIR__ . '/../App/Views/' . $view;

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
            throw new \Exception("View file not found: " . $viewFile);
        }
    }
}

