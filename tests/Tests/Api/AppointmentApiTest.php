<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Appointment API Endpoint Test Cases.
 *
 * Covers the Standard REST API endpoints:
 *   GET    /api/appointment               – list all appointments
 *   GET    /api/appointment/:eid          – retrieve a single appointment
 *   GET    /api/patient/:pid/appointment       – list for a patient
 *   GET    /api/patient/:pid/appointment/:eid  – single for a patient
 *   POST   /api/patient/:pid/appointment       – create (exercised via setUp)
 *   DELETE /api/patient/:pid/appointment/:eid  – delete
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Miguel Montano <miiguel2m@gmail.com>
 * @copyright Copyright (c) 2026 Miguel Montano
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class AppointmentApiTest extends TestCase
{
    const APPOINTMENT_API_ENDPOINT = "/apis/default/api/appointment";
    const PATIENT_APPOINTMENT_API_ENDPOINT = "/apis/default/api/patient";

    private ApiTestClient $testClient;
    private AppointmentFixtureManager $fixtureManager;
    private int $appointmentEid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new AppointmentFixtureManager();
        $dependencies = $this->fixtureManager->installDependencies();

        $pid = $dependencies['pid'];
        $facilityId = $dependencies['facility_id'];
        $appointmentData = $this->fixtureManager->getSingleAppointmentFixture($facilityId);

        $postResponse = $this->testClient->post(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$pid}/appointment",
            $appointmentData
        );
        $this->assertEquals(200, $postResponse->getStatusCode());

        /** @var array{id: int|string} $responseBody */
        $responseBody = json_decode((string) $postResponse->getBody(), true);
        $this->appointmentEid = (int) $responseBody['id'];
        $this->assertGreaterThan(0, $this->appointmentEid);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    #[Test]
    public function testGetAppointmentList(): void
    {
        $actualResponse = $this->testClient->get(self::APPOINTMENT_API_ENDPOINT);

        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var list<array<string, mixed>> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertNotEmpty($responseBody);

        $eids = array_column($responseBody, 'pc_eid');
        $this->assertContains((string) $this->appointmentEid, $eids);
    }

    #[Test]
    public function testGetAppointmentById(): void
    {
        $actualResponse = $this->testClient->getOne(
            self::APPOINTMENT_API_ENDPOINT,
            (string) $this->appointmentEid
        );

        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var list<array<string, mixed>> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertNotEmpty($responseBody);

        $appointment = $responseBody[0];
        $this->assertEquals($this->appointmentEid, $appointment['pc_eid']);
        $this->assertArrayHasKey('pc_title', $appointment);
        $this->assertArrayHasKey('pc_eventDate', $appointment);
        $this->assertArrayHasKey('pc_startTime', $appointment);
        $this->assertArrayHasKey('pc_apptstatus', $appointment);
        $this->assertArrayHasKey('pc_duration', $appointment);
    }

    #[Test]
    public function testGetAppointmentByIdNotFound(): void
    {
        $actualResponse = $this->testClient->getOne(self::APPOINTMENT_API_ENDPOINT, "999999999");

        $this->assertEquals(404, $actualResponse->getStatusCode());
    }

    #[Test]
    public function testGetAppointmentUnauthorized(): void
    {
        $this->testClient->removeAuthToken();
        $actualResponse = $this->testClient->get(self::APPOINTMENT_API_ENDPOINT);

        $this->assertEquals(401, $actualResponse->getStatusCode());
    }

    #[Test]
    public function testGetAppointmentListForPatient(): void
    {
        // Patient-compartment list route. Separate from the top-level
        // /api/appointment: this one is scoped to the fixture pid and
        // uses the int pid (not uuid) in the path.
        $dependencies = $this->fixtureManager->installDependencies();
        $pid = $dependencies['pid'];

        // Create a second appointment for a different fixture patient so
        // the test can verify the pid filter actually filters — without
        // this, a regression that drops the WHERE clause would still
        // see the fixture appointment in the response and pass.
        $otherPatient = QueryUtils::querySingleRow(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ? AND pid <> ? LIMIT 1",
            [AppointmentFixtureManager::FIXTURE_PREFIX . "%", $pid]
        );
        $this->assertIsArray($otherPatient);
        $this->assertIsNumeric($otherPatient['pid']);
        $otherPid = (int) $otherPatient['pid'];
        $otherAppointmentResponse = $this->testClient->post(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$otherPid}/appointment",
            $this->fixtureManager->getSecondAppointmentFixture((int) $dependencies['facility_id'])
        );
        $this->assertEquals(200, $otherAppointmentResponse->getStatusCode());
        $otherAppointmentBody = json_decode((string) $otherAppointmentResponse->getBody(), true);
        $this->assertIsArray($otherAppointmentBody);
        $this->assertIsNumeric($otherAppointmentBody['id'] ?? null);
        $otherAppointmentEid = (int) $otherAppointmentBody['id'];

        $actualResponse = $this->testClient->get(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$pid}/appointment"
        );
        $this->assertEquals(200, $actualResponse->getStatusCode());

        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertIsArray($responseBody);
        $this->assertNotEmpty($responseBody);
        // The patient-compartment route returns pc_eid as an int (unlike
        // the top-level `/api/appointment` route which serializes it as
        // a string). Narrow to a list of int before match.
        $eids = array_map(
            static fn($row): int => is_array($row) && is_numeric($row['pc_eid'] ?? null) ? (int) $row['pc_eid'] : -1,
            $responseBody
        );
        $this->assertContains($this->appointmentEid, $eids);
        $this->assertNotContains(
            $otherAppointmentEid,
            $eids,
            'the pid-scoped route must not surface another patient\'s appointment'
        );

        // An appointment posted through the pid with a leading zero is stored as the plain pid, so the
        // patient's list finds it.
        $zeroPostResponse = $this->testClient->post(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/0{$pid}/appointment",
            $this->fixtureManager->getSecondAppointmentFixture($dependencies['facility_id'])
        );
        $this->assertEquals(200, $zeroPostResponse->getStatusCode());
        $zeroPostBody = json_decode((string) $zeroPostResponse->getBody(), true);
        $this->assertIsArray($zeroPostBody);
        $this->assertIsNumeric($zeroPostBody['id'] ?? null);
        $zeroPostEid = (int) $zeroPostBody['id'];
        $this->assertSame(
            (string) $pid,
            QueryUtils::fetchSingleValue('SELECT pc_pid FROM openemr_postcalendar_events WHERE pc_eid = ?', 'pc_pid', [$zeroPostEid])
        );
        $plainEids = $this->listedEids("/{$pid}/appointment");
        $this->assertContains($zeroPostEid, $plainEids);

        // The pid with a leading zero names the same patient, as the numeric comparison found it.
        $this->assertSame($plainEids, $this->listedEids("/0{$pid}/appointment"));
    }

    #[Test]
    public function testGetAppointmentByIdForPatient(): void
    {
        // Patient-compartment single-GET. The route handler delegates to
        // AppointmentRestController::getOne with a pid-scope wrapper, so
        // the happy-path response shape matches testGetAppointmentById.
        $dependencies = $this->fixtureManager->installDependencies();
        $pid = $dependencies['pid'];

        $actualResponse = $this->testClient->getOne(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$pid}/appointment",
            (string) $this->appointmentEid
        );
        $this->assertEquals(200, $actualResponse->getStatusCode());

        /** @var list<array<string, mixed>> $responseBody */
        $responseBody = json_decode((string) $actualResponse->getBody(), true);
        $this->assertNotEmpty($responseBody);
        $this->assertEquals($this->appointmentEid, $responseBody[0]['pc_eid']);
    }

    #[Test]
    public function testGetAppointmentByIdForPatientRejectsWrongPatient(): void
    {
        // Patient-compartment single-GET must honor the path pid: an eid
        // that belongs to a different patient must not be reachable by
        // substituting it under another patient's compartment. The
        // route handler validates the fetched record's pid against the
        // path pid and returns 404 when they diverge.
        $dependencies = $this->fixtureManager->installDependencies();
        $pid = $dependencies['pid'];

        $otherPatient = QueryUtils::querySingleRow(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ? AND pid <> ? LIMIT 1",
            [AppointmentFixtureManager::FIXTURE_PREFIX . "%", $pid]
        );
        $this->assertIsArray($otherPatient);
        $this->assertIsNumeric($otherPatient['pid']);
        $otherPid = (int) $otherPatient['pid'];

        $actualResponse = $this->testClient->getOne(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$otherPid}/appointment",
            (string) $this->appointmentEid
        );
        $this->assertEquals(
            404,
            $actualResponse->getStatusCode(),
            'wrong-pid compartment GET must not return another patient\'s appointment'
        );
    }

    #[Test]
    public function testDeleteAppointment(): void
    {
        // setUp created the fixture appointment at $this->appointmentEid via
        // POST /api/patient/:pid/appointment. Verify the DELETE round-trip:
        // the single-GET returns 200 while the row exists, DELETE returns
        // 200, and a follow-up single-GET returns 404.
        $dependencies = $this->fixtureManager->installDependencies();
        $pid = $dependencies['pid'];

        $preDelete = $this->testClient->getOne(
            self::APPOINTMENT_API_ENDPOINT,
            (string) $this->appointmentEid
        );
        $this->assertEquals(200, $preDelete->getStatusCode(), 'fixture appointment should be present before delete');

        $deleteResponse = $this->testClient->delete(
            self::PATIENT_APPOINTMENT_API_ENDPOINT . "/{$pid}/appointment",
            $this->appointmentEid
        );
        $this->assertEquals(200, $deleteResponse->getStatusCode());

        $postDelete = $this->testClient->getOne(
            self::APPOINTMENT_API_ENDPOINT,
            (string) $this->appointmentEid
        );
        $this->assertEquals(404, $postDelete->getStatusCode(), 'deleted appointment should 404 on subsequent single-GET');
    }

    /**
     * The sorted pc_eids a patient appointment list returns.
     *
     * @return list<int>
     */
    private function listedEids(string $path): array
    {
        $response = $this->testClient->get(self::PATIENT_APPOINTMENT_API_ENDPOINT . $path);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $eids = array_values(array_map(
            static fn($row): int => is_array($row) && is_numeric($row['pc_eid'] ?? null) ? (int) $row['pc_eid'] : -1,
            $body
        ));
        sort($eids);
        return $eids;
    }
}
