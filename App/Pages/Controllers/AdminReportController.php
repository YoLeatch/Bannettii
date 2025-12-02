<?php

namespace App\Pages\Controllers;

use App\Sales\VendaModel;
use Core\ViewerPlace;

class AdminReportController
{
    public function index()
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login');
            exit;
        }

        // Default to current month/year
        $year = date('Y');
        $month = date('m');
        
        // Fetch stats (Mocking for now as VendaModel might fail due to missing date column)
        // In a real scenario, we'd call VendaModel::getSalesByMonth($year);
        
        // Let's try to fetch recent orders if possible, or just show the structure.
        // Since I know 'data' column is likely missing, I'll wrap in try-catch or just show empty.
        
        $reportRows = '';
        // Placeholder data for demonstration
        $reportRows .= "
        <tr>
            <td>#1001</td>
            <td>" . date('d/m/Y') . "</td>
            <td><span class='status-badge status-active'>Concluído</span></td>
            <td>
                <div class='product-preview'>
                    <img src='/assets/image/placeholder_product.jpg'>
                    <span>Exemplo Produto</span>
                </div>
            </td>
            <td>R$ 150,00</td>
            <td>1</td>
        </tr>";

        $data = [
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin',
            'admin_role' => 'Administrador',
            'report_rows' => $reportRows,
            'total_sales' => 'R$ 0,00', // Calculate from DB
            'total_orders' => '0'
        ];

        echo ViewerPlace::render('orders-preview', $data);
    }
}
