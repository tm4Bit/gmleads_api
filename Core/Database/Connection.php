<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Facade\Config;
use PDO;
use PDOException;

class Connection
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
            $dsn = 'mysql:'.http_build_query($db, '', ';');
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Falha ao conectar ao banco de dados: {$e->getMessage()}");
        }
    }

    /**
     * Retorna a instância ativa da conexão PDO.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
