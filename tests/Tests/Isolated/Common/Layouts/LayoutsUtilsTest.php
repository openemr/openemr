<?php

/**
 * Isolated tests for LayoutsUtils::isOption()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Layouts;

use OpenEMR\Common\Layouts\LayoutsUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LayoutsUtilsTest extends TestCase
{
    #[DataProvider('isOptionProvider')]
    public function testIsOption(?string $options, string $test, bool $expected): void
    {
        self::assertSame($expected, LayoutsUtils::isOption($options, $test));
    }

    /**
     * @return array<string, array{?string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function isOptionProvider(): array
    {
        return [
            'JSON list, present'              => ['["G","P","T"]', 'P', true],
            'JSON list, absent'               => ['["G","P","T"]', 'E', false],
            'JSON single modifier'            => ['["RO"]', 'RO', true],
            'JSON multi-letter is not a letter' => ['["EP"]', 'E', false],
            'JSON non-string entries ignored' => ['[2,"T"]', '2', false],
            'legacy letters'                  => ['GPT', 'P', true],
            'legacy letters, trimmed'         => [' GP ', 'P', true],
            'legacy digits with a letter'     => ['2E', '2', true],
            'legacy with a leading zero'      => ['012', '1', true],
            // These legacy strings are valid JSON numbers, so they have never matched.
            'legacy all-digit string'         => ['2', '2', false],
            'legacy "0"'                      => ['0', '0', false],
            'legacy JSON exponent number'     => ['2E0', 'E', false],
            'comma but not JSON'              => ['G,P', 'P', false],
            'non-ASCII legacy string'         => ['é0', '0', false],
            'JSON scalar'                     => ['"T"', 'T', false],
            'string null'                     => ['null', 'n', false],
            'empty string'                    => ['', 'E', false],
            'null'                            => [null, 'E', false],
        ];
    }
}
