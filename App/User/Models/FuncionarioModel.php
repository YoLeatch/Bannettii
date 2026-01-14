<?php
/**
 * FuncionarioModel - Modelo para manipulação de dados da tabela 'funcionario'
 * 
 * Este modelo gerencia os dados dos funcionários do sistema, extendendo
 * a classe PessoaModel para herdar os atributos pessoais.
 * 
 * Tabelas relacionadas:
 * - funcionario: Dados específicos do funcionário
 * - pessoa: Dados pessoais (via herança)
 * - cargo: Cargos disponíveis no sistema
 * - funcionario_cargo: Relacionamento N:N entre funcionário e cargo
 * 
 * @package App\User\Models
 */

namespace App\User\Models;


use Core\ConnectionFactory;
use PDO;

class FuncionarioModel extends PessoaModel {
    // ============================
    // PROPRIEDADES DO MODELO
    // ============================
    
    /** @var int ID único do funcionário (mesmo que pessoa.id) */
    protected int $funcionarioId;
    
    /** @var string Número da carteirinha do funcionário */
    protected string $carteirinha;
    
    /** @var string Status do funcionário ('1' = ativo, '0' = inativo) */
    protected string $funcionarioStatus;
    
    /** @var array Lista de cargos associados ao funcionário */
    protected array $cargos = [];

    // ============================
    // CONSTRUTOR
    // ============================
    
    /**
     * Construtor do modelo Funcionario
     * 
     * Inicializa as propriedades do funcionário e herda os dados de pessoa.
     * Carrega automaticamente os cargos associados.
     * 
     * @param int $id ID da pessoa/funcionário
     * @param string $nome Nome completo
     * @param string|null $image Caminho da imagem de perfil
     * @param string $senha Senha criptografada
     * @param string $email Email do funcionário
     * @param string|null $dt_nascimento Data de nascimento
     * @param string $dt_criacao Data de criação do registro
     * @param string $CPF CPF do funcionário
     * @param string $status Status na tabela pessoa
     * @param int $Uid UID único
     * @param string $carteirinha Número da carteirinha
     * @param string $funcionarioStatus Status na tabela funcionario
     */
    public function __construct(
        int $id,
        string $nome,
        ?string $image,
        string $senha,
        string $email,
        ?string $dt_nascimento,
        string $dt_criacao,
        string $CPF,
        string $status,
        int $Uid,
        string $carteirinha,
        string $funcionarioStatus
    ) {
        // Chama o construtor da classe pai (PessoaModel)
        parent::__construct(
            $id, $nome, $image, $senha, $email, 
            $dt_nascimento, $dt_criacao, $CPF, $status, $Uid
        );
        
        $this->funcionarioId = $id;
        $this->carteirinha = $carteirinha;
        $this->funcionarioStatus = $funcionarioStatus;
        
        // Carrega os cargos associados ao funcionário
        $this->cargos = $this->loadCargos();
    }

    // ============================
    // GETTERS - Métodos de acesso
    // ============================
    
    /**
     * Retorna o ID do funcionário
     * @return int
     */
    public function getFuncionarioId(): int {
        return $this->funcionarioId;
    }

    /**
     * Retorna o número da carteirinha
     * @return string
     */
    public function getCarteirinha(): string {
        return $this->carteirinha;
    }

    /**
     * Retorna o status do funcionário
     * @return string
     */
    public function getFuncionarioStatus(): string {
        return $this->funcionarioStatus;
    }

    /**
     * Retorna a lista de cargos do funcionário
     * @return array
     */
    public function getCargos(): array {
        return $this->cargos;
    }

    /**
     * Retorna o cargo principal (maior poder)
     * @return array|null
     */
    public function getCargoPrincipal(): ?array {
        if (empty($this->cargos)) {
            return null;
        }
        
        // Ordena por poder decrescente e retorna o primeiro
        usort($this->cargos, fn($a, $b) => $b['poder'] - $a['poder']);
        return $this->cargos[0];
    }

    /**
     * Verifica se o funcionário possui um determinado nível de poder
     * 
     * @param int $nivelPoder Nível de poder mínimo requerido
     * @return bool
     */
    public function temPoder(int $nivelPoder): bool {
        foreach ($this->cargos as $cargo) {
            if ($cargo['poder'] >= $nivelPoder) {
                return true;
            }
        }
        return false;
    }

    // ============================
    // MÉTODOS DE CARGO
    // ============================
    
    /**
     * Carrega os cargos associados ao funcionário
     * 
     * Busca todos os cargos ativos vinculados ao funcionário
     * através da tabela funcionario_cargo.
     * 
     * @return array Lista de cargos com id, nome e poder
     */
    private function loadCargos(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT c.id, c.cargo, c.poder, fc.status as vinculo_status
            FROM cargo c
            INNER JOIN funcionario_cargo fc ON fc.cargo_id = c.id
            WHERE fc.funcionario_id = ? AND fc.status = '1'
            ORDER BY c.poder DESC
        ");
        $stmt->execute([$this->funcionarioId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona um cargo ao funcionário
     * 
     * @param int $cargoId ID do cargo a ser adicionado
     * @return bool Retorna true se adicionado com sucesso
     */
    public function addCargo(int $cargoId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se o vínculo já existe
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM funcionario_cargo 
            WHERE funcionario_id = ? AND cargo_id = ?
        ");
        $stmt->execute([$this->funcionarioId, $cargoId]);
        
        if ($stmt->fetchColumn() > 0) {
            // Atualiza o status para ativo
            $stmt = $pdo->prepare("
                UPDATE funcionario_cargo 
                SET status = '1' 
                WHERE funcionario_id = ? AND cargo_id = ?
            ");
            $result = $stmt->execute([$this->funcionarioId, $cargoId]);
        } else {
            // Insere novo vínculo
            $stmt = $pdo->prepare("
                INSERT INTO funcionario_cargo (funcionario_id, cargo_id, status) 
                VALUES (?, ?, '1')
            ");
            $result = $stmt->execute([$this->funcionarioId, $cargoId]);
        }
        
        // Recarrega os cargos
        if ($result) {
            $this->cargos = $this->loadCargos();
        }
        
        return $result;
    }

    /**
     * Remove um cargo do funcionário (soft delete)
     * 
     * @param int $cargoId ID do cargo a ser removido
     * @return bool Retorna true se removido com sucesso
     */
    public function removeCargo(int $cargoId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("
            UPDATE funcionario_cargo 
            SET status = '0' 
            WHERE funcionario_id = ? AND cargo_id = ?
        ");
        
        $result = $stmt->execute([$this->funcionarioId, $cargoId]);
        
        // Recarrega os cargos
        if ($result) {
            $this->cargos = $this->loadCargos();
        }
        
        return $result;
    }

    // ============================
    // MÉTODOS ESTÁTICOS DE CARGO
    // ============================
    
    /**
     * Retorna todos os cargos disponíveis no sistema
     * 
     * @return array Lista de cargos
     */
    public static function getAllCargos(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->query("SELECT * FROM cargo ORDER BY poder DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo cargo no sistema
     * 
     * @param string $nome Nome do cargo
     * @param int $poder Nível de poder do cargo
     * @return int|null ID do cargo criado ou null em caso de erro
     */
    public static function createCargo(string $nome, int $poder): ?int {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("INSERT INTO cargo (cargo, poder) VALUES (?, ?)");
        
        if ($stmt->execute([$nome, $poder])) {
            return (int) $pdo->lastInsertId();
        }
        
        return null;
    }

    /**
     * Atualiza um cargo existente
     * 
     * @param int $id ID do cargo
     * @param string $nome Novo nome
     * @param int $poder Novo nível de poder
     * @return bool Retorna true se atualizado com sucesso
     */
    public static function updateCargo(int $id, string $nome, int $poder): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("UPDATE cargo SET cargo = ?, poder = ? WHERE id = ?");
        return $stmt->execute([$nome, $poder, $id]);
    }

    /**
     * Remove um cargo do sistema (DELETE real - tabela cargo não possui status)
     * 
     * Só permite se não houver funcionários vinculados ativos
     * 
     * @param int $id ID do cargo
     * @return bool Retorna true se removido com sucesso
     */
    public static function deleteCargo(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se tem funcionários vinculados ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM funcionario_cargo WHERE cargo_id = ? AND status = '1'");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        // Remove vínculos inativos
        $pdo->prepare("DELETE FROM funcionario_cargo WHERE cargo_id = ? AND status = '0'")->execute([$id]);
        
        // Remove o cargo
        $stmt = $pdo->prepare("DELETE FROM cargo WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ============================
    // MÉTODOS DE BUSCA
    // ============================
    
    /**
     * Busca um funcionário por ID
     * 
     * @param int $id ID do funcionário
     * @return FuncionarioModel|null
     */
    public static function findById(int $id): ?FuncionarioModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as funcionario_status
            FROM pessoa p
            INNER JOIN funcionario f ON f.id = p.id
            WHERE p.id = ? AND p.status = '1' AND f.status = '1'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }

    /**
     * Busca um funcionário por carteirinha
     * 
     * @param string $carteirinha Número da carteirinha
     * @return FuncionarioModel|null
     */
    public static function findByCarteirinha(string $carteirinha): ?FuncionarioModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as funcionario_status
            FROM pessoa p
            INNER JOIN funcionario f ON f.id = p.id
            WHERE f.carteirinha = ? AND p.status = '1' AND f.status = '1'
        ");
        $stmt->execute([$carteirinha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }

    /**
     * Busca um funcionário por email
     * 
     * @param string $email Email do funcionário
     * @return FuncionarioModel|null
     */
    public static function findByEmail(string $email): ?FuncionarioModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as funcionario_status
            FROM pessoa p
            INNER JOIN funcionario f ON f.id = p.id
            WHERE p.email = ? AND p.status = '1' AND f.status = '1'
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return self::createFromRow($row);
    }

    /**
     * Retorna todos os funcionários ativos
     * 
     * @return array Lista de FuncionarioModel
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->query("
            SELECT p.*, f.carteirinha, f.status as funcionario_status
            FROM pessoa p
            INNER JOIN funcionario f ON f.id = p.id
            WHERE p.status = '1' AND f.status = '1'
            ORDER BY p.nome
        ");
        
        $funcionarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $funcionarios[] = self::createFromRow($row);
        }
        
        return $funcionarios;
    }

    /**
     * Busca funcionários por cargo
     * 
     * @param int $cargoId ID do cargo
     * @return array Lista de FuncionarioModel
     */
    public static function findByCargo(int $cargoId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as funcionario_status
            FROM pessoa p
            INNER JOIN funcionario f ON f.id = p.id
            INNER JOIN funcionario_cargo fc ON fc.funcionario_id = f.id
            WHERE fc.cargo_id = ? AND fc.status = '1' 
                AND p.status = '1' AND f.status = '1'
            ORDER BY p.nome
        ");
        $stmt->execute([$cargoId]);
        
        $funcionarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $funcionarios[] = self::createFromRow($row);
        }
        
        return $funcionarios;
    }

    /**
     * Cria um objeto FuncionarioModel a partir de uma linha do banco
     * 
     * @param array $row Dados da linha
     * @return FuncionarioModel
     */
    private static function createFromRow(array $row): FuncionarioModel {
        return new FuncionarioModel(
            $row['id'],
            $row['nome'],
            $row['image'],
            $row['senha'],
            $row['email'],
            $row['dt_nascimento'] ?? null,
            $row['dt_criacao'],
            $row['CPF'],
            $row['status'],
            (int)$row['uid'],
            $row['carteirinha'],
            $row['funcionario_status']
        );
    }

    // ============================
    // MÉTODOS DE CRIAÇÃO
    // ============================
    
    /**
     * Registra um novo funcionário
     * 
     * Cria registros nas tabelas pessoa e funcionario.
     * 
     * @param string $nome Nome completo
     * @param string $email Email único
     * @param string $senha Senha já criptografada
     * @param string $cpf CPF único
     * @param string $carteirinha Número da carteirinha
     * @param string|null $image Caminho da imagem (opcional)
     * @param array $cargosIds IDs dos cargos a serem atribuídos
     * @return FuncionarioModel|null Retorna o funcionário criado ou null
     */
    public static function register(
        string $nome,
        string $email,
        string $senha,
        string $cpf,
        string $carteirinha,
        ?string $image = null,
        array $cargosIds = []
    ): ?FuncionarioModel {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            // Registra a pessoa primeiro
            $pessoa = PessoaModel::registerPessoa($nome, $email, $senha, $cpf, $image);
            
            if (!$pessoa) {
                throw new \Exception("Erro ao criar registro de pessoa");
            }
            
            // Cria o registro de funcionário
            $stmt = $pdo->prepare("
                INSERT INTO funcionario (id, carteirinha, status) 
                VALUES (:id, :carteirinha, '1')
            ");
            
            $stmt->execute([
                'id' => $pessoa->getId(),
                'carteirinha' => $carteirinha
            ]);
            
            // Adiciona os cargos
            foreach ($cargosIds as $cargoId) {
                $stmt = $pdo->prepare("
                    INSERT INTO funcionario_cargo (funcionario_id, cargo_id, status) 
                    VALUES (?, ?, '1')
                ");
                $stmt->execute([$pessoa->getId(), $cargoId]);
            }
            
            $pdo->commit();
            
            return self::findById($pessoa->getId());
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Erro ao registrar funcionário: " . $e->getMessage());
            return null;
        }
    }

    // ============================
    // MÉTODOS DE ATUALIZAÇÃO
    // ============================
    
    /**
     * Atualiza os dados do funcionário
     * 
     * @param string $carteirinha Nova carteirinha
     * @return bool Retorna true se atualizado com sucesso
     */
    public function updateFuncionario(string $carteirinha): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("
            UPDATE funcionario 
            SET carteirinha = :carteirinha 
            WHERE id = :id
        ");
        
        $result = $stmt->execute([
            'carteirinha' => $carteirinha,
            'id' => $this->funcionarioId
        ]);
        
        if ($result) {
            $this->carteirinha = $carteirinha;
        }
        
        return $result;
    }

    /**
     * Desativa o funcionário (soft delete)
     * 
     * @return bool Retorna true se desativado com sucesso
     */
    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("UPDATE funcionario SET status = '0' WHERE id = ?");
        $result = $stmt->execute([$this->funcionarioId]);
        
        if ($result) {
            $this->funcionarioStatus = '0';
        }
        
        return $result;
    }

    /**
     * Reativa o funcionário
     * 
     * @return bool Retorna true se reativado com sucesso
     */
    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("UPDATE funcionario SET status = '1' WHERE id = ?");
        $result = $stmt->execute([$this->funcionarioId]);
        
        if ($result) {
            $this->funcionarioStatus = '1';
        }
        
        return $result;
    }

    /**
     * Remove funcionário (soft delete - seta status = '0')
     * 
     * Como a tabela funcionario possui campo status, o delete
     * apenas desativa o registro. Também desativa os vínculos com cargos.
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            // Desativa funcionário
            $stmt = $pdo->prepare("UPDATE funcionario SET status = '0' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Desativa vínculos com cargos (funcionario_cargo tem status)
            $stmt = $pdo->prepare("UPDATE funcionario_cargo SET status = '0' WHERE funcionario_id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

}

