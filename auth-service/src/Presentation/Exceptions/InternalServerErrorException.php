<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

class InternalServerErrorException extends HttpException
{
    public function getStatusCode(): int
    {
        return 500;
    }
}
