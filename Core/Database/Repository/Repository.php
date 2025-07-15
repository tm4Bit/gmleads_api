<?php

declare(strict_types=1);

namespace Core\Database\Repository;

use Core\Database\Connection;
use PDO;
use PDOStatement;

abstract class Repository
{
    protected PDO $pdo;

    public function __construct(Connection $database)
    {
        $this->pdo = $database->getConnection();
    }

    /**
     * Prepara e executa uma consulta SQL, retornando o statement.
     *
     * @param  string  $sql  A consulta SQL a ser executada.
     * @param  array  $params  Os parâmetros para a consulta.
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Inicia uma transação.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma a transação atual.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Reverte a transação atual.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Verifica se uma transação está ativa.
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
