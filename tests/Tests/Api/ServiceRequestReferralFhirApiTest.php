<?php

/**
 * FHIR ServiceRequest API tests for referrals: the Transactions screen's referral form
 * (LBTref), served beside the orders from procedure_order (D8 of #13266).
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for GET /fhir/ServiceRequest and GET /fhir/ServiceRequest/:uuid with referrals.
 *
 * The referrals are seeded straight into transactions and lbt_data, as the
 * Transactions screen saves them; the order is made through the API. All are
 * removed in tearDown, with the address book entries and the two fixture patients.
 */
class ServiceRequestReferralFhirApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/ServiceRequest';
    private const REFERRAL = FhirCodeSystemConstants::SNOMED_CT . '|3457005';

    private ApiTestClient $testClient;
    private ConditionFixtureManager $fixtures;

    /** @var array{uuid: string, pid: int} */
    private array $patient;

    /** @var array{uuid: string, pid: int} */
    private array $otherPatient;

    /** @var array<string, array{uuid: string, id: int}> referrals by name */
    private array $referrals = [];

    /** @var array<string, array{uuid: string, id: int}> address book entries by name */
    private array $people = [];

    private ?string $orderUuid = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->fixtures = new ConditionFixtureManager();
        $this->patient = self::patientOf($this->fixtures->createTestPatient());
        $this->otherPatient = self::patientOf($this->fixtures->createTestPatient());

        // A specialist FHIR serves as a Practitioner (an NPI and an address book type
        // PractitionerService includes), and a community organization it doesn't.
        $this->people['cardiologist'] = $this->insertPerson(['fname' => 'Cara', 'lname' => 'Heart', 'npi' => '1234567893', 'abook_type' => 'spe']);
        $this->people['pantry'] = $this->insertPerson(['organization' => 'Northside Food Pantry', 'abook_type' => 'spe']);

        $this->referrals['open'] = $this->insertReferral($this->patient['pid'], [
            'refer_date' => '2026-10-10',
            'refer_from' => '1',
            'refer_to' => (string)$this->people['cardiologist']['id'],
            'body' => 'Evaluate for hypertension',
            'refer_diag' => 'ICD10:I10',
            'refer_related_code' => 'CPT4:99243',
        ]);
        $this->referrals['replied'] = $this->insertReferral($this->patient['pid'], [
            'refer_date' => '2026-10-01',
            'refer_to' => (string)$this->people['pantry']['id'],
            'body' => 'Food insecurity',
            'reply_date' => '2026-10-12',
        ]);
        $this->referrals['other'] = $this->insertReferral($this->otherPatient['pid'], ['refer_date' => '2026-10-05', 'body' => 'Other patient']);
    }

    protected function tearDown(): void
    {
        foreach ($this->referrals as $referral) {
            QueryUtils::sqlStatementThrowException('DELETE FROM lbt_data WHERE form_id = ?', [$referral['id']]);
            QueryUtils::sqlStatementThrowException('DELETE FROM transactions WHERE id = ?', [$referral['id']]);
        }
        foreach ($this->people as $person) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$person['id']]);
        }
        if ($this->orderUuid !== null) {
            $orderId = QueryUtils::fetchSingleValue('SELECT procedure_order_id FROM procedure_order WHERE uuid = ?', 'procedure_order_id', [UuidRegistry::uuidToBytes($this->orderUuid)]);
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order_code WHERE procedure_order_id = ?', [$orderId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order WHERE procedure_order_id = ?', [$orderId]);
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testAPatientsSearchHasTheirReferralsBesideTheirOrders(): void
    {
        $this->createOrder();

        $ids = self::ids($this->search(['patient' => $this->patient['uuid']]));

        $this->assertContains($this->referrals['open']['uuid'], $ids);
        $this->assertContains($this->referrals['replied']['uuid'], $ids);
        $this->assertContains($this->orderUuid, $ids, 'the order is still there');
        $this->assertNotContains($this->referrals['other']['uuid'], $ids, 'another patient\'s referral is not');
    }

    public function testTheCategorySelectsTheStore(): void
    {
        $this->createOrder();

        $referralsOnly = self::ids($this->search(['patient' => $this->patient['uuid'], 'category' => self::REFERRAL]));
        $this->assertEqualsCanonicalizing([$this->referrals['open']['uuid'], $this->referrals['replied']['uuid']], $referralsOnly);

        $ordersOnly = self::ids($this->search(['patient' => $this->patient['uuid'], 'category' => FhirCodeSystemConstants::SNOMED_CT . '|108252007']));
        $this->assertSame([$this->orderUuid], $ordersOnly);

        $both = self::ids($this->search(['patient' => $this->patient['uuid'], 'category' => '108252007,3457005']));
        $this->assertCount(3, $both);
    }

    public function testAReferralReadsAsAUsCoreServiceRequest(): void
    {
        $response = $this->testClient->get(self::RESOURCE_URL . '/' . $this->referrals['open']['uuid']);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $resource = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($resource);

        $this->assertSame('ServiceRequest', $resource['resourceType'] ?? null);
        $this->assertSame(['http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest'], self::at($resource, 'meta', 'profile'));
        $this->assertSame('active', $resource['status'] ?? null, 'no reply date yet');
        $this->assertSame('order', $resource['intent'] ?? null);
        $this->assertSame([[FhirCodeSystemConstants::SNOMED_CT, '3457005']], self::codings(self::at($resource, 'category', 0)));
        $this->assertSame([[FhirCodeSystemConstants::AMA_CPT, '99243']], self::codings(self::at($resource, 'code')), 'the requested service');
        $this->assertSame('Patient/' . $this->patient['uuid'], self::at($resource, 'subject', 'reference'));
        $this->assertSame('2026-10-10', $resource['authoredOn'] ?? null);
        $this->assertSame('Practitioner/' . $this->people['cardiologist']['uuid'], self::at($resource, 'performer', 0, 'reference'));
        $this->assertSame([[FhirCodeSystemConstants::HL7_ICD10_CM, 'I10']], self::codings(self::at($resource, 'reasonCode', 0)));
        $this->assertSame('Evaluate for hypertension', self::at($resource, 'note', 0, 'text'));
    }

    public function testARepliedReferralIsCompletedAndOneWithNoServiceUsesTheReferralCode(): void
    {
        $resource = $this->read($this->referrals['replied']['uuid']);

        $this->assertSame('completed', $resource['status'] ?? null);
        $this->assertSame([[FhirCodeSystemConstants::SNOMED_CT, '3457005']], self::codings(self::at($resource, 'code')));
        // The food pantry has no NPI, so FHIR has no Practitioner for it: a name, not a reference.
        $this->assertSame('Northside Food Pantry', self::at($resource, 'performer', 0, 'display'));
        $this->assertNull(self::at($resource, 'performer', 0, 'reference'));
        $this->assertArrayNotHasKey('requester', $resource, 'no referrer was recorded');
    }

    public function testStatusCodeAndAuthoredFiltersApplyToReferrals(): void
    {
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];

        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['status' => 'completed'])));
        $this->assertSame([$this->referrals['open']['uuid']], self::ids($this->search($patient + ['status' => 'active'])));
        $this->assertSame([$this->referrals['open']['uuid']], self::ids($this->search($patient + ['code' => FhirCodeSystemConstants::AMA_CPT . '|99243'])));
        $this->assertSame([], self::ids($this->search($patient + ['code' => FhirCodeSystemConstants::SNOMED_CT . '|99243'])), 'a code in another system');
        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['code' => self::REFERRAL])));
        $this->assertSame([$this->referrals['open']['uuid']], self::ids($this->search($patient + ['authored' => 'ge2026-10-05'])));
    }

    public function testAnotherPatientsReferralIsNotReadThroughThisPatient(): void
    {
        $ids = self::ids($this->search(['patient' => $this->patient['uuid'], '_id' => $this->referrals['other']['uuid']]));

        $this->assertSame([], $ids);
        $this->assertSame($this->referrals['other']['uuid'], $this->read($this->referrals['other']['uuid'])['id'] ?? null, 'it is there for a user token');
    }

    /**
     * @param array<string, string> $query
     * @return list<array<mixed>> the resources in the bundle
     */
    private function search(array $query): array
    {
        $response = $this->testClient->get(self::RESOURCE_URL, $query);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
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
    private function read(string $uuid): array
    {
        $response = $this->testClient->get(self::RESOURCE_URL . '/' . $uuid);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getBody());
        $resource = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($resource);
        return $resource;
    }

    private function createOrder(): void
    {
        $fixture = json_decode((string)file_get_contents(__DIR__ . '/../Fixtures/FHIR/service-request.json'), true);
        $this->assertIsArray($fixture);
        $order = $fixture[0];
        $this->assertIsArray($order);
        unset($order['id']);
        $order['subject'] = ['reference' => 'Patient/' . $this->patient['uuid']];
        $body = [];
        foreach ($order as $key => $value) {
            $body[(string)$key] = $value;
        }
        $response = $this->testClient->post(self::RESOURCE_URL, $body);
        $this->assertContains($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED], (string)$response->getBody());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $uuid = $body['uuid'] ?? $body['id'] ?? null;
        $this->assertIsString($uuid);
        $this->orderUuid = $uuid;
    }

    /**
     * @param list<array<mixed>> $resources
     * @return list<string>
     */
    private static function ids(array $resources): array
    {
        $ids = [];
        foreach ($resources as $resource) {
            if (is_string($resource['id'] ?? null)) {
                $ids[] = $resource['id'];
            }
        }
        return $ids;
    }

    /**
     * The value at a path of keys in decoded JSON, or null when any step is missing.
     */
    private static function at(mixed $value, string|int ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * @return list<array{string, string}>
     */
    private static function codings(mixed $concept): array
    {
        $out = [];
        $codings = self::at($concept, 'coding');
        foreach (is_array($codings) ? $codings : [] as $coding) {
            $system = self::at($coding, 'system');
            $code = self::at($coding, 'code');
            if (is_string($system) && is_string($code)) {
                $out[] = [$system, $code];
            }
        }
        return $out;
    }

    /**
     * @param array<mixed> $patient
     * @return array{uuid: string, pid: int}
     */
    private static function patientOf(array $patient): array
    {
        $uuid = $patient['uuid'] ?? null;
        $pid = $patient['pid'] ?? null;
        if (!is_string($uuid) || !is_int($pid)) {
            throw new \RuntimeException('the fixture patient has no uuid or pid');
        }
        return ['uuid' => $uuid, 'pid' => $pid];
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

    /**
     * @param array<string, string> $fields lbt_data field_id => field_value
     * @return array{uuid: string, id: int}
     */
    private function insertReferral(int $pid, array $fields): array
    {
        $uuid = UuidRegistry::getRegistryForTable('transactions')->createUuid();
        $id = QueryUtils::sqlInsert(
            "INSERT INTO transactions (uuid, date, title, pid, user, groupname, authorized) VALUES (?, '2026-10-15 09:00:00', 'LBTref', ?, 'admin', 'Default', 1)",
            [$uuid, $pid]
        );
        foreach ($fields as $field => $value) {
            QueryUtils::sqlStatementThrowException('INSERT INTO lbt_data (form_id, field_id, field_value) VALUES (?, ?, ?)', [$id, $field, $value]);
        }
        return ['uuid' => UuidRegistry::uuidToString($uuid), 'id' => (int)$id];
    }
}
