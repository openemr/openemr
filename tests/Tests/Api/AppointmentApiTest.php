<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Appointment API Endpoint Test Cases.
 *
 * Covers the Standard REST API read endpoints:
 *   GET /api/appointment        – list all appointments
 *   GET /api/appointment/:eid   – retrieve a single appointment by event id
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

        /** @var array<string, mixed> $responseBody */
        $responseBody = json_decode((string) $postResponse->getBody(), true);
        $appointmentId = $responseBody['id'];
        $this->assertIsInt($appointmentId);
        $this->appointmentEid = $appointmentId;
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
        $this->assertContains($this->appointmentEid, $eids);
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
}
