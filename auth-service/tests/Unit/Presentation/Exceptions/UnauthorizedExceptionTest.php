<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Exceptions;

use App\Presentation\Exceptions\UnauthorizedException;
use App\Presentation\Exceptions\HttpException;
use Exception;
use PHPUnit\Framework\TestCase;

class UnauthorizedExceptionTest extends TestCase
{
    public function test_get_status_code(): void
    {
        $exception = new UnauthorizedException('Unauthorized');
        
        $this->assertEquals(401, $exception->getStatusCode());
    }

    public function test_inherits_from_http_exception(): void
    {
        $exception = new UnauthorizedException('Unauthorized');
        
        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function test_constructor_with_message(): void
    {
        $message = 'Access denied';
        $exception = new UnauthorizedException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode()); // Default from parent
        $this->assertEquals(401, $exception->getStatusCode());
    }

    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Token expired';
        $code = 401;
        $exception = new UnauthorizedException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals(401, $exception->getStatusCode());
    }

    public function test_to_array(): void
    {
        $exception = new UnauthorizedException('Unauthorized access');
        
        $array = $exception->toArray();
        
        $this->assertIsArray($array);
        $this->assertTrue($array['error']);
        $this->assertEquals('Unauthorized access', $array['message']);
        $this->assertEquals(500, $array['code']);
    }

    public function test_constructor_with_previous_exception(): void
    {
        $previous = new Exception('Previous error');
        $exception = new UnauthorizedException('Unauthorized', 401, $previous);
        
        $this->assertEquals('Unauthorized', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
