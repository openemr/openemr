<?php

/**
 * What an OAuth client limited to an address book entry can reach (#15137), through the real API
 * with a real token. The limit is read from the client on each request, so setting it on the test
 * client's row takes effect on its next call.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\UserService;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ReferralClientAccessFhirApiTest extends TestCase
{
    private const FHIR_URL = '/apis/default/fhir/';

    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;
    private mixed $globalBefore;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array<string, array{uuid: string, id: int}> */
    private array $people = [];

    /** @var list<int> */
    private array $transactions = [];

    /** @var list<string> */
    private array $createdTasks = [];

    /**
     * The address book entries, referrals and Tasks each test starts from.
     */
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
        $this->people['pantry'] = $this->insertOrganization('Zq Client Pantry');
        $this->people['clinic'] = $this->insertOrganization('Zq Client Clinic');
    }

    /**
     * Removes everything setUp and the test created.
     */
    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, $this->globalBefore);
        foreach ($this->transactions as $id) {
            $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
            if (is_string($uuid)) {
                foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM task WHERE focus_uuid = ?', 'uuid', [$uuid]) as $taskUuid) {
                    self::forgetUuid('task', $taskUuid);
                }
                QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE focus_uuid = ?', [$uuid]);
                self::forgetUuid('transactions', $uuid);
            }
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$id]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$id]);
        }
        foreach ($this->createdTasks as $task) {
            $bytes = UuidRegistry::uuidToBytes($task);
            QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE uuid = ?', [$bytes]);
            self::forgetUuid('task', $bytes);
        }
        foreach ($this->people as $person) {
            self::forgetUuid('users', UuidRegistry::uuidToBytes($person['uuid']));
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$person['id']]);
        }
        if (isset($this->patient)) {
            self::forgetUuid('patient_data', UuidRegistry::uuidToBytes($this->patient['uuid']));
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testASearchFindsOnlyItsOwnTasksWhateverItAsksFor(): void
    {
        ['task' => $own] = $this->referralFor('pantry');
        ['task' => $other] = $this->referralFor('clinic');
        $this->limitClientTo('pantry');

        $this->assertSame([$own], $this->ids('Task', []));
        $this->assertSame([], $this->ids('Task', ['_id' => $other]), 'by id');
        $this->assertSame([], $this->ids('Task', ['owner' => 'Organization/' . $this->people['clinic']['uuid']]), "by another's owner");
        $this->assertSame([$own], $this->ids('Task', ['_id' => $own . ',' . $other]), 'by both ids');
    }

    public function testItReadsItsOwnTaskAndNotAnothers(): void
    {
        ['task' => $own] = $this->referralFor('pantry');
        ['task' => $other] = $this->referralFor('clinic');
        $this->limitClientTo('pantry');

        $this->assertSame(Response::HTTP_OK, $this->testClient->get(self::FHIR_URL . 'Task/' . $own)->getStatusCode());
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->get(self::FHIR_URL . 'Task/' . $other)->getStatusCode());
    }

    public function testItUpdatesItsOwnTaskAndNotAnothers(): void
    {
        ['task' => $own] = $this->referralFor('pantry');
        ['task' => $other] = $this->referralFor('clinic');
        $this->limitClientTo('pantry');

        $body = $this->taskBody($own, 'accepted');
        $response = $this->put($own, $body + ['owner' => ['reference' => 'Organization/' . $this->people['pantry']['uuid']]]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $this->assertSame('accepted', $this->storedStatus($own));

        $this->assertSame(Response::HTTP_NOT_FOUND, $this->putStatus($other, 'clinic', 'accepted')->getStatusCode(), 'naming its owner');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->putStatus($other, 'pantry', 'accepted')->getStatusCode(), 'claiming it');
        $this->assertSame('requested', $this->storedStatus($other));
    }

    /**
     * A Task that isn't a referral's has no lock of its own on its owner, so the limit is what keeps it.
     */
    public function testItCannotGiveATaskToAnyoneElse(): void
    {
        $task = $this->insertGeneralTask('pantry');
        $this->limitClientTo('pantry');

        $response = $this->putStatus($task, 'clinic', 'accepted');
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string)$response->getBody());
        $this->assertStringContainsString('owner', (string)$response->getBody());
        $this->assertSame('requested', $this->storedStatus($task));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $this->put($task, $this->taskBody($task, 'accepted'))->getStatusCode(), 'dropping the owner');
    }

    /**
     * A referral is readable only while the entry is its current recipient.
     */
    public function testItReadsTheReferralsItIsSentAndNoOthers(): void
    {
        ['referral' => $own] = $this->referralFor('pantry');
        ['referral' => $other] = $this->referralFor('clinic');
        $this->limitClientTo('pantry');

        $this->assertSame([$own], $this->ids('ServiceRequest', []));
        $this->assertSame([$own], $this->ids('ServiceRequest', ['patient' => $this->patient['uuid']]));
        $this->assertSame(Response::HTTP_OK, $this->testClient->get(self::FHIR_URL . 'ServiceRequest/' . $own)->getStatusCode());
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->get(self::FHIR_URL . 'ServiceRequest/' . $other)->getStatusCode());
    }

    /**
     * An order is someone else's business however it's asked for, by id or by its category.
     */
    public function testItReadsNoProcedureOrder(): void
    {
        $this->referralFor('pantry');
        $orderUuid = UuidRegistry::getRegistryForTable('procedure_order')->createUuid();
        $orderId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_order (uuid, patient_id, provider_id, encounter_id, date_ordered, order_status, procedure_order_type, activity) VALUES (?, ?, 1, 0, NOW(), 'pending', 'laboratory_test', 1)",
            [$orderUuid, $this->patient['pid']]
        );
        try {
            $order = UuidRegistry::uuidToString($orderUuid);
            $this->assertContains($order, $this->ids('ServiceRequest', ['patient' => $this->patient['uuid']]), 'an unlimited client finds it');
            $this->limitClientTo('pantry');

            $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->get(self::FHIR_URL . 'ServiceRequest/' . $order)->getStatusCode());
            $this->assertNotContains($order, $this->ids('ServiceRequest', ['category' => '108252007']));
        } finally {
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order WHERE procedure_order_id = ?', [$orderId]);
            self::forgetUuid('procedure_order', $orderUuid);
        }
    }

    /**
     * Sent elsewhere, a referral is no longer its earlier recipient's to read, though that recipient
     * keeps its own Task, set aside, as the record of what happened.
     */
    public function testAReferralSentElsewhereIsNoLongerItsToRead(): void
    {
        ['id' => $id, 'referral' => $referral, 'task' => $task] = $this->referralFor('pantry');
        QueryUtils::sqlStatementThrowException("REPLACE INTO lbt_data SET field_value = ?, form_id = ?, field_id = 'refer_to'", [$this->people['clinic']['id'], $id]);
        (new ReferralTaskService(new UserService()))->createForReferral($id, null);
        $this->limitClientTo('pantry');

        $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->get(self::FHIR_URL . 'ServiceRequest/' . $referral)->getStatusCode());
        $this->assertSame(Response::HTTP_OK, $this->testClient->get(self::FHIR_URL . 'Task/' . $task)->getStatusCode());
    }

    /**
     * @param array<string, string> $query
     */
    #[DataProvider('routesOutsideTheLimit')]
    public function testEveryOtherRouteIsRefused(string $method, string $path, array $query): void
    {
        $this->limitClientTo('pantry');

        $url = str_replace('{patient}', $this->patient['uuid'], $path);
        $response = match ($method) {
            'GET' => $this->testClient->get($url, $query),
            default => $this->testClient->post($url, ['resourceType' => 'Task', 'status' => 'requested', 'intent' => 'order']),
        };
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), $method . ' ' . $url);
    }

    /**
     * @return array<string, array{string, string, array<string, string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function routesOutsideTheLimit(): array
    {
        return [
            'patient search' => ['GET', self::FHIR_URL . 'Patient', []],
            'patient read' => ['GET', self::FHIR_URL . 'Patient/{patient}', []],
            'a clinical resource' => ['GET', self::FHIR_URL . 'Condition', []],
            'the organizations' => ['GET', self::FHIR_URL . 'Organization', []],
            'a bulk export' => ['GET', self::FHIR_URL . '$export', []],
            'a new Task' => ['POST', self::FHIR_URL . 'Task', []],
            'a new order' => ['POST', self::FHIR_URL . 'ServiceRequest', []],
            'the standard API' => ['GET', '/apis/default/api/patient', []],
        ];
    }

    /**
     * The limit is read from the client on each request, so a token already issued follows it.
     */
    public function testRemovingTheLimitTakesEffectAtOnce(): void
    {
        ['task' => $own] = $this->referralFor('pantry');
        ['task' => $other] = $this->referralFor('clinic');
        $this->limitClientTo('pantry');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->testClient->get(self::FHIR_URL . 'Task/' . $other)->getStatusCode());

        QueryUtils::sqlStatementThrowException('UPDATE oauth_clients SET address_book_uuid = NULL WHERE client_id = ?', [$this->testClient->getClientId()]);

        $this->assertSame(Response::HTTP_OK, $this->testClient->get(self::FHIR_URL . 'Task/' . $other)->getStatusCode());
        $this->assertContains($own, $this->ids('Task', ['patient' => $this->patient['uuid']]));
    }

    /**
     * Saves the limit on the test client directly, as the admin screen would.
     */
    private function limitClientTo(string $person): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE oauth_clients SET address_book_uuid = ? WHERE client_id = ?',
            [UuidRegistry::uuidToBytes($this->people[$person]['uuid']), $this->testClient->getClientId()]
        );
    }

    /**
     * @return array{id: int, referral: string, task: string}
     */
    private function referralFor(string $person): array
    {
        $id = (int)QueryUtils::sqlInsert(
            "INSERT INTO transactions SET title = 'LBTref', user = 'admin', groupname = 'Default', authorized = 1, date = NOW(), pid = ?",
            [$this->patient['pid']]
        );
        $this->transactions[] = $id;
        QueryUtils::sqlStatementThrowException("INSERT INTO lbt_data SET form_id = ?, field_id = 'refer_to', field_value = ?", [$id, $this->people[$person]['id']]);
        $task = (new ReferralTaskService(new UserService()))->createForReferral($id, null);
        $this->assertIsString($task);
        $uuid = QueryUtils::fetchSingleValue('SELECT uuid FROM transactions WHERE id = ?', 'uuid', [$id]);
        $this->assertIsString($uuid);
        return ['id' => $id, 'referral' => UuidRegistry::uuidToString($uuid), 'task' => $task];
    }

    /**
     * A Task no referral has: owned by the entry, with nothing locking its owner but the client's limit.
     */
    private function insertGeneralTask(string $person): string
    {
        $response = $this->testClient->post(self::FHIR_URL . 'Task', [
            'resourceType' => 'Task',
            'status' => 'requested',
            'intent' => 'order',
            'code' => ['coding' => [['system' => 'http://hl7.org/fhir/CodeSystem/task-code', 'code' => 'approve']]],
            'for' => ['reference' => 'Patient/' . $this->patient['uuid']],
            'owner' => ['reference' => 'Organization/' . $this->people[$person]['uuid']],
        ]);
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getBody());
        $task = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($task);
        $this->assertIsString($task['id'] ?? null);
        $this->createdTasks[] = $task['id'];
        return $task['id'];
    }

    /**
     * The Task as it reads, with a new status and no owner.
     *
     * @return array<string, mixed>
     */
    private function taskBody(string $uuid, string $status): array
    {
        $response = $this->testClient->get(self::FHIR_URL . 'Task/' . $uuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $task = ['status' => $status];
        foreach ($body as $element => $value) {
            if (is_string($element) && !in_array($element, ['status', 'owner', 'meta'], true)) {
                $task[$element] = $value;
            }
        }
        return $task;
    }

    /**
     * Updates a Task to the status, sending the rest of it unchanged.
     */
    private function putStatus(string $uuid, string $ownerName, string $status): ResponseInterface
    {
        // Read as the unlimited client would see it: a client can't read another's Task to copy it.
        $row = QueryUtils::querySingleRow('SELECT code, code_system FROM task WHERE uuid = ?', [UuidRegistry::uuidToBytes($uuid)]);
        $this->assertIsArray($row);
        return $this->put($uuid, [
            'resourceType' => 'Task',
            'id' => $uuid,
            'status' => $status,
            'intent' => 'order',
            'code' => ['coding' => [['system' => $row['code_system'], 'code' => $row['code']]]],
            'for' => ['reference' => 'Patient/' . $this->patient['uuid']],
            'owner' => ['reference' => 'Organization/' . $this->people[$ownerName]['uuid']],
        ]);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function put(string $uuid, array $body): ResponseInterface
    {
        return $this->testClient->put(self::FHIR_URL . 'Task', $uuid, $body);
    }

    /**
     * The status a Task has in the database.
     */
    private function storedStatus(string $uuid): mixed
    {
        return QueryUtils::fetchSingleValue('SELECT status FROM task WHERE uuid = ?', 'status', [UuidRegistry::uuidToBytes($uuid)]);
    }

    /**
     * @param array<string, string> $query
     * @return list<string>
     */
    private function ids(string $type, array $query): array
    {
        $response = $this->testClient->get(self::FHIR_URL . $type, $query);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $bundle = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($bundle);
        $ids = [];
        foreach (is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [] as $entry) {
            if (is_array($entry) && is_array($entry['resource'] ?? null) && is_string($entry['resource']['id'] ?? null)) {
                $ids[] = $entry['resource']['id'];
            }
        }
        return $ids;
    }

    /**
     * @return array{uuid: string, id: int}
     */
    private function insertOrganization(string $name): array
    {
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $id = QueryUtils::sqlInsert(
            "INSERT INTO users (uuid, username, active, authorized, abook_type, organization) VALUES (?, '', 1, 0, 'external_org', ?)",
            [$uuid, $name]
        );
        return ['uuid' => UuidRegistry::uuidToString($uuid), 'id' => (int)$id];
    }

    /**
     * Removes a uuid this test registered.
     */
    private static function forgetUuid(string $table, mixed $uuid): void
    {
        if (is_string($uuid)) {
            QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
        }
    }
}
