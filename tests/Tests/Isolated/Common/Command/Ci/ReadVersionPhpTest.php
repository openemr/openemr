<?php

/**
 * Isolated tests for .github/scripts/read-version-php.php.
 *
 * The script runs inside the acceptance-docker workflow's openemr
 * container to extract $v_major/$v_minor/$v_patch from an installed
 * version.php. These tests exercise the parse/validate/emit logic
 * end-to-end via subprocess against synthetic fixture files, matching
 * the pattern from PortalPatientAccessGuardExitTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\Ci;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
#[Group('ci-scripts')]
final class ReadVersionPhpTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../../../../../.github/scripts/read-version-php.php';

    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = sys_get_temp_dir() . '/read-version-php-' . bin2hex(random_bytes(6));
        mkdir($this->fixtureDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->fixtureDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->fixtureDir);
    }

    /**
     * Write a fixture version.php with the supplied variable assignments
     * and return the path. Each assignment is written verbatim so tests
     * can drive both string-literal and native-int shapes.
     */
    private function writeFixture(string $body): string
    {
        $path = $this->fixtureDir . '/version.php';
        file_put_contents($path, "<?php\n" . $body);
        return $path;
    }

    /**
     * @param list<string> $args
     */
    private function runScript(array $args = []): Process
    {
        $process = new Process(array_merge([PHP_BINARY, self::SCRIPT], $args));
        $process->run();
        return $process;
    }

    // ==== Happy paths ====

    public function testStringLiteralDigitsProducesXYZ(): void
    {
        // Matches the actual repo shape: $v_major = '8'; etc.
        $fixture = $this->writeFixture(
            "\$v_major = '8';\n\$v_minor = '5';\n\$v_patch = '0';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame("8.5.0\n", $process->getOutput());
        self::assertSame('', $process->getErrorOutput());
    }

    public function testNativeIntsProduceXYZ(): void
    {
        // Defensive: version.php could theoretically use native ints
        // in the future; script accepts both forms.
        $fixture = $this->writeFixture(
            "\$v_major = 8;\n\$v_minor = 5;\n\$v_patch = 0;\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame("8.5.0\n", $process->getOutput());
    }

    public function testMixedStringAndIntProducesXYZ(): void
    {
        $fixture = $this->writeFixture(
            "\$v_major = '8';\n\$v_minor = 5;\n\$v_patch = '0';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame("8.5.0\n", $process->getOutput());
    }

    public function testMultiDigitValuesAreAccepted(): void
    {
        // A future major.minor.patch might exceed single digit; ctype_
        // digit accepts arbitrary-length digit-only strings.
        $fixture = $this->writeFixture(
            "\$v_major = '12';\n\$v_minor = '30';\n\$v_patch = '45';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame("12.30.45\n", $process->getOutput());
    }

    // ==== Reject paths ====

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidValueProvider(): array
    {
        return [
            'non-digit string' => ["\$v_major = '8beta';\n\$v_minor = '5';\n\$v_patch = '0';\n"],
            'float' => ["\$v_major = 8.0;\n\$v_minor = 5;\n\$v_patch = 0;\n"],
            'boolean' => ["\$v_major = true;\n\$v_minor = 5;\n\$v_patch = 0;\n"],
            'null' => ["\$v_major = null;\n\$v_minor = 5;\n\$v_patch = 0;\n"],
            'array' => ["\$v_major = ['8'];\n\$v_minor = 5;\n\$v_patch = 0;\n"],
            'empty string' => ["\$v_major = '';\n\$v_minor = 5;\n\$v_patch = 0;\n"],
            'string with leading zero and dash' => ["\$v_major = '8-dev';\n\$v_minor = 5;\n\$v_patch = 0;\n"],
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidValueRejected(string $body): void
    {
        $fixture = $this->writeFixture($body);

        $process = $this->runScript([$fixture]);

        self::assertSame(1, $process->getExitCode(), 'expected failure exit, got: ' . $process->getOutput());
        self::assertStringContainsString(
            'must be int or digit-only string',
            $process->getErrorOutput(),
        );
        self::assertSame('', $process->getOutput());
    }

    public function testMissingVMajorRejected(): void
    {
        // version.php stripped down to just $v_minor/$v_patch -- $v_
        // major stays undefined, script's ?? null yields null which
        // fails is_int || ctype_digit.
        $fixture = $this->writeFixture(
            "\$v_minor = '5';\n\$v_patch = '0';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('v_major', $process->getErrorOutput());
        self::assertStringContainsString('must be int or digit-only string', $process->getErrorOutput());
    }

    public function testMissingVPatchRejected(): void
    {
        $fixture = $this->writeFixture(
            "\$v_major = '8';\n\$v_minor = '5';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('v_patch', $process->getErrorOutput());
    }

    // ==== Path handling ====

    public function testUnreadablePathExitsWithMessage(): void
    {
        $process = $this->runScript(['/nonexistent/path/to/version.php']);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString(
            'cannot read',
            $process->getErrorOutput(),
        );
        self::assertStringContainsString('/nonexistent/path/to/version.php', $process->getErrorOutput());
    }

    // ==== Variable-scope isolation ====

    public function testFixtureCannotClobberScriptInternals(): void
    {
        // Fixture assigns to $path and $argv (the script's own vars).
        // Script's require runs inside a closure so scope isolation
        // must prevent the assignment from breaking the flow that
        // follows. Result: still emits the version cleanly.
        $fixture = $this->writeFixture(
            "\$path = '/malicious/override';\n\$argv = ['override'];\n"
            . "\$v_major = '8';\n\$v_minor = '5';\n\$v_patch = '0';\n",
        );

        $process = $this->runScript([$fixture]);

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame("8.5.0\n", $process->getOutput());
    }
}
