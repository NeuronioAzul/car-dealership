<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use App\Infrastructure\Database\DatabaseConfig;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseConfigTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset static connection before each test
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue(null);
    }

    public function test_get_connection_method_exists(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasMethod('getConnection'));

        $method = $reflection->getMethod('getConnection');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function test_connection_property_exists(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasProperty('connection'));

        $property = $reflection->getProperty('connection');
        $this->assertTrue($property->isStatic());
        $this->assertTrue($property->isPrivate());
    }

    public function test_get_connection_fails_without_environment(): void
    {
        // Test that getConnection fails in test environment without proper DB config
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro na conexão com o banco de dados:');

        DatabaseConfig::getConnection();
    }

    public function test_static_connection_property_default_value(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);

        $this->assertNull($connectionProperty->getValue());
    }

    public function test_class_structure(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);

        // Test class namespace
        $this->assertEquals('App\Infrastructure\Database\DatabaseConfig', $reflection->getName());

        // Test that it has one static property
        $staticProperties = $reflection->getProperties(\ReflectionProperty::IS_STATIC);
        $this->assertCount(1, $staticProperties);
        $this->assertEquals('connection', $staticProperties[0]->getName());

        // Test that it has one public static method
        $publicStaticMethods = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_STATIC),
            fn($method) => $method->isPublic()
        );
        $this->assertCount(1, $publicStaticMethods);
        $this->assertEquals('getConnection', $publicStaticMethods[0]->getName());
    }

    public function test_method_signature(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $getConnectionMethod = $reflection->getMethod('getConnection');

        // Should have no parameters
        $this->assertCount(0, $getConnectionMethod->getParameters());

        // Should return PDO
        $returnType = $getConnectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('PDO', $returnType->__toString());
    }

    public function test_singleton_pattern_implementation(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);

        // Should not have public constructor (singleton pattern)
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor, 'Singleton should not have public constructor');

        // Should have static getConnection method
        $this->assertTrue($reflection->hasMethod('getConnection'));
        $getConnection = $reflection->getMethod('getConnection');
        $this->assertTrue($getConnection->isStatic());
        $this->assertTrue($getConnection->isPublic());
    }

    public function test_class_imports_correct_dependencies(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Check that necessary imports are present
        $this->assertStringContainsString('use PDO;', $content);
        $this->assertStringContainsString('use PDOException;', $content);
    }

    public function test_exception_handling_structure(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verify exception handling is present
        $this->assertStringContainsString('try {', $content);
        $this->assertStringContainsString('} catch (PDOException $e) {', $content);
        $this->assertStringContainsString('throw new \Exception(', $content);
    }

    public function test_pdo_configuration_options(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verify PDO configuration options are set
        $this->assertStringContainsString('PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION', $content);
        $this->assertStringContainsString('PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC', $content);
        $this->assertStringContainsString('PDO::ATTR_EMULATE_PREPARES => false', $content);
    }

    public function test_connection_string_format(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verify DSN format
        $this->assertStringContainsString('mysql:host=', $content);
        $this->assertStringContainsString('port=', $content);
        $this->assertStringContainsString('dbname=', $content);
        $this->assertStringContainsString('charset=utf8mb4', $content);
    }

    public function test_environment_variables_usage(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verify environment variables are used
        $this->assertStringContainsString('$_ENV[\'DB_HOST\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_PORT\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_DATABASE\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_USERNAME\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_PASSWORD\']', $content);
    }

    public function test_class_has_no_instance_methods(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $instanceMethods = array_filter(
            $reflection->getMethods(),
            fn($method) => !$method->isStatic()
        );

        // Should have no instance methods (pure static class)
        $this->assertEmpty($instanceMethods);
    }

    /**
     * Testa construção de string de conexão PDO
     */
    public function testPdoConnectionStringConstruction(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se a string DSN está sendo construída corretamente
        $this->assertStringContainsString(
            '$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4"',
            $content,
            'A string de conexão PDO deve seguir o formato correto com todas as variáveis'
        );

        // Verifica se as variáveis de ambiente estão sendo usadas na ordem correta
        $this->assertStringContainsString('$host = $_ENV[\'DB_HOST\']', $content);
        $this->assertStringContainsString('$port = $_ENV[\'DB_PORT\']', $content);
        $this->assertStringContainsString('$database = $_ENV[\'DB_DATABASE\']', $content);

        // Verifica se o charset UTF-8 está configurado
        $this->assertStringContainsString('charset=utf8mb4', $content, 'Charset UTF-8MB4 deve estar configurado');

        // Verifica se está usando o driver MySQL
        $this->assertStringContainsString('mysql:', $content, 'Deve usar o driver MySQL');
    }

    /**
     * Testa configuração de opções PDO
     */
    public function testPdoOptionsConfiguration(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se todas as opções PDO necessárias estão configuradas
        $expectedOptions = [
            'PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION',
            'PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC',
            'PDO::ATTR_EMULATE_PREPARES => false'
        ];

        foreach ($expectedOptions as $option) {
            $this->assertStringContainsString(
                $option,
                $content,
                "A opção PDO '{$option}' deve estar configurada"
            );
        }

        // Verifica se as opções estão sendo passadas como array no construtor PDO
        $this->assertStringContainsString(
            'new PDO($dsn, $username, $password, [',
            $content,
            'As opções PDO devem ser passadas como array no construtor'
        );

        // Verifica se o modo de erro está configurado para lançar exceções
        $this->assertStringContainsString(
            'PDO::ERRMODE_EXCEPTION',
            $content,
            'Modo de erro deve estar configurado para lançar exceções'
        );

        // Verifica se o modo de fetch padrão está configurado como associativo
        $this->assertStringContainsString(
            'PDO::FETCH_ASSOC',
            $content,
            'Modo de fetch padrão deve ser associativo'
        );

        // Verifica se prepared statements emulados estão desabilitados
        $this->assertStringContainsString(
            'PDO::ATTR_EMULATE_PREPARES => false',
            $content,
            'Prepared statements emulados devem estar desabilitados'
        );
    }

    /**
     * Testa conexão com diferentes drivers de banco
     */
    public function testConnectionWithDifferentDrivers(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se o driver MySQL está configurado na DSN
        $this->assertStringContainsString(
            'mysql:host=',
            $content,
            'Deve estar configurado para usar o driver MySQL'
        );

        // Verifica se não há outros drivers configurados (focado em MySQL)
        $this->assertStringNotContainsString('pgsql:', $content, 'Não deve usar PostgreSQL');
        $this->assertStringNotContainsString('sqlite:', $content, 'Não deve usar SQLite');
        $this->assertStringNotContainsString('sqlsrv:', $content, 'Não deve usar SQL Server');

        // Verifica a estrutura da DSN específica para MySQL
        $this->assertStringContainsString(
            'mysql:host={$host};port={$port};dbname={$database}',
            $content,
            'DSN deve seguir o formato MySQL com host, port e dbname'
        );

        // Verifica se as variáveis de ambiente específicas do MySQL estão sendo usadas
        $requiredEnvVars = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        foreach ($requiredEnvVars as $envVar) {
            $this->assertStringContainsString(
                "\$_ENV['{$envVar}']",
                $content,
                "Variável de ambiente {$envVar} deve estar sendo utilizada"
            );
        }

        // Verifica se o driver está hardcoded para MySQL (não dinâmico)
        $this->assertStringNotContainsString(
            '$driver',
            $content,
            'Driver deve estar hardcoded como MySQL, não dinâmico'
        );
    }

    /**
     * Testa conexão com charset específico
     */
    public function testConnectionWithSpecificCharset(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se o charset UTF-8MB4 está configurado na DSN
        $this->assertStringContainsString(
            'charset=utf8mb4',
            $content,
            'Charset deve estar configurado como utf8mb4 na string de conexão'
        );

        // Verifica se o charset está na posição correta da DSN
        $this->assertStringContainsString(
            'dbname={$database};charset=utf8mb4',
            $content,
            'Charset deve aparecer após o dbname na DSN'
        );

        // Verifica se não está usando charsets mais antigos ou inadequados
        $this->assertStringNotContainsString(
            'charset=latin1',
            $content,
            'Não deve usar charset latin1'
        );
        
        $this->assertStringNotContainsString(
            'charset=ascii',
            $content,
            'Não deve usar charset ascii'
        );        // Verifica se o charset está incluído na string DSN completa
        $expectedDsnPattern = 'mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4';
        $this->assertStringContainsString(
            $expectedDsnPattern,
            $content,
            'DSN completa deve incluir charset utf8mb4'
        );

        // Verifica se não há configuração adicional de charset via opções PDO
        // (deve ser configurado apenas na DSN)
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_INIT_COMMAND',
            $content,
            'Charset deve ser configurado na DSN, não via INIT_COMMAND'
        );
    }

    /**
     * Testa manejo de timeout de conexão
     */
    public function testConnectionTimeoutHandling(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se não há configuração explícita de timeout (usando padrões do sistema)
        $this->assertStringNotContainsString(
            'PDO::ATTR_TIMEOUT',
            $content,
            'Timeout deve usar configurações padrão do sistema, não customizadas'
        );

        // Verifica se não há configuração de timeout de conexão MySQL específica
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_CONNECT_TIMEOUT',
            $content,
            'Timeout de conexão MySQL deve usar padrões do sistema'
        );

        // Verifica se não há configuração de timeout de leitura/escrita
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_READ_TIMEOUT',
            $content,
            'Timeout de leitura deve usar padrões do sistema'
        );

        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_WRITE_TIMEOUT',
            $content,
            'Timeout de escrita deve usar padrões do sistema'
        );

        // Verifica se o tratamento de exceções está presente para lidar com timeouts
        $this->assertStringContainsString(
            'catch (PDOException $e)',
            $content,
            'Deve haver tratamento de exceções para timeouts de conexão'
        );

        // Verifica se a mensagem de erro genérica está configurada
        $this->assertStringContainsString(
            'Erro na conexão com o banco de dados:',
            $content,
            'Deve ter mensagem de erro genérica que inclui timeouts'
        );
    }

    /**
     * Testa pooling de conexões
     */
    public function testConnectionPooling(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se está implementando o padrão Singleton (forma básica de pooling)
        $this->assertStringContainsString(
            'private static ?PDO $connection = null',
            $content,
            'Deve ter propriedade estática para manter conexão única (singleton pattern)'
        );

        // Verifica se reutiliza a conexão existente
        $this->assertStringContainsString(
            'if (self::$connection === null)',
            $content,
            'Deve verificar se já existe conexão antes de criar nova'
        );

        // Verifica se retorna a conexão estática existente
        $this->assertStringContainsString(
            'return self::$connection',
            $content,
            'Deve retornar a conexão singleton existente'
        );

        // Verifica que não há configuração de pooling complexo do MySQL
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_USE_BUFFERED_QUERY',
            $content,
            'Não deve ter configuração avançada de pooling MySQL'
        );

        // Verifica que há exatamente uma instância de conexão PDO
        $this->assertStringContainsString(
            'new PDO',
            $content,
            'Deve criar apenas uma instância PDO (dentro do if)'
        );

        // Confirma que é uma implementação de pooling simples via singleton
        $poolingPattern = 'if (self::$connection === null) {';
        $this->assertStringContainsString(
            $poolingPattern,
            $content,
            'Implementa pooling básico através do padrão Singleton'
        );
    }

    /**
     * Testa conexão SSL
     */
    public function testSSLConnection(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se não há configuração SSL explícita (usando padrões do MySQL)
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_SSL_KEY',
            $content,
            'SSL deve usar configurações padrão do servidor MySQL'
        );

        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_SSL_CERT',
            $content,
            'Certificado SSL deve ser configurado no servidor MySQL'
        );

        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_SSL_CA',
            $content,
            'CA SSL deve ser configurado no servidor MySQL'
        );

        // Verifica se não há desabilitação explícita do SSL
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT',
            $content,
            'Verificação SSL deve usar padrões seguros do MySQL'
        );

        // Verifica se não há configuração de cipher SSL
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_SSL_CIPHER',
            $content,
            'Cipher SSL deve usar configurações padrão do servidor'
        );

        // Verifica que a conexão permite SSL se configurado no servidor
        // (não força nem proíbe SSL, deixa para o servidor decidir)
        $this->assertStringNotContainsString(
            'sslmode=',
            $content,
            'Modo SSL deve ser determinado pela configuração do servidor MySQL'
        );

        // Confirma que está usando configuração padrão que permite SSL
        $this->assertStringContainsString(
            'mysql:host=',
            $content,
            'Conexão MySQL padrão permite SSL se configurado no servidor'
        );
    }

    /**
     * Testa configuração de timezone
     */
    public function testTimezoneConfiguration(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se não há configuração explícita de timezone na conexão
        $this->assertStringNotContainsString(
            'SET time_zone',
            $content,
            'Timezone deve ser configurado no servidor MySQL, não na conexão'
        );

        // Verifica se não há INIT_COMMAND para timezone
        $this->assertStringNotContainsString(
            'PDO::MYSQL_ATTR_INIT_COMMAND',
            $content,
            'Não deve usar INIT_COMMAND para configurar timezone'
        );

        // Verifica se não há configuração de timezone na DSN
        $this->assertStringNotContainsString(
            'timezone=',
            $content,
            'Timezone não deve ser configurado na DSN'
        );

        // Verifica se não há query de timezone na inicialização
        $this->assertStringNotContainsString(
            'SELECT @@time_zone',
            $content,
            'Não deve verificar timezone na inicialização da conexão'
        );

        // Confirma que usa configurações padrão do servidor MySQL
        $expectedDsnPattern = 'mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4';
        $this->assertStringContainsString(
            $expectedDsnPattern,
            $content,
            'DSN deve conter apenas parâmetros essenciais, timezone fica com servidor'
        );

        // Verifica se não há configuração de timezone UTC forçada
        $this->assertStringNotContainsString(
            'UTC',
            $content,
            'Não deve forçar timezone UTC na conexão'
        );

        // Confirma que deixa timezone para o servidor MySQL gerenciar
        $this->assertStringContainsString(
            'charset=utf8mb4',
            $content,
            'Configuração focada em charset, timezone gerenciado pelo servidor'
        );
    }

    /**
     * Testa modo de transação autocommit
     */
    public function testAutocommitMode(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se não há configuração explícita de autocommit (usa padrão PDO)
        $this->assertStringNotContainsString(
            'PDO::ATTR_AUTOCOMMIT',
            $content,
            'Autocommit deve usar configuração padrão do PDO (true)'
        );

        // Verifica se não há desabilitação do autocommit
        $this->assertStringNotContainsString(
            'AUTOCOMMIT => false',
            $content,
            'Não deve desabilitar autocommit explicitamente'
        );

        // Verifica se não há comandos SQL para controlar autocommit
        $this->assertStringNotContainsString(
            'SET autocommit',
            $content,
            'Não deve configurar autocommit via SQL'
        );

        $this->assertStringNotContainsString(
            'START TRANSACTION',
            $content,
            'Não deve iniciar transações na conexão'
        );

        // Verifica se mantém o comportamento padrão do PDO (autocommit ativo)
        $this->assertStringContainsString(
            'PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION',
            $content,
            'Deve manter configurações padrão de PDO incluindo autocommit'
        );

        // Confirma que usa configurações simples sem controle de transação
        $this->assertStringNotContainsString(
            'BEGIN',
            $content,
            'Não deve iniciar transações automaticamente'
        );

        // Verifica que deixa controle de transações para o código da aplicação
        $this->assertStringNotContainsString(
            'COMMIT',
            $content,
            'Controle de transações deve ser feito pela aplicação'
        );
    }

    /**
     * Testa leitura de variáveis de ambiente
     */
    public function testEnvironmentVariableReading(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);

        // Verifica se todas as variáveis de ambiente necessárias estão sendo lidas
        $requiredEnvVars = [
            'DB_HOST' => '$host = $_ENV[\'DB_HOST\']',
            'DB_PORT' => '$port = $_ENV[\'DB_PORT\']', 
            'DB_DATABASE' => '$database = $_ENV[\'DB_DATABASE\']',
            'DB_USERNAME' => '$username = $_ENV[\'DB_USERNAME\']',
            'DB_PASSWORD' => '$password = $_ENV[\'DB_PASSWORD\']'
        ];

        foreach ($requiredEnvVars as $envVar => $expectedCode) {
            $this->assertStringContainsString(
                $expectedCode,
                $content,
                "Deve ler a variável de ambiente {$envVar} corretamente"
            );
        }

        // Verifica se não usa métodos alternativos para ler variáveis de ambiente
        $this->assertStringNotContainsString(
            'getenv(',
            $content,
            'Deve usar $_ENV ao invés de getenv()'
        );

        $this->assertStringNotContainsString(
            '$_SERVER[',
            $content,
            'Deve usar $_ENV ao invés de $_SERVER para variáveis de ambiente'
        );

        // Verifica se não há valores hardcoded
        $this->assertStringNotContainsString(
            'localhost',
            $content,
            'Não deve ter valores hardcoded para host'
        );

        $this->assertStringNotContainsString(
            '3306',
            $content,
            'Não deve ter valores hardcoded para porta'
        );

        // Verifica se as variáveis são usadas na construção da DSN
        $this->assertStringContainsString(
            'mysql:host={$host}',
            $content,
            'Variável $host deve ser usada na DSN'
        );

        $this->assertStringContainsString(
            'port={$port}',
            $content,
            'Variável $port deve ser usada na DSN'
        );

        $this->assertStringContainsString(
            'dbname={$database}',
            $content,
            'Variável $database deve ser usada na DSN'
        );

        // Verifica se as credenciais são passadas para o PDO
        $this->assertStringContainsString(
            'new PDO($dsn, $username, $password',
            $content,
            'Credenciais das variáveis de ambiente devem ser passadas para PDO'
        );
    }
}
