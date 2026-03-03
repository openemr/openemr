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

        /** @var array<string, mixed>|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT id, team_name, status, pid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );

        $this->assertNotEmpty($row);
        $this->assertIsArray($row);
        $this->assertEquals(self::TEST_TEAM_NAME, $row['team_name']);
        $this->assertEquals('active', $row['status']);
        // @phpstan-ignore cast.int
        $this->assertEquals($this->testPid, (int) $row['pid']);
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

        /** @var array<string, mixed>|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );

        $this->assertIsArray($row);
        $this->assertNotEmpty($row['uuid']);
        // @phpstan-ignore argument.type
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
        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        $this->assertNotEmpty($teamRow);

        // Verify the member was inserted
        /** @var array<string, mixed>|false $memberRow */
        $memberRow = QueryUtils::querySingleRow(
            "SELECT * FROM care_team_member WHERE care_team_id = ? AND user_id = ?",
            [$teamRow['id'], $this->testProviderId]
        );
        $this->assertIsArray($memberRow);
        $this->assertNotEmpty($memberRow);
        $this->assertEquals('family_medicine_specialist', $memberRow['role']);
        // @phpstan-ignore cast.int
        $this->assertEquals($this->testFacilityId, (int) $memberRow['facility_id']);
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

        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore cast.int
        $teamId = (int) $teamRow['id'];

        // Update the team name and status
        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME_ALT,
            [],
            'suspended'
        );

        // Verify the update
        /** @var array<string, mixed>|false $updatedRow */
        $updatedRow = QueryUtils::querySingleRow(
            "SELECT team_name, status FROM care_teams WHERE id = ?",
            [$teamId]
        );
        $this->assertIsArray($updatedRow);
        $this->assertEquals(self::TEST_TEAM_NAME_ALT, $updatedRow['team_name']);
        $this->assertEquals('suspended', $updatedRow['status']);

        // Verify no duplicate was created
        /** @var array<string, mixed>|false $count */
        $count = QueryUtils::querySingleRow(
            "SELECT COUNT(*) as cnt FROM care_teams WHERE pid = ? AND (team_name LIKE 'test-fixture%')",
            [$this->testPid]
        );
        $this->assertIsArray($count);
        // @phpstan-ignore cast.int
        $this->assertEquals(1, (int) $count['cnt']);
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

        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore cast.int
        $teamId = (int) $teamRow['id'];

        // Verify initial state: one active member
        /** @var array<string, mixed>|false $initialCount */
        $initialCount = QueryUtils::querySingleRow(
            "SELECT COUNT(*) as cnt FROM care_team_member WHERE care_team_id = ? AND status = 'active'",
            [$teamId]
        );
        $this->assertIsArray($initialCount);
        // @phpstan-ignore cast.int
        $this->assertEquals(1, (int) $initialCount['cnt']);

        // Get a second provider id — the admin user (id=1) should always exist
        /** @var array<string, mixed>|false $secondProvider */
        $secondProvider = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE id != ? AND id > 0 AND username IS NOT NULL AND username != '' LIMIT 1",
            [$this->testProviderId]
        );
        $this->assertIsArray($secondProvider);
        // @phpstan-ignore cast.int
        $secondProviderId = (int) ($secondProvider['id'] ?? 0);
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
        /** @var array<string, mixed>|false $memberCount */
        $memberCount = QueryUtils::querySingleRow(
            "SELECT COUNT(*) as cnt FROM care_team_member WHERE care_team_id = ? AND status = 'active'",
            [$teamId]
        );
        $this->assertIsArray($memberCount);
        // @phpstan-ignore cast.int
        $this->assertEquals(2, (int) $memberCount['cnt']);
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

        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore cast.int
        $teamId = (int) $teamRow['id'];

        // Update team with NO members (removing the provider)
        $this->service->saveCareTeam(
            $this->testPid,
            $teamId,
            self::TEST_TEAM_NAME,
            [],
            'active'
        );

        // Verify the member was marked inactive (not deleted)
        /** @var array<string, mixed>|false $memberRow */
        $memberRow = QueryUtils::querySingleRow(
            "SELECT status FROM care_team_member WHERE care_team_id = ? AND user_id = ?",
            [$teamId, $this->testProviderId]
        );
        $this->assertIsArray($memberRow);
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

        $this->assertEmpty($result['team_name']);
        $this->assertEquals('active', $result['team_status']);
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

        $this->assertEquals(self::TEST_TEAM_NAME, $result['team_name']);
        $this->assertEquals('active', $result['team_status']);
        $this->assertGreaterThanOrEqual(1, $result['member_count']);
        $this->assertNotEmpty($result['members']);

        // Verify first member structure
        $member = $result['members'][0];
        $this->assertEquals('user', $member['member_type']);
        // @phpstan-ignore cast.int
        $this->assertEquals($this->testProviderId, (int) $member['user_id']);
        $this->assertEquals('family_medicine_specialist', $member['role']);
        // @phpstan-ignore cast.int
        $this->assertEquals($this->testFacilityId, (int) $member['facility_id']);
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

        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT id FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore cast.int
        $teamId = (int) $teamRow['id'];

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
        /** @var array<string, mixed>|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($row);
        // @phpstan-ignore argument.type
        $uuidString = UuidRegistry::uuidToString($row['uuid']);

        $result = $this->service->getOne($uuidString);

        $this->assertCount(0, $result->getValidationMessages());

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);

        $this->assertEquals($uuidString, $data[0]['uuid']);
        $this->assertEquals(self::TEST_TEAM_NAME, $data[0]['team_name']);
    }

    #[Test]
    public function testGetOneWithInvalidUuidReturnsValidationMessages(): void
    {
        $result = $this->service->getOne("not-a-valid-uuid");

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
        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore argument.type
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        // Get the patient UUID
        /** @var array<string, mixed>|false $patientRow */
        $patientRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pid = ?",
            [$this->testPid]
        );
        $this->assertIsArray($patientRow);
        // @phpstan-ignore argument.type
        $patientUuid = UuidRegistry::uuidToString($patientRow['uuid']);

        $result = $this->service->getOne($teamUuid, $patientUuid);

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);
        $this->assertEquals($teamUuid, $data[0]['uuid']);
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

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);

        // Verify at least one of the results is our test team
        $found = false;
        foreach ($data as $record) {
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
        /** @var array<string, mixed>|false $patientRow */
        $patientRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pid = ?",
            [$this->testPid]
        );
        $this->assertIsArray($patientRow);
        // @phpstan-ignore argument.type
        $patientUuid = UuidRegistry::uuidToString($patientRow['uuid']);

        $result = $this->service->getAll([], true, $patientUuid);

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);

        // All returned records should belong to our test patient
        foreach ($data as $record) {
            $this->assertEquals($patientUuid, $record['puuid']);
        }
    }

    #[Test]
    public function testGetAllWithInvalidPatientUuidReturnsValidation(): void
    {
        $result = $this->service->getAll([], true, "not-a-valid-uuid");

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
        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore argument.type
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        $search = ['uuid' => new TokenSearchField('uuid', $teamUuid, true)];
        $result = $this->service->search($search);

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);

        $record = $data[0];

        // Verify providers are populated
        $this->assertArrayHasKey('providers', $record);
        $this->assertIsArray($record['providers']);
        $this->assertNotEmpty($record['providers']);

        $provider = $record['providers'][0];
        $this->assertIsArray($provider);
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

        /** @var array<string, mixed>|false $teamRow */
        $teamRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM care_teams WHERE team_name = ?",
            [self::TEST_TEAM_NAME]
        );
        $this->assertIsArray($teamRow);
        // @phpstan-ignore argument.type
        $teamUuid = UuidRegistry::uuidToString($teamRow['uuid']);

        $search = ['uuid' => new TokenSearchField('uuid', $teamUuid, true)];
        $result = $this->service->search($search);

        /** @var array<int, array<string, mixed>> $data */
        $data = $result->getData();
        $this->assertNotEmpty($data);

        $record = $data[0];

        // Verify facilities are populated
        $this->assertArrayHasKey('facilities', $record);
        $this->assertIsArray($record['facilities']);
        $this->assertNotEmpty($record['facilities']);

        $facility = $record['facilities'][0];
        $this->assertIsArray($facility);
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
        $this->assertEmpty($result['providers']);
        $this->assertEmpty($result['facilities']);
        $this->assertEmpty($result['contacts']);
    }
}
