<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class UserNotFoundException extends DomainException
{
    public function __construct(?string $identifier = null)
    {
        $message = $identifier 
            ? "User with identifier '{$identifier}' not found"
            : "User not found";
        
        parent::__construct($message, 404);
    }
}
