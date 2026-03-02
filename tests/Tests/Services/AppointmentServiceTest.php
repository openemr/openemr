<?php

/**
 * Appointment Service Tests
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\AppointmentService;
use OpenEMR\Tests\Fixtures\AppointmentFixtureManager;
use Particle\Validator\ValidationResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentServiceTest extends TestCase
{
    /**
     * @var AppointmentService
     */
    private $appointmentService;

    /**
     * @var AppointmentFixtureManager
     */
    private $fixtureManager;

    /**
     * @var int Patient ID from test fixtures
     */
    private int $testPid;

    /**
     * @var int Facility ID from test fixtures
     */
    private int $testFacilityId;

    /**
     * @var array<string, mixed> Appointment data template for tests
     */
    private array $appointmentData;

    protected function setUp(): void
    {
        $this->appointmentService = new AppointmentService();
        $this->fixtureManager = new AppointmentFixtureManager();

        // Install patient and facility dependencies
        $deps = $this->fixtureManager->installDependencies();
        $this->testPid = $deps['pid'];
        $this->testFacilityId = $deps['facility_id'];

        // Get a valid appointment data template
        $this->appointmentData = $this->fixtureManager->getSingleAppointmentFixture($this->testFacilityId);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    #[Test]
    public function testValidateSuccess(): void
    {
        $result = $this->appointmentService->validate($this->appointmentData);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid(), "Validation should pass with complete appointment data");
    }

    #[Test]
    public function testValidateFailureMissingRequiredFields(): void
    {
        // Empty data should fail validation for all required fields
        $result = $this->appointmentService->validate([]);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid(), "Validation should fail with empty data");

        $messages = $result->getMessages();
        // All required fields should have validation errors
        $this->assertArrayHasKey('pc_catid', $messages);
        $this->assertArrayHasKey('pc_title', $messages);
        $this->assertArrayHasKey('pc_duration', $messages);
        $this->assertArrayHasKey('pc_hometext', $messages);
        $this->assertArrayHasKey('pc_apptstatus', $messages);
        $this->assertArrayHasKey('pc_eventDate', $messages);
        $this->assertArrayHasKey('pc_startTime', $messages);
        $this->assertArrayHasKey('pc_facility', $messages);
        $this->assertArrayHasKey('pc_billing_location', $messages);
    }

    #[Test]
    public function testValidateFailureTitleTooShort(): void
    {
        $data = $this->appointmentData;
        $data['pc_title'] = 'A'; // Too short — minimum is 2 characters

        $result = $this->appointmentService->validate($data);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid(), "Validation should fail when title is too short");
        $messages = $result->getMessages();
        $this->assertArrayHasKey('pc_title', $messages);
    }

    #[Test]
    public function testValidateFailureInvalidDateFormat(): void
    {
        $data = $this->appointmentData;
        $data['pc_eventDate'] = '13/25/2026'; // Invalid format — expects Y-m-d

        $result = $this->appointmentService->validate($data);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid(), "Validation should fail with invalid date format");
        $messages = $result->getMessages();
        $this->assertArrayHasKey('pc_eventDate', $messages);
    }

    #[Test]
    public function testValidateFailureInvalidStartTimeLength(): void
    {
        $data = $this->appointmentData;
        $data['pc_startTime'] = '10:00:00'; // 8 chars — expects exactly 5 (HH:MM)

        $result = $this->appointmentService->validate($data);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid(), "Validation should fail when start time is not exactly 5 characters");
        $messages = $result->getMessages();
        $this->assertArrayHasKey('pc_startTime', $messages);
    }

    #[Test]
    public function testInsertSuccess(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $this->assertIsInt($insertId, "Insert should return an integer ID");
        $this->assertGreaterThan(0, $insertId, "Insert ID should be positive");

        // Verify the appointment was actually created
        $appointment = $this->appointmentService->getAppointment($insertId);
        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment, "Should retrieve the inserted appointment");
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertEquals($this->testPid, $row['pid']);
        $this->assertEquals($this->appointmentData['pc_title'], $row['pc_title']);
        $this->assertEquals($this->appointmentData['pc_eventDate'], $row['pc_eventDate']);
        $this->assertEquals($this->appointmentData['pc_duration'], $row['pc_duration']);
    }

    #[Test]
    public function testInsertSetsCorrectStartAndEndTime(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $appointment = $this->appointmentService->getAppointment($insertId);

        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        // pc_startTime should be the formatted time value
        $this->assertEquals('10:00:00', $row['pc_startTime']);
        // pc_endTime should be startTime + duration (900s = 15min => 10:15:00)
        $this->assertEquals('10:15:00', $row['pc_endTime']);
    }

    #[Test]
    public function testInsertWithOptionalWebsite(): void
    {
        $data = $this->appointmentData;
        $data['pc_website'] = 'https://example.com/telehealth';

        $insertId = $this->appointmentService->insert($this->testPid, $data);
        $appointment = $this->appointmentService->getAppointment($insertId);

        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertEquals('https://example.com/telehealth', $row['pc_website']);
    }

    #[Test]
    public function testGetAppointmentReturnsEmptyForNonExistent(): void
    {
        $result = $this->appointmentService->getAppointment(999999999);
        $this->assertEmpty($result, "Should return empty array for non-existent appointment ID");
    }

    #[Test]
    public function testGetAppointmentsForPatient(): void
    {
        // Insert two appointments for the same patient
        $this->appointmentService->insert($this->testPid, $this->appointmentData);

        $secondData = $this->fixtureManager->getSecondAppointmentFixture($this->testFacilityId);
        $this->appointmentService->insert($this->testPid, $secondData);

        $results = $this->appointmentService->getAppointmentsForPatient($this->testPid);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results), "Should return at least 2 appointments for the test patient");

        // Verify both fixtures are present
        $titles = array_column($results, 'pc_title');
        $this->assertContains($this->appointmentData['pc_title'], $titles);
        $this->assertContains($secondData['pc_title'], $titles);
    }

    #[Test]
    public function testGetAppointmentsForPatientReturnsEmptyForNoAppointments(): void
    {
        // Use a PID that has no appointments
        $results = $this->appointmentService->getAppointmentsForPatient(999999999);
        $this->assertIsArray($results);
        $this->assertEmpty($results, "Should return empty array for patient with no appointments");
    }

    #[Test]
    public function testDeleteAppointmentRecord(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $this->assertGreaterThan(0, $insertId);

        // Verify it exists
        $appointment = $this->appointmentService->getAppointment($insertId);
        $this->assertNotEmpty($appointment);

        // Delete it
        $this->appointmentService->deleteAppointmentRecord($insertId);

        // Verify it's gone
        $appointment = $this->appointmentService->getAppointment($insertId);
        $this->assertEmpty($appointment, "Appointment should be deleted");
    }

    #[Test]
    public function testGetCalendarCategories(): void
    {
        $categories = $this->appointmentService->getCalendarCategories();
        $this->assertNotEmpty($categories, "Should return at least one calendar category");

        // Every category should have required fields
        $firstCategory = $categories[0];
        $this->assertIsArray($firstCategory);
        $this->assertArrayHasKey('pc_catid', $firstCategory);
        $this->assertArrayHasKey('pc_catname', $firstCategory);
        $this->assertArrayHasKey('pc_constant_id', $firstCategory);
    }

    #[Test]
    public function testGetCalendarCategoriesContainsOfficeVisit(): void
    {
        $categories = $this->appointmentService->getCalendarCategories();
        $constantIds = array_column($categories, 'pc_constant_id');
        $this->assertContains('office_visit', $constantIds, "Categories should include 'office_visit'");
    }

    #[Test]
    public function testGetOneCalendarCategory(): void
    {
        // pc_catid 5 = office_visit in default install
        $result = $this->appointmentService->getOneCalendarCategory(5);
        $this->assertNotEmpty($result);
        $row = $result[0];
        $this->assertIsArray($row);
        $this->assertEquals('office_visit', $row['pc_constant_id']);
        $this->assertEquals('Office Visit', $row['pc_catname']);
    }

    #[Test]
    public function testGetOneCalendarCategoryReturnsEmptyForInvalid(): void
    {
        $result = $this->appointmentService->getOneCalendarCategory(999999);
        $this->assertEmpty($result, "Should return empty for non-existent category ID");
    }

    #[Test]
    public function testGetAppointmentStatuses(): void
    {
        $statuses = $this->appointmentService->getAppointmentStatuses();
        $this->assertNotEmpty($statuses, "Should return appointment statuses");

        // Check that common statuses are present
        $optionIds = array_column($statuses, 'option_id');
        $this->assertContains('-', $optionIds, "Should contain the 'None' status");
        $this->assertContains('@', $optionIds, "Should contain the 'Arrived' status");
        $this->assertContains('>', $optionIds, "Should contain the 'Checked out' status");
    }

    #[Test]
    public function testIsValidAppointmentStatus(): void
    {
        // '-' (None) is a default status that always exists
        $this->assertTrue(
            $this->appointmentService->isValidAppointmentStatus('-'),
            "'-' should be a valid appointment status"
        );

        // '@' (Arrived) is a default status
        $this->assertTrue(
            $this->appointmentService->isValidAppointmentStatus('@'),
            "'@' should be a valid appointment status"
        );

        // A non-existent status should be invalid
        $this->assertFalse(
            $this->appointmentService->isValidAppointmentStatus('NONEXISTENT_STATUS'),
            "A random string should not be a valid appointment status"
        );
    }

    #[Test]
    public function testIsCheckInStatus(): void
    {
        // '@' (Arrived) has toggle_setting_1 = 1, so it is a check-in status
        $this->assertTrue(
            AppointmentService::isCheckInStatus('@'),
            "'@' (Arrived) should be a check-in status"
        );

        // '~' (Arrived late) also has toggle_setting_1 = 1
        $this->assertTrue(
            AppointmentService::isCheckInStatus('~'),
            "'~' (Arrived late) should be a check-in status"
        );

        // '-' (None) does not have toggle_setting_1 = 1
        $this->assertFalse(
            AppointmentService::isCheckInStatus('-'),
            "'-' (None) should not be a check-in status"
        );
    }

    #[Test]
    public function testIsCheckOutStatus(): void
    {
        // '>' (Checked out) has toggle_setting_2 = 1, so it is a check-out status
        $this->assertTrue(
            AppointmentService::isCheckOutStatus('>'),
            "'>' (Checked out) should be a check-out status"
        );

        // '!' (Left w/o visit) also has toggle_setting_2 = 1
        $this->assertTrue(
            AppointmentService::isCheckOutStatus('!'),
            "'!' (Left w/o visit) should be a check-out status"
        );

        // '-' (None) does not have toggle_setting_2 = 1
        $this->assertFalse(
            AppointmentService::isCheckOutStatus('-'),
            "'-' (None) should not be a check-out status"
        );
    }

    #[Test]
    public function testIsPendingStatus(): void
    {
        // '^' is the pending status
        $this->assertTrue(
            $this->appointmentService->isPendingStatus('^'),
            "'^' should be a pending status"
        );

        // Any other status should not be pending
        $this->assertFalse(
            $this->appointmentService->isPendingStatus('-'),
            "'-' should not be a pending status"
        );

        $this->assertFalse(
            $this->appointmentService->isPendingStatus('@'),
            "'@' should not be a pending status"
        );
    }

    #[Test]
    public function testUpdateAppointmentStatus(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $this->assertGreaterThan(0, $insertId);

        // Update status from '-' to '@' (Arrived)
        $this->appointmentService->updateAppointmentStatus($insertId, '@', 1);

        // Verify the status was updated
        $appointment = $this->appointmentService->getAppointment($insertId);
        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertEquals('@', $row['pc_apptstatus']);
    }

    #[Test]
    public function testUpdateAppointmentStatusThrowsForNonExistent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->appointmentService->updateAppointmentStatus(999999999, '@', 1);
    }

    #[Test]
    public function testGetAppointmentIncludesUuid(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $appointment = $this->appointmentService->getAppointment($insertId);

        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertArrayHasKey('pc_uuid', $row);
        $this->assertNotEmpty($row['pc_uuid'], "Appointment should have a UUID");
    }

    #[Test]
    public function testGetAppointmentIncludesPatientData(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $appointment = $this->appointmentService->getAppointment($insertId);

        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertArrayHasKey('fname', $row);
        $this->assertArrayHasKey('lname', $row);
        $this->assertArrayHasKey('pid', $row);
        $this->assertEquals($this->testPid, $row['pid']);
    }

    #[Test]
    public function testGetAppointmentIncludesFacilityData(): void
    {
        $insertId = $this->appointmentService->insert($this->testPid, $this->appointmentData);
        $appointment = $this->appointmentService->getAppointment($insertId);

        $this->assertIsArray($appointment);
        $this->assertNotEmpty($appointment);
        $row = $appointment[0];
        $this->assertIsArray($row);
        $this->assertArrayHasKey('facility_name', $row);
        $this->assertNotEmpty($row['facility_name'], "Appointment should include facility name");
    }
}
