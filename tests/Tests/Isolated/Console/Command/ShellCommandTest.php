<?php

/**
 * @package    OpenEMR
 * @link       https://www.open-emr.org
 * @author     Eric Stern <erics@opencoreemr.com>
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @copyright  Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Console\Command;

use Doctrine\ORM\EntityManagerInterface;
use OpenEMR\Console\Command\ShellCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[Group('isolated')]
#[RequiresPhpExtension('posix')]
final class ShellCommandTest extends TestCase
{
    public function testInvokeStartsShellAndExits(): void
    {
        if (posix_isatty(STDIN)) {
            self::markTestSkipped('PsySH reads from STDIN; skipping in interactive mode. Run tests with `< /dev/null` at the end to test non-interactively.');
        }

        $em = $this->createStub(EntityManagerInterface::class);
        $command = new ShellCommand($em);

        $input = new ArrayInput([]);
        $output = new NullOutput();

        $result = $command($input, $output);

        self::assertSame(0, $result, 'Shell should exit cleanly');
    }
}
