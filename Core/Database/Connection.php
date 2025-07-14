<?php

declare(strict_types=1);

namespace Core;

use Core\Facade\Config;
use PDO;
use PDOException;

/**
 * Gerencia a conexão com o banco de dados e fornece a instância PDO.
 */
class Database
{
    /**
     * @var PDO A instância do objeto PDO.
     */
    private PDO $connection;

    public function __construct()
    {
        $db = Config::get('database.db');
        $username = Config::get('database.username');
        $password = Config::get('database.password');
        
        try {
            $dsn = 'mysql:' . http_build_query($db, '', ';');
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Boa prática para lançar exceções.
            ]);
        } catch (PDOException $e) {
            // Em uma aplicação real, logar este erro é crucial.
            throw new PDOException("Falha ao conectar ao banco de dados: {$e->getMessage()}");
        }
    }

    /**
     * Retorna a instância ativa da conexão PDO.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}