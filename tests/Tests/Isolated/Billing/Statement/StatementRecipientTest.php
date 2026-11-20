<?php

/**
 * Isolated tests for who and where a patient statement is addressed to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementRecipient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatementRecipientTest extends TestCase
{
    /**
     * @param array<string, ?string> $guardian
     * @param list<string> $expected
     */
    #[DataProvider('recipientProvider')]
    public function testLines(array $guardian, array $expected): void
    {
        $this->assertSame($expected, StatementRecipient::lines([...self::patient(), ...$guardian]));
    }

    /**
     * @return array<string, array{array<string, ?string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function recipientProvider(): array
    {
        $patientAddress = ['1 Main St', 'Apt 2', 'Randolph, VT 05060'];

        return [
            'no guardian: the patient' => [[], ['Ann Patient', ...$patientAddress]],
            'guardian with an address: the guardian there' => [
                ['guardiansname' => 'Pat Parent', 'guardianaddress' => '9 Elm St', 'guardiancity' => 'Barre', 'guardianstate' => 'VT', 'guardianpostalcode' => '05641'],
                ['Pat Parent', '9 Elm St', 'Barre, VT 05641'],
            ],
            'guardian without an address: the guardian at the patient\'s' => [
                ['guardiansname' => 'Pat Parent', 'guardianaddress' => '', 'guardiancity' => null],
                ['Pat Parent', ...$patientAddress],
            ],
            'guardian city but no street: the guardian at the patient\'s' => [
                ['guardiansname' => 'Pat Parent', 'guardianaddress' => '', 'guardiancity' => 'Barre', 'guardianstate' => 'VT', 'guardianpostalcode' => '05641'],
                ['Pat Parent', ...$patientAddress],
            ],
            'guardian street but no city: the guardian at the patient\'s' => [
                ['guardiansname' => 'Pat Parent', 'guardianaddress' => '9 Elm St', 'guardiancity' => ''],
                ['Pat Parent', ...$patientAddress],
            ],
            'guardian name blank: the patient, whatever the guardian address' => [
                ['guardiansname' => '  ', 'guardianaddress' => '9 Elm St', 'guardiancity' => 'Barre'],
                ['Ann Patient', ...$patientAddress],
            ],
        ];
    }

    public function testBlankStreetLinesAreLeftOut(): void
    {
        $this->assertSame(
            ['Ann Patient', 'Randolph, VT 05060'],
            StatementRecipient::lines([...self::patient(), 'street' => '', 'street_line_2' => null])
        );
    }

    /**
     * @return array<string, string>
     */
    private static function patient(): array
    {
        return [
            'fname' => 'Ann',
            'lname' => 'Patient',
            'street' => '1 Main St',
            'street_line_2' => 'Apt 2',
            'city' => 'Randolph',
            'state' => 'VT',
            'postal_code' => '05060',
        ];
    }
}
