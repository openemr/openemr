<?php

/**
 * Database tests for approving a held patient app write: the chart record is created under the
 * id the app was given, and a failed write leaves the request pending with nothing written.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PatientReview\FhirWrite;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteFields;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusal;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteRefusalReason;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewItem;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewQuery;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewEvent;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;

class PatientWriteApprovalDatabaseTest extends TestCase
{
    private const QUESTIONNAIRE_NAME = 'PHPUnit Patient App Write Questionnaire';
    private const PUBPID = 'phpunit-patient-app-write';
    private const CLIENT = 'phpunit-patient-app-client';

    private int $pid;
    private string $patientUuid;
    private string $questionnaireUuid;
    private ReviewQueueService $queue;

    protected function setUp(): void
    {
        $this->removeTestRows();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUserID', 1);

        $patient = (new PatientService())->insert([
            'DOB' => '1980-02-03',
            'fname' => 'Held',
            'lname' => 'Write',
            'sex' => 'Female',
            'pubpid' => self::PUBPID,
        ]);
        $this->assertTrue($patient->isValid(), 'test patient');
        $ids = $patient->getFirstDataResult();
        $this->assertIsArray($ids);
        $this->assertIsNumeric($ids['pid']);
        $this->assertIsString($ids['uuid']);
        $this->pid = (int) $ids['pid'];
        $this->patientUuid = $ids['uuid'];

        $questionnaireId = (new QuestionnaireService())->saveQuestionnaireResource([
            'resourceType' => 'Questionnaire',
            'name' => 'PatientAppWriteQuestionnaire',
            'title' => self::QUESTIONNAIRE_NAME,
            'status' => 'active',
            'item' => [['linkId' => '1', 'type' => 'string', 'text' => 'How are you feeling?']],
        ]);
        $uuid = QueryUtils::fetchSingleValue('SELECT `uuid` FROM `questionnaire_repository` WHERE `id` = ?', 'uuid', [$questionnaireId]);
        $this->assertIsString($uuid);
        $this->questionnaireUuid = UuidRegistry::uuidToString($uuid);

        $this->queue = ReviewQueueFactory::withFhirWriteHandler();
    }

    protected function tearDown(): void
    {
        (new FixtureManager())->removeRelatedPersonFixtures();
        $this->removeTestRows();
        SessionWrapperFactory::getInstance()->getActiveSession()->remove('authUserID');
    }

    /**
     * Removes the patient, its requests and responses, the questionnaire and the client these tests create.
     */
    private function removeTestRows(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT `pid` FROM `patient_data` WHERE `pubpid` = ?', 'pid', [self::PUBPID]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `uuid_registry` WHERE `uuid` IN (SELECT `uuid` FROM `questionnaire_response` WHERE `patient_id` = ?)',
                [$pid]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_response` WHERE `patient_id` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `patient_review_event` WHERE `request_id` IN (SELECT `id` FROM `patient_review_request` WHERE `pid` = ?)',
                [$pid]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM `patient_review_request` WHERE `pid` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `uuid_registry` WHERE `uuid` IN (SELECT `uuid` FROM `patient_data` WHERE `pid` = ?)', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [$pid]);
        }
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `uuid_registry` WHERE `uuid` IN (SELECT `uuid` FROM `questionnaire_repository` WHERE `name` = ?)',
            [self::QUESTIONNAIRE_NAME]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_repository` WHERE `name` = ?', [self::QUESTIONNAIRE_NAME]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `oauth_clients` WHERE `client_id` = ?', [self::CLIENT]);
    }

    /**
     * @param array<string, mixed> $resource
     */
    private function hold(PatientWritableType $type, array $resource): ReviewRequest
    {
        return $this->writeHold()->hold($this->context(), $type, $resource);
    }

    private function writeHold(): PatientWriteHold
    {
        return new PatientWriteHold(
            $this->queue,
            new PatientWritePolicy(PatientWriteMode::ConfidentialAndPublic, PatientWritableType::cases()),
            ReviewQueueFactory::fhirWriteHandler(),
        );
    }

    private function context(): PatientWriteContext
    {
        return new PatientWriteContext(
            $this->patientUuid,
            self::CLIENT,
            PatientWriteClientType::Public,
            ReviewActor::patient($this->pid, 'held-write-patient'),
        );
    }

    private function heldResponse(): ReviewRequest
    {
        return $this->hold(PatientWritableType::QuestionnaireResponse, $this->response('Better'));
    }

    /**
     * @return array<string, mixed>
     */
    private function response(string $answer): array
    {
        return [
            'resourceType' => 'QuestionnaireResponse',
            'id' => 'chosen-by-the-app',
            'questionnaire' => 'Questionnaire/' . $this->questionnaireUuid,
            'status' => 'completed',
            'subject' => ['reference' => 'Patient/' . $this->patientUuid],
            'item' => [['linkId' => '1', 'text' => 'How are you feeling?', 'answer' => [['valueString' => $answer]]]],
        ];
    }

    /**
     * How many questionnaire responses the test patient has in the chart.
     */
    private function storedResponseCount(): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS c FROM `questionnaire_response` WHERE `patient_id` = ?', 'c', [$this->pid]);

        return is_numeric($count) ? (int) $count : -1;
    }

    /**
     * The value at a path into decoded FHIR JSON, or null where the path doesn't lead.
     */
    private static function dig(mixed $value, int|string ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    private function registered(string $uuid): bool
    {
        return UuidRegistry::getRegistryRecordForUuid($uuid) !== [];
    }

    /**
     * Approval stores the response under the request uuid with the patient as its source.
     */
    public function testApprovalWritesTheResponseUnderTheIdTheAppWasGiven(): void
    {
        $held = $this->heldResponse();
        $this->assertSame(0, $this->storedResponseCount(), 'nothing reaches the chart while it is held');

        $done = $this->queue->approve($held->id, ReviewActor::user(1, 'admin'), 'looks right');

        $this->assertSame(ReviewStatus::Completed, $done->status);
        $row = QueryUtils::querySingleRow(
            'SELECT `response_id`, `creator_user_id`, `patient_id` FROM `questionnaire_response` WHERE `uuid` = ?',
            [UuidRegistry::uuidToBytes($held->uuid)]
        );
        $this->assertIsArray($row, 'the response is stored under the request uuid');
        $this->assertSame($held->uuid, $row['response_id']);
        $this->assertNull($row['creator_user_id'], 'the patient answered, not the approver');
        $this->assertTrue($this->registered($held->uuid));

        $read = (new FhirQuestionnaireResponseFormService())->getOne($held->uuid);
        $resource = $read->getFirstDataResult();
        $this->assertInstanceOf(FHIRQuestionnaireResponse::class, $resource);
        $this->assertSame($held->uuid, $resource->getId()->getValue());
        $json = json_decode((string) json_encode($resource), true);
        $this->assertIsArray($json);
        $this->assertIsArray($json['source'] ?? null);
        $this->assertSame('Patient/' . $this->patientUuid, $json['source']['reference'] ?? null, 'the patient answered');
    }

    /**
     * A response a caregiver answered keeps the caregiver as its source once it is in the chart.
     */
    public function testAnApprovedCaregiverResponseReadsBackWithTheCaregiverAsSource(): void
    {
        $caregiver = $this->relatedPerson();
        $held = $this->hold(
            PatientWritableType::QuestionnaireResponse,
            ['source' => ['reference' => 'RelatedPerson/' . $caregiver]] + $this->response('Better')
        );

        $this->queue->approve($held->id, ReviewActor::user(1, 'admin'));

        $resource = (new FhirQuestionnaireResponseFormService())->getOne($held->uuid)->getFirstDataResult();
        $this->assertInstanceOf(FHIRQuestionnaireResponse::class, $resource);
        $json = json_decode((string) json_encode($resource), true);
        $this->assertIsArray($json);
        $this->assertSame(['reference' => 'RelatedPerson/' . $caregiver], $json['source'] ?? null);
    }

    /**
     * A caregiver removed from the patient's related persons after the response was held can't
     * be named in the chart: approval fails and the response stays pending.
     */
    public function testApprovalFailsOnceTheCaregiverIsNoLongerRelated(): void
    {
        $caregiver = $this->relatedPerson();
        $held = $this->hold(
            PatientWritableType::QuestionnaireResponse,
            ['source' => ['reference' => 'RelatedPerson/' . $caregiver]] + $this->response('Better')
        );
        (new FixtureManager())->removeRelatedPersonFixtures();

        try {
            $this->queue->approve($held->id, ReviewActor::user(1, 'admin'));
            $this->fail('Approval must fail when the caregiver is no longer related');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(ReviewStatus::Pending, $this->queue->get($held->id)->status);
        $this->assertSame(0, $this->storedResponseCount());
    }

    /**
     * A caregiver source must be one of this patient's related persons: anyone else, or an id
     * that names no one, would put an unchecked name on the chart as the one who answered.
     */
    public function testACaregiverSourceMustBeThePatientsRelatedPerson(): void
    {
        foreach (['00000000-0000-4000-8000-000000000000', 'caregiver-1'] as $id) {
            try {
                $this->hold(PatientWritableType::QuestionnaireResponse, ['source' => ['reference' => 'RelatedPerson/' . $id]] + $this->response('Better'));
                $this->fail('Expected a refusal for RelatedPerson/' . $id);
            } catch (PatientWriteRefusal $refusal) {
                $this->assertSame(PatientWriteRefusalReason::NotWritable, $refusal->reason, $id);
            }
        }
    }

    /**
     * A related person of the test patient, from the shared fixture; returns its FHIR id.
     */
    private function relatedPerson(): string
    {
        $fixture = (array) (new FixtureManager())->getSingleFhirRelatedPersonFixture();
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        $result = (new FhirRelatedPersonService())->insert(new FHIRRelatedPerson($fixture));
        $this->assertTrue($result->isValid(), 'test related person');
        $row = $result->getFirstDataResult();
        $this->assertIsArray($row);
        $this->assertIsString($row['uuid'] ?? null);

        return $row['uuid'];
    }

    /**
     * The app corrects its answers before staff look; approval writes the corrected version
     * under the same id, and the first version stays in the request's history.
     */
    public function testApprovalOfAReplacedResponseWritesTheReplacement(): void
    {
        $held = $this->heldResponse();
        $this->writeHold()->replace(
            $this->context(),
            PatientWritableType::QuestionnaireResponse,
            $held->uuid,
            ['id' => $held->uuid] + $this->response('Much better')
        );

        $this->queue->approve($held->id, ReviewActor::user(1, 'admin'));

        $resource = (new FhirQuestionnaireResponseFormService())->getOne($held->uuid)->getFirstDataResult();
        $this->assertInstanceOf(FHIRQuestionnaireResponse::class, $resource);
        $json = json_decode((string) json_encode($resource), true);
        $this->assertIsArray($json);
        $this->assertSame('Much better', self::dig($json, 'item', 0, 'answer', 0, 'valueString'));
        $this->assertSame(1, $this->storedResponseCount());

        $replaced = array_values(array_filter(
            $this->queue->history($held->id),
            static fn(ReviewEvent $event): bool => $event->previousPayload !== null
        ));
        $this->assertCount(1, $replaced);
        $this->assertSame('Better', self::dig($replaced[0]->previousPayload, 'item', 0, 'answer', 0, 'valueString'));
    }

    /**
     * A response whose Questionnaire is gone rolls back and leaves no registered id.
     */
    public function testAFailedWriteLeavesTheRequestPendingWithNothingWritten(): void
    {
        $held = $this->heldResponse();
        // The Questionnaire is removed after the response was held.
        QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_repository` WHERE `name` = ?', [self::QUESTIONNAIRE_NAME]);

        try {
            $this->queue->approve($held->id, ReviewActor::user(1, 'admin'));
            $this->fail('Approval must fail when the response cannot be written');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(ReviewStatus::Pending, $this->queue->get($held->id)->status);
        $this->assertSame(0, $this->storedResponseCount());
        $this->assertFalse($this->registered($held->uuid), 'the issued id is not left registered');
    }

    /**
     * A held vital sign has no applier yet and stays pending.
     */
    public function testATypeThatCannotBeApprovedYetStaysPending(): void
    {
        $held = $this->hold(PatientWritableType::VitalSignObservation, [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [['coding' => [['system' => PatientWriteFields::OBSERVATION_CATEGORY_SYSTEM, 'code' => 'vital-signs']]]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '29463-7']]],
            'valueQuantity' => ['value' => 70, 'unit' => 'kg'],
        ]);
        $this->assertFalse(ReviewQueueFactory::fhirWriteHandler()->canApply($held));

        try {
            $this->queue->approve($held->id, ReviewActor::user(1, 'admin'));
            $this->fail('A vital sign has no applier yet');
        } catch (\LogicException) {
            // expected
        }

        $this->assertSame(ReviewStatus::Pending, $this->queue->get($held->id)->status);
    }

    /**
     * The list names the app and client type, shows answers and flags a repeated reading.
     */
    public function testTheReviewListShowsTheAppItsClientTypeAnswersAndPossibleDuplicates(): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `oauth_clients` (`client_id`, `client_name`, `is_confidential`) VALUES (?, ?, 0)',
            [self::CLIENT, 'PHPUnit Patient App']
        );
        $response = $this->heldResponse();
        $vital = [
            'resourceType' => 'Observation',
            'category' => [['coding' => [['system' => PatientWriteFields::OBSERVATION_CATEGORY_SYSTEM, 'code' => 'vital-signs']]]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '29463-7', 'display' => 'Body weight']]],
            'effectiveDateTime' => '2026-11-07T08:00:00Z',
            'valueQuantity' => ['value' => 70, 'unit' => 'kg'],
        ];
        $first = $this->hold(PatientWritableType::VitalSignObservation, $vital);
        $again = $this->hold(PatientWritableType::VitalSignObservation, $vital);

        $items = [];
        foreach ((new PatientWriteReviewQuery(new ReviewRequestMapper(), ReviewQueueFactory::fhirWriteHandler()))->pending()['items'] as $item) {
            $items[$item->request->id] = $item;
        }

        $this->assertArrayHasKey($response->id, $items);
        $listed = $items[$response->id];
        $this->assertSame('PHPUnit Patient App', $listed->appName);
        $this->assertSame(PatientWriteClientType::Public, $listed->clientType);
        $this->assertSame('Held Write', $listed->patientName);
        $this->assertSame([['question' => 'How are you feeling?', 'answers' => ['Better'], 'unshown' => 0]], $listed->answers);
        $this->assertSame(0, $listed->answersTooDeep);
        $this->assertTrue($listed->canApprove);
        $this->assertNull($listed->duplicateOf);
        $this->assertSame(self::QUESTIONNAIRE_NAME, $listed->questionnaireName, 'the title staff know it by');
        $this->assertNull($items[$first->id]->questionnaireName);

        $this->assertNull($items[$first->id]->duplicateOf);
        $this->assertSame($first->id, $items[$again->id]->duplicateOf, 'the same reading sent twice');
        $this->assertFalse($items[$again->id]->canApprove, 'vital signs cannot be approved yet');
    }

    /**
     * A response approval could never write is refused when the app sends it, not given an id:
     * a pinned version the repository no longer has, another server's Questionnaire, or an
     * encounter that isn't this patient's.
     */
    public function testAResponseThatCouldNeverBeApprovedIsRefusedWhenHeld(): void
    {
        $attempts = [
            'stale version' => ['questionnaire' => 'Questionnaire/' . $this->questionnaireUuid . '|999'],
            'another server' => ['questionnaire' => 'https://other.example/fhir/Questionnaire/' . $this->questionnaireUuid],
            'unknown encounter' => ['encounter' => ['reference' => 'Encounter/00000000-0000-4000-8000-000000000000']],
        ];
        foreach ($attempts as $label => $change) {
            try {
                $this->hold(PatientWritableType::QuestionnaireResponse, $change + $this->response('Better'));
                $this->fail('Expected a refusal: ' . $label);
            } catch (PatientWriteRefusal $refusal) {
                $this->assertSame(PatientWriteRefusalReason::NotWritable, $refusal->reason, $label);
            }
        }
        $this->assertSame([], $this->queue->allOpenFor($this->pid, ReviewType::FhirWrite, PatientWritableType::QuestionnaireResponse->fhirResourceType()));
    }

    /**
     * Approve is offered only while the response could still be written: once its
     * Questionnaire changes version, the screen withholds Approve rather than fail on it.
     */
    public function testApproveIsWithheldOnceTheResponseCanNoLongerBeWritten(): void
    {
        $version = QueryUtils::fetchSingleValue('SELECT `version` FROM `questionnaire_repository` WHERE `name` = ?', 'version', [self::QUESTIONNAIRE_NAME]);
        $this->assertIsScalar($version);
        $held = $this->hold(
            PatientWritableType::QuestionnaireResponse,
            ['questionnaire' => 'Questionnaire/' . $this->questionnaireUuid . '|' . $version] + $this->response('Better')
        );
        QueryUtils::sqlStatementThrowException(
            'UPDATE `questionnaire_repository` SET `version` = `version` + 1 WHERE `name` = ?',
            [self::QUESTIONNAIRE_NAME]
        );

        $query = new PatientWriteReviewQuery(new ReviewRequestMapper(), ReviewQueueFactory::fhirWriteHandler());
        $listed = array_values(array_filter($query->pending()['items'], static fn(PatientWriteReviewItem $item): bool => $item->request->id === $held->id));

        $this->assertCount(1, $listed);
        $this->assertSame(self::QUESTIONNAIRE_NAME, $listed[0]->questionnaireName);
        $this->assertFalse($listed[0]->canApprove);
    }

    public function testAnIssuedUuidAlreadyInUseIsRefused(): void
    {
        $registry = new UuidRegistry(['table_name' => 'questionnaire_repository']);
        $used = UuidRegistry::uuidToBytes($this->questionnaireUuid);

        $this->expectException(\RuntimeException::class);
        $registry->registerIssuedUuid($used);
    }
}
