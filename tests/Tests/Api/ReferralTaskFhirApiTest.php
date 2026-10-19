<?php

/**
 * The Task a saved referral gets when the fhir_referral_tasks global is on (#14710), read
 * through GET /fhir/Task. The referrals are saved as the Transactions screen saves them
 * (a transactions row without a uuid, and its lbt_data fields), then handed to
 * ReferralTaskService as add_transaction.php hands them over.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirTaskService;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\PatientTransactionService;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ReferralTaskFhirApiTest extends TestCase
{
    private const FHIR_URL = '/apis/default/fhir/';

    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;
    private mixed $globalBefore;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array<string, array{uuid: string, id: int}> address book entries and users by name */
    private array $people = [];

    /** @var list<int> transaction ids */
    private array $transactions = [];

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->fixtures = new ConditionFixtureManager();
        $patient = $this->fixtures->createTestPatient();
        $this->assertIsString($patient['uuid'] ?? null);
        $this->assertIsInt($patient['pid'] ?? null);
        $this->patient = ['uuid' => $patient['uuid'], 'pid' => $patient['pid']];

        $this->globalBefore = OEGlobalsBag::getInstance()->get(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value);
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '1');

        // A referring doctor with a login and an NPI (a Practitioner), an External
        // Organization, a specialist with an NPI (a Practitioner) and one without (neither).
        $this->people['doctor'] = $this->insertPerson(['username' => 'zq-referrer', 'fname' => 'Rae', 'lname' => 'Zq', 'npi' => '1245319599', 'authorized' => '1']);
        $this->people['pantry'] = $this->insertPerson(['abook_type' => 'external_org', 'organization' => 'Zq Food Pantry']);
        $this->people['cardiologist'] = $this->insertPerson(['abook_type' => 'spe', 'fname' => 'Cara', 'lname' => 'Zq', 'npi' => '1234567893']);
        $this->people['no npi'] = $this->insertPerson(['abook_type' => 'spe', 'fname' => 'Nell', 'lname' => 'Zq']);
        // The REST service finds people by NPI, so these NPIs are the test's own.
        $this->people['rest referrer'] = $this->insertPerson(['username' => 'zq-rest-referrer', 'fname' => 'Ray', 'lname' => 'Zq', 'npi' => '1765483213', 'authorized' => '1']);
        $this->people['oncologist'] = $this->insertPerson(['abook_type' => 'spe', 'fname' => 'Onna', 'lname' => 'Zq', 'npi' => '1765483221']);
        $this->people['dermatologist'] = $this->insertPerson(['abook_type' => 'spe', 'fname' => 'Derm', 'lname' => 'Zq', 'npi' => '1765483239']);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, $this->globalBefore);
        foreach ($this->transactions as $id) {
            $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
            if (is_string($uuid)) {
                QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE focus_uuid = ?', [$uuid]);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$id]);
        }
        foreach ($this->people as $person) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$person['id']]);
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testAReferralToAnExternalOrganizationGetsATaskTheOrganizationOwns(): void
    {
        $id = $this->saveReferral(['refer_date' => '2026-10-19', 'refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);

        $taskUuid = $this->createTask($id);
        $this->assertIsString($taskUuid);
        $referral = $this->referralUuid($id);
        $task = $this->read('Task', $taskUuid);

        $this->assertSame('requested', $task['status'] ?? null);
        $this->assertSame('order', $task['intent'] ?? null);
        $this->assertSame([['system' => FhirTaskService::TASK_CODE_SYSTEM, 'code' => 'fulfill', 'display' => 'fulfill']], self::at($task, 'code', 'coding'));
        $this->assertSame('ServiceRequest/' . $referral, self::at($task, 'focus', 'reference'));
        $this->assertSame('Patient/' . $this->patient['uuid'], self::at($task, 'for', 'reference'));
        $this->assertSame('Organization/' . $this->people['pantry']['uuid'], self::at($task, 'owner', 'reference'));
        $this->assertSame('Practitioner/' . $this->people['doctor']['uuid'], self::at($task, 'requester', 'reference'));
        $this->assertIsString($task['authoredOn'] ?? null);

        // What the Task points at is there: the referral, with the organization as its performer.
        $serviceRequest = $this->read('ServiceRequest', $referral);
        $this->assertSame('Organization/' . $this->people['pantry']['uuid'], self::at($serviceRequest, 'performer', 0, 'reference'));
        $this->assertSame('Zq Food Pantry', self::at($serviceRequest, 'performer', 0, 'display'));
        $this->assertSame('Zq Food Pantry', $this->read('Organization', $this->people['pantry']['uuid'])['name'] ?? null);
    }

    public function testAReferralToAPractitionerGetsATaskTheyOwn(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['cardiologist']['id'], 'body' => 'Evaluate']);

        $taskUuid = $this->createTask($id);
        $this->assertIsString($taskUuid);
        $this->assertSame('Practitioner/' . $this->people['cardiologist']['uuid'], self::at($this->read('Task', $taskUuid), 'owner', 'reference'));
    }

    public function testSavingAgainAddsNoTaskAndANewRecipientAddsOne(): void
    {
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'Food insecurity']);
        $first = $this->createTask($id);
        $this->assertIsString($first);

        $this->assertNull($this->createTask($id), 'saved again, same recipient');

        QueryUtils::sqlStatementThrowException("REPLACE INTO lbt_data SET field_value = ?, form_id = ?, field_id = 'refer_to'", [$this->people['cardiologist']['id'], $id]);
        $second = $this->createTask($id);
        $this->assertIsString($second);

        $owners = [];
        foreach ($this->search('Task', ['focus' => 'ServiceRequest/' . $this->referralUuid($id)]) as $task) {
            $owners[] = self::at($task, 'owner', 'reference');
        }
        $this->assertEqualsCanonicalizing(['Organization/' . $this->people['pantry']['uuid'], 'Practitioner/' . $this->people['cardiologist']['uuid']], $owners);
    }

    public function testAReferralSavedThroughTheRestServiceGetsItsTask(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        try {
            $service = new PatientTransactionService();
            $referral = [
                'type' => 'LBTref', 'groupname' => 'Default', 'body' => 'Evaluate the rash', 'referralDate' => '2026-10-19',
                'referDiagnosis' => '', 'referByNpi' => '1765483213', 'referToNpi' => '1765483221', 'riskLevel' => 'low',
                'includeVitals' => '0', 'authorization' => '', 'visits' => '', 'validFrom' => '', 'validThrough' => '',
            ];
            $saved = $service->insert($this->patient['pid'], $referral);
            $this->assertIsArray($saved);
            $this->assertIsNumeric($saved['id'] ?? null);
            $id = (int)$saved['id'];
            $this->transactions[] = $id;
            $service->update($id, ['referToNpi' => '1765483239'] + $referral);
        } finally {
            $session->remove('authUser');
        }

        $owners = [];
        foreach ($this->search('Task', ['focus' => 'ServiceRequest/' . $this->referralUuid($id)]) as $task) {
            $owners[] = self::at($task, 'owner', 'reference');
            $this->assertSame('Practitioner/' . $this->people['rest referrer']['uuid'], self::at($task, 'requester', 'reference'));
        }
        $this->assertEqualsCanonicalizing(['Practitioner/' . $this->people['oncologist']['uuid'], 'Practitioner/' . $this->people['dermatologist']['uuid']], $owners, 'one when saved, one for the new recipient');
    }

    public function testNoTaskWithoutARecipientFhirServes(): void
    {
        $toNoNpi = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['no npi']['id'], 'body' => 'x']);
        $toNobody = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'body' => 'x']);
        $notAReferral = $this->saveTransaction('LBTptreq', ['body' => 'x']);

        $this->assertNull($this->createTask($toNoNpi), 'a specialist without an NPI');
        $this->assertNull($this->createTask($toNobody), 'no Refer To');
        $this->assertNull($this->createTask($notAReferral), 'another transaction type');
        $this->assertSame(0, $this->taskCount());
    }

    public function testNoTaskWhenTheGlobalIsOff(): void
    {
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '0');
        $id = $this->saveReferral(['refer_from' => $this->people['doctor']['id'], 'refer_to' => $this->people['pantry']['id'], 'body' => 'x']);

        $this->assertNull($this->createTask($id));
        $this->assertSame(0, $this->taskCount());
    }

    /**
     * @phpstan-impure it saves a Task, so a second call can answer differently
     */
    private function createTask(int $transactionId): ?string
    {
        return (new ReferralTaskService())->createForReferral($transactionId, null);
    }

    private function taskCount(): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM task WHERE patient_id = ?', 'n', [$this->patient['pid']]);
        return is_numeric($count) ? (int)$count : -1;
    }

    private function referralUuid(int $transactionId): string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$transactionId]);
        $this->assertIsString($uuid, 'the referral was given a uuid');
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * @param array<string, int|string> $fields lbt_data field_id => field_value
     */
    private function saveReferral(array $fields): int
    {
        return $this->saveTransaction('LBTref', $fields);
    }

    /**
     * A transaction as add_transaction.php saves a new one: no uuid, and only the fields
     * that have a value.
     *
     * @param array<string, int|string> $fields
     */
    private function saveTransaction(string $formId, array $fields): int
    {
        $id = (int)QueryUtils::sqlInsert(
            "INSERT INTO transactions SET title = ?, user = 'admin', groupname = 'Default', authorized = 1, date = NOW(), pid = ?",
            [$formId, $this->patient['pid']]
        );
        $this->transactions[] = $id;
        foreach ($fields as $field => $value) {
            QueryUtils::sqlStatementThrowException('INSERT INTO lbt_data (form_id, field_id, field_value) VALUES (?, ?, ?)', [$id, $field, (string)$value]);
        }
        return $id;
    }

    /**
     * @param array<string, string> $query
     * @return list<array<mixed>>
     */
    private function search(string $type, array $query): array
    {
        $response = $this->testClient->get(self::FHIR_URL . $type, $query);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $bundle = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($bundle);
        $resources = [];
        foreach (is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [] as $entry) {
            if (is_array($entry) && is_array($entry['resource'] ?? null)) {
                $resources[] = $entry['resource'];
            }
        }
        return $resources;
    }

    /**
     * @return array<mixed>
     */
    private function read(string $type, string $uuid): array
    {
        $response = $this->testClient->get(self::FHIR_URL . $type . '/' . $uuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $resource = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($resource);
        return $resource;
    }

    /**
     * The value at a path of keys in a decoded resource, or null.
     *
     * @param array<mixed> $resource
     */
    private static function at(array $resource, string|int ...$path): mixed
    {
        $value = $resource;
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * @param array<string, string> $columns
     * @return array{uuid: string, id: int}
     */
    private function insertPerson(array $columns): array
    {
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $row = $columns + ['username' => '', 'active' => '1', 'authorized' => '0'];
        $row['uuid'] = $uuid;
        $id = QueryUtils::sqlInsert(
            'INSERT INTO users (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')',
            array_values($row)
        );
        return ['uuid' => UuidRegistry::uuidToString($uuid), 'id' => (int)$id];
    }
}
