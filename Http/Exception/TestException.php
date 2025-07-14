<?php

declare(strict_types=1);

namespace Http\Exception;

use Core\Exception\HttpNotFoundException;

class TestException extends HttpNotFoundException
{
    /**
     * @param  string  $message  A mensagem a ser exibida.
     */
    public function __construct(string $message = 'Esta é a minha mensagem de erro personalizada!')
    {
        parent::__construct($message);
    }
}
