<?php

/**
 * Isolated tests for the audit log entries the review queue writes when a patient app reads
 * back what it submitted.
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
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;

class EventAuditReviewTrailIsolatedTest extends TestCase
{
    private static function appWrite(int $id): ReviewRequest
    {
        $at = new DateTimeImmutable('2026-11-07 09:30:00');

        return new ReviewRequest(
            id: $id,
            uuid: '9a3c1f2e-5b6d-4e7f-8a9b-0c1d2e3f4a5b',
            pid: 7,
            type: ReviewType::FhirWrite,
            resourceType: new FhirResourceType('Observation'),
            source: ReviewSource::Api,
            clientId: 'client-abc',
            status: ReviewStatus::Pending,
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
}
