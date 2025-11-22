<?php

namespace App\Product;

use Core\ConnectionFactory;
use PDO;

class ProductModel {
    private ?int $id;
    private ?string $nome;
    private ?float $preco;
    private ?string $codigo; // User requested 'codigo'
    private ?int $subCategoriaId;
    private ?string $pesoliq;
    private ?string $pesototal;
    private ?string $dimensoes;
    private ?string $descricao;
    private ?string $data;
    private ?string $status;
    private ?int $desconto;
    private ?int $estoque;
    private array $images = [];

    public function __construct(
        ?int $id = null,
        ?string $nome = null,
        ?float $preco = null,
        ?string $codigo = null,
        ?int $subCategoriaId = null,
        ?string $pesoliq = null,
        ?string $pesototal = null,
        ?string $dimensoes = null,
        ?string $descricao = null,
        ?string $data = null,
        ?string $status = null,
        ?int $desconto = null,
        ?int $estoque = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->preco = $preco;
        $this->codigo = $codigo;
        $this->subCategoriaId = $subCategoriaId;
        $this->pesoliq = $pesoliq;
        $this->pesototal = $pesototal;
        $this->dimensoes = $dimensoes;
        $this->descricao = $descricao;
        $this->data = $data;
        $this->status = $status;
        $this->desconto = $desconto;
        $this->estoque = $estoque;
        
        if ($this->id) {
            $this->images = $this->fetchImages();
        }
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getNome(): ?string {
        return $this->nome;
    }

    public function getPreco(): ?float {
        return $this->preco;
    }

    public function getCodigo(): ?string {
        return $this->codigo;
    }

    public function getSubCategoriaId(): ?int {
        return $this->subCategoriaId;
    }

    public function getPesoLiq(): ?string {
        return $this->pesoliq;
    }

    public function getPesoTotal(): ?string {
        return $this->pesototal;
    }

    public function getDimensoes(): ?string {
        return $this->dimensoes;
    }

    public function getDescricao(): ?string {
        return $this->descricao;
    }

    public function getData(): ?string {
        return $this->data;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function getDesconto(): ?int {
        return $this->desconto;
    }

    public function getEstoque(): ?int {
        return $this->estoque;
    }

    public function getImages(): array {
        return $this->images;
    }

    private function fetchImages(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM Imagem WHERE Produto = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?ProductModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM Produto WHERE id = :id AND status = '1'");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new ProductModel(
                $row['id'],
                $row['nome'],
                (float)$row['preco'],
                $row['cod'], // Database column is 'cod'
                $row['Sub_categoria'],
                $row['pesoliq'],
                $row['pesototal'],
                $row['dimensoes'],
                $row['descricao'],
                $row['data'],
                $row['status'],
                $row['desconto'],
                $row['estoque']
            );
        }
        return null;
    }

    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM Produto WHERE status = '1'");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $products = [];
        foreach ($rows as $row) {
            $products[] = new ProductModel(
                $row['id'],
                $row['nome'],
                (float)$row['preco'],
                $row['cod'],
                $row['Sub_categoria'],
                $row['pesoliq'],
                $row['pesototal'],
                $row['dimensoes'],
                $row['descricao'],
                $row['data'],
                $row['status'],
                $row['desconto'],
                $row['estoque']
            );
        }
        return $products;
    }
}