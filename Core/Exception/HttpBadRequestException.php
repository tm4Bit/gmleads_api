<?php

declare(strict_types=1);

namespace Core\Exception;

class HttpBadRequestException extends HttpException
{
    public function __construct($message = 'Bad request!')
    {
        parent::__construct($message, 400);
    }
}
