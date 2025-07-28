<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

class UserCreationFailedException extends ApplicationException
{
    public function __construct(string $reason = 'Failed to create user')
    {
        parent::__construct($reason, 500);
    }
}
