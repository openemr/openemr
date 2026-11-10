<?php

/**
 * A write's patient compartment check finds the patient in Task.for, as it does
 * in subject or patient elsewhere, so a token bound to a patient is held to that
 * patient on a Task write instead of being refused for naming none.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\FHIR;

use OpenEMR\RestControllers\FHIR\FhirGenericRestController;
use PHPUnit\Framework\TestCase;

class FhirGenericRestControllerTaskCompartmentTest extends TestCase
{
    private const PATIENT = '95e8d830-3068-48cf-930a-2fefb18c2bcf';

    /** @param array<string, mixed> $json */
    private static function patientOf(array $json): ?string
    {
        $method = new \ReflectionMethod(FhirGenericRestController::class, 'extractPatientUuidFromFhirJson');
        $uuid = $method->invoke(null, $json);
        return is_string($uuid) ? $uuid : null;
    }

    public function testATasksPatientIsItsForElement(): void
    {
        $this->assertSame(self::PATIENT, self::patientOf(['resourceType' => 'Task', 'for' => ['reference' => 'Patient/' . self::PATIENT]]));
    }

    public function testAForThatIsNotAPatientNamesNoPatient(): void
    {
        $this->assertNull(self::patientOf(['resourceType' => 'Task', 'for' => ['reference' => 'Group/' . self::PATIENT]]));
        $this->assertNull(self::patientOf(['resourceType' => 'Task']));
    }

    public function testSubjectStillComesFirst(): void
    {
        $this->assertSame(self::PATIENT, self::patientOf(['subject' => ['reference' => 'Patient/' . self::PATIENT]]));
    }
}
