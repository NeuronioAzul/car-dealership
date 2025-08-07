<?php

namespace App\Application\DTOs\User;

class UserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $birth_date,
        public readonly string $role,
        public readonly bool $accept_terms,
        public readonly bool $accept_privacy,
        public readonly bool $accept_communications,
        public readonly string $created_at,
        public readonly ?string $updated_at,
        public readonly ?string $deleted_at
    ) {
    }
}
