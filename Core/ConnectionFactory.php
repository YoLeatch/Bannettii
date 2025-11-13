<?php

namespace Core;
use PDO;

class ConnectionFactory {
    /**
     * Armazena as configurações carregadas do arquivo.
     * @var array|null
     */
    private static array $config;
    /**
     * Armazena as conexões ativas (padrão Singleton)
     * @var array
     */
    private static $connections = [];

    /**
     * O método principal. Pede uma conexão pelo nome do perfil.
     *
     * @param string $profile O nome do perfil (ex: 'default', 'read_only')
     * @return PDO A instância da conexão PDO
     */
    public static function getConnection(string $profile = 'default'): \PDO {
        if (isset(self::$connections[$profile])) {
            return self::$connections[$profile];
        }

        if (self::$config === null) {
            self::loadConfig();
        }

        if (!isset(self::$config[$profile])) {
            throw new \Exception("Perfil de banco de dados '$profile' não encontrado.");
        }

        $config = self::$config[$profile];

        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new \PDO($dsn, $config['user'], $config['password'], $options);

            self::$connections[$profile] = $pdo;

            return $pdo;


        } catch (\PDOException $e) {
            //deve "logar" esse erro, não "ecoar".
            throw new \Exception("Falha na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Método auxiliar privado para carregar o arquivo de configuração.
     */
    private static function loadConfig() {
        $configFile = __DIR__ . '/../config/database.php';

        if (!file_exists($configFile)) {
            throw new \Exception("Arquivo de configuração do banco de dados não encontrado.");
        }
        self::$config = require $configFile;
    }
}