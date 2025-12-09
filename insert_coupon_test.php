<?php
// insert_coupon_test.php

// require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use Core\ConnectionFactory;
use Core\DotEnv;
use App\Product\Models\ProductModel;

// Carregar variáveis de ambiente
(new DotEnv(__DIR__ . '/.env'))->load();

try {
    echo "--- Inserindo Cupom de Teste ---\n\n";

    // 1. Buscar um produto existente para pegar a subcategoria
    $produtos = ProductModel::findAll();
    
    if (empty($produtos)) {
        die("ERRO: Nenhum produto encontrado. Cadastre um produto primeiro.\n");
    }
    
    $produto = $produtos[0];
    $subCategoriaId = $produto->getSubCategoria();
    
    echo "Produto encontrado: " . $produto->getNome() . " (ID: " . $produto->getId() . ")\n";
    echo "Subcategoria ID: " . $subCategoriaId . "\n\n";
    
    // 2. Conectar ao banco
    $pdo = ConnectionFactory::getConnection();
    
    // 3. Inserir Cupom TESTE10
    $codCupom = 'TESTE10';
    $dataHoje = date('Y-m-d');
    $dataValidade = date('Y-m-d', strtotime('+30 days'));
    
    // Verifica se já existe
    $stmt = $pdo->prepare("SELECT id FROM cupom WHERE cod = ?");
    $stmt->execute([$codCupom]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $cupomId = $existing['id'];
        echo "Cupom '$codCupom' já existe (ID: $cupomId). Atualizando validade...\n";
        
        $stmt = $pdo->prepare("UPDATE cupom SET validade = ?, status = '1' WHERE id = ?");
        $stmt->execute([$dataValidade, $cupomId]);
    } else {
        echo "Criando novo cupom '$codCupom'...\n";
        
        $stmt = $pdo->prepare("INSERT INTO cupom (cod, criacao, validade, status) VALUES (?, ?, ?, '1')");
        $stmt->execute([$codCupom, $dataHoje, $dataValidade]);
        $cupomId = $pdo->lastInsertId();
    }
    
    echo "Cupom ID: " . $cupomId . "\n";
    
    // 4. Vincular à Subcategoria
    echo "Vinculando cupom à subcategoria $subCategoriaId...\n";
    
    // Remove vínculos anteriores deste cupom para evitar duplicidade
    $stmt = $pdo->prepare("DELETE FROM cupom_sub_categoria WHERE cupom_id = ?");
    $stmt->execute([$cupomId]);
    
    // Insere novo vínculo
    $stmt = $pdo->prepare("INSERT INTO cupom_sub_categoria (cupom_id, sub_categoria_id) VALUES (?, ?)");
    $stmt->execute([$cupomId, $subCategoriaId]);
    
    echo "\nSUCESSO! Cupom criado e vinculado.\n";
    echo "Código: $codCupom\n";
    echo "Aplica-se a produtos da subcategoria ID: $subCategoriaId\n";
    echo "Validade: $dataValidade\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
