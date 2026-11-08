<?php

/**
 * Isolated tests for the Provenance of a patient app write, built from its review queue row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteProvenance;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;

class PatientWriteProvenanceIsolatedTest extends TestCase
{
    use PatientWriteQueueFixture;

    private const APPROVER = '5d6e7f80-91a2-4b3c-8d4e-5f60718293a4';

    protected function setUp(): void
    {
        $this->setUpQueue();
    }

    /**
     * @param array<string, mixed> $who
     * @return array{type: array{coding: list<array{system: string, code: string, display: string}>}, who: array<string, mixed>}
     */
    private static function agent(string $system, string $code, string $display, array $who): array
    {
        return ['type' => ['coding' => [['system' => $system, 'code' => $code, 'display' => $display]]], 'who' => $who];
    }

    public function testAHeldWriteNamesThePatientAsAuthorAndTheAppAsTransmitter(): void
    {
        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign());

        $provenance = PatientWriteProvenance::of($request, self::PATIENT, 'Home Scale App', null);

        $this->assertSame([
            'resourceType' => 'Provenance',
            'id' => PatientWriteProvenance::ID_PREFIX . $request->uuid,
            'target' => [['reference' => 'Observation/' . $request->uuid]],
            'recorded' => $request->createdAt->format(DATE_ATOM),
            'agent' => [
                self::agent(PatientWriteProvenance::PARTICIPANT_TYPE_SYSTEM, 'author', 'Author', ['reference' => 'Patient/' . self::PATIENT]),
                self::agent(
                    PatientWriteProvenance::US_CORE_PARTICIPANT_TYPE_SYSTEM,
                    'transmitter',
                    'Transmitter',
                    ['identifier' => ['value' => self::CLIENT], 'display' => 'Home Scale App']
                ),
            ],
        ], $provenance, 'no verifier while it is held');
    }

    public function testAnApprovedWriteNamesTheStaffUserAsVerifier(): void
    {
        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign());

        $agents = PatientWriteProvenance::of($request, self::PATIENT, null, self::APPROVER)['agent'];

        $this->assertCount(3, $agents);
        $this->assertSame(['identifier' => ['value' => self::CLIENT]], $agents[1]['who'], 'an app no longer registered is named by its client_id');
        $this->assertSame(
            self::agent(PatientWriteProvenance::PARTICIPANT_TYPE_SYSTEM, 'verifier', 'Verifier', ['reference' => 'Practitioner/' . self::APPROVER]),
            $agents[2]
        );
    }

    /**
     * A caregiver who answered a questionnaire is its author.
     */
    public function testACaregiversQuestionnaireResponseNamesTheCaregiverAsAuthor(): void
    {
        $answered = ['source' => ['reference' => 'RelatedPerson/3f1b2c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d']] + $this->resourceOf(PatientWritableType::QuestionnaireResponse);
        $request = $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $answered);

        $agents = PatientWriteProvenance::of($request, self::PATIENT, null, null)['agent'];

        $this->assertSame(['reference' => 'RelatedPerson/3f1b2c4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d'], $agents[0]['who']);
    }

    public function testOnlyAPatientAppWriteHasThisProvenance(): void
    {
        $profile = $this->queue->submit(
            new ReviewSubmission(self::PID, ReviewType::Profile, 'Patient request changes to demographics.', ['fname' => 'Ann']),
            ReviewActor::patient(self::PID, 'ann7')
        );

        $this->expectException(\DomainException::class);
        PatientWriteProvenance::of($profile, self::PATIENT, null, null);
    }
}
