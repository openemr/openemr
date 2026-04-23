<?php

/**
 * Isolated test: fatal-category PHPStan baseline entry caps.
 *
 * Enforces that baseline files for error identifiers representing code that
 * cannot run — missing classes, methods, functions, constants, includes,
 * missing return values, undefined variables — never grow. Each file has a
 * committed cap in `.phpstan/fatal-baseline-caps.php`; this test asserts the
 * actual entry count never exceeds its cap.
 *
 * See openemr/openemr#11792 for context and the plan to drive caps to zero.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FatalBaselineCapsIsolatedTest extends TestCase
{
    private const REPO_ROOT = __DIR__ . '/../../../..';
    private const CAPS_FILE = self::REPO_ROOT . '/.phpstan/fatal-baseline-caps.php';
    private const BASELINE_DIR = self::REPO_ROOT . '/.phpstan/baseline';

    #[DataProvider('capsProvider')]
    public function testEntryCountMatchesCap(string $filename, int $cap): void
    {
        $path = self::BASELINE_DIR . '/' . $filename;
        $count = file_exists($path)
            ? preg_match_all('/\$ignoreErrors\[\] = \[/', (string) file_get_contents($path))
            : 0;

        if ($count > $cap) {
            $this->fail(sprintf(
                "%s has %d entries, exceeding cap of %d.\n"
                    . "These categories represent code that cannot run at load/call time.\n"
                    . "Do not raise the cap — fix the underlying code. See openemr/openemr#11792.",
                $filename,
                $count,
                $cap
            ));
        }

        // Caps must also track actual counts so a fix forces a decrement in the same PR.
        $this->assertSame(
            $cap,
            $count,
            sprintf(
                "%s has %d entries but cap is %d.\n"
                    . 'Caps must match actual counts — lower the entry in '
                    . '`.phpstan/fatal-baseline-caps.php`.',
                $filename,
                $count,
                $cap
            )
        );
    }

    /**
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function capsProvider(): array
    {
        /** @var array<string, int> $caps */
        $caps = require self::CAPS_FILE;
        $cases = [];
        foreach ($caps as $filename => $cap) {
            $cases[$filename] = [$filename, $cap];
        }
        return $cases;
    }
}
