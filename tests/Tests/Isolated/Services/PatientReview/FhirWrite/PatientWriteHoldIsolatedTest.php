<?php

/**
 * Isolated tests for holding a patient app write: the policy, the limits and the server-set
 * fields, against an in-memory queue.
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
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\PatientReview\FhirWrite\FhirWriteReviewHandler;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteApplierInterface;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteFields;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusal;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusalReason;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use OpenEMR\Tests\Isolated\Services\PatientReview\InMemoryReviewRequestStore;
use OpenEMR\Tests\Isolated\Services\PatientReview\RecordingReviewAuditTrail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;

class PatientWriteHoldIsolatedTest extends TestCase
{
    private const PID = 7;
    private const PATIENT = '9a3c1f2e-5b6d-4e7f-8a9b-0c1d2e3f4a5b';
    private const OTHER_PATIENT = '1b2c3d4e-5f6a-4b7c-8d9e-0f1a2b3c4d5e';
    private const CLIENT = 'client-abc';

    private InMemoryReviewRequestStore $store;
    private ReviewQueueService $queue;

    protected function setUp(): void
    {
        $this->store = new InMemoryReviewRequestStore();
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('2026-11-07 09:30:00');
            }
        };
        $this->queue = new ReviewQueueService(
            $this->store,
            new RecordingReviewAuditTrail(),
            $clock,
            new UuidFactory(),
            $this->createStub(CryptoInterface::class),
            new NullLogger(),
        );
    }

    /**
     * The hold step with the given mode, and every type enabled unless one is named.
     */
    private function hold(
        PatientWriteMode $mode = PatientWriteMode::ConfidentialAndPublic,
        ?PatientWritableType $enabled = null,
        ?FhirWriteReviewHandler $handler = null,
    ): PatientWriteHold {
        $types = $enabled === null ? PatientWritableType::cases() : [$enabled];

        return new PatientWriteHold($this->queue, new PatientWritePolicy($mode, $types), $handler ?? new FhirWriteReviewHandler([]));
    }

    private function context(PatientWriteClientType $clientType = PatientWriteClientType::Confidential): PatientWriteContext
    {
        return new PatientWriteContext(self::PATIENT, self::CLIENT, $clientType, ReviewActor::patient(self::PID, 'ann7'));
    }

    /**
     * @return array<string, mixed>
     */
    private function questionnaireResponse(): array
    {
        return [
            'resourceType' => 'QuestionnaireResponse',
            'id' => 'app-chosen-id',
            'meta' => ['versionId' => '9', 'lastUpdated' => '2020-01-01T00:00:00Z'],
            'text' => ['status' => 'generated', 'div' => '<div xmlns="http://www.w3.org/1999/xhtml"><script>x</script></div>'],
            'questionnaire' => 'http://example.org/Questionnaire/phq-2',
            'status' => 'completed',
            'subject' => ['reference' => 'Patient/' . self::PATIENT],
            'item' => [['linkId' => '1', 'answer' => [['valueInteger' => 1]]]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function vitalSign(): array
    {
        return [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [['system' => PatientWriteFields::OBSERVATION_CATEGORY_SYSTEM, 'code' => 'vital-signs']],
            ]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '29463-7', 'display' => 'Body weight']]],
            'valueQuantity' => ['value' => 70, 'unit' => 'kg'],
        ];
    }

    /**
     * Asserts the write is refused for the reason given and adds nothing to the queue.
     */
    private function assertRefused(PatientWriteRefusalReason $reason, callable $write): void
    {
        $before = $this->queue->countPending();
        try {
            $write();
            $this->fail('The write should be refused with ' . $reason->name);
        } catch (PatientWriteRefusal $refusal) {
            $this->assertSame($reason, $refusal->reason);
            $this->assertSame($reason->diagnostics(), $refusal->getMessage());
        }
        $this->assertSame($before, $this->queue->countPending(), 'a refused write adds nothing');
    }

    public function testAnAllowedWriteIsHeldWithTheServerSetFields(): void
    {
        $request = $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $this->questionnaireResponse());

        $this->assertSame(ReviewStatus::Pending, $request->status);
        $this->assertSame(ReviewType::FhirWrite, $request->type);
        $this->assertSame('QuestionnaireResponse', $request->resourceType?->value);
        $this->assertSame(ReviewSource::Api, $request->source);
        $this->assertSame(self::CLIENT, $request->clientId);
        $this->assertSame(self::PID, $request->pid);
        $this->assertSame('Patient app questionnaire response: http://example.org/Questionnaire/phq-2', $request->summary);

        $this->assertArrayNotHasKey('id', $request->payload, 'the request uuid is the id');
        $this->assertArrayNotHasKey('meta', $request->payload);
        $this->assertArrayNotHasKey('text', $request->payload, 'app markup is not kept');
        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $request->payload['subject']);
        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $request->payload['source'], 'the patient answered');
        $this->assertSame($this->questionnaireResponse()['item'], $request->payload['item']);
    }

    public function testAMissingSubjectIsTheTokensPatient(): void
    {
        $resource = $this->vitalSign();

        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource);

        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $request->payload['subject']);
    }

    public function testAnAbsoluteReferenceToTheTokensPatientIsAccepted(): void
    {
        $resource = $this->vitalSign();
        $resource['subject'] = ['reference' => 'https://ehr.example.org/apis/default/fhir/Patient/' . self::PATIENT . '/_history/2'];

        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource);

        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $request->payload['subject']);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function otherSubjectProvider(): array
    {
        return [
            'another patient' => [['reference' => 'Patient/' . self::OTHER_PATIENT]],
            'a patient whose id only ends the same' => [['reference' => 'Patient/x' . self::PATIENT]],
            'a group' => [['reference' => 'Group/' . self::PATIENT]],
            'no reference' => [['display' => 'Ann']],
            'not an object' => ['Patient/' . self::PATIENT],
        ];
    }

    #[DataProvider('otherSubjectProvider')]
    public function testASubjectOtherThanTheTokensPatientIsRefused(mixed $subject): void
    {
        $resource = $this->vitalSign();
        $resource['subject'] = $subject;

        $this->assertRefused(
            PatientWriteRefusalReason::OtherPatient,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource)
        );
    }

    public function testAnAllergyNamesItsPatientInThePatientField(): void
    {
        $resource = ['resourceType' => 'AllergyIntolerance', 'patient' => ['reference' => 'Patient/' . self::OTHER_PATIENT]];

        $this->assertRefused(
            PatientWriteRefusalReason::OtherPatient,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::AllergyIntolerance, $resource)
        );
    }

    public function testAQuestionnaireAnsweredByACaregiverKeepsItsSource(): void
    {
        $resource = $this->questionnaireResponse();
        $resource['source'] = ['reference' => 'RelatedPerson/42'];

        $request = $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $resource);

        $this->assertSame(['reference' => 'RelatedPerson/42'], $request->payload['source']);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function otherSourceProvider(): array
    {
        return [
            'practitioner' => [['reference' => 'Practitioner/9a3c1f2e-0000-4000-8000-000000000001']],
            'organization' => [['reference' => 'Organization/1']],
            'display only' => [['display' => 'Dr Lee']],
            'absolute related person' => [['reference' => 'https://other.example/fhir/RelatedPerson/42']],
        ];
    }

    /**
     * A patient app cannot vouch for anyone but the patient or a caregiver, so any other source
     * becomes the patient, and author is dropped.
     *
     * @param array<string, mixed> $source
     */
    #[DataProvider('otherSourceProvider')]
    public function testAQuestionnaireSourceOtherThanACaregiverBecomesThePatient(array $source): void
    {
        $resource = $this->questionnaireResponse();
        $resource['source'] = $source;
        $resource['author'] = ['reference' => 'Practitioner/9a3c1f2e-0000-4000-8000-000000000001'];

        $request = $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $resource);

        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $request->payload['source']);
        $this->assertArrayNotHasKey('author', $request->payload);
    }

    public function testAQuestionnaireAnsweredAsAnotherPatientIsRefused(): void
    {
        $resource = $this->questionnaireResponse();
        $resource['source'] = ['reference' => 'Patient/' . self::OTHER_PATIENT];

        $this->assertRefused(
            PatientWriteRefusalReason::OtherPatient,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $resource)
        );
    }

    public function testAVitalSignIsHeldAsPreliminary(): void
    {
        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign());

        $this->assertSame('preliminary', $request->payload['status']);
        $this->assertSame('Patient app vital sign: Body weight', $request->summary);
    }

    public function testAnObservationOutsideTheVitalSignsCategoryIsRefused(): void
    {
        $resource = $this->vitalSign();
        $resource['category'] = [['coding' => [['system' => PatientWriteFields::OBSERVATION_CATEGORY_SYSTEM, 'code' => 'laboratory']]]];

        $this->assertRefused(
            PatientWriteRefusalReason::WrongType,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource)
        );
    }

    /**
     * @return array<string, array{PatientWritableType, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function verificationProvider(): array
    {
        return [
            'allergy' => [PatientWritableType::AllergyIntolerance, 'AllergyIntolerance', PatientWriteFields::ALLERGY_VERIFICATION_SYSTEM],
            'condition' => [PatientWritableType::Condition, 'Condition', PatientWriteFields::CONDITION_VERIFICATION_SYSTEM],
        ];
    }

    #[DataProvider('verificationProvider')]
    public function testAnAppCannotClaimAConfirmedAllergyOrCondition(PatientWritableType $type, string $resourceType, string $system): void
    {
        $resource = [
            'resourceType' => $resourceType,
            'verificationStatus' => ['coding' => [['system' => $system, 'code' => 'confirmed']]],
            'code' => ['text' => "  Peanut\n allergy "],
        ];

        $request = $this->hold()->hold($this->context(), $type, $resource);

        $this->assertSame(
            ['coding' => [['system' => $system, 'code' => 'unconfirmed', 'display' => 'Unconfirmed']]],
            $request->payload['verificationStatus']
        );
        $this->assertStringEndsWith(': Peanut allergy', $request->summary);
    }

    public function testAResourceOfAnotherTypeIsRefused(): void
    {
        $resource = $this->questionnaireResponse();
        $resource['resourceType'] = 'Patient';

        $this->assertRefused(
            PatientWriteRefusalReason::WrongType,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::QuestionnaireResponse, $resource)
        );
    }

    public function testAPublicClientIsRefusedWhenOnlyConfidentialAppsMayWrite(): void
    {
        $hold = $this->hold(PatientWriteMode::Confidential);

        $this->assertRefused(
            PatientWriteRefusalReason::NotAllowed,
            fn(): ReviewRequest => $hold->hold($this->context(PatientWriteClientType::Public), PatientWritableType::VitalSignObservation, $this->vitalSign())
        );
        $this->assertSame(
            ReviewStatus::Pending,
            $hold->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign())->status
        );
    }

    public function testATypeThePracticeHasNotEnabledIsRefused(): void
    {
        $hold = $this->hold(PatientWriteMode::ConfidentialAndPublic, PatientWritableType::QuestionnaireResponse);

        $this->assertRefused(
            PatientWriteRefusalReason::NotAllowed,
            fn(): ReviewRequest => $hold->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign())
        );
    }

    public function testEveryWriteIsRefusedWhenTheModeIsOff(): void
    {
        $hold = $this->hold(PatientWriteMode::Off);

        $this->assertRefused(
            PatientWriteRefusalReason::NotAllowed,
            fn(): ReviewRequest => $hold->hold($this->context(), PatientWritableType::QuestionnaireResponse, $this->questionnaireResponse())
        );
    }

    public function testAResourceOverTheSizeLimitIsRefused(): void
    {
        $resource = $this->vitalSign();
        $resource['note'] = [['text' => str_repeat('a', PatientWriteHold::MAX_PAYLOAD_BYTES)]];

        $this->assertRefused(
            PatientWriteRefusalReason::TooLarge,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource)
        );
    }

    /**
     * Non-ASCII text under the limit as sent is over it as stored, where it is escaped.
     */
    public function testTheSizeLimitIsMeasuredAsTheQueueStoresIt(): void
    {
        $resource = $this->vitalSign();
        // 2 bytes each in UTF-8, 6 bytes each once stored as é.
        $resource['note'] = [['text' => str_repeat('é', intdiv(PatientWriteHold::MAX_PAYLOAD_BYTES, 4))]];
        $this->assertLessThan(PatientWriteHold::MAX_PAYLOAD_BYTES, strlen((string) json_encode($resource, JSON_UNESCAPED_UNICODE)));

        $this->assertRefused(
            PatientWriteRefusalReason::TooLarge,
            fn(): ReviewRequest => $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource)
        );
    }

    /**
     * A large narrative the server drops doesn't count against the limit.
     */
    public function testADroppedNarrativeDoesNotCountAgainstTheSizeLimit(): void
    {
        $resource = $this->vitalSign();
        $resource['text'] = ['status' => 'generated', 'div' => str_repeat('a', PatientWriteHold::MAX_PAYLOAD_BYTES)];

        $request = $this->hold()->hold($this->context(), PatientWritableType::VitalSignObservation, $resource);

        $this->assertArrayNotHasKey('text', $request->payload);
    }

    /**
     * A write that refers to something this server lacks is refused, rather than given an id
     * for a record that could never exist.
     */
    public function testAWriteThatCouldNeverBeApprovedIsRefused(): void
    {
        $applier = $this->createStub(PatientWriteApplierInterface::class);
        $applier->method('type')->willReturn(PatientWritableType::QuestionnaireResponse);
        $applier->method('canWrite')->willReturn(false);
        $hold = $this->hold(handler: new FhirWriteReviewHandler([$applier]));

        $this->assertRefused(
            PatientWriteRefusalReason::NotWritable,
            fn(): ReviewRequest => $hold->hold($this->context(), PatientWritableType::QuestionnaireResponse, $this->questionnaireResponse())
        );
    }

    /**
     * The 51st waiting write from one app is refused with 429.
     */
    public function testTheAppIsRefusedOnceItHasTheLimitOfWritesWaiting(): void
    {
        $hold = $this->hold();
        for ($i = 0; $i < PatientWriteHold::MAX_OPEN_PER_CLIENT; $i++) {
            $hold->hold($this->context(), PatientWritableType::VitalSignObservation, $this->vitalSign());
        }

        $this->assertRefused(
            PatientWriteRefusalReason::TooManyOpen,
            fn(): ReviewRequest => $hold->hold($this->context(), PatientWritableType::QuestionnaireResponse, $this->questionnaireResponse())
        );
        $this->assertSame(PatientWriteHold::MAX_OPEN_PER_CLIENT, $this->queue->countPending(ReviewType::FhirWrite));
    }

    /**
     * @return array<string, array{PatientWriteRefusalReason, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusalProvider(): array
    {
        return [
            'not allowed' => [PatientWriteRefusalReason::NotAllowed, 403, 'forbidden'],
            'other patient' => [PatientWriteRefusalReason::OtherPatient, 403, 'forbidden'],
            'wrong type' => [PatientWriteRefusalReason::WrongType, 400, 'invalid'],
            'too large' => [PatientWriteRefusalReason::TooLarge, 413, 'too-costly'],
            'too many open' => [PatientWriteRefusalReason::TooManyOpen, 429, 'throttled'],
            'too many revisions' => [PatientWriteRefusalReason::TooManyRevisions, 429, 'throttled'],
            'not writable' => [PatientWriteRefusalReason::NotWritable, 400, 'not-found'],
            'not held for the app' => [PatientWriteRefusalReason::NotHeldForApp, 403, 'forbidden'],
            'gone' => [PatientWriteRefusalReason::Gone, 410, 'deleted'],
            'id mismatch' => [PatientWriteRefusalReason::IdMismatch, 400, 'invalid'],
        ];
    }

    #[DataProvider('refusalProvider')]
    public function testEachRefusalHasItsStatusAndIssueCode(PatientWriteRefusalReason $reason, int $status, string $code): void
    {
        $this->assertSame($status, $reason->httpStatus());
        $this->assertSame($code, $reason->issueCode());
        $this->assertNotSame('', $reason->diagnostics());
    }

    /**
     * The context needs a lower-case uuid, a client id and a patient actor.
     */
    public function testTheContextRefusesAnythingButAPatientWithAUuidAndAClient(): void
    {
        $patient = ReviewActor::patient(self::PID, 'ann7');
        $attempts = [
            'patient id not a uuid' => static fn(): PatientWriteContext => new PatientWriteContext('42', self::CLIENT, PatientWriteClientType::Public, $patient),
            'upper-case uuid' => static fn(): PatientWriteContext => new PatientWriteContext(strtoupper(self::PATIENT), self::CLIENT, PatientWriteClientType::Public, $patient),
            'no client' => static fn(): PatientWriteContext => new PatientWriteContext(self::PATIENT, '', PatientWriteClientType::Public, $patient),
            'staff actor' => static fn(): PatientWriteContext => new PatientWriteContext(self::PATIENT, self::CLIENT, PatientWriteClientType::Public, ReviewActor::user(3, 'drsmith')),
        ];
        foreach ($attempts as $label => $attempt) {
            try {
                $attempt();
                $this->fail('Expected a refusal: ' . $label);
            } catch (\DomainException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame(self::PID, $this->context()->pid());
    }
}
