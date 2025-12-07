<?php
require_once __DIR__ . '/../autoload.php';

use App\User\ClienteModel;
use Core\ConnectionFactory;

session_start();

echo "<h1>Debug Status do Usuário</h1>";

echo "<h2>Sessão:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red'>Não está logado!</p>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<p>user_id = $userId</p>";

// Verificar se é cliente
$isCliente = ClienteModel::isClienteRegistered($userId);
echo "<p><strong>isClienteRegistered:</strong> " . ($isCliente ? 'SIM' : 'NÃO') . "</p>";

// Tentar buscar como cliente
$cliente = ClienteModel::findByClienteId($userId);
echo "<p><strong>findByClienteId:</strong> " . ($cliente ? 'ENCONTRADO' : 'NÃO ENCONTRADO') . "</p>";

// Verificar diretamente no banco
$pdo = ConnectionFactory::getConnection('read_only');

echo "<h2>Tabela pessoa:</h2>";
$stmt = $pdo->prepare("SELECT id, nome, email FROM pessoa WHERE id = ?");
$stmt->execute([$userId]);
$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($pessoa);
echo "</pre>";

echo "<h2>Tabela cliente:</h2>";
$stmt = $pdo->prepare("SELECT * FROM cliente WHERE id = ?");
$stmt->execute([$userId]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cliente) {
    echo "<pre>";
    print_r($cliente);
    echo "</pre>";
} else {
    echo "<p style='color:orange'>Não existe registro na tabela cliente para id=$userId</p>";
    echo "<p><a href='/completar-cadastro'>Clique aqui para completar seu cadastro</a></p>";
}
