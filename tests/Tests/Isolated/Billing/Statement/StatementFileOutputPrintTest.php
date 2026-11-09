<?php

/**
 * Printing a statement batch with the site's print command.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementFileOutput;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class StatementFileOutputPrintTest extends TestCase
{
    private string $dir;
    private PrintTestLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new PrintTestLogger();
        $this->dir = sys_get_temp_dir() . '/stmt-print-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    public function testACommandWithOptionsGetsTheBatchFileAsItsLastArgument(): void
    {
        // A batch file name a shell would split, to show it arrives as one argument.
        $batch = $this->dir . '/batch; $(id) *.txt';
        file_put_contents($batch, 'statement 1');
        // A stand-in for lpr that writes down its arguments, one per line.
        $script = $this->dir . '/print.sh';
        file_put_contents($script, "#!/bin/sh\nprintf '%s\\n' \"\$@\" > " . escapeshellarg($this->dir . '/args') . "\n");
        chmod($script, 0700);

        $this->assertTrue($this->fileOutput($script . '  -P HPLaserjet6P   -o cpi=10')->print($batch));
        $this->assertSame("-P\nHPLaserjet6P\n-o\ncpi=10\n" . $batch . "\n", file_get_contents($this->dir . '/args'));
        $this->assertSame([], $this->logger->records);
    }

    public function testACommandThatFailsLogsItsErrorOutput(): void
    {
        $script = $this->dir . '/print.sh';
        file_put_contents($script, "#!/bin/sh\necho 'lpr: The printer or class does not exist.' >&2\nexit 1\n");
        chmod($script, 0700);

        $this->assertFalse($this->fileOutput($script)->print($this->dir . '/batch'));
        $this->assertSame([[
            'Statements not printed: the print command failed',
            ['command' => $script, 'exitCode' => 1, 'stderr' => 'lpr: The printer or class does not exist.'],
        ]], $this->logger->records);
    }

    public function testAMissingProgramIsLoggedNotThrown(): void
    {
        $this->assertFalse($this->fileOutput($this->dir . '/no-such-lpr -P x')->print($this->dir . '/batch'));
        $this->assertCount(1, $this->logger->records);
        $this->assertStringStartsWith('Statements not printed: ', $this->logger->records[0][0]);
    }

    public function testNoPrintCommand(): void
    {
        $this->assertFalse($this->fileOutput('  ')->print($this->dir . '/batch'));
        $this->assertSame(
            [["Statements not printed: no print command is set (OPENEMR_PRINT_COMMAND in the site's config.php)", []]],
            $this->logger->records
        );
    }

    private function fileOutput(string $printCommand): StatementFileOutput
    {
        return new StatementFileOutput('', '', false, false, $printCommand, $this->logger);
    }
}

final class PrintTestLogger extends AbstractLogger
{
    /** @var list<array{string, array<mixed>}> */
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [(string) $message, $context];
    }
}
