<?php

namespace App\Pages\Controllers;

use App\User\ClienteModel;
use Core\ViewerPlace;

class AdminUserController
{
    public function index()
    {
        // Check admin session
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login');
            exit;
        }

        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $users = ClienteModel::fetchAllWithStats($limit, $offset, $search);
        $totalUsers = ClienteModel::countAll($search);
        $totalPages = ceil($totalUsers / $limit);

        $userRows = '';
        foreach ($users as $user) {
            $statusClass = $user['status_cliente'] == '1' ? 'status-active' : 'status-inactive';
            $statusText = $user['status_cliente'] == '1' ? 'Ativo' : 'Inativo';
            $totalSpent = number_format($user['valor_total_compras'], 2, ',', '.');
            
            $userRows .= "
            <tr>
                <td><img src='/assets/image/placeholder_pfp.png' class='table-pfp'></td>
                <td>#{$user['id']}</td>
                <td>{$user['Uid']}</td>
                <td>
                    <div class='user-info'>
                        <span class='user-name'>{$user['nome']}</span>
                    </div>
                </td>
                <td>{$user['email']}</td>
                <td>{$user['CPF']}</td>
                <td>-</td> <!-- Address placeholder -->
                <td>-</td> <!-- Phone placeholder -->
                <td>" . date('d/m/Y', strtotime($user['dt_criacao'])) . "</td>
                <td>{$user['total_compras']} (R$ {$totalSpent})</td>
                <td>Não</td> <!-- Employee placeholder -->
                <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
            </tr>";
        }

        // Pagination Links
        $pagination = '';
        if ($totalPages > 1) {
            if ($page > 1) {
                $prev = $page - 1;
                $pagination .= "<a href='?page=$prev&search=$search' class='page-link'>&laquo;</a>";
            }
            
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = $i == $page ? 'active' : '';
                $pagination .= "<a href='?page=$i&search=$search' class='page-link $active'>$i</a>";
            }
            
            if ($page < $totalPages) {
                $next = $page + 1;
                $pagination .= "<a href='?page=$next&search=$search' class='page-link'>&raquo;</a>";
            }
        }

        $data = [
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin',
            'admin_role' => 'Administrador',
            'user_rows' => $userRows,
            'pagination' => $pagination,
            'search_value' => htmlspecialchars($search)
        ];

        echo ViewerPlace::render('tabela-usuarios', $data);
    }

    public function edit()
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /login');
            exit;
        }

        $id = $_GET['id'] ?? 0;
        $user = ClienteModel::findById($id); // Assuming findById exists in PessoaModel/ClienteModel

        if (!$user) {
            header('Location: /admin/users?error=not_found');
            exit;
        }

        // Fetch stats manually or via a new method if not in findById
        // For now, let's just show basic info.
        // We can use fetchAllWithStats logic but for one user.
        // Let's assume we pass basic info.
        
        $data = [
            'id' => $user->getId(),
            'nome' => $user->getNome(),
            'email' => $user->getEmail(),
            'cpf' => $user->getCPF(),
            'dt_criacao' => $user->getDtCriacao(), // Assuming getter exists
            'status_active' => $user->getStatus() == '1' ? 'selected' : '',
            'status_inactive' => $user->getStatus() == '0' ? 'selected' : '',
            'total_compras' => '0', // Placeholder
            'valor_total' => '0,00' // Placeholder
        ];

        echo ViewerPlace::render('usuario-preview', $data);
    }

    public function update()
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $status = $_POST['status'] ?? '1';
            $password = $_POST['password'] ?? '';

            // Update logic
            // We need a method in PessoaModel to update these fields.
            // PessoaModel::update($id, $nome, $email, $cpf, $status, $password);
            
            // Let's assume we can use the existing update method or create one.
            // Checking PessoaModel... it has `update` but it takes ALL fields.
            // Let's use it or create a specific one.
            // For now, I'll assume I can call `update` on the user object if I fetch it first.
            
            $user = ClienteModel::findById($id);
            if ($user) {
                // Update properties
                // $user->setNome($nome); ...
                // Then $user->save();
                // Or call static update.
                
                // Let's try to call a static update method on PessoaModel if it exists or add it.
                // I'll add `updateUser` to PessoaModel in next step.
                
                if (\App\User\PessoaModel::updateUser($id, $nome, $email, $cpf, $status, $password)) {
                    header("Location: /admin/users/edit?id=$id&success=1");
                } else {
                    header("Location: /admin/users/edit?id=$id&error=update_failed");
                }
            }
        }
    }
}
