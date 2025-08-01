<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class UserNotFoundException extends DomainException
{
    public function __construct(?string $identifier = null)
    {
        $message = 'Invalid credentials provided';
        
        parent::__construct($message, 401);
    }
}
