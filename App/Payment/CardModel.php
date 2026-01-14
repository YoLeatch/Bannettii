<?php
/**
 * CardModel - Modelo para tabela 'cartao_credito'
 * 
 * Gerencia cartões de crédito dos clientes.
 * 
 * Tabela: cartao_credito (FK: usuario -> cliente.id)
 * 
 * @package App\Payment
 */

namespace App\Payment;

use Core\ConnectionFactory;
use Core\Encryption;
use PDO;

class CardModel {
    /** @var int ID do cartão */
    protected int $id;
    
    /** @var string Nome no cartão */
    protected string $nome;
    
    /** @var string Número do cartão (mascarado ou completo) */
    protected string $numero;
    
    /** @var string Data de validade (YYYY-MM-DD) */
    protected string $validade;
    
    /** @var int ID do usuário/cliente */
    protected int $usuario;
    
    /** @var string Status ('1' = ativo, '0' = inativo) */
    protected string $status;

    /**
     * Construtor do modelo Cartão
     */
    public function __construct(int $id, string $nome, string $numero, string $validade, int $usuario, string $status) {
        $this->id = $id;
        $this->nome = $nome;
        $this->numero = $numero;
        $this->validade = $validade;
        $this->usuario = $usuario;
        $this->status = $status;
    }

    // === GETTERS ===
    
    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    
    /**
     * Retorna o número do cartão descriptografado
     * ATENÇÃO: Use com cautela, prefira métodos mascarados
     */
    public function getNumero(): string {
        return Encryption::decrypt($this->numero);
    }
    
    /**
     * Retorna o número criptografado (como está no banco)
     */
    public function getNumeroEncrypted(): string {
        return $this->numero;
    }
    
    public function getValidade(): string { return $this->validade; }
    public function getUsuario(): int { return $this->usuario; }
    public function getStatus(): string { return $this->status; }
    
    /**
     * Verifica se o cartão está vencido
     */
    public function isExpired(): bool {
        return strtotime($this->validade) < strtotime(date('Y-m-d'));
    }
    
    /**
     * Retorna validade formatada (MM/YY)
     */
    public function getValidadeFormatada(): string {
        return date('m/y', strtotime($this->validade));
    }
    
    /**
     * Retorna os últimos 4 dígitos do cartão
     */
    public function getUltimosDigitos(): string {
        $numeros = preg_replace('/\D/', '', $this->getNumero());
        return substr($numeros, -4);
    }
    
    /**
     * Retorna número mascarado (•••• •••• •••• 1234)
     */
    public function getNumeroMascarado(): string {
        return '•••• •••• •••• ' . $this->getUltimosDigitos();
    }
    
    /**
     * Identifica a bandeira do cartão pela numeração
     * 
     * Regras baseadas nos primeiros dígitos:
     * - Visa: começa com 4
     * - Mastercard: começa com 51-55 ou 2221-2720
     * - Elo: começa com 636368, 438935, 504175, 451416, 636297, 5067, 4576, 4011
     * - American Express: começa com 34 ou 37
     * - Hipercard: começa com 606282
     * - Diners Club: começa com 300-305, 36, 38
     */
    public function getBandeira(): string {
        $numero = preg_replace('/\D/', '', $this->getNumero());
        
        if (empty($numero)) {
            return 'CARD';
        }
        
        // Visa: começa com 4
        if (preg_match('/^4/', $numero)) {
            return 'VISA';
        }
        
        // Mastercard: 51-55 ou 2221-2720
        if (preg_match('/^5[1-5]/', $numero) || preg_match('/^2(2[2-9][1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720)/', $numero)) {
            return 'MASTERCARD';
        }
        
        // Elo
        if (preg_match('/^(636368|438935|504175|451416|636297|5067|4576|4011)/', $numero)) {
            return 'ELO';
        }
        
        // American Express: 34 ou 37
        if (preg_match('/^3[47]/', $numero)) {
            return 'AMEX';
        }
        
        // Hipercard: 606282
        if (preg_match('/^606282/', $numero)) {
            return 'HIPERCARD';
        }
        
        // Diners Club
        if (preg_match('/^3(0[0-5]|[68])/', $numero)) {
            return 'DINERS';
        }
        
        // Discover
        if (preg_match('/^6(?:011|5)/', $numero)) {
            return 'DISCOVER';
        }
        
        return 'CARD';
    }
    
    /**
     * Valida número de cartão usando algoritmo de Luhn (Mod 10)
     * 
     * O algoritmo de Luhn é usado por todas as principais bandeiras
     * para validar se um número de cartão é potencialmente válido.
     * 
     * @param string $numero Número do cartão (apenas dígitos)
     * @return bool True se o número passa na validação
     */
    public static function isValidNumber(string $numero): bool {
        // Remove caracteres não numéricos
        $numero = preg_replace('/\D/', '', $numero);
        
        // Tamanho inválido (cartões têm entre 13 e 19 dígitos)
        if (strlen($numero) < 13 || strlen($numero) > 19) {
            return false;
        }
        
        // Algoritmo de Luhn
        $sum = 0;
        $length = strlen($numero);
        $parity = $length % 2;
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $numero[$i];
            
            // Dobra cada segundo dígito da direita para esquerda
            if ($i % 2 == $parity) {
                $digit *= 2;
                // Se o resultado for maior que 9, subtrai 9
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        // O número é válido se a soma for divisível por 10
        return ($sum % 10) == 0;
    }
    
    /**
     * Valida se este cartão tem um número válido
     */
    public function isValid(): bool {
        return self::isValidNumber($this->getNumero());
    }

    // === MÉTODOS DE BUSCA ===
    
    /**
     * Busca cartão por ID
     */
    public static function findById(int $id): ?CardModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cartao_credito WHERE id = ? AND status = '1'");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca todos cartões de um usuário
     */
    public static function findByUsuario(int $usuarioId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cartao_credito WHERE usuario = ? AND status = '1' ORDER BY id DESC");
        $stmt->execute([$usuarioId]);
        $cards = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cards[] = self::createFromRow($row);
        }
        return $cards;
    }

    /**
     * Retorna todos os cartões ativos
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM cartao_credito WHERE status = '1' ORDER BY id DESC");
        $cards = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cards[] = self::createFromRow($row);
        }
        return $cards;
    }

    /**
     * Cria objeto a partir de linha do banco
     */
    private static function createFromRow(array $row): CardModel {
        return new CardModel(
            $row['id'],
            $row['nome'],
            $row['numero'] ?? '',
            $row['validade'],
            (int)$row['usuario'],
            $row['status']
        );
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    /**
     * Cria novo cartão para usuário
     * 
     * @param string $nome Nome no cartão
     * @param string $numero Número do cartão
     * @param string $validade Data de validade (YYYY-MM-DD)
     * @param int $usuarioId ID do cliente
     */
    public static function create(string $nome, string $numero, string $validade, int $usuarioId): ?CardModel {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Criptografa o número do cartão antes de salvar
        $numeroEncrypted = Encryption::encrypt($numero);
        
        $stmt = $pdo->prepare("
            INSERT INTO cartao_credito (nome, numero, validade, usuario, status) 
            VALUES (:nome, :numero, :validade, :usuario, '1')
        ");
        
        $result = $stmt->execute([
            'nome' => $nome,
            'numero' => $numeroEncrypted,
            'validade' => $validade,
            'usuario' => $usuarioId
        ]);
        
        if ($result) {
            return self::findById((int)$pdo->lastInsertId());
        }
        return null;
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    /**
     * Atualiza dados do cartão
     */
    public function update(string $nome, string $validade): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            UPDATE cartao_credito SET nome = :nome, validade = :validade WHERE id = :id
        ");
        $result = $stmt->execute(['nome' => $nome, 'validade' => $validade, 'id' => $this->id]);
        if ($result) {
            $this->nome = $nome;
            $this->validade = $validade;
        }
        return $result;
    }

    /**
     * Desativa o cartão (soft delete)
     */
    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cartao_credito SET status = '0' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Reativa o cartão
     */
    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cartao_credito SET status = '1' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Remove cartão (soft delete - seta status = '0')
     * 
     * Como a tabela cartao_credito possui campo status,
     * o delete apenas desativa o registro.
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cartao_credito SET status = '0' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Conta cartões ativos de um usuário
     */
    public static function countByUsuario(int $usuarioId): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cartao_credito WHERE usuario = ? AND status = '1'");
        $stmt->execute([$usuarioId]);
        return (int)$stmt->fetchColumn();
    }
}
