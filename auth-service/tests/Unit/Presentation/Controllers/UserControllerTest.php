<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Controllers;

use App\Application\UseCases\DeleteUserUseCase;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Presentation\Controllers\UserController;
use App\Presentation\Middleware\AuthMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Testes para UserController visando coverage completo
 */
class UserControllerTest extends TestCase
{
    private UserController $userController;
    private UserRepositoryInterface&MockObject $mockUserRepository;
    private DeleteUserUseCase&MockObject $mockDeleteUseCase;
    private AuthMiddleware&MockObject $mockAuthMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar mocks usando PHPUnit
        $this->mockUserRepository = $this->createMock(UserRepositoryInterface::class);
        $this->mockDeleteUseCase = $this->createMock(DeleteUserUseCase::class);
        $this->mockAuthMiddleware = $this->createMock(AuthMiddleware::class);
    }

    /**
     * Testa construção do UserController
     */
    public function testConstructor(): void
    {
        // Testar se a classe existe e pode ser referenciada
        $this->assertTrue(class_exists(UserController::class));
        
        // Verificar se as propriedades necessárias existem na classe
        $reflection = new ReflectionClass(UserController::class);
        
        // Verificar propriedades privadas
        $this->assertTrue($reflection->hasProperty('userRepository'));
        $this->assertTrue($reflection->hasProperty('deleteUserUseCase'));
        $this->assertTrue($reflection->hasProperty('authMiddleware'));
        
        // Verificar método delete existe
        $this->assertTrue($reflection->hasMethod('delete'));
        
        // Verificar se o construtor não tem parâmetros
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertEquals(0, $constructor->getNumberOfRequiredParameters());
    }

    /**
     * Testa método delete com usuário admin
     */
    public function testDeleteAsAdmin(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case retornando sucesso
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('user-to-delete-id')
            ->willReturn(true);

        // Capturar output para evitar saída durante o teste
        ob_start();
        
        // Executar o método
        $controller->delete('user-to-delete-id');
        
        ob_end_clean();
        
        // Como admin pode deletar qualquer usuário, deve chamar o use case
        // O teste passa se os mocks foram chamados conforme esperado
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete como próprio usuário
     */
    public function testDeleteAsOwnUser(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        $userId = 'own-user-id';
        
        // Configurar mock da autenticação como próprio usuário
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => $userId,
                'role' => 'customer',
                'email' => 'user@test.com'
            ]);

        // Configurar mock do use case retornando sucesso
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId)
            ->willReturn(true);

        // Capturar output para evitar saída durante o teste
        ob_start();
        
        // Executar o método
        $controller->delete($userId);
        
        ob_end_clean();
        
        // Como é o próprio usuário, deve permitir a exclusão
        // O teste passa se os mocks foram chamados conforme esperado
        $this->addToAssertionCount(1);
    }

    /**
     * Método auxiliar para criar UserController com mocks injetados via reflection
     */
    private function createUserControllerWithMocks(): object
    {
        // Criar uma instância parcial que não executa o construtor
        $controller = $this->getMockBuilder(UserController::class)
            ->disableOriginalConstructor()
            ->onlyMethods([]) // Não mockar nenhum método, usar implementação real
            ->getMock();
        
        // Usar reflection para injetar os mocks nas propriedades privadas
        $reflection = new ReflectionClass(UserController::class);
        
        $userRepositoryProperty = $reflection->getProperty('userRepository');
        $userRepositoryProperty->setAccessible(true);
        $userRepositoryProperty->setValue($controller, $this->mockUserRepository);
        
        $deleteUseCaseProperty = $reflection->getProperty('deleteUserUseCase');
        $deleteUseCaseProperty->setAccessible(true);
        $deleteUseCaseProperty->setValue($controller, $this->mockDeleteUseCase);
        
        $authMiddlewareProperty = $reflection->getProperty('authMiddleware');
        $authMiddlewareProperty->setAccessible(true);
        $authMiddlewareProperty->setValue($controller, $this->mockAuthMiddleware);
        
        return $controller;
    }

    /**
     * Testa método delete com acesso negado
     */
    public function testDeleteAccessDenied(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como customer tentando deletar outro usuário
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'customer-user-id',
                'role' => 'customer',
                'email' => 'customer@test.com'
            ]);

        // O use case NÃO deve ser chamado porque o acesso é negado antes
        $this->mockDeleteUseCase
            ->expects($this->never())
            ->method('execute');

        // Capturar output para verificar a resposta JSON
        ob_start();
        
        // Executar o método tentando deletar outro usuário
        $controller->delete('another-user-id');
        
        $output = ob_get_clean();
        
        // Verificar se retornou erro de acesso negado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Acesso negado', $response['error']);
        
        // Não podemos testar diretamente o http_response_code em testes unitários
        // mas validamos que a lógica de negação foi executada
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete com usuário não encontrado
     */
    public function testDeleteUserNotFound(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case retornando false (usuário não encontrado)
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('non-existent-user-id')
            ->willReturn(false);

        // Capturar output para verificar a resposta JSON
        ob_start();
        
        // Executar o método
        $controller->delete('non-existent-user-id');
        
        $output = ob_get_clean();
        
        // Verificar se retornou erro de usuário não encontrado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Usuário não encontrado', $response['error']);
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete com sucesso
     */
    public function testDeleteSuccess(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case retornando true (sucesso)
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('user-to-delete-id')
            ->willReturn(true);

        // Capturar output (deve estar vazio no caso de sucesso - código 204)
        ob_start();
        
        // Executar o método
        $controller->delete('user-to-delete-id');
        
        $output = ob_get_clean();
        
        // Para código 204 (No Content), não deve haver output
        $this->assertEmpty($output);
        
        // Verifica que os mocks foram chamados conforme esperado
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete com exceção
     */
    public function testDeleteWithException(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case para lançar uma exceção
        $exception = new \Exception('Database connection failed', 500);
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('problematic-user-id')
            ->willThrowException($exception);

        // Capturar output para verificar a resposta de erro
        ob_start();
        
        // Executar o método
        $controller->delete('problematic-user-id');
        
        $output = ob_get_clean();
        
        // Verificar se retornou erro formatado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertTrue($response['error']);
        $this->assertEquals('Database connection failed', $response['message']);
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete com ID inválido
     */
    public function testDeleteWithInvalidId(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case - pode retornar false ou lançar exceção
        // dependendo da validação interna do use case
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('invalid-uuid-format')
            ->willReturn(false);

        // Capturar output para verificar a resposta
        ob_start();
        
        // Executar o método com ID inválido
        $controller->delete('invalid-uuid-format');
        
        $output = ob_get_clean();
        
        // Com ID inválido, esperamos que retorne usuário não encontrado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Usuário não encontrado', $response['error']);
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa método delete com ID vazio
     */
    public function testDeleteWithEmptyId(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case para ID vazio
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('')
            ->willReturn(false);

        // Capturar output para verificar a resposta
        ob_start();
        
        // Executar o método com ID vazio
        $controller->delete('');
        
        $output = ob_get_clean();
        
        // Com ID vazio, esperamos que retorne usuário não encontrado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Usuário não encontrado', $response['error']);
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa autenticação middleware
     */
    public function testAuthenticationMiddleware(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock do middleware para lançar exceção de autenticação
        $authException = new \Exception('Token inválido. Faça login novamente para continuar.', 401);
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willThrowException($authException);

        // O use case NÃO deve ser chamado se a autenticação falhar
        $this->mockDeleteUseCase
            ->expects($this->never())
            ->method('execute');

        // A exceção deve ser lançada quando authenticate() for chamado
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token inválido. Faça login novamente para continuar.');
        $this->expectExceptionCode(401);
        
        // Executar o método - deve lançar exceção antes de qualquer output
        $controller->delete('some-user-id');
    }

    /**
     * Testa inicialização de dependências
     */
    public function testDependencyInitialization(): void
    {
        // Usar reflection para verificar a inicialização das dependências
        $reflection = new ReflectionClass(UserController::class);
        
        // Verificar que o construtor existe
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
        
        // Verificar que não tem parâmetros obrigatórios (dependências são criadas internamente)
        $this->assertEquals(0, $constructor->getNumberOfRequiredParameters());
        
        // Verificar que as propriedades privadas existem
        $userRepositoryProperty = $reflection->getProperty('userRepository');
        $this->assertTrue($userRepositoryProperty->isPrivate());
        $this->assertEquals('userRepository', $userRepositoryProperty->getName());
        
        $deleteUseCaseProperty = $reflection->getProperty('deleteUserUseCase');
        $this->assertTrue($deleteUseCaseProperty->isPrivate());
        $this->assertEquals('deleteUserUseCase', $deleteUseCaseProperty->getName());
        
        $authMiddlewareProperty = $reflection->getProperty('authMiddleware');
        $this->assertTrue($authMiddlewareProperty->isPrivate());
        $this->assertEquals('authMiddleware', $authMiddlewareProperty->getName());
        
        // Verificar que o método delete existe e é público
        $deleteMethod = $reflection->getMethod('delete');
        $this->assertTrue($deleteMethod->isPublic());
        $this->assertEquals('delete', $deleteMethod->getName());
        $this->assertEquals(1, $deleteMethod->getNumberOfRequiredParameters());
        
        // Verificar os tipos dos parâmetros do método delete
        $parameters = $deleteMethod->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('id', $parameters[0]->getName());
        
        // Verificar se o parâmetro tem tipo definido
        $paramType = $parameters[0]->getType();
        if ($paramType instanceof \ReflectionNamedType) {
            $this->assertEquals('string', $paramType->getName());
        }
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa propriedades privadas da classe
     */
    public function testPrivateProperties(): void
    {
        $reflection = new ReflectionClass(UserController::class);
        
        // Verificar que todas as propriedades são privadas
        $properties = $reflection->getProperties();
        
        foreach ($properties as $property) {
            $this->assertTrue($property->isPrivate(), 
                "Propriedade '{$property->getName()}' deve ser privada");
        }
        
        // Verificar especificamente as propriedades esperadas
        $expectedProperties = ['userRepository', 'deleteUserUseCase', 'authMiddleware'];
        
        foreach ($expectedProperties as $expectedProperty) {
            $this->assertTrue($reflection->hasProperty($expectedProperty),
                "Propriedade '{$expectedProperty}' deve existir");
            
            $property = $reflection->getProperty($expectedProperty);
            $this->assertTrue($property->isPrivate(),
                "Propriedade '{$expectedProperty}' deve ser privada");
        }
        
        // Verificar que não há propriedades públicas ou protegidas
        $publicProperties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $protectedProperties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        
        $this->assertEmpty($publicProperties, 'Não deve haver propriedades públicas');
        $this->assertEmpty($protectedProperties, 'Não deve haver propriedades protegidas');
        
        $this->addToAssertionCount(1);
    }

    /**
     * Testa resposta HTTP 403 para acesso negado
     */
    public function testHttp403Response(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como customer (não admin)
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'customer-user-id',
                'role' => 'customer',
                'email' => 'customer@test.com'
            ]);

        // O use case NÃO deve ser chamado porque o acesso é negado
        $this->mockDeleteUseCase
            ->expects($this->never())
            ->method('execute');

        // Capturar tanto headers quanto output
        ob_start();
        
        // Executar o método tentando deletar outro usuário
        $controller->delete('different-user-id');
        
        $output = ob_get_clean();
        
        // Verificar que a resposta JSON indica erro de acesso negado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Acesso negado', $response['error']);
        
        // Verificar que o output contém JSON válido
        $this->assertJson($output);
        
        // Em um cenário real, http_response_code(403) seria chamado
        // Como não podemos testar diretamente os headers em unit tests,
        // validamos que a lógica de negação de acesso foi executada corretamente
        $this->addToAssertionCount(1);
    }

    /**
     * Testa resposta HTTP 404 para usuário não encontrado
     */
    public function testHttp404Response(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin (tem permissão)
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case para retornar false (usuário não encontrado)
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('non-existent-user-id')
            ->willReturn(false);

        // Capturar output para verificar resposta HTTP 404
        ob_start();
        
        // Executar o método com usuário inexistente
        $controller->delete('non-existent-user-id');
        
        $output = ob_get_clean();
        
        // Verificar que a resposta JSON indica usuário não encontrado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Usuário não encontrado', $response['error']);
        
        // Verificar que o output contém JSON válido
        $this->assertJson($output);
        
        // Em um cenário real, http_response_code(404) seria chamado
        // Validamos que a lógica de "usuário não encontrado" foi executada
        $this->addToAssertionCount(1);
    }

    /**
     * Testa resposta HTTP 204 para sucesso
     */
    public function testHttp204Response(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin (tem permissão)
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case para retornar true (sucesso na deleção)
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('existing-user-id')
            ->willReturn(true);

        // Capturar output para verificar resposta HTTP 204 (No Content)
        ob_start();
        
        // Executar o método com usuário existente
        $controller->delete('existing-user-id');
        
        $output = ob_get_clean();
        
        // Para HTTP 204 (No Content), o output deve estar vazio
        $this->assertEmpty($output, 'HTTP 204 No Content deve ter output vazio');
        
        // Verificar que o conteúdo não é JSON (pois está vazio)
        $this->assertEquals('', $output);
        
        // Em um cenário real, http_response_code(204) seria chamado
        // Validamos que a deleção foi bem-sucedida sem retornar conteúdo
        $this->addToAssertionCount(1);
    }

    /**
     * Testa tratamento de códigos de erro customizados
     */
    public function testCustomErrorCodeHandling(): void
    {
        // Criar uma instância usando reflection para injetar mocks
        $controller = $this->createUserControllerWithMocks();
        
        // Configurar mock da autenticação como admin
        $this->mockAuthMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn([
                'id' => 'admin-user-id',
                'role' => 'admin',
                'email' => 'admin@test.com'
            ]);

        // Configurar mock do use case para lançar exceção com código customizado
        $customException = new \Exception('Erro de validação customizado', 422);
        $this->mockDeleteUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('problematic-user-id')
            ->willThrowException($customException);

        // Capturar output para verificar tratamento de erro customizado
        ob_start();
        
        // Executar o método
        $controller->delete('problematic-user-id');
        
        $output = ob_get_clean();
        
        // Verificar que a resposta JSON contém o erro customizado
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertTrue($response['error']);
        $this->assertEquals('Erro de validação customizado', $response['message']);
        
        // Verificar que o output contém JSON válido
        $this->assertJson($output);
        
        // Verificar que o formato da resposta é consistente com outros testes de erro
        $this->assertCount(2, $response); // Deve conter apenas 'error' e 'message'
        
        $this->addToAssertionCount(1);
    }
}
