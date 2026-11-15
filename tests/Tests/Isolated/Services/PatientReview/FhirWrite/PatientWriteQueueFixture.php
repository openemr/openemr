<?php

/**
 * An in-memory review queue with patient app writes held in it, for the isolated tests of what
 * happens to a write after it is held.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\FhirWrite\FhirWriteReviewHandler;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteFields;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Tests\Isolated\Services\PatientReview\InMemoryReviewRequestStore;
use OpenEMR\Tests\Isolated\Services\PatientReview\RecordingReviewAuditTrail;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

trait PatientWriteQueueFixture
{
    private const PID = 7;
    private const PATIENT = '9a3c1f2e-5b6d-4e7f-8a9b-0c1d2e3f4a5b';
    private const CLIENT = 'client-abc';

    private InMemoryReviewRequestStore $store;
    private RecordingReviewAuditTrail $trail;
    private ReviewQueueService $queue;
    private FrozenClock $clock;

    private function setUpQueue(): void
    {
        $this->store = new InMemoryReviewRequestStore();
        $this->trail = new RecordingReviewAuditTrail();
        $this->clock = new FrozenClock(new DateTimeImmutable('2026-11-07 09:30:00'));
        $this->queue = new ReviewQueueService(
            $this->store,
            $this->trail,
            $this->clock,
            new UuidFactory(),
            $this->createStub(CryptoInterface::class),
            new NullLogger(),
        );
    }

    private function hold(PatientWriteMode $mode = PatientWriteMode::ConfidentialAndPublic): PatientWriteHold
    {
        return new PatientWriteHold($this->queue, new PatientWritePolicy($mode, PatientWritableType::cases()), new FhirWriteReviewHandler([]));
    }

    private function context(string $clientId = self::CLIENT, int $pid = self::PID): PatientWriteContext
    {
        return new PatientWriteContext(self::PATIENT, $clientId, PatientWriteClientType::Confidential, ReviewActor::patient($pid, 'ann7'));
    }

    /**
     * @return array<string, mixed>
     */
    private function vitalSign(int $kilograms = 70): array
    {
        return [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [['system' => PatientWriteFields::OBSERVATION_CATEGORY_SYSTEM, 'code' => 'vital-signs']],
            ]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '29463-7', 'display' => 'Body weight']]],
            'valueQuantity' => ['value' => $kilograms, 'unit' => 'kg'],
        ];
    }

    /**
     * A resource of each writable type, for the token's patient.
     *
     * @return array<string, mixed>
     */
    private function resourceOf(PatientWritableType $type): array
    {
        return match ($type) {
            PatientWritableType::QuestionnaireResponse => [
                'resourceType' => 'QuestionnaireResponse',
                'questionnaire' => 'http://example.org/Questionnaire/phq-2',
                'status' => 'completed',
                'item' => [['linkId' => '1', 'answer' => [['valueInteger' => 1]]]],
            ],
            PatientWritableType::VitalSignObservation => $this->vitalSign(),
            PatientWritableType::AllergyIntolerance => [
                'resourceType' => 'AllergyIntolerance',
                'code' => ['text' => 'Peanut'],
            ],
            PatientWritableType::Condition => [
                'resourceType' => 'Condition',
                'code' => ['text' => 'Hay fever'],
            ],
        };
    }
}
