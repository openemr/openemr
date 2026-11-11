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
        foreach (glob($this->root . '/*/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->root . '/*') ?: [] as $entry) {
            is_dir($entry) ? rmdir($entry) : unlink($entry);
        }
        rmdir($this->root);
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

    /**
     * @param list<int> $counts
     */
    private function writeBaseline(string $side, string $identifier, array $counts, string $extraCode = ''): void
    {
        $php = "<?php declare(strict_types = 1);\n\n\$ignoreErrors = [];\n";
        foreach ($counts as $i => $count) {
            $php .= "\$ignoreErrors[] = [\n"
                . "    'message' => '#^Message $i\\\\.\$#',\n"
                . "    'count' => $count,\n"
                . "    'path' => __DIR__ . '/../../file$i.php',\n"
                . "];\n";
        }
        $php .= $extraCode;
        $php .= "\nreturn ['parameters' => ['ignoreErrors' => \$ignoreErrors]];\n";
        file_put_contents("{$this->root}/$side/$identifier.php", $php);
    }

    private function runDiff(): string
    {
        $process = new Process([PHP_BINARY, self::SCRIPT, $this->root . '/base', $this->root . '/head']);
        $process->run();
        self::assertSame(0, $process->getExitCode(), 'the script should succeed; stderr: ' . $process->getErrorOutput());
        return $process->getOutput();
    }
}
