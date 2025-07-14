<?php

declare(strict_types=1);

namespace Core\Exception;

class HttpUnauthorizadeException extends HttpException
{
    public function __construct($message = 'Unauthorized!')
    {
        parent::__construct($message, 401);
    }
}
