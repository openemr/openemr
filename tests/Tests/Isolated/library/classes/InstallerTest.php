<?php

/**
 * Isolated Installer Test
 *
 * Tests Installer functionality without database dependencies.
 * Uses stubs and mocks to test business logic in isolation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// Include standard libraries/classes
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstallerTest extends TestCase
{
    protected Installer $installer;

    private function createMockInstaller(array $config = [], array $mockMethods = []): MockObject
    {
        $defaultConfig = [
            'server' => 'localhost',
            'root' => 'root',
            'rootpass' => 'password',
            'port' => '3306',
            'login' => 'openemr',
            'pass' => 'openemr',
            'dbname' => 'openemr'
        ];

        $config = array_merge($defaultConfig, $config);

        $defaultMockMethods = ['mysqliInit', 'mysqliRealConnect', 'fileExists', 'mysqliSslSet', 'mysqliQuery', 'mysqliError', 'mysqliErrno', 'mysqliSelectDb', 'execute_sql', 'connect_to_database', 'set_sql_strict', 'set_collation', 'escapeSql', 'mysqliNumRows', 'load_file', 'openFile', 'atEndOfFile', 'getLine', 'closeFile'];
        $mockMethods = array_unique(array_merge($defaultMockMethods, $mockMethods));

        return $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([$config])
            ->onlyMethods($mockMethods)
            ->getMock();
    }

    protected function setUp(): void
    {
        $installSettings = [
            'iuser'                    => 'admin',
            'iuname'                   => 'Administrator',
            'iuserpass'                => 'pass',
            'igroup'                   => 'Default',
            'server'                   => 'localhost', // mysql server
            'loginhost'                => 'localhost', // php/apache server
            'port'                     => '3306',
            'root'                     => 'root',
            'rootpass'                 => 'hunter2',
            'login'                    => 'openemr',
            'pass'                     => 'openemr',
            'dbname'                   => 'openemr',
            'collate'                  => 'utf8mb4_general_ci',
            'site'                     => 'default',
            'source_site_id'           => 'default',
        ];

        $this->installer = new Installer($installSettings);
    }

    public function testLoginIsValid(): void
    {
        $this->assertTrue($this->installer->login_is_valid());
        $this->installer->login = '';
        $this->assertFalse($this->installer->login_is_valid());
    }

    public function testCharIsValid(): void
    {
        $this->assertFalse($this->installer->char_is_valid('     '));
        $this->assertTrue($this->installer->char_is_valid('happy path'));
        $badChars = ['\\', ';', '(', ')', '<', '>', '/', '"', "'"];
        foreach ($badChars as $badChar) {
            $this->assertFalse($this->installer->char_is_valid($badChar), "Failed asserting that '{$badChar}' is invalid");
        }
    }

    public function testDatabaseNameIsValid(): void
    {
        $this->assertTrue($this->installer->databaseNameIsValid('12345'));
        $this->assertFalse($this->installer->databaseNameIsValid('@12345'));
    }

    public function testCollateNameIsValid(): void
    {
        $this->assertTrue($this->installer->collateNameIsValid('utf8mb4_general_ci'));
        $this->assertFalse($this->installer->collateNameIsValid('@utf8mb4_general_ci'));
    }

    public function testIuserIsValid(): void
    {
        $this->assertTrue($this->installer->iuser_is_valid());
        // whitespace is not allowed
        $this->installer->iuser = 'roger felton';
        $this->assertFalse($this->installer->iuser_is_valid());
    }

    public function testIunameIsValid(): void
    {
        $this->assertTrue($this->installer->iuname_is_valid());
        $this->installer->iuname = '';
        $this->assertFalse($this->installer->iuname_is_valid());
    }

    public function testPasswordIsValid(): void
    {
        $this->assertTrue($this->installer->password_is_valid());
        $this->installer->pass = '';
        $this->assertFalse($this->installer->password_is_valid());
    }

    public function testUserPasswordIsValid(): void
    {
        $this->assertTrue($this->installer->user_password_is_valid());
        $this->installer->iuserpass = '';
        $this->assertFalse($this->installer->user_password_is_valid());
    }

    public function testRootDatabaseConnectionSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'root', 'password', '3306')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $result = $mockInstaller->root_database_connection();

        $this->assertTrue($result);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    public function testRootDatabaseConnectionFailsWhenConnectionFails(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'root', 'password', '3306')
            ->willReturn(false);

        $mockInstaller->expects($this->never())
            ->method('set_sql_strict');

        $result = $mockInstaller->root_database_connection();

        $this->assertFalse($result);
        $this->assertEquals('unable to connect to database as root', $mockInstaller->error_message);
    }

    public function testRootDatabaseConnectionFailsWhenSqlStrictFails(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'root', 'password', '3306')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(false);

        $result = $mockInstaller->root_database_connection();

        $this->assertFalse($result);
        $this->assertEquals('unable to set strict sql setting', $mockInstaller->error_message);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    public function testRootDatabaseConnectionWithSSLCertificates(): void
    {
        $mockInstaller = $this->createMockInstaller(['site' => 'default']);
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'root', 'password', '3306')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $result = $mockInstaller->root_database_connection();

        $this->assertTrue($result);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    public function testUserDatabaseConnectionSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'openemr', 'openemr', '3306', 'openemr')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('mysqliSelectDb')
            ->with($mockMysqli, 'openemr')
            ->willReturn(true);

        $result = $mockInstaller->user_database_connection();

        $this->assertTrue($result);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    public function testUserDatabaseConnectionFailsWhenConnectionFails(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'openemr', 'openemr', '3306', 'openemr')
            ->willReturn(false);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        $this->assertEquals("unable to connect to database as user: 'openemr'", $mockInstaller->error_message);
    }

    public function testUserDatabaseConnectionFailsWhenSqlStrictFails(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'openemr', 'openemr', '3306', 'openemr')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(false);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        $this->assertEquals('unable to set strict sql setting', $mockInstaller->error_message);
    }

    public function testUserDatabaseConnectionFailsWhenCollationFails(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'openemr', 'openemr', '3306', 'openemr')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(false);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        $this->assertEquals('unable to set sql collation', $mockInstaller->error_message);
    }

    public function testUserDatabaseConnectionFailsWhenSelectDbFails(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('connect_to_database')
            ->with('localhost', 'openemr', 'openemr', '3306', 'openemr')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('mysqliSelectDb')
            ->with($mockMysqli, 'openemr')
            ->willReturn(false);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        $this->assertEquals("unable to select database: 'openemr'", $mockInstaller->error_message);
    }

    public function testCreateDatabaseSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("create database openemr character set utf8mb4 collate utf8mb4_general_ci")
            ->willReturn(true);

        $result = $mockInstaller->create_database();

        $this->assertTrue($result);
    }

    public function testCreateDatabaseWithCustomCollation(): void
    {
        $mockInstaller = $this->createMockInstaller(['collate' => 'utf8mb4_unicode_ci']);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("create database openemr character set utf8mb4 collate utf8mb4_unicode_ci")
            ->willReturn(true);

        $result = $mockInstaller->create_database();

        $this->assertTrue($result);
    }

    public function testCreateDatabaseWithLegacyCollation(): void
    {
        $mockInstaller = $this->createMockInstaller(['collate' => 'utf8_general_ci']);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("create database openemr character set utf8mb4 collate utf8mb4_general_ci")
            ->willReturn(true);

        $result = $mockInstaller->create_database();

        $this->assertTrue($result);
        // Verify that legacy collation was updated
        $this->assertEquals('utf8mb4_general_ci', $mockInstaller->collate);
    }

    public function testCreateDatabaseFailsWhenExecuteSqlFails(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("create database openemr character set utf8mb4 collate utf8mb4_general_ci")
            ->willReturn(false);

        $result = $mockInstaller->create_database();

        $this->assertFalse($result);
    }

    public function testCreateDatabaseWithDifferentDbName(): void
    {
        $mockInstaller = $this->createMockInstaller(['dbname' => 'test_db']);

        $mockInstaller->expects($this->once())
            ->method('set_collation')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("create database test_db character set utf8mb4 collate utf8mb4_general_ci")
            ->willReturn(true);

        $result = $mockInstaller->create_database();

        $this->assertTrue($result);
    }

    public function testDropDatabase(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with('drop database if exists openemr')
            ->willReturn(true);

        $result = $mockInstaller->drop_database();

        $this->assertTrue($result);
    }

    public function testCreateDatabaseUserWhenUserDoesNotExist(): void
    {
        $mockInstaller = $this->createMockInstaller(['loginhost' => 'localhost']);

        $mockInstaller->expects($this->exactly(3))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockResult = $this->createMock(mysqli_result::class);

        $callCount = 0;
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function($sql) use (&$callCount, $mockResult) {
                $callCount++;
                if ($callCount === 1 && strpos($sql, 'SELECT user FROM mysql.user') !== false) {
                    return $mockResult;
                }
                return true;
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliNumRows')
            ->with($mockResult)
            ->willReturn(0);

        $result = $mockInstaller->create_database_user();

        $this->assertTrue($result);
    }

    public function testCreateDatabaseUserWhenUserExists(): void
    {
        $mockInstaller = $this->createMockInstaller(['loginhost' => 'localhost']);

        $mockInstaller->expects($this->exactly(3))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockResult = $this->createMock(mysqli_result::class);

        $callCount = 0;
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function($sql) use (&$callCount, $mockResult) {
                $callCount++;
                if ($callCount === 1 && strpos($sql, 'SELECT user FROM mysql.user') !== false) {
                    return $mockResult;
                }
                return true;
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliNumRows')
            ->with($mockResult)
            ->willReturn(1);

        $result = $mockInstaller->create_database_user();

        $this->assertTrue($result);
    }

    public function testCreateDatabaseUserWhenCheckUserFails(): void
    {
        $mockInstaller = $this->createMockInstaller(['loginhost' => 'localhost']);

        $mockInstaller->expects($this->exactly(3))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturn(false);

        $result = $mockInstaller->create_database_user();

        $this->assertFalse($result);
    }

    public function testGrantPrivileges(): void
    {
        $mockInstaller = $this->createMockInstaller(['dbname' => 'testdb', 'login' => 'testuser', 'loginhost' => 'localhost']);

        $mockInstaller->expects($this->exactly(2))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("GRANT ALL PRIVILEGES ON testdb.* TO 'testuser'@'localhost'")
            ->willReturn(true);

        $result = $mockInstaller->grant_privileges();

        $this->assertTrue($result);
    }

    public function testLoadDumpfilesSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Set up dumpfiles array
        $mockInstaller->dumpfiles = [
            '/path/to/main.sql' => 'Main Database',
            '/path/to/translations.sql' => 'Language Translations'
        ];

        $callCount = 0;
        $mockInstaller->expects($this->exactly(2))
            ->method('load_file')
            ->willReturnCallback(function($filename, $title) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return "Creating Main Database tables...\n<span class='text-success'><b>OK</b></span>.<br>\n";
                } else {
                    return "Creating Language Translations tables...\n<span class='text-success'><b>OK</b></span>.<br>\n";
                }
            });

        $result = $mockInstaller->load_dumpfiles();

        $expectedResult = "Creating Main Database tables...\n<span class='text-success'><b>OK</b></span>.<br>\nCreating Language Translations tables...\n<span class='text-success'><b>OK</b></span>.<br>\n";
        $this->assertEquals($expectedResult, $result);
    }

    public function testLoadDumpfilesFailure(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->dumpfiles = [
            '/path/to/main.sql' => 'Main Database',
            '/path/to/bad.sql' => 'Bad File'
        ];

        $callCount = 0;
        $mockInstaller->expects($this->exactly(2))
            ->method('load_file')
            ->willReturnCallback(function($filename, $title) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return "Creating Main Database tables...\n<span class='text-success'><b>OK</b></span>.<br>\n";
                } else {
                    return false;
                }
            });

        $result = $mockInstaller->load_dumpfiles();

        $this->assertFalse($result);
    }

    public function testLoadFileSuccessWithMockedMethods(): void
    {
        $mockInstaller = $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([[
                'server' => 'localhost',
                'root' => 'root',
                'rootpass' => 'password',
                'port' => '3306',
                'login' => 'openemr',
                'pass' => 'openemr',
                'dbname' => 'openemr'
            ]])
            ->onlyMethods(['openFile', 'atEndOfFile', 'getLine', 'execute_sql', 'closeFile'])
            ->getMock();

        $mockResource = fopen('php://memory', 'w+');

        $mockInstaller->expects($this->once())
            ->method('openFile')
            ->with('/path/to/test.sql', 'r')
            ->willReturn($mockResource);

        $eofCallCount = 0;
        $mockInstaller->expects($this->exactly(3))
            ->method('atEndOfFile')
            ->willReturnCallback(function($resource) use (&$eofCallCount) {
                $eofCallCount++;
                return $eofCallCount > 2;
            });

        $mockInstaller->expects($this->exactly(2))
            ->method('getLine')
            ->with($mockResource, 1024)
            ->willReturnOnConsecutiveCalls(
                "CREATE TABLE users;",
                "INSERT INTO users VALUES (1, 'admin');"
            );

        $mockInstaller->expects($this->exactly(6))
            ->method('execute_sql')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('closeFile')
            ->with($mockResource)
            ->willReturn(true);

        $result = $mockInstaller->load_file('/path/to/test.sql', 'Test Database');

        $this->assertIsString($result);
        $this->assertStringContainsString('Creating Test Database tables', $result);
        $this->assertStringContainsString('OK', $result);
    }

    public function testLoadFileOpenFailure(): void
    {
        $mockInstaller = $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([[
                'server' => 'localhost',
                'root' => 'root',
                'rootpass' => 'password',
                'port' => '3306',
                'login' => 'openemr',
                'pass' => 'openemr',
                'dbname' => 'openemr'
            ]])
            ->onlyMethods(['openFile'])
            ->getMock();

        $mockInstaller->expects($this->once())
            ->method('openFile')
            ->with('/path/to/missing.sql', 'r')
            ->willReturn(false);

        $result = $mockInstaller->load_file('/path/to/missing.sql', 'Missing Database');

        $this->assertFalse($result);
    }

    public function testLoadFileSqlExecutionFailure(): void
    {
        $mockInstaller = $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([[
                'server' => 'localhost',
                'root' => 'root',
                'rootpass' => 'password',
                'port' => '3306',
                'login' => 'openemr',
                'pass' => 'openemr',
                'dbname' => 'openemr'
            ]])
            ->onlyMethods(['openFile', 'atEndOfFile', 'getLine', 'execute_sql'])
            ->getMock();

        $mockResource = fopen('php://memory', 'w+');

        $mockInstaller->expects($this->once())
            ->method('openFile')
            ->with('/path/to/test.sql', 'r')
            ->willReturn($mockResource);

        $mockInstaller->expects($this->once())
            ->method('atEndOfFile')
            ->willReturn(false);

        $mockInstaller->expects($this->once())
            ->method('getLine')
            ->with($mockResource, 1024)
            ->willReturn("CREATE TABLE users;");

        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $result = $mockInstaller->load_file('/path/to/test.sql', 'Test Database');

        $this->assertFalse($result);
    }

    public function testAddVersionInfoSuccess(): void
    {
        $mockInstaller = $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([[
                'server' => 'localhost',
                'root' => 'root',
                'rootpass' => 'password',
                'port' => '3306',
                'login' => 'openemr',
                'pass' => 'openemr',
                'dbname' => 'openemr'
            ]])
            ->onlyMethods(['execute_sql', 'escapeSql', 'mysqliError'])
            ->getMock();

        $mockInstaller->expects($this->exactly(7))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with($this->stringContains('UPDATE version SET'))
            ->willReturn(true);

        $result = $mockInstaller->add_version_info();

        $this->assertTrue($result);
    }

    public function testAddVersionInfoFailure(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockMysqli = $this->createMock(mysqli::class);
        $mockInstaller->dbh = $mockMysqli;

        $mockInstaller->expects($this->exactly(7))
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with($this->stringContains('UPDATE version SET'))
            ->willReturn(false);

        $mockInstaller->expects($this->once())
            ->method('mysqliError')
            ->with($mockMysqli)
            ->willReturn('Mock SQL error');

        $mockInstaller->expects($this->once())
            ->method('mysqliErrno')
            ->with($mockMysqli)
            ->willReturn(1062);

        $result = $mockInstaller->add_version_info();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR. Unable insert version information into database', $mockInstaller->error_message);
        $this->assertStringContainsString('Mock SQL error', $mockInstaller->error_message);
        $this->assertStringContainsString('1062', $mockInstaller->error_message);
    }
}
