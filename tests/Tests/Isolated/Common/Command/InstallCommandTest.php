<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\InstallCommand;
use OpenEMR\Common\Installer\InstallerInterface;
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
            ->with($this->isInstanceOf(LoggerInterface::class));
        $installer->expects($this->once())
            ->method('install')
            ->with($this->callback(function (array $params) use (&$capturedParams): bool {
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
            '--db-root-user' => 'myroot',
            '--db-root-password' => 'myrootpass',
            '--oe-admin-name' => 'Test Admin',
            '--oe-admin-username' => 'testadmin',
            '--oe-admin-password' => 'adminpass',
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertNotNull($capturedParams);

        // Verify CLI options are mapped to correct param keys
        $this->assertSame('mydbhost', $capturedParams['server']);
        $this->assertSame(3307, $capturedParams['port']);
        $this->assertSame('myuser', $capturedParams['login']);
        $this->assertSame('mypass', $capturedParams['pass']);
        $this->assertSame('mydb', $capturedParams['dbname']);
        $this->assertSame('myroot', $capturedParams['root']);
        $this->assertSame('myrootpass', $capturedParams['rootpass']);
        $this->assertSame('Test Admin', $capturedParams['iuname']);
        $this->assertSame('testadmin', $capturedParams['iuser']);
        $this->assertSame('adminpass', $capturedParams['iuserpass']);

        // Verify hardcoded values
        $this->assertSame('%', $capturedParams['loginhost']);
        $this->assertSame('default', $capturedParams['site']);
    }

    public function testInstallFailureReturnsError(): void
    {
        $installer = $this->createMock(InstallerInterface::class);
        $installer->method('install')->willReturn(false);
        $installer->method('getErrorMessage')->willReturn('Database connection failed');

        $command = new InstallCommand($installer);
        $tester = $this->createTester($command);

        $tester->execute([], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Database connection failed', $tester->getDisplay());
    }

    private function createTester(InstallCommand $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('install'));
    }
}
