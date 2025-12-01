<?php

namespace App\Cart;

use App\Product\ProductModel;
use App\Pages\Views\View;

class CartController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index() {
        $cartItems = $_SESSION['cart'];
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Load view
        echo View::render('cart', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($productId) {
                $product = ProductModel::findById($productId);

                if ($product) {
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] += $quantity;
                    } else {
                        $_SESSION['cart'][$productId] = [
                            'id' => $product->getId(),
                            'name' => $product->getNome(),
                            'price' => $product->getPreco(),
                            'image' => !empty($product->getImages()) ? $product->getImages()[0]['imagem'] : 'default.jpg',
                            'quantity' => $quantity
                        ];
                    }
                }
            }
        }
        $this->index();
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            if ($productId && isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
        }
        header('Location: /cart');
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($productId && isset($_SESSION['cart'][$productId])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$productId]);
                }
            }
        }
        header('Location: /cart');
        exit;
    }

    public function clear() {
        $_SESSION['cart'] = [];
        header('Location: /cart');
        exit;
    }
}
