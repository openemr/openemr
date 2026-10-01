<?php

/**
 * Regression tests for the actual local HL7 placer-order identifier parser.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Orders;

use OpenEMR\Common\Orders\Hl7PlacerOrderId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Hl7PlacerOrderIdTest extends TestCase
{
    #[DataProvider('identifiers')]
    public function testLocalOrderIdentifier(string $value, string $delimiter, string $facility, int $expected): void
    {
        self::assertSame($expected, Hl7PlacerOrderId::parse($value, $delimiter, $facility));
    }

    /** @return array<string, array{string, string, string, int}> */
    public static function identifiers(): array
    {
        return [
            'plain number' => ['175', '^', '', 175],
            'zero padded number' => ['0175', '^', '', 175],
            'configured numeric facility' => ['11545596-0175', '^', '11545596', 175],
            'configured textual facility' => ['ACME-0175', '^', 'ACME', 175],
            'facility containing dashes' => ['ACME-WEST-0175', '^', 'ACME-WEST', 175],
            'authority dash is not the order number' => ['0175^OTHER-0042', '^', '', 175],
            'compound identifier before authority' => ['ACME-0175^OTHER-0042', '^', 'ACME', 175],
            'custom component delimiter' => ['ACME-0175#OTHER-0042', '#', 'ACME', 175],
            'leading zeros do not overflow' => [str_repeat('0', 40) . '175', '^', '', 175],
            'native maximum' => [(string) PHP_INT_MAX, '^', '', PHP_INT_MAX],
            'unknown local order is resolved by caller' => ['999', '^', '', 999],
            'empty identifier' => ['', '^', '', 0],
            'empty entity before authority' => ['^OTHER-0042', '^', '', 0],
            'zero' => ['0', '^', '', 0],
            'zero padded zero' => ['0000', '^', '', 0],
            'negative sign' => ['-175', '^', '', 0],
            'positive sign' => ['+175', '^', '', 0],
            'scientific notation' => ['1.75e2', '^', '', 0],
            'trailing characters' => ['175junk', '^', '', 0],
            'surrounding whitespace' => [' 175 ', '^', '', 0],
            'unknown facility' => ['OTHER-0175', '^', 'ACME', 0],
            'compound without configuration' => ['ACME-0175', '^', '', 0],
            'compound zero' => ['ACME-0000', '^', 'ACME', 0],
            'compound positive sign' => ['ACME-+175', '^', 'ACME', 0],
            'compound negative sign' => ['ACME--175', '^', 'ACME', 0],
            'compound trailing characters' => ['ACME-175junk', '^', 'ACME', 0],
            'extra suffix component' => ['ACME-0175-0042', '^', 'ACME', 0],
            'empty compound suffix' => ['ACME-', '^', 'ACME', 0],
            'overflow' => [(string) PHP_INT_MAX . '0', '^', '', 0],
            'empty delimiter' => ['175', '', '', 0],
            'invalid multi-character delimiter' => ['175', '^^', '', 0],
        ];
    }
}
