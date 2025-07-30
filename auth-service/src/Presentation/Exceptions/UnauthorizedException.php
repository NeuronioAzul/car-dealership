<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

class UnauthorizedException extends HttpException
{
    public function getStatusCode(): int
    {
        return 401;
    }
}
