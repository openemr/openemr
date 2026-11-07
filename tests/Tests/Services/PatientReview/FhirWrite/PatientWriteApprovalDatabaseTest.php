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
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteContext;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteFields;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteHold;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteMode;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewQuery;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireService;
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
        $this->removeTestRows();
        SessionWrapperFactory::getInstance()->getActiveSession()->remove('authUserID');
    }

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
        $hold = new PatientWriteHold($this->queue, new PatientWritePolicy(PatientWriteMode::ConfidentialAndPublic, PatientWritableType::cases()));
        $context = new PatientWriteContext(
            $this->patientUuid,
            self::CLIENT,
            PatientWriteClientType::Public,
            ReviewActor::patient($this->pid, 'held-write-patient'),
        );

        return $hold->hold($context, $type, $resource);
    }

    private function heldResponse(): ReviewRequest
    {
        return $this->hold(PatientWritableType::QuestionnaireResponse, [
            'resourceType' => 'QuestionnaireResponse',
            'id' => 'chosen-by-the-app',
            'questionnaire' => 'Questionnaire/' . $this->questionnaireUuid,
            'status' => 'completed',
            'subject' => ['reference' => 'Patient/' . $this->patientUuid],
            'item' => [['linkId' => '1', 'text' => 'How are you feeling?', 'answer' => [['valueString' => 'Better']]]],
        ]);
    }

    private function storedResponseCount(): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS c FROM `questionnaire_response` WHERE `patient_id` = ?', 'c', [$this->pid]);

        return is_numeric($count) ? (int) $count : -1;
    }

    private function registered(string $uuid): bool
    {
        return UuidRegistry::getRegistryRecordForUuid($uuid) !== [];
    }

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
        $this->assertSame([['question' => 'How are you feeling?', 'answers' => ['Better']]], $listed->answers);
        $this->assertTrue($listed->canApprove);
        $this->assertNull($listed->duplicateOf);
        $this->assertSame(self::QUESTIONNAIRE_NAME, $listed->questionnaireName, 'the title staff know it by');
        $this->assertNull($items[$first->id]->questionnaireName);

        $this->assertNull($items[$first->id]->duplicateOf);
        $this->assertSame($first->id, $items[$again->id]->duplicateOf, 'the same reading sent twice');
        $this->assertFalse($items[$again->id]->canApprove, 'vital signs cannot be approved yet');
    }

    public function testAnIssuedUuidAlreadyInUseIsRefused(): void
    {
        $registry = new UuidRegistry(['table_name' => 'questionnaire_repository']);
        $used = UuidRegistry::uuidToBytes($this->questionnaireUuid);

        $this->expectException(\RuntimeException::class);
        $registry->registerIssuedUuid($used);
    }
}
