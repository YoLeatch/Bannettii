<?php
/**
 * PessoaModel - Modelo para manipulação de dados da tabela 'pessoa'
 * 
 * Este modelo gerencia os dados pessoais dos usuários do sistema,
 * incluindo informações de contato e relacionamentos com telefones.
 * 
 * Tabelas relacionadas:
 * - pessoa: Dados principais do usuário
 * - telefone: Números de telefone associados à pessoa
 * 
 * @package App\User
 */

namespace App\User\Models;

use Core\ConnectionFactory;
use PDO;

class PessoaModel {
    // ============================
    // PROPRIEDADES DO MODELO
    // ============================
    
    /** @var int ID único da pessoa no banco de dados */
    protected int $id;
    
    /** @var string Nome completo da pessoa */
    protected string $nome;
    
    /** @var string|null Caminho da imagem de perfil */
    protected ?string $image;
    
    /** @var string Senha criptografada do usuário */
    protected string $senha;
    
    /** @var string Email único do usuário */
    protected string $email;
    
    /** @var string|null Data de nascimento (formato: YYYY-MM-DD) */
    protected ?string $dt_nascimento;
    
    /** @var string Data de criação do registro */
    protected string $dt_criacao;
    
    /** @var string CPF único do usuário (formato: XXX.XXX.XXX-XX) */
    protected string $CPF;
    
    /** @var string Status do registro ('1' = ativo, '0' = inativo) */
    protected string $status;
    
    /** @var int UID único gerado para o usuário */
    protected int $Uid;
    
    /** @var array Lista de endereços associados à pessoa */
    protected array $addresses = [];
    
    /** @var array Lista de telefones associados à pessoa */
    protected array $telefones = [];

    // ============================
    // CONSTRUTOR
    // ============================
    
    /**
     * Construtor do modelo Pessoa
     * 
     * Inicializa todas as propriedades e carrega os relacionamentos
     * com endereços e telefones automaticamente.
     * 
     * @param int $id ID da pessoa
     * @param string $nome Nome completo
     * @param string|null $image Caminho da imagem de perfil
     * @param string $senha Senha criptografada
     * @param string $email Email do usuário
     * @param string|null $dt_nascimento Data de nascimento
     * @param string $dt_criacao Data de criação do registro
     * @param string $CPF CPF do usuário
     * @param string $status Status do registro
     * @param int $Uid UID único do usuário
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
        int $Uid
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->image = $image;
        $this->senha = $senha;
        $this->email = $email;
        $this->dt_nascimento = $dt_nascimento;
        $this->dt_criacao = $dt_criacao;
        $this->CPF = $CPF;
        $this->status = $status;
        $this->Uid = $Uid;
        
        // Carrega endereços associados à pessoa
        if ($this->id) {
            try {
                $this->addresses = \App\Services\Models\EnderecoModel::getByPessoa($this->id);
            } catch (\Exception $e) {
                $this->addresses = [];
            }
            
            // Carrega telefones associados à pessoa
            try {
                $this->telefones = $this->loadTelefones();
            } catch (\Exception $e) {
                $this->telefones = [];
            }
        }
    }

    // ============================
    // GETTERS - Métodos de acesso às propriedades
    // ============================
    
    /**
     * Retorna o ID da pessoa
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Retorna o nome da pessoa
     * @return string
     */
    public function getNome(): string {
        return $this->nome;
    }

    /**
     * Retorna o caminho da imagem de perfil
     * @return string|null
     */
    public function getImage(): ?string {
        return $this->image;
    }

    /**
     * Retorna a senha criptografada
     * @return string
     */
    public function getSenha(): string {
        return $this->senha;
    }

    /**
     * Retorna o email do usuário
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * Retorna a data de nascimento
     * @return string|null
     */
    public function getDtNascimento(): ?string {
        return $this->dt_nascimento;
    }

    /**
     * Retorna a data de criação do registro
     * @return string
     */
    public function getDtCriacao(): string {
        return $this->dt_criacao;
    }

    /**
     * Retorna o CPF do usuário
     * @return string
     */
    public function getCpf(): string {
        return $this->CPF;
    }

    /**
     * Retorna o status do registro
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * Retorna o UID único do usuário
     * @return int
     */
    public function getUid(): int {
        return $this->Uid;
    }

    /**
     * Retorna a lista de endereços
     * @return array
     */
    public function getAddresses(): array {
        return $this->addresses;
    }

    /**
     * Retorna a lista de telefones
     * @return array
     */
    public function getTelefones(): array {
        return $this->telefones;
    }

    // ============================
    // MÉTODOS DE TELEFONE
    // ============================
    
    /**
     * Carrega os telefones associados à pessoa do banco de dados
     * 
     * @return array Lista de telefones com id, numero e status
     */
    private function loadTelefones(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT id, telefone, status 
            FROM telefone 
            WHERE pessoa = ? AND status = '1'
        ");
        $stmt->execute([$this->id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona um novo telefone para a pessoa
     * 
     * @param int $telefone Número do telefone
     * @return bool Retorna true se inserido com sucesso
     */
    public function addTelefone(int $telefone): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("
            INSERT INTO telefone (telefone, pessoa, status) 
            VALUES (:telefone, :pessoa, '1')
        ");
        
        $result = $stmt->execute([
            'telefone' => $telefone,
            'pessoa' => $this->id
        ]);
        
        // Recarrega a lista de telefones
        if ($result) {
            $this->telefones = $this->loadTelefones();
        }
        
        return $result;
    }

    /**
     * Atualiza um telefone existente
     * 
     * @param int $telefoneId ID do telefone a ser atualizado
     * @param int $novoTelefone Novo número de telefone
     * @return bool Retorna true se atualizado com sucesso
     */
    public function updateTelefone(int $telefoneId, int $novoTelefone): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("
            UPDATE telefone 
            SET telefone = :telefone 
            WHERE id = :id AND pessoa = :pessoa
        ");
        
        $result = $stmt->execute([
            'telefone' => $novoTelefone,
            'id' => $telefoneId,
            'pessoa' => $this->id
        ]);
        
        // Recarrega a lista de telefones
        if ($result) {
            $this->telefones = $this->loadTelefones();
        }
        
        return $result;
    }

    /**
     * Remove (desativa) um telefone
     * 
     * @param int $telefoneId ID do telefone a ser removido
     * @return bool Retorna true se desativado com sucesso
     */
    public function removeTelefone(int $telefoneId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $stmt = $pdo->prepare("
            UPDATE telefone 
            SET status = '0' 
            WHERE id = :id AND pessoa = :pessoa
        ");
        
        $result = $stmt->execute([
            'id' => $telefoneId,
            'pessoa' => $this->id
        ]);
        
        // Recarrega a lista de telefones
        if ($result) {
            $this->telefones = $this->loadTelefones();
        }
        
        return $result;
    }

    /**
     * Busca todos os telefones de uma pessoa pelo ID
     * 
     * @param int $pessoaId ID da pessoa
     * @return array Lista de telefones
     */
    public static function getTelefonesByPessoa(int $pessoaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->prepare("
            SELECT id, telefone, status 
            FROM telefone 
            WHERE pessoa = ?
        ");
        $stmt->execute([$pessoaId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // MÉTODOS DE BUSCA
    // ============================
    
    /**
     * Busca uma pessoa por um campo específico
     * 
     * Permite buscar por id, uid, email ou qualquer outro campo da tabela.
     * 
     * @param string $search Nome da coluna para busca
     * @param mixed $value Valor a ser buscado
     * @return PessoaModel|null Retorna a pessoa encontrada ou null
     */
    public static function findByData(string $search, $value): ?PessoaModel {
        $pdo = ConnectionFactory::getConnection('read_only');

        // Lista de colunas permitidas para busca segura
        $allowedColumns = ['id', 'uid', 'email', 'CPF'];
        if (!in_array($search, $allowedColumns)) {
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE $search = ? AND status = '1'");
        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new PessoaModel(
            $row['id'],
            $row['nome'],
            $row['image'],
            $row['senha'],
            $row['email'],
            $row['dt_nascimento'] ?? null,
            $row['dt_criacao'],
            $row['CPF'],
            $row['status'],
            (int)$row['uid']
        );
    }

    /**
     * Busca todas as pessoas ativas
     * 
     * @return array Lista de objetos PessoaModel
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        
        $stmt = $pdo->query("SELECT * FROM pessoa WHERE status = '1' ORDER BY nome");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pessoas = [];
        foreach ($rows as $row) {
            $pessoas[] = new PessoaModel(
                $row['id'],
                $row['nome'],
                $row['image'],
                $row['senha'],
                $row['email'],
                $row['dt_nascimento'] ?? null,
                $row['dt_criacao'],
                $row['CPF'],
                $row['status'],
                (int)$row['uid']
            );
        }
        
        return $pessoas;
    }

    // ============================
    // MÉTODOS DE ATUALIZAÇÃO
    // ============================
    
    /**
     * Atualiza dados básicos da pessoa
     * 
     * @param string|null $nome Novo nome (opcional)
     * @param string|null $email Novo email (opcional)
     * @param string|null $image Nova imagem (opcional)
     * @return bool Retorna true se atualizado com sucesso
     */
    public function updatePessoa(string $nome = null, string $email = null, string $image = null): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            UPDATE pessoa 
            SET nome = :nome, email = :email, image = :image 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $this->id,
            'nome' => $nome ?? $this->nome,
            'email' => $email ?? $this->email,
            'image' => $image ?? $this->image
        ]);
    }

    /**
     * Atualiza a senha do usuário
     * 
     * @param int $id ID da pessoa
     * @param string $newPasswordHash Nova senha já criptografada
     * @return bool Retorna true se atualizado com sucesso
     */
    public static function updatePassword(int $id, string $newPasswordHash): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET senha = :senha WHERE id = :id");
        
        return $stmt->execute([
            'senha' => $newPasswordHash,
            'id' => $id
        ]);
    }

    /**
     * Atualiza todos os dados do usuário
     * 
     * Método completo para atualização de usuário, incluindo senha e imagem opcionais.
     * 
     * @param int $id ID da pessoa
     * @param string $nome Novo nome
     * @param string $email Novo email
     * @param string $cpf Novo CPF
     * @param string $status Novo status
     * @param string $password Nova senha (vazia = não alterar)
     * @param string|null $image Nova imagem (null = não alterar)
     * @return bool Retorna true se atualizado com sucesso
     */
    public static function updateUser(
        int $id, 
        string $nome, 
        string $email, 
        string $cpf, 
        string $status, 
        string $password = '', 
        string $image = null
    ): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE pessoa SET nome = :nome, email = :email, CPF = :cpf, status = :status";
        $params = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'cpf' => $cpf,
            'status' => $status
        ];

        // Adiciona senha à query se fornecida
        if (!empty($password)) {
            $sql .= ", senha = :senha";
            $params['senha'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Adiciona imagem à query se fornecida
        if ($image !== null) {
            $sql .= ", image = :image";
            $params['image'] = $image;
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // ============================
    // MÉTODOS DE CRIAÇÃO
    // ============================
    
    /**
     * Registra uma nova pessoa no sistema
     * 
     * NOTA: Este método NÃO gerencia transações internamente.
     * O chamador deve iniciar e controlar a transação se necessário.
     * Isso permite que ClienteModel e FuncionarioModel controlem
     * suas próprias transações ao criar pessoa + cliente/funcionario.
     * 
     * @param string $nome Nome completo
     * @param string $email Email único
     * @param string $senha Senha já criptografada
     * @param string $cpf CPF único
     * @param string|null $image Caminho da imagem (opcional)
     * @return PessoaModel|null Retorna a pessoa criada ou null em caso de erro
     */
    public static function registerPessoa(
        string $nome, 
        string $email, 
        string $senha, 
        string $cpf, 
        ?string $image = null
    ): ?PessoaModel {
        $pdo = ConnectionFactory::getConnection('default');
        $logFile = __DIR__ . '/../../../debug_register.txt';
        
        try {
            file_put_contents($logFile, "PessoaModel::registerPessoa - Preparando INSERT\n", FILE_APPEND);
            
            $stmt = $pdo->prepare("
                INSERT INTO pessoa (nome, image, senha, email, CPF, dt_criacao, status, uid) 
                VALUES (:nome, :image, :senha, :email, :cpf, NOW(), '1', :uid)
            ");

            // Gera um UID único para o usuário
            $uid = self::generateUID();
            
            file_put_contents($logFile, "PessoaModel::registerPessoa - UID gerado: $uid\n", FILE_APPEND);

            $result = $stmt->execute([
                'nome' => $nome,
                'image' => $image,
                'senha' => $senha,
                'email' => $email,
                'cpf' => $cpf,
                'uid' => $uid
            ]);
            
            file_put_contents($logFile, "PessoaModel::registerPessoa - INSERT result: " . ($result ? 'true' : 'false') . "\n", FILE_APPEND);

            $newId = (int) $pdo->lastInsertId();
            
            file_put_contents($logFile, "PessoaModel::registerPessoa - New ID: $newId\n", FILE_APPEND);
            
            if ($newId <= 0) {
                file_put_contents($logFile, "PessoaModel::registerPessoa - ERRO: ID inválido\n", FILE_APPEND);
                return null;
            }

            // Buscar usando a MESMA conexão (default) para ver dados da transação
            $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE id = ?");
            $stmt->execute([$newId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$row) {
                file_put_contents($logFile, "PessoaModel::registerPessoa - Não encontrou pessoa após INSERT\n", FILE_APPEND);
                return null;
            }
            
            file_put_contents($logFile, "PessoaModel::registerPessoa - Pessoa encontrada, criando objeto\n", FILE_APPEND);

            return new PessoaModel(
                $row['id'],
                $row['nome'],
                $row['image'],
                $row['senha'],
                $row['email'],
                $row['dt_nascimento'] ?? null,
                $row['dt_criacao'],
                $row['CPF'],
                $row['status'],
                (int)$row['uid']
            );

        } catch (\Exception $e) {
            file_put_contents($logFile, "PessoaModel::registerPessoa - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log("Erro no registro de pessoa: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gera um UID único para o usuário
     * 
     * O UID é um número aleatório entre 10000000 e 2147483647
     * que não existe em nenhum outro registro.
     * 
     * @return int UID único gerado
     */
    private static function generateUID(): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        $uid = 0;
        
        do {
            $uid = random_int(10000000, 2147483647);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE uid = ?");
            $stmt->execute([$uid]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        
        return $uid;
    }

    // ============================
    // MÉTODOS DE EXCLUSÃO
    // ============================
    
    /**
     * Desativa uma pessoa (soft delete)
     * 
     * @param int $id ID da pessoa
     * @return bool Retorna true se desativado com sucesso
     */
    /**
     * Desativa uma pessoa pelo ID (soft delete)
     * 
     * @param int $id ID da pessoa
     * @return bool Retorna true se desativado com sucesso
     */
    public static function deactivateById(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET status = '0' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Reativa uma pessoa
     * 
     * @param int $id ID da pessoa
     * @return bool Retorna true se reativado com sucesso
     */
    /**
     * Reativa uma pessoa pelo ID
     * 
     * @param int $id ID da pessoa
     * @return bool Retorna true se reativado com sucesso
     */
    public static function activateById(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET status = '1' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Remove pessoa (soft delete - seta status = '0')
     * 
     * Como a tabela pessoa possui campo status, o delete
     * apenas desativa o registro. Os telefones também são desativados.
     * 
     * @param int $id ID da pessoa
     * @return bool Retorna true se removido com sucesso
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            // Desativa pessoa
            $stmt = $pdo->prepare("UPDATE pessoa SET status = '0' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Desativa telefones (soft delete - telefone tem status)
            $stmt = $pdo->prepare("UPDATE telefone SET status = '0' WHERE pessoa = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Remove um telefone (soft delete - seta status = '0')
     * 
     * Como a tabela telefone possui campo status, o delete
     * apenas desativa o registro.
     * 
     * @param int $telefoneId ID do telefone
     * @return bool Retorna true se removido com sucesso
     */
    public static function deleteTelefone(int $telefoneId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE telefone SET status = '0' WHERE id = ?");
        return $stmt->execute([$telefoneId]);
    }
}