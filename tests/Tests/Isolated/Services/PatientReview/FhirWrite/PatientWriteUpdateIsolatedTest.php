<?php

/**
 * Isolated tests for a patient app changing or withdrawing its own write while it waits for
 * review (a FHIR update), and for every update it may not make.
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
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteFields;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusal;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusalReason;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientWriteUpdateIsolatedTest extends TestCase
{
    use PatientWriteQueueFixture;

    private const OTHER_CLIENT = 'client-xyz';

    protected function setUp(): void
    {
        $this->setUpQueue();
    }

    /**
     * Holds a vital sign from the app and returns its request.
     */
    private function held(): ReviewRequest
    {
        return $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign());
    }

    /**
     * @param array<string, mixed> $resource
     * @return array<string, mixed>
     */
    private static function withId(array $resource, string $id): array
    {
        return ['id' => $id] + $resource;
    }

    /**
     * Asserts the update is refused for the reason given and the request is unchanged.
     */
    private function assertRefused(PatientWriteRefusalReason $reason, ReviewRequest $request, callable $update): void
    {
        try {
            $update();
            $this->fail('The update should be refused with ' . $reason->name);
        } catch (PatientWriteRefusal $refusal) {
            $this->assertSame($reason, $refusal->reason);
        }
        $now = $this->queue->get($request->id);
        $this->assertSame($request->payload, $now->payload, 'a refused update changes nothing');
        $this->assertSame($request->status, $now->status);
    }

    /**
     * An update of the app's own pending write replaces what is held and keeps the id.
     */
    public function testTheAppReplacesItsWaitingWriteUnderTheSameId(): void
    {
        $request = $this->held();
        $this->clock->setTo(new DateTimeImmutable('2026-11-07 10:00:00'));

        $replaced = $this->hold()->replace(
            $this->context(),
            PatientWritableType::VitalSignObservation,
            $request->uuid,
            self::withId($this->vitalSign(72), $request->uuid)
        );

        $this->assertSame($request->id, $replaced->id);
        $this->assertSame($request->uuid, $replaced->uuid);
        $this->assertSame(ReviewStatus::Pending, $replaced->status);
        $this->assertSame(['value' => 72, 'unit' => 'kg'], $replaced->payload['valueQuantity']);
        $this->assertSame('preliminary', $replaced->payload['status'], 'the replacement gets the server-set fields too');
        $this->assertArrayNotHasKey('id', $replaced->payload);
        $this->assertSame('2026-11-07 10:00:00', $replaced->updatedAt->format('Y-m-d H:i:s'));

        $last = array_slice($this->queue->history($request->id), -1)[0];
        $this->assertSame(ReviewQueueService::NOTE_REVISED, $last->note);
        $this->assertSame(['value' => 70, 'unit' => 'kg'], $last->previousPayload['valueQuantity'] ?? null, 'the earlier version is kept');
    }

    /**
     * Each replacement keeps what it overwrote, so the app may replace one write only so often.
     * It can still withdraw it.
     */
    public function testAWriteCanBeReplacedOnlyUpToTheLimit(): void
    {
        $request = $this->held();
        $replace = fn(int $weight): ReviewRequest => $this->hold()->replace(
            $this->context(),
            PatientWritableType::VitalSignObservation,
            $request->uuid,
            self::withId($this->vitalSign($weight), $request->uuid)
        );
        for ($i = 1; $i <= PatientWriteHold::MAX_REVISIONS; $i++) {
            $replace(70 + $i);
        }
        $atLimit = $this->queue->get($request->id);

        $this->assertRefused(PatientWriteRefusalReason::TooManyRevisions, $atLimit, static fn(): ReviewRequest => $replace(99));
        $this->assertSame(PatientWriteHold::MAX_REVISIONS, $this->store->countRevisions($request->id));

        $withdrawn = $this->hold()->replace(
            $this->context(),
            PatientWritableType::VitalSignObservation,
            $request->uuid,
            self::withId(['status' => 'entered-in-error'] + $this->vitalSign(), $request->uuid)
        );
        $this->assertSame(ReviewStatus::Cancelled, $withdrawn->status);
    }

    public function testAReplacementNamingAnotherPatientIsRefused(): void
    {
        $request = $this->held();
        $resource = self::withId($this->vitalSign(72), $request->uuid);
        $resource['subject'] = ['reference' => 'Patient/1b2c3d4e-5f6a-4b7c-8d9e-0f1a2b3c4d5e'];

        $this->assertRefused(
            PatientWriteRefusalReason::OtherPatient,
            $request,
            fn(): ReviewRequest => $this->hold()->replace($this->context(), PatientWritableType::VitalSignObservation, $request->uuid, $resource)
        );
    }

    /**
     * @return array<string, array{PatientWritableType, array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function withdrawalProvider(): array
    {
        $verification = static fn(string $system): array => [
            'verificationStatus' => ['coding' => [['system' => $system, 'code' => 'entered-in-error']]],
        ];

        return [
            'questionnaire response' => [PatientWritableType::QuestionnaireResponse, ['status' => 'entered-in-error']],
            'vital sign' => [PatientWritableType::VitalSignObservation, ['status' => 'entered-in-error']],
            'allergy' => [PatientWritableType::AllergyIntolerance, $verification(PatientWriteFields::ALLERGY_VERIFICATION_SYSTEM)],
            'condition' => [PatientWritableType::Condition, $verification(PatientWriteFields::CONDITION_VERIFICATION_SYSTEM)],
        ];
    }

    /**
     * @param array<string, mixed> $marking
     */
    #[DataProvider('withdrawalProvider')]
    public function testAnUpdateMarkedEnteredInErrorWithdrawsTheWrite(PatientWritableType $type, array $marking): void
    {
        $request = $this->hold()->hold($this->context(), $type, $this->resourceOf($type));

        $withdrawn = $this->hold()->replace($this->context(), $type, $request->uuid, self::withId($marking + $this->resourceOf($type), $request->uuid));

        $this->assertSame(ReviewStatus::Cancelled, $withdrawn->status);
        $this->assertSame($request->payload, $withdrawn->payload, 'what was held is kept as it was');
        $last = array_slice($this->queue->history($request->id), -1)[0];
        $this->assertSame(PatientWriteHold::NOTE_WITHDRAWN, $last->note);
    }

    /**
     * entered-in-error in another system, or as another field, is an ordinary replacement.
     */
    public function testOnlyTheTypesOwnEnteredInErrorCodeWithdraws(): void
    {
        $request = $this->hold()->hold($this->context(), PatientWritableType::Condition, $this->resourceOf(PatientWritableType::Condition));
        $resource = self::withId($this->resourceOf(PatientWritableType::Condition), $request->uuid);
        $resource['verificationStatus'] = ['coding' => [['system' => PatientWriteFields::ALLERGY_VERIFICATION_SYSTEM, 'code' => 'entered-in-error']]];
        $resource['status'] = 'entered-in-error';

        $replaced = $this->hold()->replace($this->context(), PatientWritableType::Condition, $request->uuid, $resource);

        $this->assertSame(ReviewStatus::Pending, $replaced->status);
        $this->assertSame(
            ['coding' => [['system' => PatientWriteFields::CONDITION_VERIFICATION_SYSTEM, 'code' => 'unconfirmed', 'display' => 'Unconfirmed']]],
            $replaced->payload['verificationStatus']
        );
    }

    /**
     * A withdrawal is checked for its resourceType as a replacement is: entered-in-error on
     * another kind of resource doesn't cancel the write.
     */
    public function testAWithdrawalOfAnotherResourceTypeIsRefused(): void
    {
        $request = $this->held();
        $resource = self::withId(['resourceType' => 'Patient', 'status' => 'entered-in-error'], $request->uuid);

        $this->assertRefused(
            PatientWriteRefusalReason::WrongType,
            $request,
            fn(): ReviewRequest => $this->hold()->replace($this->context(), PatientWritableType::VitalSignObservation, $request->uuid, $resource)
        );
    }

    /**
     * FHIR update requires the resource to carry the id it is sent to.
     */
    public function testTheResourceIdMustMatchTheIdItIsSentTo(): void
    {
        $request = $this->held();
        foreach ([$this->vitalSign(72), self::withId($this->vitalSign(72), 'something-else')] as $resource) {
            $this->assertRefused(
                PatientWriteRefusalReason::IdMismatch,
                $request,
                fn(): ReviewRequest => $this->hold()->replace($this->context(), PatientWritableType::VitalSignObservation, $request->uuid, $resource)
            );
        }
    }

    /**
     * A chart record, another app's or patient's write, a write of another type, an approved
     * write and an id that names nothing all get the same 403.
     */
    public function testAnUpdateOfAnythingButTheAppsOwnWaitingWriteIsForbidden(): void
    {
        $request = $this->held();
        $approved = $this->held();
        $this->queue->complete($approved->id, ReviewActor::user(3, 'drsmith'));
        $hold = $this->hold();
        $vital = PatientWritableType::VitalSignObservation;
        $attempts = [
            'another app' => fn(): ReviewRequest => $hold->replace($this->context(self::OTHER_CLIENT), $vital, $request->uuid, self::withId($this->vitalSign(), $request->uuid)),
            'another patient' => fn(): ReviewRequest => $hold->replace($this->context(pid: 8), $vital, $request->uuid, self::withId($this->vitalSign(), $request->uuid)),
            'another type' => fn(): ReviewRequest => $hold->replace(
                $this->context(),
                PatientWritableType::Condition,
                $request->uuid,
                self::withId($this->resourceOf(PatientWritableType::Condition), $request->uuid)
            ),
            'approved' => fn(): ReviewRequest => $hold->replace($this->context(), $vital, $approved->uuid, self::withId($this->vitalSign(), $approved->uuid)),
            'nothing' => fn(): ReviewRequest => $hold->replace(
                $this->context(),
                $vital,
                '00000000-0000-4000-8000-000000000000',
                self::withId($this->vitalSign(), '00000000-0000-4000-8000-000000000000')
            ),
            'not a uuid' => fn(): ReviewRequest => $hold->replace($this->context(), $vital, '42', self::withId($this->vitalSign(), '42')),
        ];
        foreach ($attempts as $label => $attempt) {
            try {
                $attempt();
                $this->fail('Expected 403: ' . $label);
            } catch (PatientWriteRefusal $refusal) {
                $this->assertSame(PatientWriteRefusalReason::NotHeldForApp, $refusal->reason, $label);
            }
        }
        $this->assertSame($request->payload, $this->queue->get($request->id)->payload);
    }

    /**
     * The app's own denied or withdrawn write can't be updated: Gone (410), and it stays closed.
     */
    public function testAnUpdateOfADeniedOrWithdrawnWriteIsGone(): void
    {
        $denied = $this->held();
        $this->queue->deny($denied->id, ReviewActor::user(3, 'drsmith'), 'not a home reading');
        $withdrawn = $this->held();
        $this->queue->cancel($withdrawn->id, $this->context()->actor);

        foreach ([$denied, $withdrawn] as $request) {
            $closed = $this->queue->get($request->id);
            $this->assertRefused(
                PatientWriteRefusalReason::Gone,
                $closed,
                fn(): ReviewRequest => $this->hold()->replace(
                    $this->context(),
                    PatientWritableType::VitalSignObservation,
                    $request->uuid,
                    self::withId($this->vitalSign(72), $request->uuid)
                )
            );
        }
    }

    /**
     * Turning the mode down stops updates from tokens already issued, as it stops new writes.
     */
    public function testAnUpdateIsRefusedOnceThePracticeTurnsWritesOff(): void
    {
        $request = $this->held();

        $this->assertRefused(
            PatientWriteRefusalReason::NotAllowed,
            $request,
            fn(): ReviewRequest => $this->hold(PatientWriteMode::Off)->replace(
                $this->context(),
                PatientWritableType::VitalSignObservation,
                $request->uuid,
                self::withId($this->vitalSign(72), $request->uuid)
            )
        );
    }

    /**
     * If the request changes between the lookup and the withdrawal, and is still pending when
     * looked at again, the queue's own error goes up: there is no refusal to give.
     */
    public function testAWithdrawalThatLosesARaceIsNotReportedAsDone(): void
    {
        $request = $this->held();
        $this->store->loseNextUpdate = true;
        $resource = self::withId(['status' => 'entered-in-error'] + $this->vitalSign(), $request->uuid);

        $this->expectException(InvalidReviewTransitionException::class);
        $this->hold()->replace($this->context(), PatientWritableType::VitalSignObservation, $request->uuid, $resource);
    }
}
