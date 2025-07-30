<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Exceptions;

use App\Presentation\Exceptions\BadRequestException;
use App\Presentation\Exceptions\HttpException;
use Exception;
use PHPUnit\Framework\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function test_get_status_code(): void
    {
        $exception = new BadRequestException('Bad request');
        
        $this->assertEquals(400, $exception->getStatusCode());
    }

    public function test_inherits_from_http_exception(): void
    {
        $exception = new BadRequestException('Bad request');
        
        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function test_constructor_with_message(): void
    {
        $message = 'Invalid request data';
        $exception = new BadRequestException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode()); // Default from parent
        $this->assertEquals(400, $exception->getStatusCode());
    }

    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Invalid request data';
        $code = 123;
        $exception = new BadRequestException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals(400, $exception->getStatusCode());
    }

    public function test_to_array(): void
    {
        $exception = new BadRequestException('Bad request message');
        
        $array = $exception->toArray();
        
        $this->assertIsArray($array);
        $this->assertTrue($array['error']);
        $this->assertEquals('Bad request message', $array['message']);
        $this->assertEquals(500, $array['code']);
    }
}
