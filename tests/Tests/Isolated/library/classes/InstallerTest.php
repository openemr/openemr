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
            'dbname' => 'openemr',
            'iuser' => 'openemr',
            'iuname' => 'Administrator',
            'iuserpass' => 'admin',
            'igroup' => 'Default'
        ];

        $config = array_merge($defaultConfig, $config);
        $defaultMockMethods = [
            'atEndOfFile',
            'closeFile',
            'createTotpInstance',
            'cryptoGenClassExists',
            'die',
            'encryptTotpSecret',
            'escapeSql',
            'execute_sql',
            'fileExists',
            'getLine',
            'globPattern',
            'load_file',
            'mysqliErrno',
            'mysqliError',
            'mysqliFetchArray',
            'mysqliInit',
            'mysqliNumRows',
            'mysqliQuery',
            'mysqliRealConnect',
            'mysqliSelectDb',
            'mysqliSslSet',
            'newGaclApi',
            'openFile',
            'recurse_copy',
            'scanDir',
            'set_collation',
            'set_sql_strict',
            'totpClassExists',
            'touchFile',
            'unlinkFile',
            'writeToFile',
        ];
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);

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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller(['site' => 'default'], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);

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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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
        $mockInstaller = $this->createMockInstaller([], ['connect_to_database']);
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

    public function testCreateDatabaseWithInvalidCollationName(): void
    {
        // Create installer with invalid collation name containing illegal characters
        $mockInstaller = $this->createMockInstaller(['collate' => 'utf8@invalid!']);

        // create_database will die before getting here.
        $mockInstaller->expects($this->never())
            ->method('set_collation');

        // Expect die() to be called with the error message for invalid collation
        $mockInstaller->expects($this->once())
            ->method('die')
            ->with('Illegal character(s) in collation name')
            ->willThrowException(new \Exception('Die called with: Illegal character(s) in collation name'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Die called with: Illegal character(s) in collation name');

        // This should trigger die() in escapeCollateName when it validates the collation name
        $mockInstaller->create_database();
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
            ->willReturnCallback(function ($sql) use (&$callCount, $mockResult) {
                $callCount++;
                if ($callCount === 1 && str_contains($sql, 'SELECT user FROM mysql.user')) {
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
            ->willReturnCallback(function ($sql) use (&$callCount, $mockResult) {
                $callCount++;
                if ($callCount === 1 && str_contains($sql, 'SELECT user FROM mysql.user')) {
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

    public function testGrantPrivilegesWithInvalidDatabaseName(): void
    {
        // Create installer with invalid database name containing illegal characters
        $mockInstaller = $this->createMockInstaller(['dbname' => 'test$db!', 'login' => 'testuser', 'loginhost' => 'localhost']);

        // grant_privileges will die before calling escapeSql
        $mockInstaller->expects($this->never())
            ->method('escapeSql');

        $mockInstaller->expects($this->once())
            ->method('die')
            ->with('Illegal character(s) in database name')
            ->willThrowException(new \Exception('Die called with: Illegal character(s) in database name'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Die called with: Illegal character(s) in database name');

        // This should trigger die() in escapeDatabaseName when it validates the database name
        $mockInstaller->grant_privileges();
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
            ->willReturnCallback(function ($filename, $title) use (&$callCount) {
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

    public function testQuickInstallSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller([], [
            'add_initial_user',
            'add_version_info',
            'create_database',
            'create_database_user',
            'create_dumpfiles',
            'create_site_directory',
            'disconnect',
            'grant_privileges',
            'install_additional_users',
            'install_gacl',
            'insert_globals',
            'iuser_is_valid',
            'load_dumpfiles',
            'login_is_valid',
            'on_care_coordination',
            'password_is_valid',
            'root_database_connection',
            'user_database_connection',
            'user_password_is_valid',
            'write_configuration_file'
        ]);

        // Mock all validation methods to return true
        $mockInstaller->expects($this->once())->method('login_is_valid')->willReturn(true);
        $mockInstaller->expects($this->once())->method('iuser_is_valid')->willReturn(true);
        $mockInstaller->expects($this->once())->method('user_password_is_valid')->willReturn(true);
        $mockInstaller->expects($this->once())->method('password_is_valid')->willReturn(true);

        // Mock database connection methods
        $mockInstaller->expects($this->exactly(2))->method('root_database_connection')->willReturn(true);
        $mockInstaller->expects($this->exactly(2))->method('user_database_connection')->willReturnOnConsecutiveCalls(false, true);
        $mockInstaller->expects($this->exactly(2))->method('disconnect')->willReturn(true);

        // Mock database setup methods
        $mockInstaller->expects($this->once())->method('create_database')->willReturn(true);
        $mockInstaller->expects($this->once())->method('create_database_user')->willReturn(true);
        $mockInstaller->expects($this->once())->method('grant_privileges')->willReturn(true);

        // Mock configuration and setup methods
        $mockInstaller->expects($this->once())->method('load_dumpfiles')->willReturn("Creating Main Database tables...\n<span class='text-success'><b>OK</b></span>.<br>\nCreating Language Translations tables...\n<span class='text-success'><b>OK</b></span>.<br>\n");
        $mockInstaller->expects($this->once())->method('write_configuration_file')->willReturn(true);
        $mockInstaller->expects($this->once())->method('add_version_info')->willReturn(true);
        $mockInstaller->expects($this->once())->method('insert_globals')->willReturn(true);
        $mockInstaller->expects($this->once())->method('add_initial_user')->willReturn(true);
        $mockInstaller->expects($this->once())->method('install_gacl')->willReturn(true);
        $mockInstaller->expects($this->once())->method('install_additional_users')->willReturn(true);
        $mockInstaller->expects($this->once())->method('on_care_coordination')->willReturn(true);

        $result = $mockInstaller->quick_install();

        $this->assertTrue($result);
    }

    public function testQuickInstallFailsOnLoginValidation(): void
    {
        $mockInstaller = $this->createMockInstaller([], ['login_is_valid']);

        $mockInstaller->expects($this->once())->method('login_is_valid')->willReturn(false);

        $result = $mockInstaller->quick_install();

        $this->assertFalse($result);
    }

    public function testQuickInstallWithCloneDatabaseSkipsValidation(): void
    {
        $mockInstaller = $this->createMockInstaller(['clone_database' => 'source_db'], [
            'add_initial_user',
            'add_version_info',
            'create_database',
            'create_database_user',
            'create_dumpfiles',
            'disconnect',
            'grant_privileges',
            'insert_globals',
            'install_additional_users',
            'install_gacl',
            'iuser_is_valid',
            'load_dumpfiles',
            'login_is_valid',
            'password_is_valid',
            'root_database_connection',
            'user_database_connection',
            'user_password_is_valid',
            'write_configuration_file'
        ]);

        // Should not call user validation methods when cloning
        $mockInstaller->expects($this->never())->method('login_is_valid');
        $mockInstaller->expects($this->never())->method('iuser_is_valid');
        $mockInstaller->expects($this->never())->method('user_password_is_valid');

        $mockInstaller->expects($this->once())->method('password_is_valid')->willReturn(true);
        $mockInstaller->expects($this->exactly(2))->method('root_database_connection')->willReturn(true);
        $mockInstaller->expects($this->once())->method('create_dumpfiles')->willReturn("a string");
        $mockInstaller->expects($this->exactly(2))->method('user_database_connection')->willReturnOnConsecutiveCalls(false, true);
        $mockInstaller->expects($this->exactly(2))->method('disconnect')->willReturn(true);

        $mockInstaller->expects($this->once())->method('create_database')->willReturn(true);
        $mockInstaller->expects($this->once())->method('create_database_user')->willReturn(true);
        $mockInstaller->expects($this->once())->method('grant_privileges')->willReturn(true);
        $mockInstaller->expects($this->once())->method('load_dumpfiles')->willReturn("a string");
        $mockInstaller->expects($this->once())->method('write_configuration_file')->willReturn(true);

        // Should not call these methods when cloning
        $mockInstaller->expects($this->never())->method('add_version_info');
        $mockInstaller->expects($this->never())->method('insert_globals');
        $mockInstaller->expects($this->never())->method('add_initial_user');
        $mockInstaller->expects($this->never())->method('install_gacl');
        $mockInstaller->expects($this->never())->method('install_additional_users');

        $result = $mockInstaller->quick_install();

        $this->assertTrue($result);
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
            ->willReturnCallback(function ($filename, $title) use (&$callCount) {
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
            ->willReturnCallback(function ($resource) use (&$eofCallCount) {
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

    public function testAddInitialUserSuccess(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => false
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users",
            "INSERT INTO users_secure"
        ];
        $callCount = 0;

        // Mock the three SQL executions for groups, users, and users_secure
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return true;
            });

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testAddInitialUserGroupInsertFails(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // First SQL call (groups insert) fails
        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with($this->stringContains("INSERT INTO `groups`"))
            ->willReturn(false);

        $mockInstaller->expects($this->once())
            ->method('mysqliError')
            ->willReturn('Mock groups error');

        $mockInstaller->expects($this->once())
            ->method('mysqliErrno')
            ->willReturn(1062);

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR. Unable to add initial user group', $mockInstaller->error_message);
        $this->assertStringContainsString('Mock groups error', $mockInstaller->error_message);
        $this->assertStringContainsString('1062', $mockInstaller->error_message);
    }

    public function testAddInitialUserUserInsertFails(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users"
        ];
        $callCount = 0;

        // First SQL call succeeds, second fails
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return $callCount === 1 ? true : false; // First succeeds, second fails
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliError')
            ->willReturn('Mock user error');

        $mockInstaller->expects($this->once())
            ->method('mysqliErrno')
            ->willReturn(1062);

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR. Unable to add initial user', $mockInstaller->error_message);
        $this->assertStringContainsString('Mock user error', $mockInstaller->error_message);
        $this->assertStringContainsString('1062', $mockInstaller->error_message);
    }

    public function testAddInitialUserSecureInsertFails(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users",
            "INSERT INTO users_secure"
        ];
        $callCount = 0;

        // First two SQL calls succeed, third fails
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return $callCount <= 2 ? true : false; // First two succeed, third fails
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliError')
            ->willReturn('Mock secure error');

        $mockInstaller->expects($this->once())
            ->method('mysqliErrno')
            ->willReturn(1062);

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR. Unable to add initial user login credentials', $mockInstaller->error_message);
        $this->assertStringContainsString('Mock secure error', $mockInstaller->error_message);
        $this->assertStringContainsString('1062', $mockInstaller->error_message);
    }

    public function testAddInitialUserWith2FASuccess(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => true,
            'i2fasecret' => 'test2fasecret'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Mock class existence checks to return true
        $mockInstaller->method('totpClassExists')
            ->willReturn(true);

        $mockInstaller->method('cryptoGenClassExists')
            ->willReturn(true);

        $mockInstaller->method('encryptTotpSecret')
            ->willReturn('encrypted_secret');

        // Mock execute_sql to succeed for all calls (including 2FA)
        $mockInstaller->method('execute_sql')
            ->willReturn(true);

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testAddInitialUserWith2FAInsertFails(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => true,
            'i2fasecret' => 'test2fasecret'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Mock class existence checks to return true
        $mockInstaller->method('totpClassExists')
            ->willReturn(true);

        $mockInstaller->method('cryptoGenClassExists')
            ->willReturn(true);

        $mockInstaller->method('encryptTotpSecret')
            ->willReturn('encrypted_secret');

        // Mock execute_sql to succeed for non-2FA calls, fail for 2FA calls
        $mockInstaller->method('execute_sql')
            ->willReturnCallback(function ($sql) {
                // Fail specifically on 2FA insert - return exactly false
                if (stripos($sql, 'login_mfa_registrations') !== false) {
                    return false;
                }
                // Succeed on all other calls
                return true;
            });

        $mockInstaller->method('mysqliError')
            ->willReturn('Mock 2FA error');

        $mockInstaller->method('mysqliErrno')
            ->willReturn(1062);

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertFalse($result);
        $this->assertStringContainsString("ERROR. Unable to add initial user's 2FA credentials", $mockInstaller->error_message);
    }

    public function testAddInitialUserWith2FADisabled(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => false,
            'i2fasecret' => 'test2fasecret'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users",
            "INSERT INTO users_secure"
        ];
        $callCount = 0;

        // Only three SQL executions (no 2FA insert)
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return true;
            });

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testAddInitialUserWith2FANoSecret(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => true,
            'i2fasecret' => ''
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users",
            "INSERT INTO users_secure"
        ];
        $callCount = 0;

        // Only three SQL executions (no 2FA insert due to empty secret)
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return true;
            });

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testAddInitialUserWith2FAClassesNotExist(): void
    {
        $config = [
            'igroup' => 'testgroup',
            'iuser' => 'testuser',
            'iuname' => 'TestLastName',
            'iufname' => 'TestFirstName',
            'iuserpass' => 'testpassword',
            'i2faenable' => true,
            'i2fasecret' => 'test2fasecret'
        ];

        $mockInstaller = $this->createMockInstaller($config);
        $mockInstaller->dbh = $this->createMock(mysqli::class);

        // Mock class existence checks to return false
        $mockInstaller->method('totpClassExists')
            ->willReturn(false);

        $mockInstaller->method('cryptoGenClassExists')
            ->willReturn(false);

        $mockInstaller->method('encryptTotpSecret')
            ->willReturn('encrypted_secret');

        // Track expected SQL calls
        $expectedSqlCalls = [
            "INSERT INTO `groups`",
            "INSERT INTO users",
            "INSERT INTO users_secure"
        ];
        $callCount = 0;

        // Only three SQL executions (no 2FA insert due to missing classes)
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$expectedSqlCalls, &$callCount) {
                $this->assertStringContainsString($expectedSqlCalls[$callCount], $sql);
                $callCount++;
                return true;
            });

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->add_initial_user();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testInstallAdditionalUsersSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('load_file')
            ->with($mockInstaller->additional_users, 'Additional Official Users')
            ->willReturn("Creating Additional Official Users tables...\n<span class='text-success'><b>OK</b></span>.<br>\n");

        $result = $mockInstaller->install_additional_users();

        $this->assertTrue($result);
    }

    public function testInstallAdditionalUsersFailure(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('load_file')
            ->with($mockInstaller->additional_users, 'Additional Official Users')
            ->willReturn(false);

        $result = $mockInstaller->install_additional_users();

        $this->assertFalse($result);
    }

    public function testInstallAdditionalUsersWithCorrectFilePath(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Verify the additional_users property contains the expected path
        $expectedPath = __DIR__ . '/../../../../../sql/official_additional_users.sql';
        $this->assertEquals(realpath($expectedPath), realpath($mockInstaller->additional_users));

        $mockInstaller->expects($this->once())
            ->method('load_file')
            ->willReturn("Creating Additional Official Users tables...\n<span class='text-success'><b>OK</b></span>.<br>\n");

        $result = $mockInstaller->install_additional_users();

        $this->assertTrue($result);
    }

    public function testInstallAdditionalUsersWithLoadFileReturnString(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $expectedReturnString = "Creating Additional Official Users tables...\nLoading official users...\n<span class='text-success'><b>OK</b></span>.<br>\n";

        $mockInstaller->expects($this->once())
            ->method('load_file')
            ->with($mockInstaller->additional_users, 'Additional Official Users')
            ->willReturn($expectedReturnString);

        $result = $mockInstaller->install_additional_users();

        $this->assertTrue($result);
    }

    public function testOnCareCoordinationSuccess(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Mock the database query results
        $mockModuleResult = $this->createMock(mysqli_result::class);
        $mockSectionResult = $this->createMock(mysqli_result::class);
        $mockGroupResult = $this->createMock(mysqli_result::class);

        // Set up execute_sql expectations for the three SELECT queries and one INSERT
        $callCount = 0;
        $mockInstaller->expects($this->exactly(4))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$callCount, $mockModuleResult, $mockSectionResult, $mockGroupResult) {
                $callCount++;
                switch ($callCount) {
                    case 1:
                        $this->assertStringContainsString("SELECT `mod_id` FROM `modules`", $sql);
                        return $mockModuleResult;
                    case 2:
                        $this->assertStringContainsString("SELECT `section_id` FROM `module_acl_sections`", $sql);
                        return $mockSectionResult;
                    case 3:
                        $this->assertStringContainsString("SELECT `id` FROM `gacl_aro_groups`", $sql);
                        return $mockGroupResult;
                    case 4:
                        $this->assertStringContainsString("INSERT INTO `module_acl_group_settings`", $sql);
                        return true;
                    default:
                        return false;
                }
            });

        // Mock mysqliFetchArray to return the expected data
        $mockInstaller->expects($this->exactly(3))
            ->method('mysqliFetchArray')
            ->willReturnOnConsecutiveCalls(
                ['mod_id' => '123'],
                ['section_id' => '456'],
                ['id' => '789']
            );

        // Mock escapeSql to return the input unchanged for testing
        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->on_care_coordination();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testOnCareCoordinationFailsWhenModuleNotFound(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockModuleResult = $this->createMock(mysqli_result::class);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with($this->stringContains("SELECT `mod_id` FROM `modules`"))
            ->willReturn($mockModuleResult);

        // Return empty mod_id to simulate module not found
        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockModuleResult, MYSQLI_ASSOC)
            ->willReturn(['mod_id' => '']);

        $result = $mockInstaller->on_care_coordination();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR configuring Care Coordination module. Unable to get mod_id for Carecoordination module', $mockInstaller->error_message);
    }

    public function testOnCareCoordinationFailsWhenSectionNotFound(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockModuleResult = $this->createMock(mysqli_result::class);
        $mockSectionResult = $this->createMock(mysqli_result::class);

        $callCount = 0;
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$callCount, $mockModuleResult, $mockSectionResult) {
                $callCount++;
                if ($callCount === 1) {
                    $this->assertStringContainsString("SELECT `mod_id` FROM `modules`", $sql);
                    return $mockModuleResult;
                } else {
                    $this->assertStringContainsString("SELECT `section_id` FROM `module_acl_sections`", $sql);
                    return $mockSectionResult;
                }
            });

        // First call returns valid mod_id, second call returns empty section_id
        $mockInstaller->expects($this->exactly(2))
            ->method('mysqliFetchArray')
            ->willReturnOnConsecutiveCalls(
                ['mod_id' => '123'],
                ['section_id' => '']
            );

        $result = $mockInstaller->on_care_coordination();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR configuring Care Coordination module. Unable to get section_id for carecoordination module section', $mockInstaller->error_message);
    }

    public function testOnCareCoordinationFailsWhenGroupNotFound(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockModuleResult = $this->createMock(mysqli_result::class);
        $mockSectionResult = $this->createMock(mysqli_result::class);
        $mockGroupResult = $this->createMock(mysqli_result::class);

        $callCount = 0;
        $mockInstaller->expects($this->exactly(3))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$callCount, $mockModuleResult, $mockSectionResult, $mockGroupResult) {
                $callCount++;
                switch ($callCount) {
                    case 1:
                        $this->assertStringContainsString("SELECT `mod_id` FROM `modules`", $sql);
                        return $mockModuleResult;
                    case 2:
                        $this->assertStringContainsString("SELECT `section_id` FROM `module_acl_sections`", $sql);
                        return $mockSectionResult;
                    case 3:
                        $this->assertStringContainsString("SELECT `id` FROM `gacl_aro_groups`", $sql);
                        return $mockGroupResult;
                    default:
                        return false;
                }
            });

        // First two calls succeed, third returns empty group id
        $mockInstaller->expects($this->exactly(3))
            ->method('mysqliFetchArray')
            ->willReturnOnConsecutiveCalls(
                ['mod_id' => '123'],
                ['section_id' => '456'],
                ['id' => '']
            );

        $result = $mockInstaller->on_care_coordination();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR configuring Care Coordination module. Unable to get id for gacl_aro_groups admin section', $mockInstaller->error_message);
    }

    public function testOnCareCoordinationFailsWhenInsertFails(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockModuleResult = $this->createMock(mysqli_result::class);
        $mockSectionResult = $this->createMock(mysqli_result::class);
        $mockGroupResult = $this->createMock(mysqli_result::class);

        $callCount = 0;
        $mockInstaller->expects($this->exactly(4))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$callCount, $mockModuleResult, $mockSectionResult, $mockGroupResult) {
                $callCount++;
                switch ($callCount) {
                    case 1:
                        $this->assertStringContainsString("SELECT `mod_id` FROM `modules`", $sql);
                        return $mockModuleResult;
                    case 2:
                        $this->assertStringContainsString("SELECT `section_id` FROM `module_acl_sections`", $sql);
                        return $mockSectionResult;
                    case 3:
                        $this->assertStringContainsString("SELECT `id` FROM `gacl_aro_groups`", $sql);
                        return $mockGroupResult;
                    case 4:
                        $this->assertStringContainsString("INSERT INTO `module_acl_group_settings`", $sql);
                        return false; // Insert fails
                    default:
                        return false;
                }
            });

        // All SELECT queries succeed
        $mockInstaller->expects($this->exactly(3))
            ->method('mysqliFetchArray')
            ->willReturnOnConsecutiveCalls(
                ['mod_id' => '123'],
                ['section_id' => '456'],
                ['id' => '789']
            );

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->on_care_coordination();

        $this->assertFalse($result);
        $this->assertStringContainsString('ERROR configuring Care Coordination module. Unable to add the module_acl_group_settings acl entry', $mockInstaller->error_message);
    }

    public function testOnCareCoordinationWithCorrectSqlQueries(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockModuleResult = $this->createMock(mysqli_result::class);
        $mockSectionResult = $this->createMock(mysqli_result::class);
        $mockGroupResult = $this->createMock(mysqli_result::class);

        $expectedQueries = [
            "SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Carecoordination' LIMIT 1",
            "SELECT `section_id` FROM `module_acl_sections` WHERE `section_identifier` = 'carecoordination' LIMIT 1",
            "SELECT `id` FROM `gacl_aro_groups` WHERE `value` = 'admin' LIMIT 1"
        ];

        $callCount = 0;
        $mockInstaller->expects($this->exactly(4))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use (&$callCount, $mockModuleResult, $mockSectionResult, $mockGroupResult, $expectedQueries) {
                $callCount++;
                switch ($callCount) {
                    case 1:
                    case 2:
                    case 3:
                        $this->assertEquals($expectedQueries[$callCount - 1], $sql);
                        return [$mockModuleResult, $mockSectionResult, $mockGroupResult][$callCount - 1];
                    case 4:
                        $this->assertStringContainsString("INSERT INTO `module_acl_group_settings`", $sql);
                        $this->assertStringContainsString("'123'", $sql);
                        $this->assertStringContainsString("'789'", $sql);
                        $this->assertStringContainsString("'456'", $sql);
                        $this->assertStringContainsString("1", $sql);
                        return true;
                    default:
                        return false;
                }
            });

        $mockInstaller->expects($this->exactly(3))
            ->method('mysqliFetchArray')
            ->willReturnOnConsecutiveCalls(
                ['mod_id' => '123'],
                ['section_id' => '456'],
                ['id' => '789']
            );

        $mockInstaller->method('escapeSql')
            ->willReturnArgument(0);

        $result = $mockInstaller->on_care_coordination();

        $this->assertTrue($result);
    }

    public function testGetInitialUserMfaTotpSuccess(): void
    {
        $config = [
            'i2faenable' => true,
            'i2fasecret' => 'test2fasecret',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockTotp = $this->createMock(Totp::class);

        $mockInstaller->expects($this->once())
            ->method('totpClassExists')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('createTotpInstance')
            ->with('test2fasecret', 'testuser')
            ->willReturn($mockTotp);

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertSame($mockTotp, $result);
    }

    public function testGetInitialUserMfaTotpWhen2faDisabled(): void
    {
        $config = [
            'i2faenable' => false,
            'i2fasecret' => 'test2fasecret',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        // These methods should not be called when 2FA is disabled
        $mockInstaller->expects($this->never())
            ->method('totpClassExists');

        $mockInstaller->expects($this->never())
            ->method('createTotpInstance');

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertFalse($result);
    }

    public function testGetInitialUserMfaTotpWithEmptySecret(): void
    {
        $config = [
            'i2faenable' => true,
            'i2fasecret' => '',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        // These methods should not be called when secret is empty
        $mockInstaller->expects($this->never())
            ->method('totpClassExists');

        $mockInstaller->expects($this->never())
            ->method('createTotpInstance');

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertFalse($result);
    }

    public function testGetInitialUserMfaTotpWhenTotpClassDoesNotExist(): void
    {
        $config = [
            'i2faenable' => true,
            'i2fasecret' => 'test2fasecret',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockInstaller->expects($this->once())
            ->method('totpClassExists')
            ->willReturn(false);

        // createTotpInstance should not be called when Totp class doesn't exist
        $mockInstaller->expects($this->never())
            ->method('createTotpInstance');

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertFalse($result);
    }

    public function testGetInitialUserMfaTotpWithTruthyString2faEnable(): void
    {
        $config = [
            'i2faenable' => 'true',  // String 'true' should be truthy
            'i2fasecret' => 'test2fasecret',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockTotp = $this->createMock(Totp::class);

        $mockInstaller->expects($this->once())
            ->method('totpClassExists')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('createTotpInstance')
            ->with('test2fasecret', 'testuser')
            ->willReturn($mockTotp);

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertSame($mockTotp, $result);
    }

    public function testGetInitialUserMfaTotpWithFalsyString2faEnable(): void
    {
        $config = [
            'i2faenable' => '0',  // String '0' should be falsy
            'i2fasecret' => 'test2fasecret',
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockInstaller->expects($this->never())
            ->method('totpClassExists');

        $mockInstaller->expects($this->never())
            ->method('createTotpInstance');

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertFalse($result);
    }

    public function testGetInitialUserMfaTotpWithNullSecret(): void
    {
        $config = [
            'i2faenable' => true,
            'i2fasecret' => null,
            'iuser' => 'testuser'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockInstaller->expects($this->never())
            ->method('totpClassExists');

        $mockInstaller->expects($this->never())
            ->method('createTotpInstance');

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertFalse($result);
    }

    public function testGetInitialUserMfaTotpParameterPassing(): void
    {
        $config = [
            'i2faenable' => true,
            'i2fasecret' => 'my_secret_key_123',
            'iuser' => 'admin_user'
        ];

        $mockInstaller = $this->createMockInstaller($config);

        $mockTotp = $this->createMock(Totp::class);

        $mockInstaller->expects($this->once())
            ->method('totpClassExists')
            ->willReturn(true);

        // Verify exact parameters are passed to createTotpInstance
        $mockInstaller->expects($this->once())
            ->method('createTotpInstance')
            ->with(
                $this->equalTo('my_secret_key_123'),
                $this->equalTo('admin_user')
            )
            ->willReturn($mockTotp);

        $result = $mockInstaller->get_initial_user_mfa_totp();

        $this->assertSame($mockTotp, $result);
    }

    public function testGetInitialUserMfaTotpAllConditionsMustBeTrue(): void
    {
        // Test that ALL conditions must be satisfied for success
        $scenarios = [
            // 2FA disabled, has secret, class exists
            [
                'config' => ['i2faenable' => false, 'i2fasecret' => 'secret', 'iuser' => 'user'],
                'totpClassExists' => true,
                'expectedTotpClassCalls' => 0,
                'expectedCreateTotpCalls' => 0
            ],
            // 2FA enabled, no secret, class exists
            [
                'config' => ['i2faenable' => true, 'i2fasecret' => '', 'iuser' => 'user'],
                'totpClassExists' => true,
                'expectedTotpClassCalls' => 0,
                'expectedCreateTotpCalls' => 0
            ],
            // 2FA enabled, has secret, no class
            [
                'config' => ['i2faenable' => true, 'i2fasecret' => 'secret', 'iuser' => 'user'],
                'totpClassExists' => false,
                'expectedTotpClassCalls' => 1,
                'expectedCreateTotpCalls' => 0
            ]
        ];

        foreach ($scenarios as $scenario) {
            $mockInstaller = $this->createMockInstaller($scenario['config']);

            if ($scenario['expectedTotpClassCalls'] > 0) {
                $mockInstaller->expects($this->exactly($scenario['expectedTotpClassCalls']))
                    ->method('totpClassExists')
                    ->willReturn($scenario['totpClassExists']);
            } else {
                $mockInstaller->expects($this->never())
                    ->method('totpClassExists');
            }

            $mockInstaller->expects($this->exactly($scenario['expectedCreateTotpCalls']))
                ->method('createTotpInstance');

            $result = $mockInstaller->get_initial_user_mfa_totp();

            $this->assertFalse($result, 'Expected false for scenario: ' . json_encode($scenario['config']));
        }
    }

    public function testCreateSiteDirectoryWhenDirectoryAlreadyExists(): void
    {
        $mockInstaller = $this->createMockInstaller(['source_site_id' => 'default']);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/existing/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory already exists
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/existing/site')
            ->willReturn(true);

        // These methods should not be called when directory exists
        $mockInstaller->expects($this->never())
            ->method('recurse_copy');

        $mockInstaller->expects($this->never())
            ->method('globPattern');

        $mockInstaller->expects($this->never())
            ->method('unlinkFile');

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectorySuccess(): void
    {
        $config = [
            'source_site_id' => 'source_site',
            'clone_database' => ''
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/new/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory does not exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/new/site')
            ->willReturn(false);

        // Copy succeeds
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/path/to/sites/source_site', '/path/to/new/site')
            ->willReturn(true);

        // Not cloning database, so files should be deleted
        $mockFiles = [
            '/path/to/new/site/documents/logs_and_misc/methods/file1.key',
            '/path/to/new/site/documents/logs_and_misc/methods/file2.cert'
        ];

        $mockInstaller->expects($this->once())
            ->method('globPattern')
            ->with('/path/to/new/site/documents/logs_and_misc/methods/*')
            ->willReturn($mockFiles);

        $mockInstaller->expects($this->exactly(2))
            ->method('unlinkFile')
            ->willReturnCallback(function ($file) use ($mockFiles) {
                $this->assertContains($file, $mockFiles);
                return true;
            });

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectorySuccessWithCloneDatabase(): void
    {
        $config = [
            'source_site_id' => 'source_site',
            'clone_database' => 'true'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/new/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory does not exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/new/site')
            ->willReturn(false);

        // Copy succeeds
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/path/to/sites/source_site', '/path/to/new/site')
            ->willReturn(true);

        // Cloning database, so files should NOT be deleted
        $mockInstaller->expects($this->never())
            ->method('globPattern');

        $mockInstaller->expects($this->never())
            ->method('unlinkFile');

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectoryFailsWhenCopyFails(): void
    {
        $config = [
            'source_site_id' => 'source_site',
            'clone_database' => ''
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/new/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory does not exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/new/site')
            ->willReturn(false);

        // Copy fails
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/path/to/sites/source_site', '/path/to/new/site')
            ->willReturn(false);

        // Set error message in the mock (simulating recurse_copy failure)
        $mockInstaller->error_message = 'Copy failed';

        // These methods should not be called when copy fails
        $mockInstaller->expects($this->never())
            ->method('globPattern');

        $mockInstaller->expects($this->never())
            ->method('unlinkFile');

        $result = $mockInstaller->create_site_directory();

        $this->assertFalse($result);
        $this->assertStringContainsString("unable to copy directory: '/path/to/sites/source_site' to '/path/to/new/site'. Copy failed", $mockInstaller->error_message);
    }

    public function testCreateSiteDirectoryWithEmptyGlobResult(): void
    {
        $config = [
            'source_site_id' => 'source_site',
            'clone_database' => ''
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/new/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory does not exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/new/site')
            ->willReturn(false);

        // Copy succeeds
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/path/to/sites/source_site', '/path/to/new/site')
            ->willReturn(true);

        // No files to delete
        $mockInstaller->expects($this->once())
            ->method('globPattern')
            ->with('/path/to/new/site/documents/logs_and_misc/methods/*')
            ->willReturn([]);

        // unlinkFile should not be called when no files exist
        $mockInstaller->expects($this->never())
            ->method('unlinkFile');

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectoryWithGlobReturnsFalse(): void
    {
        $config = [
            'source_site_id' => 'source_site',
            'clone_database' => ''
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals
        $GLOBALS['OE_SITE_DIR'] = '/path/to/new/site';
        $GLOBALS['OE_SITES_BASE'] = '/path/to/sites';

        // Directory does not exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/path/to/new/site')
            ->willReturn(false);

        // Copy succeeds
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/path/to/sites/source_site', '/path/to/new/site')
            ->willReturn(true);

        // glob() returns false (error case)
        $mockInstaller->expects($this->once())
            ->method('globPattern')
            ->with('/path/to/new/site/documents/logs_and_misc/methods/*')
            ->willReturn(false);

        // unlinkFile should not be called when glob returns false
        $mockInstaller->expects($this->never())
            ->method('unlinkFile');

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectoryUsesCorrectPaths(): void
    {
        $config = [
            'source_site_id' => 'test_source',
            'clone_database' => ''
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up specific globals to test path construction
        $GLOBALS['OE_SITE_DIR'] = '/custom/site/path';
        $GLOBALS['OE_SITES_BASE'] = '/custom/sites/base';

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/custom/site/path')
            ->willReturn(false);

        // Verify exact source and destination paths
        $mockInstaller->expects($this->once())
            ->method('recurse_copy')
            ->with('/custom/sites/base/test_source', '/custom/site/path')
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('globPattern')
            ->with('/custom/site/path/documents/logs_and_misc/methods/*')
            ->willReturn([]);

        $result = $mockInstaller->create_site_directory();

        $this->assertTrue($result);
    }

    public function testCreateSiteDirectoryWithVariousCloneDatabaseValues(): void
    {
        $scenarios = [
            // These values should be considered "truthy" and skip file deletion
            ['clone_database' => 'true', 'shouldDeleteFiles' => false],
            ['clone_database' => '1', 'shouldDeleteFiles' => false],
            ['clone_database' => 'yes', 'shouldDeleteFiles' => false],
            ['clone_database' => 'anything_non_empty', 'shouldDeleteFiles' => false],
            // These values should be considered "falsy" and allow file deletion
            ['clone_database' => '', 'shouldDeleteFiles' => true],
            ['clone_database' => '0', 'shouldDeleteFiles' => true],
            ['clone_database' => false, 'shouldDeleteFiles' => true],
            ['clone_database' => null, 'shouldDeleteFiles' => true]
        ];

        foreach ($scenarios as $scenario) {
            $config = [
                'source_site_id' => 'source',
                'clone_database' => $scenario['clone_database']
            ];
            $mockInstaller = $this->createMockInstaller($config);

            // Set up globals
            $GLOBALS['OE_SITE_DIR'] = '/test/site';
            $GLOBALS['OE_SITES_BASE'] = '/test/sites';

            $mockInstaller->method('fileExists')->willReturn(false);
            $mockInstaller->method('recurse_copy')->willReturn(true);

            if ($scenario['shouldDeleteFiles']) {
                $mockInstaller->expects($this->once())
                    ->method('globPattern')
                    ->willReturn([]);
            } else {
                $mockInstaller->expects($this->never())
                    ->method('globPattern');
            }

            $result = $mockInstaller->create_site_directory();

            $this->assertTrue($result, 'Failed for clone_database value: ' . json_encode($scenario['clone_database']));
        }
    }

    public function testWriteConfigurationFileSuccess(): void
    {
        $config = [
            'server' => 'test.server.com',
            'port' => '3307',
            'login' => 'test_user',
            'pass' => 'test_pass',
            'dbname' => 'test_db'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals and conffile
        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        // Directory already exists
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->with('/test/site/dir')
            ->willReturn(true);

        // create_site_directory should not be called when directory exists (no mocking needed)

        // touchFile should be called
        $mockInstaller->expects($this->once())
            ->method('touchFile')
            ->with('/test/site/dir/sqlconf.php')
            ->willReturn(true);

        // openFile should succeed
        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->expects($this->once())
            ->method('openFile')
            ->with('/test/site/dir/sqlconf.php', 'w')
            ->willReturn($mockFileHandle);

        // All writeToFile calls should succeed
        $mockInstaller->expects($this->exactly(10))
            ->method('writeToFile')
            ->willReturn(10); // Simulate successful writes

        // closeFile should succeed
        $mockInstaller->expects($this->once())
            ->method('closeFile')
            ->with($mockFileHandle)
            ->willReturn(true);

        $result = $mockInstaller->write_configuration_file();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);

        fclose($mockFileHandle);
    }

    public function testWriteConfigurationFileCreatesDirectoryWhenNotExists(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Set up globals and conffile
        $GLOBALS['OE_SITE_DIR'] = '/test/new/site/dir';
        $mockInstaller->conffile = '/test/new/site/dir/sqlconf.php';

        // Directory does not exist
        $mockInstaller->method('fileExists')
            ->with('/test/new/site/dir')
            ->willReturn(false);

        // create_site_directory should be called (no mocking needed - it will call the real method)

        // Mock successful file operations
        $mockInstaller->method('touchFile')->willReturn(true);
        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->method('openFile')->willReturn($mockFileHandle);
        $mockInstaller->method('writeToFile')->willReturn(10);
        $mockInstaller->method('closeFile')->willReturn(true);

        $result = $mockInstaller->write_configuration_file();

        $this->assertTrue($result);

        fclose($mockFileHandle);
    }

    public function testWriteConfigurationFileFailsWhenOpenFileFails(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Set up globals and conffile
        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        // Directory exists
        $mockInstaller->method('fileExists')->willReturn(true);
        $mockInstaller->method('touchFile')->willReturn(true);

        // openFile fails
        $mockInstaller->expects($this->once())
            ->method('openFile')
            ->with('/test/site/dir/sqlconf.php', 'w')
            ->willReturn(false);

        // writeToFile should not be called when file opening fails
        $mockInstaller->expects($this->never())
            ->method('writeToFile');

        $result = $mockInstaller->write_configuration_file();

        $this->assertFalse($result);
        $this->assertEquals('unable to open configuration file for writing: /test/site/dir/sqlconf.php', $mockInstaller->error_message);
    }

    public function testWriteConfigurationFileFailsWhenWritesFail(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Set up globals and conffile
        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        $mockInstaller->method('fileExists')->willReturn(true);
        $mockInstaller->method('touchFile')->willReturn(true);

        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->method('openFile')->willReturn($mockFileHandle);

        // Simulate some write failures - return false for some calls
        $writeCallCount = 0;
        $mockInstaller->expects($this->exactly(10))
            ->method('writeToFile')
            ->willReturnCallback(function () use (&$writeCallCount) {
                $writeCallCount++;
                // Fail on calls 3 and 7 to simulate partial write failures
                return ($writeCallCount === 3 || $writeCallCount === 7) ? false : 10;
            });

        $mockInstaller->method('closeFile')->willReturn(false); // Also fail closeFile

        $result = $mockInstaller->write_configuration_file();

        $this->assertFalse($result);
        // Should report 3 failed operations (2 writeToFile + 1 closeFile)
        $this->assertStringContainsString("ERROR. Couldn't write 3 lines to config file '/test/site/dir/sqlconf.php'", $mockInstaller->error_message);

        fclose($mockFileHandle);
    }

    public function testWriteConfigurationFileGeneratesCorrectContent(): void
    {
        $config = [
            'server' => 'db.example.com',
            'port' => '3308',
            'login' => 'dbuser',
            'pass' => 'dbpass',
            'dbname' => 'mydb'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Set up globals and conffile
        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        $mockInstaller->method('fileExists')->willReturn(true);
        $mockInstaller->method('touchFile')->willReturn(true);

        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->method('openFile')->willReturn($mockFileHandle);

        // Capture the content being written
        $writtenContent = [];
        $mockInstaller->expects($this->exactly(10))
            ->method('writeToFile')
            ->willReturnCallback(function ($handle, $data) use (&$writtenContent) {
                $writtenContent[] = $data;
                return strlen($data);
            });

        $mockInstaller->method('closeFile')->willReturn(true);

        $result = $mockInstaller->write_configuration_file();

        $this->assertTrue($result);

        // Verify the content contains the expected configuration values
        $allContent = implode('', $writtenContent);
        $this->assertStringContainsString('db.example.com', $allContent);
        $this->assertStringContainsString('3308', $allContent);
        $this->assertStringContainsString('dbuser', $allContent);
        $this->assertStringContainsString('dbpass', $allContent);
        $this->assertStringContainsString('mydb', $allContent);
        $this->assertStringContainsString('utf8mb4', $allContent);
        $this->assertStringContainsString('$config = 1', $allContent);

        fclose($mockFileHandle);
    }

    public function testWriteConfigurationFileHandlesSpecialCharactersInConfig(): void
    {
        $config = [
            'server' => 'server-with-dashes.com',
            'login' => 'user_with_underscores',
            'pass' => 'p@ssw0rd!#$%',
            'dbname' => 'db-name_123'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        $mockInstaller->method('fileExists')->willReturn(true);
        $mockInstaller->method('touchFile')->willReturn(true);

        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->method('openFile')->willReturn($mockFileHandle);

        $writtenContent = [];
        $mockInstaller->method('writeToFile')
            ->willReturnCallback(function ($handle, $data) use (&$writtenContent) {
                $writtenContent[] = $data;
                return strlen($data);
            });

        $mockInstaller->method('closeFile')->willReturn(true);

        $result = $mockInstaller->write_configuration_file();

        $this->assertTrue($result);

        // Verify special characters are handled properly
        $allContent = implode('', $writtenContent);
        $this->assertStringContainsString('server-with-dashes.com', $allContent);
        $this->assertStringContainsString('user_with_underscores', $allContent);
        $this->assertStringContainsString('p@ssw0rd!#$%', $allContent);
        $this->assertStringContainsString('db-name_123', $allContent);

        fclose($mockFileHandle);
    }

    public function testWriteConfigurationFileCountsErrorsCorrectly(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $GLOBALS['OE_SITE_DIR'] = '/test/site/dir';
        $mockInstaller->conffile = '/test/site/dir/sqlconf.php';

        $mockInstaller->method('fileExists')->willReturn(true);
        $mockInstaller->method('touchFile')->willReturn(true);

        $mockFileHandle = fopen('php://memory', 'w+');
        $mockInstaller->method('openFile')->willReturn($mockFileHandle);

        // Simulate exactly 5 write failures
        $writeCallCount = 0;
        $mockInstaller->method('writeToFile')
            ->willReturnCallback(function () use (&$writeCallCount) {
                $writeCallCount++;
                // Fail on calls 2, 4, 6, 8, 10
                return ($writeCallCount % 2 === 0) ? false : 10;
            });

        $mockInstaller->method('closeFile')->willReturn(true);

        $result = $mockInstaller->write_configuration_file();

        $this->assertFalse($result);
        $this->assertStringContainsString("ERROR. Couldn't write 5 lines to config file", $mockInstaller->error_message);

        fclose($mockFileHandle);
    }

    public function testInstallGaclSuccess(): void
    {
        $config = [
            'iuser' => 'admin_user',
            'iuname' => 'Administrator Name'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Create a mock GACL API
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        // Mock the newGaclApi method to return our mock
        $mockInstaller->expects($this->once())
            ->method('newGaclApi')
            ->willReturn($mockGacl);

        // Mock all add_object_section calls - first one has error checking, others don't
        $mockGacl->method('add_object_section')
            ->willReturn(true);

        // Mock all other GACL method calls that will be made
        $mockGacl->method('add_object')->willReturn(true);
        $mockGacl->method('add_group')->willReturn(1);
        $mockGacl->method('add_group_object')->willReturn(true);
        $mockGacl->method('add_acl')->willReturn(true);

        // The method should succeed and return true
        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    public function testInstallGaclFailsOnFirstAddObjectSection(): void
    {
        $config = [
            'iuser' => 'admin_user',
            'iuname' => 'Administrator Name'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        // Create a mock GACL API
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->expects($this->once())
            ->method('newGaclApi')
            ->willReturn($mockGacl);

        // Mock add_object_section calls - first one fails (which should stop execution)
        $callCount = 0;
        $mockGacl->method('add_object_section')
            ->willReturnCallback(function ($name, $identifier) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    // First call (Accounting) should fail
                    $this->assertEquals('Accounting', $name);
                    $this->assertEquals('acct', $identifier);
                    return false;
                }
                // Subsequent calls should not happen due to early return
                return true;
            });

        $result = $mockInstaller->install_gacl();

        $this->assertFalse($result);
        $this->assertEquals("ERROR, Unable to create the access controls for OpenEMR.", $mockInstaller->error_message);
    }

    public function testInstallGaclUsesCorrectUserInfo(): void
    {
        $config = [
            'iuser' => 'test_admin',
            'iuname' => 'Test Administrator'
        ];
        $mockInstaller = $this->createMockInstaller($config);

        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')
            ->willReturn($mockGacl);

        // Mock basic calls
        $mockGacl->method('add_object_section')->willReturn(true);
        $mockGacl->method('add_acl')->willReturn(true);

        // Mock add_group to return incremental IDs
        $groupIdCounter = 0;
        $mockGacl->method('add_group')
            ->willReturnCallback(function () use (&$groupIdCounter) {
                return ++$groupIdCounter;
            });

        // Mock add_object - verify the user ARO is created correctly
        $addObjectCalls = [];
        $mockGacl->method('add_object')
            ->willReturnCallback(function ($section, $name, $identifier, $order, $hidden, $type) use (&$addObjectCalls) {
                $addObjectCalls[] = [$section, $name, $identifier, $type];
                return true;
            });

        // Mock add_group_object - verify the user is added to admin group
        $addGroupObjectCalls = [];
        $mockGacl->method('add_group_object')
            ->willReturnCallback(function ($groupId, $section, $identifier, $type) use (&$addGroupObjectCalls) {
                $addGroupObjectCalls[] = [$groupId, $section, $identifier, $type];
                return true;
            });

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);

        // Verify the user ARO was created correctly
        $this->assertContains(['users', 'Test Administrator', 'test_admin', 'ARO'], $addObjectCalls);

        // Verify the user was added to the admin group (group ID 2)
        $this->assertContains([2, 'users', 'test_admin', 'ARO'], $addGroupObjectCalls);
    }

    public function testInstallGaclCreatesExpectedSections(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')->willReturn($mockGacl);

        // Define expected sections in order
        $expectedSections = [
            ['Accounting', 'acct'],
            ['Administration', 'admin'],
            ['Encounters', 'encounters'],
            ['Lists', 'lists'],
            ['Patients', 'patients'],
            ['Squads', 'squads'],
            ['Sensitivities', 'sensitivities'],
            ['Placeholder', 'placeholder'],
            ['Nation Notes', 'nationnotes'],
            ['Patient Portal', 'patientportal'],
            ['Menus', 'menus'],
            ['Groups', 'groups'],
            ['Inventory', 'inventory'],
            ['Users', 'users'] // ARO section
        ];

        // Expect all sections to be created
        $mockGacl->expects($this->exactly(count($expectedSections)))
            ->method('add_object_section')
            ->willReturnCallback(function ($name, $identifier) use ($expectedSections) {
                static $callCount = 0;
                $expected = $expectedSections[$callCount];
                $this->assertEquals($expected[0], $name);
                $this->assertEquals($expected[1], $identifier);
                $callCount++;
                return true;
            });

        // Mock other methods to avoid errors
        $mockGacl->method('add_object')->willReturn(true);
        $mockGacl->method('add_group')->willReturn(1);
        $mockGacl->method('add_group_object')->willReturn(true);
        $mockGacl->method('add_acl')->willReturn(true);

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);
    }

    public function testInstallGaclCreatesExpectedGroups(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')->willReturn($mockGacl);

        $expectedGroups = [
            ['users', 'OpenEMR Users', 0],
            ['admin', 'Administrators', 1], // 1 is the users group ID
            ['clin', 'Clinicians', 1],
            ['doc', 'Physicians', 1],
            ['front', 'Front Office', 1],
            ['back', 'Accounting', 1],
            ['breakglass', 'Emergency Login', 1]
        ];

        // Mock add_group calls and return incremental IDs
        $callCount = 0;
        $mockGacl->expects($this->exactly(count($expectedGroups)))
            ->method('add_group')
            ->willReturnCallback(function ($identifier, $name, $parent) use ($expectedGroups, &$callCount) {
                $expected = $expectedGroups[$callCount];
                $this->assertEquals($expected[0], $identifier);
                $this->assertEquals($expected[1], $name);
                $this->assertEquals($expected[2], $parent);
                return ++$callCount; // Return group ID
            });

        // Mock other methods
        $mockGacl->method('add_object_section')->willReturn(true);
        $mockGacl->method('add_object')->willReturn(true);
        $mockGacl->method('add_group_object')->willReturn(true);
        $mockGacl->method('add_acl')->willReturn(true);

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);
    }

    public function testInstallGaclSetsAdministratorPermissions(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')->willReturn($mockGacl);

        // Mock the basic setup calls
        $mockGacl->method('add_object_section')->willReturn(true);
        $mockGacl->method('add_object')->willReturn(true);
        $mockGacl->method('add_group')->willReturn(2); // Return admin group ID = 2
        $mockGacl->method('add_group_object')->willReturn(true);

        // The key test: verify administrator ACL is created correctly
        $expectedAdminAcos = [
            'acct' => ['bill', 'disc', 'eob', 'rep', 'rep_a'],
            'admin' => ['calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'],
            'encounters' => ['auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'],
            'inventory' => ['lots', 'sales', 'purchases', 'transfers', 'adjustments', 'consumption', 'destruction', 'reporting'],
            'lists' => ['default','state','country','language','ethrace'],
            'patients' => ['appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm','pat_rep'],
            'sensitivities' => ['normal', 'high'],
            'nationnotes' => ['nn_configure'],
            'patientportal' => ['portal'],
            'menus' => ['modle'],
            'groups' => ['gadd','gcalendar','glog','gdlog','gm']
        ];

        // Expect add_acl to be called multiple times, we'll verify the first one (admin)
        $mockGacl->expects($this->atLeastOnce())
            ->method('add_acl')
            ->willReturnCallback(function ($acos, $aros1, $aros2, $axos1, $axos2, $enabled, $enabled2, $access, $note) use ($expectedAdminAcos) {
                static $firstCall = true;
                if ($firstCall) {
                    // Verify the first ACL call is for administrators
                    $this->assertEquals($expectedAdminAcos, $acos);
                    $this->assertEquals([2], $aros2); // Admin group ID
                    $this->assertEquals('write', $access);
                    $this->assertStringContainsString('Administrators can do anything', $note);
                    $firstCall = false;
                }
                return true;
            });

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);
    }

    public function testInstallGaclWithDefaultUserConfig(): void
    {
        // Test with default config values
        $mockInstaller = $this->createMockInstaller(); // No custom config
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')->willReturn($mockGacl);

        // Mock basic calls
        $mockGacl->method('add_object_section')->willReturn(true);
        $mockGacl->method('add_object')->willReturn(true);
        $mockGacl->method('add_group')->willReturn(1);
        $mockGacl->method('add_acl')->willReturn(true);

        // Test default user values are used
        $mockGacl->expects($this->once())
            ->method('add_group_object')
            ->with(1, 'users', 'openemr', 'ARO') // Default iuser value
            ->willReturn(true);

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);
    }

    public function testInstallGaclCreatesAllObjectTypes(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockGacl = $this->createMock(\OpenEMR\Gacl\GaclApi::class);

        $mockInstaller->method('newGaclApi')->willReturn($mockGacl);

        // Track calls to add_object to ensure all expected objects are created
        $addObjectCalls = [];
        $mockGacl->method('add_object')
            ->willReturnCallback(function ($section, $name, $identifier, $order, $hidden, $type) use (&$addObjectCalls) {
                $addObjectCalls[] = [$section, $identifier, $type];
                return true;
            });

        // Mock other methods
        $mockGacl->method('add_object_section')->willReturn(true);
        $mockGacl->method('add_group')->willReturn(1);
        $mockGacl->method('add_group_object')->willReturn(true);
        $mockGacl->method('add_acl')->willReturn(true);

        $result = $mockInstaller->install_gacl();

        $this->assertTrue($result);

        // Verify some key objects were created
        $this->assertContains(['acct', 'bill', 'ACO'], $addObjectCalls);
        $this->assertContains(['admin', 'super', 'ACO'], $addObjectCalls);
        $this->assertContains(['patients', 'demo', 'ACO'], $addObjectCalls);
        $this->assertContains(['users', 'openemr', 'ARO'], $addObjectCalls); // Default user

        // Should have created many objects (ACOs and at least one ARO)
        $this->assertGreaterThan(50, count($addObjectCalls));
    }

    private function createMockInstallerWithoutExecuteSql(array $config = []): MockObject
    {
        $defaultConfig = [
            'server' => 'localhost',
            'root' => 'root',
            'rootpass' => 'password',
            'port' => '3306',
            'login' => 'openemr',
            'pass' => 'openemr',
            'dbname' => 'openemr',
            'iuser' => 'openemr',
            'iuname' => 'Administrator',
            'iuserpass' => 'admin',
            'igroup' => 'Default'
        ];

        $config = array_merge($defaultConfig, $config);

        // Mock all methods except execute_sql so we can test it
        $mockMethods = [
            'atEndOfFile',
            'closeFile',
            'createTotpInstance',
            'cryptoGenClassExists',
            'die',
            'encryptTotpSecret',
            'escapeDatabaseName',
            'escapeSql',
            'fileExists',
            'getLine',
            'globPattern',
            'load_file',
            'mysqliErrno',
            'mysqliError',
            'mysqliFetchArray',
            'mysqliInit',
            'mysqliNumRows',
            'mysqliQuery',
            'mysqliRealConnect',
            'mysqliSelectDb',
            'mysqliSslSet',
            'newGaclApi',
            'openFile',
            'recurse_copy',
            'set_collation',
            'set_sql_strict',
            'totpClassExists',
            'touchFile',
            'unlinkFile',
            'user_database_connection',
            'writeToFile',
        ];

        return $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([$config])
            ->onlyMethods($mockMethods)
            ->getMock();
    }

    // Test execute_sql through drop_database() - Success case
    public function testExecuteSqlSuccessViaDropDatabase(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutExecuteSql();
        $mockMysqli = $this->createMock(mysqli::class);
        $mockResult = $this->createMock(mysqli_result::class);

        // Set up the database connection
        $mockInstaller->dbh = $mockMysqli;

        $mockInstaller->expects($this->once())
            ->method('escapeDatabaseName')
            ->with('openemr')
            ->willReturn('`openemr`');

        $mockInstaller->expects($this->once())
            ->method('mysqliQuery')
            ->with($mockMysqli, "drop database if exists `openemr`")
            ->willReturn(true); // DDL statements return boolean

        $result = $mockInstaller->drop_database();

        $this->assertTrue($result);
        $this->assertEmpty($mockInstaller->error_message);
    }

    // Test execute_sql through drop_database() - Query failure with error logging
    public function testExecuteSqlFailureWithErrorViaDropDatabase(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutExecuteSql();
        $mockMysqli = $this->createMock(mysqli::class);

        // Set up the database connection
        $mockInstaller->dbh = $mockMysqli;

        $mockInstaller->expects($this->once())
            ->method('escapeDatabaseName')
            ->with('openemr')
            ->willReturn('`openemr`');

        $mockInstaller->expects($this->once())
            ->method('mysqliQuery')
            ->with($mockMysqli, "drop database if exists `openemr`")
            ->willReturn(false);

        $mockInstaller->expects($this->once())
            ->method('mysqliError')
            ->with($mockMysqli)
            ->willReturn('Access denied for user');

        $result = $mockInstaller->drop_database();

        $this->assertFalse($result);
        $this->assertStringContainsString('unable to execute SQL', $mockInstaller->error_message);
        $this->assertStringContainsString('Access denied for user', $mockInstaller->error_message);
    }

    // Test execute_sql auto-connection when no dbh exists
    public function testExecuteSqlAutoConnectsWhenNoDbh(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutExecuteSql();
        $mockMysqli = $this->createMock(mysqli::class);

        // Initially no database connection
        $mockInstaller->dbh = false;

        $mockInstaller->expects($this->once())
            ->method('escapeDatabaseName')
            ->with('openemr')
            ->willReturn('`openemr`');

        $mockInstaller->expects($this->once())
            ->method('user_database_connection')
            ->willReturnCallback(function () use ($mockInstaller, $mockMysqli) {
                $mockInstaller->dbh = $mockMysqli;
                return true;
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliQuery')
            ->with($mockMysqli, "drop database if exists `openemr`")
            ->willReturn(true);

        $result = $mockInstaller->drop_database();

        $this->assertTrue($result);
    }

    // Test execute_sql exception handling through mysqli_sql_exception
    public function testExecuteSqlHandlesException(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutExecuteSql();
        $mockMysqli = $this->createMock(mysqli::class);

        // Set up the database connection
        $mockInstaller->dbh = $mockMysqli;

        $mockInstaller->expects($this->once())
            ->method('escapeDatabaseName')
            ->with('openemr')
            ->willReturn('`openemr`');

        $exception = new mysqli_sql_exception('SQL exception occurred', 1234);

        $mockInstaller->expects($this->once())
            ->method('mysqliQuery')
            ->with($mockMysqli, "drop database if exists `openemr`")
            ->willThrowException($exception);

        $result = $mockInstaller->drop_database();

        $this->assertFalse($result);
        $this->assertStringContainsString('unable to execute SQL', $mockInstaller->error_message);
        $this->assertStringContainsString('SQL exception occurred', $mockInstaller->error_message);
    }

    // Test execute_sql with showError=false - no error message should be set when query fails
    public function testExecuteSqlWithShowErrorFalse(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutExecuteSql();
        $mockMysqli = $this->createMock(mysqli::class);
        $mockResult = $this->createMock(mysqli_result::class);

        // Set up the database connection
        $mockInstaller->dbh = $mockMysqli;

        // Mock escapeSql calls
        $mockInstaller->expects($this->exactly(3))
            ->method('escapeSql')
            ->willReturnArgument(0);

        // First query with showError=false fails - should not log error
        // Second query (global_priv) succeeds
        // Third query (CREATE USER) succeeds
        $mockInstaller->expects($this->exactly(3))
            ->method('mysqliQuery')
            ->willReturnOnConsecutiveCalls(false, $mockResult, true);

        // mysqliNumRows called for checking user existence
        $mockInstaller->expects($this->once())
            ->method('mysqliNumRows')
            ->with($mockResult)
            ->willReturn(0);

        // mysqliError should NOT be called for the first query (showError=false)
        $mockInstaller->expects($this->never())
            ->method('mysqliError');

        $result = $mockInstaller->create_database_user();

        $this->assertTrue($result);
        // Error message should be empty because the first failed query had showError=false
        $this->assertEmpty($mockInstaller->error_message);
    }

    private function createMockInstallerWithoutConnectToDatabase(array $config = []): MockObject
    {
        $defaultConfig = [
            'server' => 'localhost',
            'root' => 'root',
            'rootpass' => 'password',
            'port' => '3306',
            'login' => 'openemr',
            'pass' => 'openemr',
            'dbname' => 'openemr',
            'site' => 'default'
        ];

        $config = array_merge($defaultConfig, $config);

        // Mock all methods except connect_to_database so we can test it
        $mockMethods = [
            'atEndOfFile',
            'closeFile',
            'createTotpInstance',
            'cryptoGenClassExists',
            'die',
            'encryptTotpSecret',
            'escapeDatabaseName',
            'escapeSql',
            'execute_sql',
            'fileExists',
            'getLine',
            'globPattern',
            'load_file',
            'mysqliErrno',
            'mysqliError',
            'mysqliFetchArray',
            'mysqliInit',
            'mysqliNumRows',
            'mysqliQuery',
            'mysqliRealConnect',
            'mysqliSelectDb',
            'mysqliSslSet',
            'newGaclApi',
            'openFile',
            'recurse_copy',
            'set_collation',
            'set_sql_strict',
            'totpClassExists',
            'touchFile',
            'unlinkFile',
            'writeToFile',
        ];

        return $this->getMockBuilder(Installer::class)
            ->setConstructorArgs([$config])
            ->onlyMethods($mockMethods)
            ->getMock();
    }

    // Test connect_to_database through root_database_connection() - Success without SSL
    public function testConnectToDatabaseSuccessNoSSL(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        // Mock mysqliInit to return mock mysqli object
        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        // No SSL certificates exist
        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        // Mock successful connection
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->with(
                $mockMysqli,
                'localhost',
                'root',
                'password',
                '',
                3306,
                '',
                0  // No SSL flags
            )
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $result = $mockInstaller->root_database_connection();

        $this->assertTrue($result);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    // Test connect_to_database through user_database_connection() - Success with SSL CA only
    public function testConnectToDatabaseSuccessSSLCAOnly(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        // Mock mysqliInit to return mock mysqli object
        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        // Mock fileExists calls for SSL certificates
        // First call checks mysql-ca (exists), then mysql-key (doesn't exist, short-circuits)
        $mockInstaller->expects($this->exactly(2))
            ->method('fileExists')
            ->willReturnOnConsecutiveCalls(true, false);

        // Mock SSL setup (CA only, no client certs)
        $mockInstaller->expects($this->once())
            ->method('mysqliSslSet')
            ->with(
                $mockMysqli,
                null,  // no key
                null,  // no cert
                $this->stringContains('mysql-ca'),  // CA file
                null,
                null
            );

        // Mock successful SSL connection
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->with(
                $mockMysqli,
                'localhost',
                'openemr',
                'openemr',
                'openemr',
                3306,
                '',
                MYSQLI_CLIENT_SSL  // SSL flags
            )
            ->willReturn(true);

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

    // Test connect_to_database - Success with full SSL (CA + client cert/key)
    public function testConnectToDatabaseSuccessFullSSL(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        // All SSL certificates exist
        $mockInstaller->expects($this->exactly(3))
            ->method('fileExists')
            ->willReturn(true);

        // Mock SSL setup with client certificates
        $mockInstaller->expects($this->once())
            ->method('mysqliSslSet')
            ->with(
                $mockMysqli,
                $this->stringContains('mysql-key'),
                $this->stringContains('mysql-cert'),
                $this->stringContains('mysql-ca'),
                null,
                null
            );

        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->with(
                $mockMysqli,
                'localhost',
                'root',
                'password',
                '',
                3306,
                '',
                MYSQLI_CLIENT_SSL
            )
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $result = $mockInstaller->root_database_connection();

        $this->assertTrue($result);
        $this->assertEquals($mockMysqli, $mockInstaller->dbh);
    }

    // Test connect_to_database - Connection failure
    public function testConnectToDatabaseConnectionFailure(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        // Mock connection failure
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->willReturn(false);

        $result = $mockInstaller->root_database_connection();

        $this->assertFalse($result);
        $this->assertStringContainsString('unable to connect to database as root', $mockInstaller->error_message);
    }

    // Test connect_to_database - Exception handling
    public function testConnectToDatabaseExceptionHandling(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $exception = new mysqli_sql_exception('Connection timeout', 2002);

        // Mock connection throwing exception
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->willThrowException($exception);

        $result = $mockInstaller->root_database_connection();

        $this->assertFalse($result);
        $this->assertStringContainsString('unable to connect to database as root', $mockInstaller->error_message);
    }

    // Test connect_to_database - Custom port handling
    public function testConnectToDatabaseCustomPort(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase(['port' => '3307']);
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        // Expect custom port to be used
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->with(
                $mockMysqli,
                'localhost',
                'root',
                'password',
                '',
                3307,  // Custom port
                '',
                0
            )
            ->willReturn(true);

        $mockInstaller->expects($this->once())
            ->method('set_sql_strict')
            ->willReturn(true);

        $result = $mockInstaller->root_database_connection();

        $this->assertTrue($result);
    }

    // Test connect_to_database exception handling through user_database_connection
    // (preserves the original error message from connect_to_database)
    public function testConnectToDatabaseExceptionHandlingViaUser(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $exception = new mysqli_sql_exception('Connection timeout', 2002);

        // Mock connection throwing exception
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->willThrowException($exception);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        // user_database_connection overrides the error message from connect_to_database
        $this->assertStringContainsString("unable to connect to database as user: 'openemr'", $mockInstaller->error_message);
    }

    // Test connect_to_database regular connection failure through user_database_connection
    public function testConnectToDatabaseRegularFailureViaUser(): void
    {
        $mockInstaller = $this->createMockInstallerWithoutConnectToDatabase();
        $mockMysqli = $this->createMock(mysqli::class);

        $mockInstaller->expects($this->once())
            ->method('mysqliInit')
            ->willReturn($mockMysqli);

        $mockInstaller->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        // Mock connection failure
        $mockInstaller->expects($this->once())
            ->method('mysqliRealConnect')
            ->willReturn(false);

        $result = $mockInstaller->user_database_connection();

        $this->assertFalse($result);
        // user_database_connection sets its own error message for failed connections
        $this->assertStringContainsString("unable to connect to database as user: 'openemr'", $mockInstaller->error_message);
    }

    // Test extractFileName method through displayThemesDivs output
    public function testExtractFileNameThroughDisplayThemes(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Mock scanDir to return specific theme files to test extractFileName logic
        $mockInstaller->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..', 'theme_modern_light.png', 'theme_bootstrap_blue_dark.png']);

        ob_start();
        $mockInstaller->displayThemesDivs();
        $output = ob_get_clean();

        // Verify extractFileName correctly parsed the theme names
        $this->assertStringContainsString('Modern Light', $output);  // theme_modern_light.png -> Modern Light
        $this->assertStringContainsString('Bootstrap Blue Dark', $output);  // theme_bootstrap_blue_dark.png -> Bootstrap Blue Dark
        $this->assertStringContainsString('value=\'modern_light\'', $output);
        $this->assertStringContainsString('value=\'bootstrap_blue_dark\'', $output);
    }

    // Test listThemes method with mocked scanDir
    public function testListThemes(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Mock scanDir to return sample directory listing
        $mockInstaller->expects($this->once())
            ->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..', 'theme_modern_light.png', 'theme_classic_dark.png', '.gitignore']);

        $result = $mockInstaller->listThemes();

        // Should filter out . and .. and other dot files
        $expected = ['theme_modern_light.png', 'theme_classic_dark.png'];
        $this->assertEquals($expected, $result);
    }

    // Test listThemes with empty directory
    public function testListThemesEmptyDirectory(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..']);

        $result = $mockInstaller->listThemes();

        $this->assertEmpty($result);
    }

    // Test listThemes with mixed file types
    public function testListThemesMixedFiles(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockInstaller->expects($this->once())
            ->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..', 'theme1.png', 'theme2.jpg', 'readme.txt', '.hidden', 'theme3.gif']);

        $result = $mockInstaller->listThemes();

        // Should include all non-dot files
        $expected = ['theme1.png', 'theme2.jpg', 'readme.txt', 'theme3.gif'];
        $this->assertEquals($expected, $result);
    }

    // Test displayThemesDivs with multiple themes using real listThemes and extractFileName
    public function testDisplayThemesDivsIntegration(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Mock scanDir to provide theme files
        $mockInstaller->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..', 'theme_modern_light.png', 'theme_classic_dark.png']);

        ob_start();
        $mockInstaller->displayThemesDivs();
        $output = ob_get_clean();

        // Verify the output contains expected elements from real extractFileName
        $this->assertStringContainsString('<div class=\'row\'>', $output);
        $this->assertStringContainsString('Modern Light', $output);
        $this->assertStringContainsString('Classic Dark', $output);

        // Verify radio button structure
        $this->assertStringContainsString('name=\'stylesheet\'', $output);
        $this->assertStringContainsString('type=\'radio\'', $output);
        $this->assertStringContainsString('value=\'modern_light\'', $output);
        $this->assertStringContainsString('value=\'classic_dark\'', $output);

        // Verify image paths
        $this->assertStringContainsString('public/images/stylesheets/theme_modern_light.png', $output);
        $this->assertStringContainsString('public/images/stylesheets/theme_classic_dark.png', $output);
    }

    // Test displayThemesDivs with no themes
    public function testDisplayThemesDivsNoThemes(): void
    {
        $mockInstaller = $this->createMockInstaller();

        // Mock scanDir to return empty directory
        $mockInstaller->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn(['.', '..']);

        ob_start();
        $mockInstaller->displayThemesDivs();
        $output = ob_get_clean();

        // With no themes, the loop shouldn't execute, so output should be empty
        $this->assertEmpty($output);
    }

    // Test displayThemesDivs with exactly 6 themes (one complete row)
    public function testDisplayThemesDivsCompleteRow(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockFiles = ['.', '..',
            'theme_1.png', 'theme_2.png', 'theme_3.png',
            'theme_4.png', 'theme_5.png', 'theme_6.png'
        ];

        $mockInstaller->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn($mockFiles);

        ob_start();
        $mockInstaller->displayThemesDivs();
        $output = ob_get_clean();

        // Should have one complete row that starts and ends properly
        $this->assertStringContainsString('<div class=\'row\'>', $output);
        $this->assertStringContainsString('</div>', $output);
        $this->assertStringContainsString('<br />', $output);

        // Should have 6 radio buttons
        $this->assertEquals(6, substr_count($output, 'type=\'radio\''));
    }

    // Test displayThemesDivs with 7 themes (partial second row)
    public function testDisplayThemesDivsPartialRow(): void
    {
        $mockInstaller = $this->createMockInstaller();

        $mockFiles = ['.', '..',
            'theme_1.png', 'theme_2.png', 'theme_3.png',
            'theme_4.png', 'theme_5.png', 'theme_6.png',
            'theme_7.png'
        ];

        $mockInstaller->method('scanDir')
            ->with('public/images/stylesheets/')
            ->willReturn($mockFiles);

        ob_start();
        $mockInstaller->displayThemesDivs();
        $output = ob_get_clean();

        // Should have two row starts (positions 0 and 6)
        $this->assertEquals(2, substr_count($output, '<div class=\'row\'>'));
        // Should have multiple </div> tags (one per theme div + row ending divs)
        $this->assertGreaterThan(0, substr_count($output, '</div>'));
        // Should have 7 radio buttons
        $this->assertEquals(7, substr_count($output, 'type=\'radio\''));
    }

    // Test getCurrentTheme method
    public function testGetCurrentTheme(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockResult = $this->createMock(mysqli_result::class);

        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("SELECT gl_value FROM globals WHERE gl_name LIKE '%css_header%'")
            ->willReturn($mockResult);

        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockResult)
            ->willReturn(['style_light.css']);

        $result = $mockInstaller->getCurrentTheme();

        $this->assertEquals('style_light.css', $result);
    }

    // Test setCurrentTheme method when new_theme is set
    public function testSetCurrentThemeWithNewTheme(): void
    {
        $mockInstaller = $this->createMockInstaller(['new_theme' => 'style_dark.css']);
        $mockResult = $this->createMock(mysqli_result::class);

        // setCurrentTheme calls getCurrentTheme first to get current theme
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use ($mockResult) {
                if (str_contains($sql, 'SELECT')) {
                    return $mockResult;
                } else {
                    return true;
                }
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockResult)
            ->willReturn(['style_light.css']);

        $mockInstaller->expects($this->once())
            ->method('escapeSql')
            ->with('style_dark.css')
            ->willReturn('style_dark.css');

        $result = $mockInstaller->setCurrentTheme();

        $this->assertTrue($result);
    }

    // Test setCurrentTheme method when new_theme is not set (gets current theme)
    public function testSetCurrentThemeWithoutNewTheme(): void
    {
        $mockInstaller = $this->createMockInstaller(['new_theme' => '']);
        $mockResult = $this->createMock(mysqli_result::class);

        // Should call getCurrentTheme when new_theme is empty
        $mockInstaller->expects($this->exactly(2))
            ->method('execute_sql')
            ->willReturnCallback(function ($sql) use ($mockResult) {
                if (str_contains($sql, 'SELECT')) {
                    return $mockResult;
                } else {
                    return true;
                }
            });

        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockResult)
            ->willReturn(['style_light.css']);

        $mockInstaller->expects($this->once())
            ->method('escapeSql')
            ->with('style_light.css')
            ->willReturn('style_light.css');

        $result = $mockInstaller->setCurrentTheme();

        $this->assertTrue($result);
        $this->assertEquals('style_light.css', $mockInstaller->new_theme);
    }

    // Test displaySelectedThemeDiv method
    public function testDisplaySelectedThemeDiv(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockResult = $this->createMock(mysqli_result::class);

        // Mock getCurrentTheme call
        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("SELECT gl_value FROM globals WHERE gl_name LIKE '%css_header%'")
            ->willReturn($mockResult);

        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockResult)
            ->willReturn(['style_modern_light.css']);

        ob_start();
        $mockInstaller->displaySelectedThemeDiv();
        $output = ob_get_clean();

        // Verify output contains expected elements
        $this->assertStringContainsString('<div class="row">', $output);
        $this->assertStringContainsString('Modern Light', $output);  // From extractFileName
        $this->assertStringContainsString('public/images/stylesheets/style_modern_light.png', $output);
        $this->assertStringContainsString('id="current_theme"', $output);
        $this->assertStringContainsString('id="current_theme_title"', $output);
    }

    // Test displayNewThemeDiv method with new_theme set
    public function testDisplayNewThemeDivWithNewTheme(): void
    {
        $mockInstaller = $this->createMockInstaller(['new_theme' => 'style_dark_blue.css']);

        ob_start();
        $mockInstaller->displayNewThemeDiv();
        $output = ob_get_clean();

        // Verify output contains expected elements
        $this->assertStringContainsString('<div class="row">', $output);
        $this->assertStringContainsString('Dark Blue', $output);  // From extractFileName
        $this->assertStringContainsString('public/images/stylesheets/style_dark_blue.png', $output);
        $this->assertStringContainsString('id="current_theme"', $output);
        $this->assertStringContainsString('id="current_theme_title"', $output);
    }

    // Test displayNewThemeDiv method without new_theme (gets current theme)
    public function testDisplayNewThemeDivWithoutNewTheme(): void
    {
        $mockInstaller = $this->createMockInstaller(['new_theme' => '']);
        $mockResult = $this->createMock(mysqli_result::class);

        // Should call getCurrentTheme when new_theme is empty
        $mockInstaller->expects($this->once())
            ->method('execute_sql')
            ->with("SELECT gl_value FROM globals WHERE gl_name LIKE '%css_header%'")
            ->willReturn($mockResult);

        $mockInstaller->expects($this->once())
            ->method('mysqliFetchArray')
            ->with($mockResult)
            ->willReturn(['style_classic_green.css']);

        ob_start();
        $mockInstaller->displayNewThemeDiv();
        $output = ob_get_clean();

        // Verify output contains expected elements from current theme
        $this->assertStringContainsString('<div class="row">', $output);
        $this->assertStringContainsString('Classic Green', $output);  // From extractFileName
        $this->assertStringContainsString('public/images/stylesheets/style_classic_green.png', $output);
        $this->assertEquals('style_classic_green.css', $mockInstaller->new_theme);
    }

    // Test setupHelpModal method
    public function testSetupHelpModal(): void
    {
        $mockInstaller = $this->createMockInstaller();

        ob_start();
        $mockInstaller->setupHelpModal();
        $output = ob_get_clean();

        // Verify modal HTML structure
        $this->assertStringContainsString('<div class="modal fade" id="myModal"', $output);
        $this->assertStringContainsString('class="modal-dialog modal-lg"', $output);
        $this->assertStringContainsString('class="modal-content  oe-modal-content"', $output);
        $this->assertStringContainsString('class="modal-header clearfix"', $output);
        $this->assertStringContainsString('class="modal-body"', $output);
        $this->assertStringContainsString('class="modal-footer"', $output);

        // Verify iframe for help content
        $this->assertStringContainsString('<iframe src="" id="targetiframe"', $output);

        // Verify JavaScript functionality
        $this->assertStringContainsString('<script>', $output);
        $this->assertStringContainsString('#help-href', $output);
        $this->assertStringContainsString('openemr_installation_help.php', $output);
        $this->assertStringContainsString('drag-action', $output);
        $this->assertStringContainsString('resize-action', $output);

        // Verify modal controls
        $this->assertStringContainsString('data-dismiss="modal"', $output);
        $this->assertStringContainsString('Close', $output);
    }

    public function testUpsertCustomGlobal(): void
    {
        $mockInstaller = $this->createMockInstaller();
        $mockResult = $this->createMock(mysqli_result::class);

        $mockInstaller
            ->method('escapeSql')
            ->willReturnArgument(0);

        $mockInstaller
            ->method('execute_sql')
            ->with("REPLACE INTO globals ( gl_name, gl_index, gl_value ) VALUES ( 'test_monkey', '0', 'sure is' )")
            ->willReturn($mockResult);
        $mockInstaller->upsertCustomGlobals(['test_monkey' => ['index' => 0, 'value' => 'sure is']]);
    }
}
