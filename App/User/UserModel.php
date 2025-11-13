<?php

namespace App\User;

use Core\ConnectionFactory;

class UserModel {
    private int $id;
    private string $UID;
    private string $username;
    private string $email;
    private string $RG;
    private string $CPF_CNPJ;

    public function __construct(int $id, string $UID, string $username, string $email, string $RG, string $CPF_CNPJ) {
        $this->id = $id;
        $this->UID = $UID;
        $this->username = $username;
        $this->email = $email;
        $this->RG = $RG;
        $this->CPF_CNPJ = $CPF_CNPJ;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUID(): string {
        return $this->UID;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }
    public function getRG(): string {
        return $this->RG;
    }
    public function getCPF_CNPJ(): string {
        return $this->CPF_CNPJ;
    }

    public function getPasswordHash(): string {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(['id' => $this->id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['password_hash'] ?? '';
    }


    public static function findByData(string $column, $value): ?UserModel {
        $allowedColumns = ['id', 'email', 'UID']; 

        if (!in_array($column, $allowedColumns)) {
            throw new \Exception("Coluna de busca inválida.");
        }

        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE $column = ?");
        $stmt->execute([$value]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!($row && (isset($row['status'])) && $row['status'] === '1')) {
                return null; 
            }

            return new UserModel($row['id'], $row['UID'], $row['username'], $row['email'], $row['RG'], $row['CPF_CNPJ']);
    }

    public static function registerUser(string $username, string $email, string $password_hash, string $RG, string $CPF_CNPJ): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO users (UID, username, email, password_hash, RG, CPF_CNPJ, status) VALUES (:UID, :username, :email, :password_hash, :RG, :CPF_CNPJ, '1')");
        
        return $stmt->execute([
            'UID' => self::generateUID(),
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'RG' => $RG,
            'CPF_CNPJ' => $CPF_CNPJ
        ]);
    }

    private static function generateUID(): string {
        $UID = bin2hex(random_bytes(16));
        if(self::findByData('UID', $UID) !== null){
            self::generateUID();
        }
        return $UID;
    }
}