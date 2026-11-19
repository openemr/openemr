<?php

/**
 * Tests openemr:composer-annotations end to end against captured tool output
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\ComposerAnnotationsCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[Group('isolated')]
final class ComposerAnnotationsCommandTest extends TestCase
{
    private const VALIDATE_OUTPUT = <<<'TEXT'
        ./composer.json is valid, but with a few warnings
        # General warnings
        - require.foo/bar : unbound version constraints (*) should be avoided

        TEXT;

    private Filesystem $filesystem;
    private string $scratch;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->scratch = Path::join(sys_get_temp_dir(), uniqid('composer-annotations-', true));
        $this->filesystem->mkdir($this->scratch);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->scratch);
    }

    public function testPrintsAPlainReportWithoutAnnotationsByDefault(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $exit = $tester->execute(['check' => 'validate', 'output-file' => $this->write('validate.txt', self::VALIDATE_OUTPUT)]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertSame(self::VALIDATE_OUTPUT, $tester->getDisplay());
    }

    public function testAnnotatePrintsAWorkflowCommandAfterTheLog(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $tester->execute(['check' => 'validate', 'output-file' => $this->write('validate.txt', self::VALIDATE_OUTPUT), '--annotate' => true]);

        self::assertSame(
            self::VALIDATE_OUTPUT . '::error file=composer.json,title=composer validate::require.foo/bar : unbound version constraints (*) should be avoided' . PHP_EOL,
            $tester->getDisplay(),
        );
    }

    public function testAnnotationsStartOnTheirOwnLineAfterOutputWithNoFinalNewline(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $tester->execute(['check' => 'validate', 'output-file' => $this->write('validate.txt', '- no final newline'), '--annotate' => true]);

        self::assertSame(
            '- no final newline' . PHP_EOL . '::error file=composer.json,title=composer validate::no final newline' . PHP_EOL,
            $tester->getDisplay(),
        );
    }

    public function testSummaryIsAppendedToTheGivenFile(): void
    {
        $summary = $this->write('summary.md', '## Earlier step' . PHP_EOL);
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $tester->execute(['check' => 'validate', 'output-file' => $this->write('validate.txt', self::VALIDATE_OUTPUT), '--summary' => $summary]);

        self::assertStringStartsWith('## Earlier step' . PHP_EOL . '## composer validate --strict failed' . PHP_EOL, $this->filesystem->readFile($summary));
    }

    public function testNothingIsAppendedWhenTheReportHasNoSummary(): void
    {
        $summary = Path::join($this->scratch, 'summary.md');
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $tester->execute(['check' => 'require-checker', 'output-file' => $this->write('crc.json', 'not JSON'), '--summary' => $summary]);

        self::assertFileDoesNotExist($summary);
    }

    public function testAnUnknownCheckIsAUsageError(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown check');
        $tester->execute(['check' => 'unknown', 'output-file' => $this->write('present.txt', '')]);
    }

    public function testAMissingOutputFileIsAUsageError(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot read the output file.');
        $tester->execute(['check' => 'validate', 'output-file' => Path::join($this->scratch, 'missing.txt')]);
    }

    public function testAMissingArgumentIsAUsageError(): void
    {
        $tester = new CommandTester(new ComposerAnnotationsCommand());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');
        $tester->execute(['check' => 'validate']);
    }

    private function write(string $name, string $content): string
    {
        $path = Path::join($this->scratch, $name);
        $this->filesystem->dumpFile($path, $content);
        return $path;
    }
}
