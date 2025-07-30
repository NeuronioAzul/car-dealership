<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class UserAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("User with email '{$email}' already exists", 409);
    }
}
