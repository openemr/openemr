<?php

/**
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\PatientFinder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientFinderTest extends TestCase
{
    #[DataProvider('addPatientVisibilityProvider')]
    public function testAddPatientVisibility(bool $patientSelectionOnly, bool $aclAllowsAdd, bool $expected): void
    {
        self::assertSame($expected, PatientFinder::canAddPatient($patientSelectionOnly, $aclAllowsAdd));
    }

    /**
     * @return array<string, array{bool, bool, bool}>
     */
    public static function addPatientVisibilityProvider(): array
    {
        return [
            'normal finder with add permission' => [false, true, true],
            'patient-selection-only finder' => [true, true, false],
            'no add permission' => [false, false, false],
            'both restrictions' => [true, false, false],
        ];
    }
}
