<?php

declare(strict_types=1);

namespace Core\Facade;

use Core\Config\ConfigInterface;
use Core\Config\ConfigLoader;

class Config
{
    /**
     * A instância única do ConfigLoader.
     */
    private static ?ConfigInterface $instance = null;

    /**
     * Método para obter a instância do ConfigLoader.
     *
     * Este método garante que sempre retornamos a mesma instância do ConfigLoader,
     * seguindo o padrão Singleton.
     */
    private static function resolveInstance(): ConfigInterface
    {
        if (self::$instance === null) {
            self::$instance = new ConfigLoader;
        }

        return self::$instance;
    }

    /**
     * O método mágico que captura todas as chamadas estáticas.
     *
     * Quando você chama Config::get('db.host'), o PHP não encontra um método
     * estático chamado 'get' e, então, chama este método mágico.
     *
     * @param  string  $name  O nome do método chamado (ex: 'get').
     * @param  array  $arguments  Os argumentos passados para o método (ex: ['db.host']).
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $instance = self::resolveInstance();

        return $instance->{$name}(...$arguments);
    }
}
