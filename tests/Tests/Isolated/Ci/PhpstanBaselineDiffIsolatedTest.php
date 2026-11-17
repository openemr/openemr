<?php

/**
 * Isolated test: .phpstan/baseline-diff.php treats the PR's baseline files as data.
 *
 * The PHPStan Baseline Diff workflow runs this script on a baseline artifact
 * uploaded by a pull request's own PHPStan run, in a privileged workflow_run
 * job, so the PR author controls those files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Ci;

use PHPUnit\Framework\TestCase;
use ShipMonk\PHPStan\Baseline\Handler\PhpBaselineHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class PhpstanBaselineDiffIsolatedTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../../../.phpstan/baseline-diff.php';

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/baseline-diff-test-' . bin2hex(random_bytes(8));
        mkdir($this->root . '/base', recursive: true);
        mkdir($this->root . '/head');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->root);
    }

    public function testHeadBaselineIsNotExecuted(): void
    {
        $marker = $this->root . '/executed';
        $this->writeBaseline('base', 'argument.type', [2]);
        $this->writeBaseline('head', 'argument.type', [2, 3], sprintf("file_put_contents(%s, 'x');\n", var_export($marker, true)));

        $output = $this->runDiff();

        self::assertFileDoesNotExist($marker, 'code in a PR baseline file must not run');
        self::assertStringContainsString('| `argument.type` | 2 | 5 | +3 |', $output, 'counts should still be summed');
    }

    public function testIdentifierThatIsNotAnErrorIdentifierIsIgnored(): void
    {
        $this->writeBaseline('base', 'argument.type', [2]);
        $this->writeBaseline('head', 'argument.type', [2]);
        $this->writeBaseline('head', 'x` | **injected** | `y', [1]);

        $output = $this->runDiff();

        self::assertStringNotContainsString('injected', $output, 'a PR-chosen file name must not reach the PR comment');
    }

    public function testGeneratorFallbackIdentifierIsCounted(): void
    {
        $this->writeBaseline('base', 'missing-identifier', [1]);
        $this->writeBaseline('head', 'missing-identifier', [4]);

        $output = $this->runDiff();

        self::assertStringContainsString('| `missing-identifier` | 1 | 4 | +3 |', $output, 'the generator writes identifier-less errors to missing-identifier.php');
    }

    public function testUnreadableBaselineFails(): void
    {
        $this->writeBaseline('base', 'argument.type', [2]);
        $this->writeBaseline('head', 'argument.type', [2]);
        $file = $this->root . '/head/argument.type.php';
        chmod($file, 0);
        if (is_readable($file)) {
            self::markTestSkipped('This user can read files without read permission (e.g. root).');
        }

        $process = $this->diffProcess();
        $process->run();

        self::assertNotSame(0, $process->getExitCode(), 'an unreadable file must not be counted as 0');
        self::assertStringContainsString('Cannot read baseline file', $process->getOutput() . $process->getErrorOutput(), 'the failure should name the cause');
    }

    /**
     * Writes a baseline file the way `composer phpstan-baseline` does, so the
     * script is tested against the generator's real output format.
     *
     * @param list<int> $counts
     */
    private function writeBaseline(string $side, string $identifier, array $counts, string $extraCode = ''): void
    {
        $errors = [];
        foreach ($counts as $i => $count) {
            $errors[] = ['message' => "#^Message $i\\.$#", 'count' => $count, 'path' => "file$i.php"];
        }
        $php = (new PhpBaselineHandler())->encodeBaseline(null, $errors, '    ');
        $php = str_replace("\nreturn ", "\n{$extraCode}return ", $php);
        file_put_contents("{$this->root}/$side/$identifier.php", $php);
    }

    private function diffProcess(): Process
    {
        return new Process([PHP_BINARY, self::SCRIPT, $this->root . '/base', $this->root . '/head']);
    }

    private function runDiff(): string
    {
        $process = $this->diffProcess();
        $process->run();
        self::assertSame(0, $process->getExitCode(), 'the script should succeed; stderr: ' . $process->getErrorOutput());
        return $process->getOutput();
    }
}
