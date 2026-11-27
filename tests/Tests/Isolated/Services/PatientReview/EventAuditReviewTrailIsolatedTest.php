<?php

/**
 * Isolated tests for the audit log entries the review queue writes: reads, changes and
 * refusals of patient app writes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use DateTimeImmutable;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Services\PatientReview\EventAuditReviewTrail;
use OpenEMR\Services\PatientReview\FhirResourceType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;

class EventAuditReviewTrailIsolatedTest extends TestCase
{
    private static function appWrite(
        int $id,
        ReviewStatus $status = ReviewStatus::Pending,
        string $resourceType = 'Observation',
        string $clientId = 'client-abc',
    ): ReviewRequest
    {
        $at = new DateTimeImmutable('2026-11-07 09:30:00');

        return new ReviewRequest(
            id: $id,
            uuid: '9a3c1f2e-5b6d-4e7f-8a9b-0c1d2e3f4a5b',
            pid: 7,
            type: ReviewType::FhirWrite,
            resourceType: new FhirResourceType($resourceType),
            source: ReviewSource::Api,
            clientId: $clientId,
            status: $status,
            summary: 'Patient app vital sign',
            payload: [],
            payloadFormat: PayloadFormat::Json,
            targetTable: null,
            targetId: null,
            createdAt: $at,
            updatedAt: $at,
            reviewedBy: null,
            reviewedAt: null,
            reviewNote: null,
        );
    }

    /**
     * @return list<mixed> the arguments recordLogItem() was called with, once
     */
    private function recordAccess(ReviewRequest $first, ReviewRequest ...$more): array
    {
        $arguments = [];
        $logger = $this->createMock(EventAuditLogger::class);
        $logger->expects($this->once())->method('recordLogItem')->willReturnCallback(
            static function (mixed ...$given) use (&$arguments): void {
                $arguments = array_values($given);
            }
        );

        (new EventAuditReviewTrail($logger, 'Default'))->recordAccess([$first, ...array_values($more)], ReviewActor::patient(7, 'ann7'));

        return $arguments;
    }

    public function testAReadIsLoggedAgainstThePatientWithTheAppThatReadIt(): void
    {
        $arguments = $this->recordAccess(self::appWrite(31));

        $this->assertSame(1, $arguments[0], 'success');
        $this->assertSame('patient-review', $arguments[1]);
        $this->assertSame('ann7', $arguments[2]);
        $this->assertSame('Review request 31 (fhir-write) read for Observation via API client client-abc', $arguments[4]);
        $this->assertSame(7, $arguments[5], 'logged against the patient');
        $this->assertSame('api', $arguments[7]);
    }

    public function testASearchIsOneEntryNamingEveryRequestShown(): void
    {
        $arguments = $this->recordAccess(self::appWrite(31), self::appWrite(32));

        $this->assertSame('Review requests 31, 32 (fhir-write) read for Observation via API client client-abc', $arguments[4]);
    }

    /**
     * A logger that keeps the arguments of its one recordLogItem() call.
     *
     * @param list<mixed> $arguments
     */
    private function capturingLogger(array &$arguments): EventAuditLogger
    {
        $logger = $this->createMock(EventAuditLogger::class);
        $logger->expects($this->once())->method('recordLogItem')->willReturnCallback(
            static function (mixed ...$given) use (&$arguments): void {
                $arguments = array_values($given);
            }
        );

        return $logger;
    }

    /**
     * @return \Closure(string): ?PatientWriteClientType
     */
    private static function clientType(?PatientWriteClientType $type): \Closure
    {
        return static fn(string $clientId): ?PatientWriteClientType => $clientId === 'client-abc' ? $type : null;
    }

    public function testAnEntrySaysWhetherTheAppIsConfidentialOrPublic(): void
    {
        foreach (['confidential' => PatientWriteClientType::Confidential, 'public' => PatientWriteClientType::Public] as $label => $type) {
            $arguments = [];
            (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default', self::clientType($type)))
                ->record(self::appWrite(31), null, ReviewActor::patient(7, 'ann7'), null);

            $this->assertSame(
                'Review request 31 (fhir-write) submitted as pending for Observation via API client client-abc (' . $label . ')',
                $arguments[4]
            );
        }
    }

    public function testStaffShownARequestAreNamedAsTheReader(): void
    {
        $arguments = [];
        (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default', self::clientType(null)))
            ->recordAccess([self::appWrite(31)], ReviewActor::user(3, 'nurse'));

        $this->assertSame('nurse', $arguments[2]);
        $this->assertSame('Review request 31 (fhir-write) read for Observation via API client client-abc', $arguments[4], 'an unregistered client has no type');
        $this->assertSame(7, $arguments[5], 'logged against the patient');
    }

    /**
     * Staff shown one patient's writes from two apps and of two types get one entry naming each
     * request with its own resource type, app and kind of app.
     */
    public function testAReadOfMixedRequestsIsOneEntryNamingEachWithItsOwnAppAndType(): void
    {
        $arguments = [];
        $types = static fn(string $clientId): PatientWriteClientType => $clientId === 'client-abc'
            ? PatientWriteClientType::Confidential
            : PatientWriteClientType::Public;
        (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default', $types))->recordAccess([
            self::appWrite(31, resourceType: 'QuestionnaireResponse'),
            self::appWrite(32, resourceType: 'QuestionnaireResponse', clientId: 'client-xyz'),
            self::appWrite(33, resourceType: 'QuestionnaireResponse'),
            self::appWrite(34),
        ], ReviewActor::user(3, 'nurse'));

        $this->assertSame(
            'Review requests 31, 32, 33, 34 read:'
            . ' 31, 33 (fhir-write) for QuestionnaireResponse via API client client-abc (confidential);'
            . ' 32 (fhir-write) for QuestionnaireResponse via API client client-xyz (public);'
            . ' 34 (fhir-write) for Observation via API client client-abc (confidential)',
            $arguments[4]
        );
        $this->assertSame(7, $arguments[5], 'one entry, logged against the patient');
    }

    public function testCompletingAnApprovedAppWriteNamesTheChartRecordWritten(): void
    {
        $arguments = [];
        $completed = self::appWrite(31, ReviewStatus::Completed, 'QuestionnaireResponse');
        (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default'))
            ->record($completed, ReviewStatus::Approved, ReviewActor::user(3, 'nurse'), null);

        $this->assertSame(
            'Review request 31 (fhir-write) approved to completed for QuestionnaireResponse via API client client-abc;'
            . ' written to the chart as QuestionnaireResponse/9a3c1f2e-5b6d-4e7f-8a9b-0c1d2e3f4a5b',
            $arguments[4]
        );
    }

    public function testOtherChangesNameNoChartRecord(): void
    {
        $arguments = [];
        (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default'))
            ->record(self::appWrite(31, ReviewStatus::Approved), ReviewStatus::Pending, ReviewActor::user(3, 'nurse'), 'looks right');

        $this->assertSame('Review request 31 (fhir-write) pending to approved for Observation via API client client-abc', $arguments[4]);
        $this->assertSame('looks right', $arguments[10]);
    }

    public function testARefusedWriteIsLoggedAsAFailureAgainstThePatient(): void
    {
        $arguments = [];
        (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default', self::clientType(PatientWriteClientType::Public)))
            ->recordRefusal(7, ReviewActor::patient(7, 'ann7'), ReviewType::FhirWrite, new FhirResourceType('QuestionnaireResponse'), 'client-abc', 'NotAllowed (403)', null);

        $this->assertSame(0, $arguments[0], 'failure');
        $this->assertSame('patient-review', $arguments[1]);
        $this->assertSame('ann7', $arguments[2]);
        $this->assertSame(
            'Review request (fhir-write) refused for QuestionnaireResponse via API client client-abc (public): NotAllowed (403)',
            $arguments[4]
        );
        $this->assertSame(7, $arguments[5]);
        $this->assertSame('api', $arguments[7]);
    }

    public function testARefusedChangeNamesTheIdOnlyWhenItIsAUuid(): void
    {
        $uuid = '1b2c3d4e-5f6a-4b7c-8d9e-0f1a2b3c4d5e';
        foreach ([$uuid => '; sent to ' . $uuid, 'not-a-uuid<script>' => ''] as $target => $suffix) {
            $arguments = [];
            (new EventAuditReviewTrail($this->capturingLogger($arguments), 'Default'))
                ->recordRefusal(7, ReviewActor::patient(7, 'ann7'), ReviewType::FhirWrite, new FhirResourceType('QuestionnaireResponse'), 'client-abc', 'NotHeldForApp (403)', $target);

            $this->assertSame(
                'Change to review request (fhir-write) refused for QuestionnaireResponse via API client client-abc: NotHeldForApp (403)' . $suffix,
                $arguments[4]
            );
        }
    }
}
