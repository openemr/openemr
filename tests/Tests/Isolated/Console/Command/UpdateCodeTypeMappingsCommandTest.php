<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Console\Command;

use OpenEMR\Console\Command\UpdateCodeTypeMappingsCommand;
use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('isolated')]
class UpdateCodeTypeMappingsCommandTest extends TestCase
{
    public function testCommandCallsUpdater(): void
    {
        $updater = $this->createMock(CodeTypeMappingUpdater::class);
        $updater->expects($this->once())
            ->method('updateActivatedMappings');

        $command = new UpdateCodeTypeMappingsCommand($updater);
        $tester = $this->createTester($command);

        $tester->execute([]);

        self::assertSame(
            Command::SUCCESS,
            $tester->getStatusCode(),
            'Command should return success',
        );
    }

    private function createTester(UpdateCodeTypeMappingsCommand $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('codes:update-mappings'));
    }
}
