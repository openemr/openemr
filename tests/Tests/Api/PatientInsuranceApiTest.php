<?php

/**
 * Patient Insurance API Endpoint Test Cases
 *
 * Covers the patient-compartment insurance endpoints:
 *   GET  /api/patient/{puuid}/insurance                  — list
 *   GET  /api/patient/{puuid}/insurance/{uuid}           — single
 *   POST /api/patient/{puuid}/insurance                  — create
 *   PUT  /api/patient/{puuid}/insurance/{uuid}           — update
 *   GET  /api/patient/{puuid}/insurance/$swap-insurance  — swap operation
 *   GET  /api/patient/{puuid}/insurance/{uuid}/$reopen-charges — encounters never billed to the policy's payer
 *   POST /api/patient/{puuid}/insurance/{uuid}/$reopen-charges — reopen the chosen ones
 *
 * Insurance coverage lives in `insurance_data` keyed on `pid` + `type`
 * (primary/secondary/tertiary) + `date` (effective date). This test
 * seeds a fresh patient + a fresh insurance_company via FixtureManager
 * so the policy POST doesn't collide with demo data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PatientInsuranceApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $fixturePuuid;
    private int $fixtureInsurerId;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $this->fixtureManager->installInsuranceCompanyFixture();

        $puuidBytes = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM patient_data WHERE pubpid LIKE ? ORDER BY pid DESC LIMIT 1",
            'uuid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%"]
        );
        $this->assertIsString($puuidBytes);
        $this->fixturePuuid = UuidRegistry::uuidToString($puuidBytes);

        $insurerId = QueryUtils::fetchSingleValue(
            "SELECT id FROM insurance_companies WHERE name = ? ORDER BY id DESC LIMIT 1",
            'id',
            ['test-fixture-insurer']
        );
        $this->assertIsNumeric($insurerId);
        $this->fixtureInsurerId = (int) $insurerId;
    }

    protected function tearDown(): void
    {
        // Remove in dependency order: insurance_data (coverage) rows
        // reference pid + provider (insurance_company id), so clean
        // those first. Wrap in try/finally so cleanupClient still runs.
        try {
            $this->removeBilledEncounter();
            $this->fixtureManager->removeCoverageFixtures();
            $this->fixtureManager->removeInsuranceCompanyFixtures();
            $this->fixtureManager->removePatientFixtures();
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    private function listEndpoint(): string
    {
        return "/apis/default/api/patient/" . $this->fixturePuuid . "/insurance";
    }

    /**
     * @return array{uuid: string}
     */
    private function postFixturePolicy(
        string $type = 'primary',
        string $date = '2026-01-01',
        ?string $dateEnd = null
    ): array {
        $response = $this->testClient->post($this->listEndpoint(), [
            'type' => $type,
            'provider' => (string) $this->fixtureInsurerId,
            'policy_number' => 'TEST-POLICY-' . uniqid(),
            'subscriber_lname' => 'FixtureLast',
            'subscriber_fname' => 'FixtureFirst',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Fixture Street',
            'subscriber_postal_code' => '62701',
            'subscriber_city' => 'Springfield',
            'subscriber_state' => 'IL',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => $date,
            'date_end' => $dateEnd,
        ]);
        $this->assertEquals(200, $response->getStatusCode(), 'POST should 200. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame([], $body['validationErrors'] ?? ['SENTINEL'], 'POST must produce no validation errors. Body: ' . (string) $response->getBody());
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
        return ['uuid' => $data['uuid']];
    }

    #[Test]
    public function testPostCreatesInsurancePolicy(): void
    {
        $created = $this->postFixturePolicy();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $created['uuid']
        );
    }

    /**
     * #7676: a policy created with an end date keeps it, as an update already does.
     */
    #[Test]
    public function testPostStoresTheEndDate(): void
    {
        $created = $this->postFixturePolicy('primary', '2024-09-01', '2026-09-01');

        $response = $this->testClient->getOne($this->listEndpoint(), $created['uuid']);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertSame('2024-09-01', $data['date'] ?? null);
        $this->assertSame('2026-09-01', $data['date_end'] ?? null);
    }

    #[Test]
    public function testGetAllListsCreatedPolicy(): void
    {
        $created = $this->postFixturePolicy();

        $response = $this->testClient->get($this->listEndpoint());
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $uuids = array_map(
            static fn($row): string => is_array($row) && is_string($row['uuid'] ?? null) ? (string) $row['uuid'] : '',
            $data
        );
        $this->assertContains($created['uuid'], $uuids);
    }

    #[Test]
    public function testGetOneReturnsCreatedPolicy(): void
    {
        $created = $this->postFixturePolicy();

        $response = $this->testClient->getOne($this->listEndpoint(), $created['uuid']);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertEquals($created['uuid'], $data['uuid'] ?? null);
    }

    #[Test]
    public function testPutUpdatesInsurancePolicy(): void
    {
        $created = $this->postFixturePolicy();

        $renamed = 'FixtureLast-Renamed-' . uniqid();
        $putResponse = $this->testClient->put(
            $this->listEndpoint(),
            $created['uuid'],
            [
                'type' => 'primary',
                'provider' => (string) $this->fixtureInsurerId,
                'policy_number' => 'TEST-POLICY-UPDATED',
                'subscriber_lname' => $renamed,
                'subscriber_fname' => 'FixtureFirst',
                'subscriber_relationship' => 'self',
                'subscriber_DOB' => '1980-01-01',
                'subscriber_street' => '456 Updated Street',
                'subscriber_postal_code' => '62702',
                'subscriber_city' => 'Springfield',
                'subscriber_state' => 'IL',
                'subscriber_sex' => 'Male',
                'accept_assignment' => 'TRUE',
                'date' => '2026-01-01',
                'date_end' => null,
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->getOne($this->listEndpoint(), $created['uuid']);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $data = $after['data'];
        $this->assertIsArray($data);
        $this->assertEquals($renamed, $data['subscriber_lname'] ?? null);
        $this->assertEquals('456 Updated Street', $data['subscriber_street'] ?? null);
    }

    private function swapEndpoint(): string
    {
        return $this->listEndpoint() . '/$swap-insurance';
    }

    private function fetchPolicyType(string $uuid): ?string
    {
        $response = $this->testClient->getOne($this->listEndpoint(), $uuid);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'] ?? null;
        if (!is_array($data)) {
            return null;
        }
        $type = $data['type'] ?? null;
        return is_string($type) ? $type : null;
    }

    #[Test]
    public function testSwapInsuranceSwapsPrimaryAndSecondaryTypes(): void
    {
        // Seed two policies with distinct effective dates so the swap
        // doesn't trip the pid+type+date uniqueness constraint.
        $primary = $this->postFixturePolicy('primary', '2026-01-01');
        $secondary = $this->postFixturePolicy('secondary', '2026-02-01');

        $response = $this->testClient->get($this->swapEndpoint(), [
            'type' => 'primary',
            'uuid' => $secondary['uuid'],
        ]);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Swap should 200. Body: ' . (string) $response->getBody()
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            [],
            $body['validationErrors'] ?? ['SENTINEL'],
            'Swap must produce no validation errors. Body: ' . (string) $response->getBody()
        );
        $this->assertSame([], $body['internalErrors'] ?? ['SENTINEL']);

        // Response body is a single assoc row with 'src' and 'target'
        // keys reflecting the policies after the swap.
        $data = $body['data'] ?? null;
        $this->assertIsArray($data);
        $this->assertArrayHasKey('src', $data);
        $this->assertArrayHasKey('target', $data);
        $this->assertIsArray($data['src']);
        $this->assertIsArray($data['target']);
        $this->assertSame('primary', $data['src']['type'] ?? null);
        $this->assertSame('secondary', $data['target']['type'] ?? null);
        $this->assertSame($secondary['uuid'], $data['src']['uuid'] ?? null);
        $this->assertSame($primary['uuid'], $data['target']['uuid'] ?? null);

        // Verify the swap persisted by re-reading each row.
        $this->assertSame('secondary', $this->fetchPolicyType($primary['uuid']));
        $this->assertSame('primary', $this->fetchPolicyType($secondary['uuid']));
    }

    #[Test]
    public function testSwapInsuranceWithOnlyOnePolicyMovesItToTargetType(): void
    {
        // Only a primary exists — swap to secondary. There's no target
        // policy to move the other way, so the response's 'target' slot
        // should be null and the source row simply changes type.
        $primary = $this->postFixturePolicy('primary', '2026-03-01');

        $response = $this->testClient->get($this->swapEndpoint(), [
            'type' => 'secondary',
            'uuid' => $primary['uuid'],
        ]);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Swap should 200. Body: ' . (string) $response->getBody()
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame([], $body['validationErrors'] ?? ['SENTINEL']);
        $this->assertSame([], $body['internalErrors'] ?? ['SENTINEL']);

        $data = $body['data'] ?? null;
        $this->assertIsArray($data);
        $this->assertArrayHasKey('src', $data);
        $this->assertArrayHasKey('target', $data);
        $this->assertIsArray($data['src']);
        $this->assertNull($data['target']);
        $this->assertSame('secondary', $data['src']['type'] ?? null);
        $this->assertSame($primary['uuid'], $data['src']['uuid'] ?? null);

        // Verify the policy was moved to secondary.
        $this->assertSame('secondary', $this->fetchPolicyType($primary['uuid']));
    }

    #[Test]
    public function testSwapInsuranceWithUnknownInsuranceUuidReturnsValidationError(): void
    {
        // Unknown insurance uuid — the swap validator should reject
        // without touching the database.
        $unknownUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->testClient->get($this->swapEndpoint(), [
            'type' => 'primary',
            'uuid' => $unknownUuid,
        ]);
        $this->assertEquals(
            400,
            $response->getStatusCode(),
            'Unknown uuid swap should 400. Body: ' . (string) $response->getBody()
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertIsArray($body['validationErrors']);
        $this->assertNotEmpty($body['validationErrors']);
    }

    #[Test]
    public function testPutRejectsWrongPatient(): void
    {
        // Create a policy on the setUp fixturePuuid, then try to update it
        // through a different patient's path. InsuranceService::update resolves
        // the row's owning pid and reports not-found when the pid supplied by
        // the controller (from the path puuid) does not match. The follow-up
        // GET through the owning patient's path confirms the row is intact.
        $created = $this->postFixturePolicy();

        // Pick another test-fixture patient. installPatientFixtures seeded the
        // full set; pick one whose uuid differs from fixturePuuid.
        $otherPuuidBytes = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM patient_data WHERE pubpid LIKE ? AND uuid <> ? ORDER BY pid DESC LIMIT 1",
            'uuid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%", UuidRegistry::uuidToBytes($this->fixturePuuid)]
        );
        $this->assertIsString($otherPuuidBytes);
        $otherPuuid = UuidRegistry::uuidToString($otherPuuidBytes);

        $wrongPath = "/apis/default/api/patient/" . $otherPuuid . "/insurance";
        $putResponse = $this->testClient->put($wrongPath, $created['uuid'], [
            'type' => 'primary',
            'provider' => (string) $this->fixtureInsurerId,
            'policy_number' => 'MUTATED-ACROSS-PATIENT',
            'subscriber_lname' => 'MutatedLast',
            'subscriber_fname' => 'MutatedFirst',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '000 Mutated Street',
            'subscriber_postal_code' => '00000',
            'subscriber_city' => 'Mutatedville',
            'subscriber_state' => 'XX',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2026-01-01',
            'date_end' => null,
        ]);
        // Reported as not-found rather than forbidden so a uuid probe cannot
        // confirm the existence of another patient's policy. We assert the
        // row was not mutated regardless of the exact status code.
        $this->assertNotEquals(
            200,
            $putResponse->getStatusCode(),
            'Wrong-patient PUT must not report success. Body: ' . (string) $putResponse->getBody()
        );

        $owningPath = "/apis/default/api/patient/" . $this->fixturePuuid . "/insurance";
        $follow = $this->testClient->getOne($owningPath, $created['uuid']);
        $this->assertEquals(200, $follow->getStatusCode());
        $followBody = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($followBody);
        $data = $followBody['data'] ?? null;
        $this->assertIsArray($data);
        $policyNumber = $data['policy_number'] ?? '';
        $this->assertIsString($policyNumber);
        $this->assertStringStartsWith(
            'TEST-POLICY-',
            $policyNumber,
            'Insurance policy_number must not have been overwritten through another patient\'s path.'
        );
    }

    #[Test]
    public function testSwapInsuranceWithInvalidPatientUuidReturnsValidationError(): void
    {
        // Invalid puuid string — handler short-circuits with the
        // puuid::INVALID_PUUID validation message.
        $badPuuidEndpoint = '/apis/default/api/patient/not-a-real-uuid/insurance/$swap-insurance';

        $response = $this->testClient->get($badPuuidEndpoint, [
            'type' => 'primary',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ]);
        $this->assertEquals(
            400,
            $response->getStatusCode(),
            'Invalid puuid swap should 400. Body: ' . (string) $response->getBody()
        );

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertIsArray($body['validationErrors']);
        $this->assertArrayHasKey(
            'puuid::INVALID_PUUID',
            $body['validationErrors'],
            'invalid puuid should surface the puuid::INVALID_PUUID key so a response that only fails on the unknown insurance uuid cannot silently satisfy this assertion'
        );
    }

    private const REOPEN_ENCOUNTER = 99990161;
    private const REOPEN_INSURER = 99990061;

    private function fixturePid(): int
    {
        $pid = QueryUtils::fetchSingleValue(
            'SELECT pid FROM patient_data WHERE uuid = ?',
            'pid',
            [UuidRegistry::uuidToBytes($this->fixturePuuid)]
        );
        $this->assertIsNumeric($pid);
        return (int) $pid;
    }

    /**
     * An encounter on 2026-03-01 with one 100.00 charge, marked as billed with no payer.
     */
    private function seedBilledEncounter(): void
    {
        $pid = $this->fixturePid();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason) VALUES (?, ?, '2026-03-01', 'test')",
            [$pid, self::REOPEN_ENCOUNTER]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, encounter, user, groupname, authorized, activity, "
            . "billed, bill_date, units, fee, code_text) "
            . "VALUES (NOW(), 'CPT4', '99213', ?, ?, 1, 'Default', 1, 1, 1, NOW(), 1, 100.00, 'Office visit')",
            [$pid, self::REOPEN_ENCOUNTER]
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO claims (patient_id, encounter_id, version, payer_id, status, bill_time) VALUES (?, ?, 1, 0, 2, NOW())',
            [$pid, self::REOPEN_ENCOUNTER]
        );
    }

    /**
     * A primary policy starting on the date, with an insurance company that has an id: the shared
     * fixture insurer is inserted without one (the table's id is not auto-increment), and a policy
     * whose company id is 0 names no payer to bill.
     *
     * @return string the policy's uuid
     */
    private function postPolicyWithPayer(string $date): string
    {
        $policy = $this->postFixturePolicy('primary', $date);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_companies (id, name) VALUES (?, 'test-fixture-reopen-insurer')",
            [self::REOPEN_INSURER]
        );
        QueryUtils::sqlStatementThrowException(
            'UPDATE insurance_data SET provider = ? WHERE uuid = ?',
            [self::REOPEN_INSURER, UuidRegistry::uuidToBytes($policy['uuid'])]
        );
        return $policy['uuid'];
    }

    private function removeBilledEncounter(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM claims WHERE encounter_id = ?', [self::REOPEN_ENCOUNTER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE encounter = ?', [self::REOPEN_ENCOUNTER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE encounter = ?', [self::REOPEN_ENCOUNTER]);
    }

    private function reopenEndpoint(string $policyUuid): string
    {
        return $this->listEndpoint() . '/' . $policyUuid . '/$reopen-charges';
    }

    /**
     * @return array<mixed> the response's data
     */
    private function reopenData(ResponseInterface $response): array
    {
        $this->assertEquals(200, $response->getStatusCode(), 'Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame([], $body['validationErrors'] ?? ['SENTINEL']);
        $data = $body['data'] ?? null;
        $this->assertIsArray($data);
        return $data;
    }

    #[Test]
    public function testReopenChargesListsAnEncounterBilledBeforeThePolicyWasOnFile(): void
    {
        $this->seedBilledEncounter();
        $policy = $this->postPolicyWithPayer('2026-01-01');

        $data = $this->reopenData($this->testClient->get($this->reopenEndpoint($policy)));

        $this->assertSame(
            ['encounters' => [[
                'encounter' => self::REOPEN_ENCOUNTER,
                'date' => '2026-03-01',
                'last_billed_to' => null,
                'balance' => '100.00',
            ]]],
            $data
        );
    }

    #[Test]
    public function testReopenChargesListsNothingForAPolicyThatStartsAfterTheEncounter(): void
    {
        $this->seedBilledEncounter();
        $policy = $this->postPolicyWithPayer('2026-04-01');

        $data = $this->reopenData($this->testClient->get($this->reopenEndpoint($policy)));

        $this->assertSame(['encounters' => []], $data);
    }

    #[Test]
    public function testReopenChargesPostReopensTheChosenEncounter(): void
    {
        $this->seedBilledEncounter();
        $policy = $this->postPolicyWithPayer('2026-01-01');

        $data = $this->reopenData($this->testClient->post(
            $this->reopenEndpoint($policy),
            ['encounters' => [self::REOPEN_ENCOUNTER, self::REOPEN_ENCOUNTER + 1]]
        ));

        $this->assertSame(['reopened' => [self::REOPEN_ENCOUNTER]], $data);
        // Loose on purpose: whether the driver hands back int or string is not the point.
        $this->assertEquals(0, QueryUtils::fetchSingleValue(
            'SELECT billed FROM billing WHERE encounter = ?',
            'billed',
            [self::REOPEN_ENCOUNTER]
        ));
        // Reopened, it is no longer listed.
        $after = $this->reopenData($this->testClient->get($this->reopenEndpoint($policy)));
        $this->assertSame(['encounters' => []], $after);
    }

    #[Test]
    public function testReopenChargesPostWithoutAnEncounterListIsRejected(): void
    {
        $this->seedBilledEncounter();
        $policy = $this->postPolicyWithPayer('2026-01-01');

        foreach ([['encounter' => self::REOPEN_ENCOUNTER], ['encounters' => ['abc']]] as $payload) {
            $response = $this->testClient->post($this->reopenEndpoint($policy), $payload);
            $this->assertEquals(400, $response->getStatusCode(), 'Body: ' . (string) $response->getBody());
            $body = json_decode((string) $response->getBody(), true);
            $this->assertIsArray($body);
            $this->assertIsArray($body['validationErrors'] ?? null);
            $this->assertArrayHasKey('encounters', $body['validationErrors']);
        }
        $this->assertEquals(1, QueryUtils::fetchSingleValue(
            'SELECT billed FROM billing WHERE encounter = ?',
            'billed',
            [self::REOPEN_ENCOUNTER]
        ));
    }

    #[Test]
    public function testReopenChargesWithInvalidPatientUuidReturnsValidationError(): void
    {
        $endpoint = '/apis/default/api/patient/not-a-real-uuid/insurance/00000000-0000-0000-0000-000000000000/$reopen-charges';

        foreach ([$this->testClient->get($endpoint), $this->testClient->post($endpoint, ['encounters' => [1]])] as $response) {
            $this->assertEquals(400, $response->getStatusCode(), 'Body: ' . (string) $response->getBody());
            $body = json_decode((string) $response->getBody(), true);
            $this->assertIsArray($body);
            $this->assertIsArray($body['validationErrors'] ?? null);
            $this->assertArrayHasKey('puuid::INVALID_PUUID', $body['validationErrors']);
        }
    }
}
