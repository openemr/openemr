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

}
