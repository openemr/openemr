<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Console\Command;

use OpenEMR\Common\Installer\InstallerInterface;
use OpenEMR\Console\Command\InstallCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('isolated')]
class InstallCommandTest extends TestCase
{
    public function testOptionToParamMapping(): void
    {
        $capturedParams = null;

        $installer = $this->createMock(InstallerInterface::class);
        $installer->expects($this->once())
            ->method('setLogger')
            ->with(self::isInstanceOf(LoggerInterface::class));
        $installer->expects($this->once())
            ->method('install')
            ->with(self::callback(function (array $params) use (&$capturedParams): bool {
                $capturedParams = $params;
                return true;
            }))
            ->willReturn(true);

        $command = new InstallCommand($installer);
        $tester = $this->createTester($command);

        $tester->execute([
            '--db-host' => 'mydbhost',
            '--db-port' => '3307',
            '--db-user' => 'myuser',
            '--db-password' => 'mypass',
            '--db-name' => 'mydb',
            '--oe-admin-name' => 'Test Admin',
            '--oe-admin-username' => 'testadmin',
            '--oe-admin-password' => 'adminpass',
        ], ['interactive' => false]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertNotNull($capturedParams);

        // Verify CLI options are mapped to correct param keys
        self::assertSame('mydbhost', $capturedParams['server']);
        self::assertSame(3307, $capturedParams['port']);
        self::assertSame('myuser', $capturedParams['login']);
        self::assertSame('mypass', $capturedParams['pass']);
        self::assertSame('mydb', $capturedParams['dbname']);
        self::assertSame('Test Admin', $capturedParams['iuname']);
        self::assertSame('testadmin', $capturedParams['iuser']);
        self::assertSame('adminpass', $capturedParams['iuserpass']);

        // Verify hardcoded values
        self::assertSame('1', $capturedParams['no_root_db_access'], 'installer must not try to provision the database');
        self::assertSame('default', $capturedParams['site']);
        self::assertArrayNotHasKey('root', $capturedParams, 'no root credentials should be passed');
        self::assertArrayNotHasKey('rootpass', $capturedParams, 'no root credentials should be passed');
    }

    public function testRootDatabaseUserIsRejected(): void
    {
        $installer = $this->createMock(InstallerInterface::class);
        $installer->expects($this->never())->method('install');

        $command = new InstallCommand($installer);
        $tester = $this->createTester($command);

        $tester->execute(['--db-user' => 'root'], ['interactive' => false]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode(), 'installing as root should fail');
        self::assertStringContainsString('root', $tester->getDisplay(), 'error should explain the root rejection');
    }

    public function testInstallFailureReturnsError(): void
    {
        $installer = $this->createMock(InstallerInterface::class);
        $installer->method('install')->willReturn(false);
        $installer->method('getErrorMessage')->willReturn('Database connection failed');

        $command = new InstallCommand($installer);
        $tester = $this->createTester($command);

        $tester->execute([], ['interactive' => false]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Database connection failed', $tester->getDisplay());
    }

    private function createTester(InstallCommand $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('install'));
    }
}
