<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use Exception;

/**
 * Base exception para HTTP responses
 */
abstract class HttpException extends Exception
{
    public function __construct(string $message, int $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    abstract public function getStatusCode(): int;
    
    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ];
    }
}

class BadRequestException extends HttpException
{
    public function getStatusCode(): int
    {
        return 400;
    }
}

class UnauthorizedException extends HttpException
{
    public function getStatusCode(): int
    {
        return 401;
    }
}

class ForbiddenException extends HttpException
{
    public function getStatusCode(): int
    {
        return 403;
    }
}

class InternalServerErrorException extends HttpException
{
    public function getStatusCode(): int
    {
        return 500;
    }
}

class ConflictException extends HttpException
{
    public function getStatusCode(): int
    {
        return 409;
    }
}

class UnprocessableEntityException extends HttpException
{
    public function getStatusCode(): int
    {
        return 422;
    }
}

class NotImplementedException extends HttpException
{
    public function getStatusCode(): int
    {
        return 501;
    }
}
