<?php

namespace App\Payment\Controllers;

use Core\ViewerPlace;

class PaymentController
{
    public function index()
    {
        // Mock data for checkout
        $data = [
            'product_name' => 'Exemplo de Produto',
            'price' => 'R$ 100,00',
            'total' => 'R$ 100,00'
        ];
        echo ViewerPlace::render('payment.html', $data);
    }

    public function process()
    {
        // Handle payment processing here
        echo "Processando pagamento...";
    }
}
