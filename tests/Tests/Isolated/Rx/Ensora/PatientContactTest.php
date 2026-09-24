<?php

/**
 * Isolated tests for the patient phone numbers sent to Ensora on eRx launch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use OpenEMR\Rx\Ensora\PatientContact;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientContactTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patientProvider(): array
    {
        return [
            'home only' => [['phone_home' => '555-111-2222', 'phone_cell' => ''], '5551112222', null],
            'cell only' => [['phone_home' => '', 'phone_cell' => '555-333-4444'], null, '5553334444'],
            'both' => [['phone_home' => '555-111-2222', 'phone_cell' => '555-333-4444'], '5551112222', '5553334444'],
            'neither' => [['phone_home' => '', 'phone_cell' => ''], null, null],
            'columns missing' => [['pid' => 1], null, null],
            'null columns' => [['phone_home' => null, 'phone_cell' => null], null, null],
            'whitespace only' => [['phone_home' => ' ', 'phone_cell' => '-'], null, null],
            'no patient row' => [false, null, null],
        ];
    }

    #[DataProvider('patientProvider')]
    public function testEachNumberGoesInItsOwnField(mixed $patient, ?string $home, ?string $cell): void
    {
        $contact = PatientContact::fromPatientRow($patient);

        $this->assertSame($home, $contact->homeTelephone);
        $this->assertSame($cell, $contact->cellularTelephone);
    }
}
