<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials provided', 401);
    }
}
