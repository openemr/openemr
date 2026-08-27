<?php

/**
 * FHIR Appointment API tests, focused on the practitioner search parameter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cree <766378+Cree@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Cree
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for GET /fhir/Appointment search, in particular the practitioner
 * search parameter (participant actor), which filters the schedule to a
 * single provider server-side.
 *
 * Modeled on ConditionFhirApiTest. There is no POST /fhir/Appointment route,
 * so test data is seeded through AppointmentFixtureManager (patient +
 * facility) and AppointmentService::insert, and removed in tearDown.
 *
 * Two provider users are created directly (there is no practitioner fixture
 * manager); because they are created fresh for each run, an appointment
 * referencing them can only be one this test seeded, which keeps the
 * filtered-count assertions exact even on a database that already holds
 * appointments.
 */
class AppointmentFhirApiTest extends TestCase
{
    private const PROVIDER_USERNAME_PREFIX = "test-fixture-appt-provider";

    private ApiTestClient $testClient;
    private AppointmentFixtureManager $fixtureManager;
    private AppointmentService $appointmentService;

    private int $providerAId;
    private int $providerBId;
    private string $providerAUuid;
    private string $providerBUuid;

    /** FHIR uuids of the appointments seeded for provider A. */
    private array $providerAApptUuids = [];

    /** FHIR uuid of the appointment seeded for provider B. */
    private string $providerBApptUuid;

    private string $eventDate;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new AppointmentFixtureManager();
        $deps = $this->fixtureManager->installDependencies();

        $this->providerAId = $this->createProviderUser("a", "1234567893");
        $this->providerBId = $this->createProviderUser("b", "1679576722");

        // The AppointmentService constructor back-fills missing uuids for the
        // users table, which our two fresh providers need before the FHIR
        // layer can reference them.
        $this->appointmentService = new AppointmentService();
        $this->providerAUuid = $this->getUserUuid($this->providerAId);
        $this->providerBUuid = $this->getUserUuid($this->providerBId);

        // Seed three same-day appointments: two for provider A, one for B.
        $this->eventDate = date('Y-m-d');
        $base = $this->fixtureManager->getSingleAppointmentFixture($deps['facility_id']);
        $base['pc_eventDate'] = $this->eventDate;

        $first = $base;
        $first['pc_aid'] = $this->providerAId;
        $this->providerAApptUuids[] = $this->insertAppointmentReturningUuid($deps['pid'], $first);

        $second = $base;
        $second['pc_aid'] = $this->providerAId;
        $second['pc_startTime'] = '11:00';
        $this->providerAApptUuids[] = $this->insertAppointmentReturningUuid($deps['pid'], $second);

        $third = $base;
        $third['pc_aid'] = $this->providerBId;
        $third['pc_startTime'] = '12:00';
        $this->providerBApptUuid = $this->insertAppointmentReturningUuid($deps['pid'], $third);
    }

    protected function tearDown(): void
    {
        // Appointments (matched by the fixture title prefix) and their
        // patient/facility dependencies.
        $this->fixtureManager->removeFixtures();

        // The two provider users and their uuid registry entries.
        foreach ([$this->providerAId ?? null, $this->providerBId ?? null] as $userId) {
            if (empty($userId)) {
                continue;
            }
            $uuid = QueryUtils::fetchSingleValue("SELECT uuid FROM users WHERE id = ?", 'uuid', [$userId]);
            if (!empty($uuid)) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'users' AND uuid = ?",
                    [$uuid]
                );
            }
            QueryUtils::sqlStatementThrowException("DELETE FROM users WHERE id = ?", [$userId]);
        }

        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * practitioner=<uuid> should return exactly that provider's appointments.
     */
    public function testSearchByPractitionerReturnsOnlyThatProvidersAppointments(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Appointment",
            ['practitioner' => $this->providerAUuid]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertArrayHasKey("entry", $bundle);
        $this->assertIsArray($bundle['entry']);

        // Provider A exists only within this test run, so the filter must
        // return exactly the two appointments seeded for them.
        $this->assertCount(2, $bundle['entry'], "practitioner filter should return only provider A's appointments");

        $returnedIds = [];
        foreach ($bundle['entry'] as $entry) {
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertEquals("Appointment", $resource['resourceType'] ?? null);
            $returnedIds[] = $resource['id'] ?? null;
            $this->assertParticipantActor($resource, $this->providerAUuid);
        }
        sort($returnedIds);
        $expectedIds = $this->providerAApptUuids;
        sort($expectedIds);
        $this->assertSame($expectedIds, $returnedIds);
    }

    /**
     * Without the practitioner parameter, behavior is unchanged: the search
     * returns both providers' appointments.
     */
    public function testSearchWithoutPractitionerReturnsAllProviders(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Appointment",
            ['date' => $this->eventDate]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $ids = array_map(
            static fn($entry) => $entry['resource']['id'] ?? null,
            $bundle['entry'] ?? []
        );
        foreach ($this->providerAApptUuids as $uuid) {
            $this->assertContains($uuid, $ids, "unfiltered search should include provider A's appointments");
        }
        $this->assertContains($this->providerBApptUuid, $ids, "unfiltered search should include provider B's appointment");
    }

    /**
     * practitioner pointing at a valid-but-absent uuid matches nothing:
     * 200 with an empty bundle, not an error.
     */
    public function testSearchByAbsentPractitionerReturnsEmptyBundle(): void
    {
        $result = $this->testClient->get(
            "/apis/default/fhir/Appointment",
            ['practitioner' => "00000000-0000-0000-0000-000000000000"]
        );
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $bundle = json_decode((string) $result->getBody()->getContents(), true);
        $this->assertIsArray($bundle);
        $this->assertEquals("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(0, $bundle['total'] ?? null);
        $this->assertEmpty($bundle['entry'] ?? []);
    }

    private function createProviderUser(string $suffix, string $npi): int
    {
        $username = self::PROVIDER_USERNAME_PREFIX . "-" . $suffix;
        $id = QueryUtils::sqlInsert(
            "INSERT INTO users SET username = ?, fname = ?, lname = ?, npi = ?, authorized = 1, active = 1",
            [$username, "Test", "Provider" . strtoupper($suffix), $npi]
        );
        if (empty($id)) {
            throw new \RuntimeException("Failed to create provider fixture user " . $username);
        }
        return (int) $id;
    }

    private function getUserUuid(int $userId): string
    {
        $uuid = QueryUtils::fetchSingleValue("SELECT uuid FROM users WHERE id = ?", 'uuid', [$userId]);
        if (empty($uuid)) {
            throw new \RuntimeException("Provider fixture user has no uuid — did AppointmentService back-fill run?");
        }
        return UuidRegistry::uuidToString($uuid);
    }

    private function insertAppointmentReturningUuid(int $pid, array $data): string
    {
        $eid = $this->appointmentService->insert($pid, $data);
        $uuid = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM openemr_postcalendar_events WHERE pc_eid = ?",
            'uuid',
            [$eid]
        );
        if (empty($uuid)) {
            throw new \RuntimeException("Seeded appointment has no uuid");
        }
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Assert that one of the resource's participants references the given
     * practitioner uuid as its actor.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertParticipantActor(array $resource, string $practitionerUuid): void
    {
        $this->assertArrayHasKey("participant", $resource);
        $this->assertIsArray($resource['participant']);
        $actorReferences = [];
        foreach ($resource['participant'] as $participant) {
            $reference = $participant['actor']['reference'] ?? null;
            if (is_string($reference)) {
                $actorReferences[] = $reference;
            }
        }
        $this->assertContains(
            "Practitioner/" . $practitionerUuid,
            $actorReferences,
            "appointment returned by the practitioner filter should include that practitioner as a participant actor"
        );
    }
}
