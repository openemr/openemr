<?php

/**
 * Isolated test: fatal-category PHPStan baseline entry caps.
 *
 * Enforces that baseline files for error identifiers representing code that
 * cannot run never grow. `.phpstan/fatal-baseline-caps.php` holds two kinds
 * of caps:
 *
 *   - `all` — cap applies to every `$ignoreErrors[] = [` entry in the file.
 *   - `confidentNonObject` — cap applies only to entries whose reported
 *     type narrows to a definitely-non-object (null / false / true / scalar
 *     / array). PHPStan also fires `*.nonObject` on `mixed` and class-union
 *     types, which aren't certain crashes; those are excluded.
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

    /**
     * Primitive type names PHPStan reports that definitely aren't objects.
     *
     * `mixed`, `object`, `iterable`, and any class/interface name are
     * excluded — they don't prove the value is non-object at runtime.
     */
    private const NON_OBJECT_ATOMS = [
        'null', 'false', 'true',
        'int', 'string', 'bool', 'float', 'array', 'resource',
        'void', 'never', 'scalar',
    ];

    private const NON_OBJECT_PREFIXES = [
        'array<', 'list<', 'non-empty-array', 'non-empty-list',
        'int<', 'non-empty-string', 'numeric-string', 'literal-string',
        'lowercase-string', 'uppercase-string',
        'positive-int', 'negative-int', 'non-negative-int', 'non-positive-int',
        'class-string', 'interface-string', 'enum-string', 'trait-string',
        'callable-string',
    ];

    #[DataProvider('wholeFileCapsProvider')]
    public function testWholeFileEntryCountMatchesCap(string $filename, int $cap): void
    {
        $count = $this->countEntries($filename);
        $this->assertCapMatches($filename, $count, $cap, 'entries');
    }

    #[DataProvider('confidentNonObjectCapsProvider')]
    public function testConfidentNonObjectEntryCountMatchesCap(string $filename, int $cap): void
    {
        $count = $this->countConfidentNonObjectEntries($filename);
        $this->assertCapMatches(
            $filename,
            $count,
            $cap,
            'entries whose reported type is definitely non-object'
        );
    }

    private function assertCapMatches(string $filename, int $count, int $cap, string $what): void
    {
        if ($count > $cap) {
            $this->fail(sprintf(
                "%s has %d %s, exceeding cap of %d.\n"
                    . "This category represents code that crashes at load or call time.\n"
                    . "Do not raise the cap — fix the underlying code.\n"
                    . 'See openemr/openemr#11792.',
                $filename,
                $count,
                $what,
                $cap
            ));
        }
        $this->assertSame(
            $cap,
            $count,
            sprintf(
                "%s has %d %s but cap is %d.\n"
                    . 'Lower the entry in `.phpstan/fatal-baseline-caps.php` to match.',
                $filename,
                $count,
                $what,
                $cap
            )
        );
    }

    private function countEntries(string $filename): int
    {
        $path = self::BASELINE_DIR . '/' . $filename;
        if (!file_exists($path)) {
            return 0;
        }
        $matches = preg_match_all(
            '/\$ignoreErrors\[\] = \[/',
            (string) file_get_contents($path)
        );
        // preg_match_all returns false only on a bad pattern; ours is a fixed literal.
        return is_int($matches) ? $matches : 0;
    }

    private function countConfidentNonObjectEntries(string $filename): int
    {
        $path = self::BASELINE_DIR . '/' . $filename;
        if (!file_exists($path)) {
            return 0;
        }
        /** @var list<array{message: string, count: int, path: string}> $ignoreErrors */
        $ignoreErrors = [];
        require $path;
        $count = 0;
        foreach ($ignoreErrors as $entry) {
            if (self::isConfidentlyNonObjectMessage((string) $entry['message'])) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Extract the reported type from a PHPStan nonObject message and decide
     * whether every union member is a non-object primitive. Returns false
     * when the message can't be parsed or any union member could be an
     * object (class name, `mixed`, `object`, `iterable`).
     */
    private static function isConfidentlyNonObjectMessage(string $pattern): bool
    {
        // Strip PHPStan's `#^...$#` regex anchors and unescape.
        $inner = $pattern;
        if (str_starts_with($inner, '#^')) {
            $inner = substr($inner, 2);
        }
        if (str_ends_with($inner, '$#')) {
            $inner = substr($inner, 0, -2);
        }
        $inner = str_replace(
            ['\\\\', '\\|', '\\.', '\\(', '\\)', '\\$', '\\-'],
            ['\\', '|', '.', '(', ')', '$', '-'],
            $inner
        );

        if (preg_match('/^Cannot clone (?:non-object variable \$\w+ of type )?(.+)\.$/', $inner, $m)) {
            $type = $m[1];
        } elseif (preg_match('/ on (.+)\.$/', $inner, $m)) {
            $type = $m[1];
        } else {
            return false;
        }

        foreach (explode('|', $type) as $part) {
            if (!self::isNonObjectType(trim($part))) {
                return false;
            }
        }
        return true;
    }

    private static function isNonObjectType(string $type): bool
    {
        if ($type === '') {
            return false;
        }
        if (in_array($type, ['mixed', 'object', 'iterable'], true)) {
            return false;
        }
        if (in_array($type, self::NON_OBJECT_ATOMS, true)) {
            return true;
        }
        foreach (self::NON_OBJECT_PREFIXES as $prefix) {
            if (str_starts_with($type, $prefix)) {
                return true;
            }
        }
        // Anything starting uppercase or with a namespace separator is a class/interface.
        return false;
    }

    /**
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function wholeFileCapsProvider(): array
    {
        return self::buildProviderCases('all');
    }

    /**
     * @return array<string, array{string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function confidentNonObjectCapsProvider(): array
    {
        return self::buildProviderCases('confidentNonObject');
    }

    /**
     * @return array<string, array{string, int}>
     */
    private static function buildProviderCases(string $section): array
    {
        /** @var array{all: array<string, int>, confidentNonObject: array<string, int>} $caps */
        $caps = require self::CAPS_FILE;
        $cases = [];
        foreach ($caps[$section] as $filename => $cap) {
            $cases[$filename] = [$filename, $cap];
        }
        return $cases;
    }
}
