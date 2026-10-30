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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\PatientTransactionService;
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

    /** @var list<string> the orders made through the API, in the order they were made */
    private array $orderUuids = [];

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
        foreach ($this->orderUuids as $orderUuid) {
            $orderId = QueryUtils::fetchSingleValue('SELECT procedure_order_id FROM procedure_order WHERE uuid = ?', 'procedure_order_id', [UuidRegistry::uuidToBytes($orderUuid)]);
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order_code WHERE procedure_order_id = ?', [$orderId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order WHERE procedure_order_id = ?', [$orderId]);
        }
        $this->fixtures->removeFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testAPatientsSearchHasTheirReferralsBesideTheirOrders(): void
    {
        $order = $this->createOrder();

        $ids = self::ids($this->search(['patient' => $this->patient['uuid']]));

        $this->assertContains($this->referrals['open']['uuid'], $ids);
        $this->assertContains($this->referrals['replied']['uuid'], $ids);
        $this->assertContains($order, $ids, 'the order is still there');
        $this->assertNotContains($this->referrals['other']['uuid'], $ids, 'another patient\'s referral is not');
    }

    public function testTheCategorySelectsTheStore(): void
    {
        $order = $this->createOrder();

        $referralsOnly = self::ids($this->search(['patient' => $this->patient['uuid'], 'category' => self::REFERRAL]));
        $this->assertEqualsCanonicalizing([$this->referrals['open']['uuid'], $this->referrals['replied']['uuid']], $referralsOnly);

        $ordersOnly = self::ids($this->search(['patient' => $this->patient['uuid'], 'category' => FhirCodeSystemConstants::SNOMED_CT . '|108252007']));
        $this->assertSame([$order], $ordersOnly);

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

    /**
     * The Referral Date is a date, so a search on that day finds it: before it was compared as text,
     * and '2026-10-10' sorted before '2026-10-10 00:00:00'.
     */
    public function testAnAuthoredSearchFindsAReferralOnItsOwnDate(): void
    {
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];
        $open = $this->referrals['open']['uuid'];
        $replied = $this->referrals['replied']['uuid'];

        $this->assertSame([$open], self::ids($this->search($patient + ['authored' => '2026-10-10'])), 'eq, its own date');
        $this->assertSame([$open], self::ids($this->search($patient + ['authored' => 'ge2026-10-10'])));
        $this->assertEqualsCanonicalizing([$open, $replied], self::ids($this->search($patient + ['authored' => 'le2026-10-10'])));
        $this->assertSame([], self::ids($this->search($patient + ['authored' => 'gt2026-10-10'])));
        $this->assertSame([$replied], self::ids($this->search($patient + ['authored' => 'lt2026-10-10'])));
    }

    /**
     * Without a Referral Date there's no authoredOn: the transaction's date moves with every edit,
     * so it can't stand in for when the referral was written.
     */
    public function testAReferralWithoutAReferralDateHasNoAuthoredDate(): void
    {
        $this->referrals['undated'] = $this->insertReferral($this->patient['pid'], ['body' => 'Undated referral']);
        $this->assertArrayNotHasKey('authoredOn', $this->read($this->referrals['undated']['uuid']));

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        try {
            (new PatientTransactionService())->update($this->referrals['undated']['id'], [
                'referByNpi' => '', 'referToNpi' => '', 'body' => 'Undated referral, edited', 'referralDate' => '',
                'referDiagnosis' => '', 'riskLevel' => '', 'includeVitals' => '', 'authorization' => '', 'visits' => '',
                'validFrom' => '', 'validThrough' => '',
            ]);
        } finally {
            $session->remove('authUser');
        }

        $this->assertArrayNotHasKey('authoredOn', $this->read($this->referrals['undated']['uuid']), 'nor after an edit');
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];
        $this->assertNotContains($this->referrals['undated']['uuid'], self::ids($this->search($patient + ['authored' => 'ge2000-01-01'])));
    }

    /**
     * _count and _offset page the orders and referrals together, as the order search paged its own
     * results before referrals joined it.
     */
    public function testCountAndOffsetPageOrdersAndReferralsTogether(): void
    {
        $this->createOrder();
        $patient = ['patient' => $this->patient['uuid']];
        $all = self::ids($this->search($patient));
        $this->assertCount(3, $all, 'the order and two referrals');

        $first = self::ids($this->search($patient + ['_count' => '2']));
        $second = self::ids($this->search($patient + ['_count' => '2', '_offset' => '2']));

        $this->assertCount(2, $first, '_count=2 returns two in all, not two from each store');
        $this->assertCount(1, $second);
        $this->assertEqualsCanonicalizing($all, array_merge($first, $second), 'the two pages together are every result, once');
    }

    public function testPagesOfSeveralOrdersKeepOneOrder(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->createOrder();
        }
        $patient = ['patient' => $this->patient['uuid']];
        $all = self::ids($this->search($patient));
        $this->assertCount(6, $all, 'four orders and two referrals');
        $this->assertSame($this->orderUuids, array_slice($all, 0, 4), 'the orders come first, in the order they were made');

        $pages = [];
        foreach (['0', '2', '4'] as $offset) {
            $pages = array_merge($pages, self::ids($this->search($patient + ['_count' => '2', '_offset' => $offset])));
        }
        $this->assertSame($all, $pages, 'pages of two, read in turn, are the whole list once and in its order');
    }

    public function testAnEditThroughTheRestApiMovesTheLastUpdate(): void
    {
        // The fixtures were saved on 2026-10-15; the REST update is the path that left that date as it was.
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];
        $this->assertSame([], self::ids($this->search($patient + ['_lastUpdated' => 'gt2026-10-16'])));

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        try {
            (new PatientTransactionService())->update($this->referrals['replied']['id'], [
                'referByNpi' => '', 'referToNpi' => '', 'body' => 'Food insecurity, rescheduled', 'referralDate' => '2026-10-01',
                'referDiagnosis' => '', 'riskLevel' => '', 'includeVitals' => '', 'authorization' => '', 'visits' => '',
                'validFrom' => '', 'validThrough' => '',
            ]);
        } finally {
            $session->remove('authUser');
        }

        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['_lastUpdated' => 'gt2026-10-16'])));
    }

    public function testAFreeTextDiagnosisIsTheReasonsText(): void
    {
        // Referrer Diagnosis is a text field on a stock install, so it can hold codes, words, or both.
        QueryUtils::sqlStatementThrowException("REPLACE INTO lbt_data SET field_value = ?, form_id = ?, field_id = 'refer_diag'", ['Chest pain; ICD10:R07.9; Hypertension: follow up', $this->referrals['replied']['id']]);

        $reasons = self::at($this->read($this->referrals['replied']['uuid']), 'reasonCode');

        $this->assertIsArray($reasons);
        $this->assertCount(2, $reasons);
        $this->assertSame([[FhirCodeSystemConstants::HL7_ICD10_CM, 'R07.9']], self::codings($reasons[0]));
        $this->assertSame(['text' => 'Chest pain; Hypertension: follow up'], $reasons[1], 'words are text, not a code with no system');
    }

    public function testARestEditWithoutAReferralDateKeepsTheAuthoredDate(): void
    {
        // A PUT that leaves referralDate out (or sends it empty) reaches update() with no value
        // for it, and updateTransactionForm() skips empty values, so the stored date stays.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        try {
            (new PatientTransactionService())->update($this->referrals['replied']['id'], [
                'referByNpi' => '', 'referToNpi' => '', 'body' => 'Food insecurity, rescheduled', 'referralDate' => '',
                'referDiagnosis' => '', 'riskLevel' => '', 'includeVitals' => '', 'authorization' => '', 'visits' => '',
                'validFrom' => '', 'validThrough' => '',
            ]);
        } finally {
            $session->remove('authUser');
        }

        $referral = $this->read($this->referrals['replied']['uuid']);
        $this->assertSame('2026-10-01', $referral['authoredOn'] ?? null, 'the Referral Date, not the time of the edit');
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];
        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['_lastUpdated' => 'gt2026-10-16'])), 'while the last update moves');
        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['authored' => 'lt2026-10-05'])));
    }

    public function testAnotherPatientsReferralIsNotReadThroughThisPatient(): void
    {
        $ids = self::ids($this->search(['patient' => $this->patient['uuid'], '_id' => $this->referrals['other']['uuid']]));

        $this->assertSame([], $ids);
        $this->assertSame($this->referrals['other']['uuid'], $this->read($this->referrals['other']['uuid'])['id'] ?? null, 'it is there for a user token');
    }

    public function testLastUpdatedFiltersReferrals(): void
    {
        QueryUtils::sqlStatementThrowException("UPDATE transactions SET date = '2026-09-01 09:00:00' WHERE id = ?", [$this->referrals['replied']['id']]);
        $patient = ['patient' => $this->patient['uuid'], 'category' => self::REFERRAL];

        $this->assertSame([$this->referrals['open']['uuid']], self::ids($this->search($patient + ['_lastUpdated' => 'gt2026-10-01'])));
        $this->assertSame([$this->referrals['replied']['uuid']], self::ids($this->search($patient + ['_lastUpdated' => 'lt2026-10-01'])));
        $this->assertSame([], self::ids($this->search($patient + ['_lastUpdated' => 'lt2026-10-01', 'status' => 'active'])), 'with a filter on the grouped fields too');
    }

    public function testEachReferralHasAProvenanceWhoseAgentsAreReferences(): void
    {
        // Provenance needs a primary business entity, which a new install doesn't mark.
        $facility = QueryUtils::fetchRecords('SELECT id, primary_business_entity FROM facility ORDER BY id LIMIT 1')[0] ?? null;
        $this->assertIsArray($facility);
        QueryUtils::sqlStatementThrowException('UPDATE facility SET primary_business_entity = 1 WHERE id = ?', [$facility['id']]);
        try {
            $resources = $this->search(['patient' => $this->patient['uuid'], 'category' => self::REFERRAL, '_revinclude' => 'Provenance:target']);
        } finally {
            QueryUtils::sqlStatementThrowException('UPDATE facility SET primary_business_entity = ? WHERE id = ?', [$facility['primary_business_entity'], $facility['id']]);
        }

        $targets = [];
        foreach ($resources as $resource) {
            if (($resource['resourceType'] ?? null) !== 'Provenance') {
                continue;
            }
            foreach (is_array($resource['target'] ?? null) ? $resource['target'] : [] as $target) {
                $targets[] = is_array($target) ? ($target['reference'] ?? null) : null;
            }
            // The open referral's requester is a user FHIR doesn't serve as a Practitioner,
            // so the organization is the author rather than a name with nothing to resolve.
            foreach (is_array($resource['agent'] ?? null) ? $resource['agent'] : [] as $agent) {
                $this->assertIsString(is_array($agent) && is_array($agent['who'] ?? null) ? ($agent['who']['reference'] ?? null) : null, json_encode($resource) ?: '');
            }
        }
        $this->assertContains('ServiceRequest/' . $this->referrals['open']['uuid'], $targets);
        $this->assertContains('ServiceRequest/' . $this->referrals['replied']['uuid'], $targets);
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

    private function createOrder(): string
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
        $this->orderUuids[] = $uuid;
        return $uuid;
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
