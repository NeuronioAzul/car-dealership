<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

class UnprocessableEntityException extends HttpException
{
    public function getStatusCode(): int
    {
        return 422;
    }
}
