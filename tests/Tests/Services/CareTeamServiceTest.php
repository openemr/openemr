<?php

/**
 * CareTeamServiceTest - Tests for CareTeamService CRUD, member management, and queries.
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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Tests\Fixtures\CareTeamFixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CareTeamServiceTest extends TestCase
{
    /**
     * @var CareTeamService
     */
    private $service;

    /**
     * @var CareTeamFixtureManager
     */
    private $fixtureManager;

    /**
     * @var int
     */
    private int $testPid;

    /**
     * @var int
     */
    private int $testFacilityId;

    /**
     * @var int
     */
    private int $testProviderId;

    private const TEST_TEAM_NAME = "test-fixture-Primary Care Team";
    private const TEST_TEAM_NAME_ALT = "test-fixture-Secondary Care Team";

    protected function setUp(): void
    {
        $this->service = new CareTeamService();
        $this->fixtureManager = new CareTeamFixtureManager();
        $deps = $this->fixtureManager->installDependencies();
        $this->testPid = $deps['pid'];
        $this->testFacilityId = $deps['facility_id'];
        $this->testProviderId = $deps['provider_id'];

        // saveCareTeam reads $_SESSION['authUserID']
        if (!isset($_SESSION['authUserID'])) {
            $_SESSION['authUserID'] = 1;
        }
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    // =========================================================================
    // getUuidFields
    // =========================================================================

    #[Test]
    public function testGetUuidFieldsReturnsExpectedFields(): void
    {
        $fields = $this->service->getUuidFields();
        $this->assertIsArray($fields);
        $this->assertContains('uuid', $fields);
        $this->assertContains('puuid', $fields);
    }

    // =========================================================================
    // hasActiveCareTeam
    // =========================================================================

    #[Test]
    public function testHasActiveCareTeamReturnsFalseWhenNoTeam(): void
    {
        $result = $this->service->hasActiveCareTeam($this->testPid);
        $this->assertFalse($result);
    }

    #[Test]
    public function testHasActiveCareTeamReturnsTrueAfterSave(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $result = $this->service->hasActiveCareTeam($this->testPid);
        $this->assertTrue($result);
    }

    #[Test]
    public function testHasActiveCareTeamReturnsFalseForInactiveTeam(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'inactive'
        );

        $result = $this->service->hasActiveCareTeam($this->testPid);
        $this->assertFalse($result);
    }

    // =========================================================================
    // saveCareTeam — create
    // =========================================================================

    #[Test]
    public function testSaveCareTeamCreatesNewTeam(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $row = sqlQuery(
            "SELECT id, team_name, status, pid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );

        $this->assertNotEmpty($row);
        $this->assertEquals(self::TEST_TEAM_NAME, $row['team_name']);
        $this->assertEquals('active', $row['status']);
        $this->assertEquals($this->testPid, intval($row['pid']));
    }

    #[Test]
    public function testSaveCareTeamAssignsUuid(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $row = sqlQuery(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );

        $this->assertNotEmpty($row['uuid']);
        $uuidString = UuidRegistry::uuidToString($row['uuid']);
        $this->assertNotEmpty($uuidString);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $uuidString
        );
    }

    #[Test]
    public function testSaveCareTeamWithProviderMember(): void
    {
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'provider_since' => date('Y-m-d'),
                'status' => 'active',
                'note' => 'Primary physician',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        // Verify the team was created
        $teamRow = sqlQuery(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertNotEmpty($teamRow);

        // Verify the member was inserted
        $memberRow = sqlQuery(
            "SELECT * FROM care_team_member WHERE care_team_id = ? AND user_id = ?",
            [$teamRow['id'], $this->testProviderId]
        );
        $this->assertNotEmpty($memberRow);
        $this->assertEquals('family_medicine_specialist', $memberRow['role']);
        $this->assertEquals($this->testFacilityId, intval($memberRow['facility_id']));
        $this->assertEquals('active', $memberRow['status']);
        $this->assertEquals('Primary physician', $memberRow['note']);
    }

    // =========================================================================
    // saveCareTeam — update
    // =========================================================================

    #[Test]
    public function testSaveCareTeamUpdatesExistingTeam(): void
    {
        // Create initial team
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $teamRow = sqlQuery(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamId = intval($teamRow['id']);

        // Update the team name and status
        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME_ALT,
            [],
            'suspended'
        );

        // Verify the update
        $updatedRow = sqlQuery(
            "SELECT team_name, status FROM care_teams WHERE id = ?",
            [$teamId]
        );
        $this->assertEquals(self::TEST_TEAM_NAME_ALT, $updatedRow['team_name']);
        $this->assertEquals('suspended', $updatedRow['status']);

        // Verify no duplicate was created
        $count = sqlQuery(
            "SELECT COUNT(*) as cnt FROM care_teams WHERE pid = ? AND (team_name LIKE 'test-fixture%')",
            [$this->testPid]
        );
        $this->assertEquals(1, intval($count['cnt']));
    }

    #[Test]
    public function testSaveCareTeamAddsNewMemberToExistingTeam(): void
    {
        // Create team with one member
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        $teamRow = sqlQuery(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamId = intval($teamRow['id']);

        // Verify initial state: one active member
        $initialCount = sqlQuery(
            "SELECT COUNT(*) as cnt FROM care_team_member WHERE care_team_id = ? AND status = 'active'",
            [$teamId]
        );
        $this->assertEquals(1, intval($initialCount['cnt']));

        // Get a second provider id — the admin user (id=1) should always exist
        $secondProvider = sqlQuery(
            "SELECT id FROM users WHERE id != ? AND id > 0 AND username IS NOT NULL AND username != '' LIMIT 1",
            [$this->testProviderId]
        );
        $secondProviderId = intval($secondProvider['id'] ?? 0);
        $this->assertGreaterThan(0, $secondProviderId, "A second user with positive id must exist for this test");

        // Update team with both members
        $teamUpdated = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ],
            [
                'user_id' => $secondProviderId,
                'role' => 'nurse',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => 'Added nurse',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME,
            $teamUpdated,
            'active'
        );

        // Verify two active members exist
        $memberCount = sqlQuery(
            "SELECT COUNT(*) as cnt FROM care_team_member WHERE care_team_id = ? AND status = 'active'",
            [$teamId]
        );
        $this->assertEquals(2, intval($memberCount['cnt']));
    }

    #[Test]
    public function testSaveCareTeamMarksMemberInactiveWhenRemoved(): void
    {
        // Create team with one member
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        $teamRow = sqlQuery(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamId = intval($teamRow['id']);

        // Update team with NO members (removing the provider)
        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        // Verify the member was marked inactive (not deleted)
        $memberRow = sqlQuery(
            "SELECT status FROM care_team_member WHERE care_team_id = ? AND user_id = ?",
            [$teamId, $this->testProviderId]
        );
        $this->assertNotEmpty($memberRow);
        $this->assertEquals('inactive', $memberRow['status']);
    }

    // =========================================================================
    // getCareTeamData
    // =========================================================================

    #[Test]
    public function testGetCareTeamDataReturnsEmptyStructureWhenNoTeam(): void
    {
        $result = $this->service->getCareTeamData($this->testPid);

        $this->assertIsArray($result);
        $this->assertEmpty($result['team_name']);
        $this->assertEquals('active', $result['team_status']);
        $this->assertIsArray($result['members']);
        $this->assertEmpty($result['members']);
        $this->assertEquals(0, $result['member_count']);
    }

    #[Test]
    public function testGetCareTeamDataReturnsTeamWithMembers(): void
    {
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'provider_since' => date('Y-m-d'),
                'status' => 'active',
                'note' => 'Test provider note',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        $result = $this->service->getCareTeamData($this->testPid);

        $this->assertIsArray($result);
        $this->assertEquals(self::TEST_TEAM_NAME, $result['team_name']);
        $this->assertEquals('active', $result['team_status']);
        $this->assertGreaterThanOrEqual(1, $result['member_count']);
        $this->assertNotEmpty($result['members']);

        // Verify first member structure
        $member = $result['members'][0];
        $this->assertEquals('user', $member['member_type']);
        $this->assertEquals($this->testProviderId, intval($member['user_id']));
        $this->assertEquals('family_medicine_specialist', $member['role']);
        $this->assertEquals($this->testFacilityId, intval($member['facility_id']));
        $this->assertEquals('active', $member['status']);
        $this->assertEquals('Test provider note', $member['note']);
    }

    #[Test]
    public function testGetCareTeamDataPrefersActiveTeam(): void
    {
        // Create an inactive team first
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME_ALT,
            [],
            'inactive'
        );

        // Create an active team second
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $result = $this->service->getCareTeamData($this->testPid);

        $this->assertEquals('active', $result['team_status']);
        $this->assertEquals(self::TEST_TEAM_NAME, $result['team_name']);
    }

    #[Test]
    public function testGetCareTeamDataExcludesInactiveMembers(): void
    {
        // Create team with a member
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        $teamRow = sqlQuery(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamId = intval($teamRow['id']);

        // Remove the member (marks inactive)
        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $result = $this->service->getCareTeamData($this->testPid);

        // getCareTeamData filters out inactive/entered-in-error members
        $this->assertEquals(0, $result['member_count']);
    }

    // =========================================================================
    // getOne
    // =========================================================================

    #[Test]
    public function testGetOneWithValidUuidReturnsData(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        // Get the UUID from the database
        $row = sqlQuery(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $uuidString = UuidRegistry::uuidToString($row['uuid']);

        $result = $this->service->getOne($uuidString);

        $this->assertNotNull($result);
        $this->assertCount(0, $result->getValidationMessages());
        $this->assertNotEmpty($result->getData());

        $data = $result->getData()[0];
        $this->assertEquals($uuidString, $data['uuid']);
        $this->assertEquals(self::TEST_TEAM_NAME, $data['team_name']);
    }

    #[Test]
    public function testGetOneWithInvalidUuidReturnsValidationMessages(): void
    {
        $result = $this->service->getOne("not-a-valid-uuid");

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getValidationMessages());
        $this->assertArrayHasKey('uuid', $result->getValidationMessages());
        $this->assertEmpty($result->getData());
    }

    #[Test]
    public function testGetOneWithPatientUuidBinding(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        // Get the care team UUID
        $teamRow = sqlQuery(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        // Get the patient UUID
        $patientRow = sqlQuery(
            "SELECT uuid FROM patient_data WHERE pid = ?",
            [$this->testPid]
        );
        $patientUuid = UuidRegistry::uuidToString($patientRow['uuid']);

        $result = $this->service->getOne($teamUuid, $patientUuid);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getData());
        $this->assertEquals($teamUuid, $result->getData()[0]['uuid']);
    }

    // =========================================================================
    // getAll
    // =========================================================================

    #[Test]
    public function testGetAllReturnsResults(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        $result = $this->service->getAll();

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getData());

        // Verify at least one of the results is our test team
        $found = false;
        foreach ($result->getData() as $record) {
            if ($record['team_name'] === self::TEST_TEAM_NAME) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Test care team should be found in getAll results");
    }

    #[Test]
    public function testGetAllWithPatientUuidBinding(): void
    {
        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        // Get the patient UUID
        $patientRow = sqlQuery(
            "SELECT uuid FROM patient_data WHERE pid = ?",
            [$this->testPid]
        );
        $patientUuid = UuidRegistry::uuidToString($patientRow['uuid']);

        $result = $this->service->getAll([], true, $patientUuid);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getData());

        // All returned records should belong to our test patient
        foreach ($result->getData() as $record) {
            $this->assertEquals($patientUuid, $record['puuid']);
        }
    }

    #[Test]
    public function testGetAllWithInvalidPatientUuidReturnsValidation(): void
    {
        $result = $this->service->getAll([], true, "not-a-valid-uuid");

        // Should return a ProcessingResult with validation errors
        $this->assertNotNull($result);
        // BaseValidator returns ProcessingResult with validation messages for invalid UUID
        $this->assertNotEmpty($result->getValidationMessages());
    }

    // =========================================================================
    // search — result structure
    // =========================================================================

    #[Test]
    public function testSearchReturnsResultsWithProviders(): void
    {
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        // Get the care team UUID for searching
        $teamRow = sqlQuery(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        $search = ['uuid' => new TokenSearchField('uuid', $teamUuid, true)];
        $result = $this->service->search($search);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getData());

        $record = $result->getData()[0];

        // Verify providers are populated
        $this->assertArrayHasKey('providers', $record);
        $this->assertNotEmpty($record['providers']);

        $provider = $record['providers'][0];
        $this->assertArrayHasKey('provider_uuid', $provider);
        $this->assertArrayHasKey('provider_name', $provider);
        $this->assertArrayHasKey('role', $provider);
        $this->assertArrayHasKey('role_title', $provider);
    }

    #[Test]
    public function testSearchReturnsResultsWithFacilities(): void
    {
        $team = [
            [
                'user_id' => $this->testProviderId,
                'role' => 'family_medicine_specialist',
                'facility_id' => $this->testFacilityId,
                'status' => 'active',
                'note' => '',
            ]
        ];

        $this->service->saveCareTeam(
            $this->testPid,
            null,
            self::TEST_TEAM_NAME,
            $team,
            'active'
        );

        $teamRow = sqlQuery(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        $search = ['uuid' => new TokenSearchField('uuid', $teamUuid, true)];
        $result = $this->service->search($search);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getData());

        $record = $result->getData()[0];

        // Verify facilities are populated
        $this->assertArrayHasKey('facilities', $record);
        $this->assertNotEmpty($record['facilities']);

        $facility = $record['facilities'][0];
        $this->assertArrayHasKey('uuid', $facility);
        $this->assertArrayHasKey('name', $facility);
    }

    #[Test]
    public function testSearchReturnsEmptyForNonMatchingCriteria(): void
    {
        // Search with a UUID that doesn't exist
        $fakeUuid = "00000000-0000-0000-0000-000000000000";
        $search = ['uuid' => new TokenSearchField('uuid', $fakeUuid, true)];
        $result = $this->service->search($search);

        $this->assertNotNull($result);
        $this->assertEmpty($result->getData());
    }

    // =========================================================================
    // createResultRecordFromDatabaseResult — default values
    // =========================================================================

    #[Test]
    public function testCreateResultRecordSetsDefaults(): void
    {
        // Provide a minimal row to verify defaults
        $minimalRow = [
            'id' => 999,
            'uuid' => null,
            'pid' => $this->testPid,
            'puuid' => null,
        ];

        $result = $this->service->createResultRecordFromDatabaseResult($minimalRow);

        $this->assertEquals('', $result['team_name']);
        $this->assertEquals('active', $result['care_team_status']);
        $this->assertNull($result['date']);
        $this->assertIsArray($result['providers']);
        $this->assertEmpty($result['providers']);
        $this->assertIsArray($result['facilities']);
        $this->assertEmpty($result['facilities']);
        $this->assertIsArray($result['contacts']);
        $this->assertEmpty($result['contacts']);
    }
}
