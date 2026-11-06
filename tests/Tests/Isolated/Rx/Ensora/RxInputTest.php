<?php

/**
 * Isolated tests for the value text written into the Ensora RxInput XML.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use OpenEMR\Rx\Ensora\RxInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RxInputTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function valueProvider(): array
    {
        return [
            'plain' => ['Lisinopril 10 mg', 'Lisinopril 10 mg'],
            'carriage return' => ['one' . chr(13) . chr(10) . 'two', 'one' . chr(10) . 'two'],
            'tab' => ['tw' . chr(9) . 'o', 'two'],
            'double quotes' => ['take "as needed"', "take 'as needed'"],
            'markup characters kept' => ['HTN & HLD <2>', 'HTN & HLD <2>'],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('valueProvider')]
    public function testText(string $value, string $expected): void
    {
        $this->assertSame($expected, RxInput::text($value));
    }
}
