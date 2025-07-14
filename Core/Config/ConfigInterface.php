<?php

declare(strict_types=1);

namespace Core\Config;

interface ConfigInterface
{
    /**
     * Busca um valor de configuração usando um caminho em notação de ponto.
     *
     * @param  string  $path  O caminho completo para a propriedade (ex: 'database.host').
     * @return mixed O valor da configuração ou nulo se não for encontrado.
     */
    public function get(string $path): mixed;
}
