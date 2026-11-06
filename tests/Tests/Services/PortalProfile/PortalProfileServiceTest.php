<?php

/**
 * DB tests for the portal profile service: registration creates a chart through PatientService,
 * the record and profile-row shapes, saving a record, and the review payload's columns.
 *
 * Each test removes the chart it created, with its uuid registry row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PortalProfile;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PortalProfile\PortalProfileService;
use PHPUnit\Framework\TestCase;

final class PortalProfileServiceTest extends TestCase
{
    private PortalProfileService $profiles;

    /** @var list<int> */
    private array $createdPids = [];

    protected function setUp(): void
    {
        $this->profiles = new PortalProfileService();
    }

    /**
     * Deletes the charts the test created.
     */
    protected function tearDown(): void
    {
        foreach ($this->createdPids as $pid) {
            $uuid = QueryUtils::fetchSingleValue('SELECT `uuid` FROM `patient_data` WHERE `pid` = ?', 'uuid', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `history_data` WHERE `pid` = ?', [$pid]);
            if (is_string($uuid) && $uuid !== '') {
                QueryUtils::sqlStatementThrowException('DELETE FROM `uuid_registry` WHERE `uuid` = ?', [$uuid]);
            }
        }
    }

    /**
     * Registration gets a new pid, ignoring one the request names.
     */
    public function testRegistrationCreatesAChartWithItsOwnPid(): void
    {
        // A pid no chart has or gets next: a fresh database gives the first chart pid 1.
        $maxPid = QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(`pid`), 0) AS `pid` FROM `patient_data`', 'pid');
        $chosenPid = (string) (1000 + (is_numeric($maxPid) ? (int) $maxPid : 0));
        $record = $this->register(['fname' => 'Portal', 'lname' => 'Registrant', 'pid' => $chosenPid, 'pubpid' => 'chosen']);

        $this->assertSame(array_merge(['note'], array_keys(PortalProfileService::FIELDS)), array_keys($record));
        $this->assertNull($record['note']);
        $this->assertSame('Portal', $record['fname']);
        $this->assertNotSame($chosenPid, $record['pid'], 'the request cannot choose the pid');
        $this->assertSame($record['pid'], $record['pubpid'], 'the public id is the new pid, as before');
        $this->assertIsString($record['id']);
        $this->assertSame($record, $this->profiles->record((int) $record['id']));
    }

    public function testTheProfileRowHasEveryFormKeyAndOnlyTheProfileFieldsSet(): void
    {
        $record = $this->register(['fname' => 'Portal', 'lname' => 'Profile', 'city' => 'Springfield']);
        $this->assertIsString($record['pid']);

        $row = $this->profiles->profileRow((int) $record['pid']);
        $this->assertSame(PortalProfileService::PROFILE_KEYS, array_keys($row));
        $this->assertSame('Springfield', $row['city']);
        $this->assertNull($row['usertext1'], 'keys outside the profile fields are always empty');

        $this->assertSame([], $this->profiles->profileRow(990000201), 'no row for a pid with no chart');
        $empty = $this->profiles->profileRow(0);
        $this->assertSame(PortalProfileService::PROFILE_KEYS, array_keys($empty), 'registration gets every key');
        $this->assertSame([], array_filter($empty, static fn(mixed $v): bool => $v !== null));
    }

    public function testARegistrationNeedsNoPidButASavedProfileDoes(): void
    {
        // As PatientController::Create() builds it: every field, no pid yet.
        $registration = array_fill_keys(array_keys(PortalProfileService::FIELDS), null)
            + ['hipaaAllowsms' => 'NO', 'hipaaAllowemail' => 'NO'];
        $registration['fname'] = 'Portal';
        $registration['lname'] = 'Registrant';
        $registration['dob'] = '1980-01-02';
        $registration['email'] = 'registrant@example.com';
        // The form always sends these: its provider select, and 0 for the other number fields.
        $registration['providerid'] = '1';
        $registration['pharmacyId'] = $registration['refProviderid'] = $registration['careTeam'] = 0;

        $this->assertSame([], PortalProfileService::validate($registration, newChart: true));
        $this->assertArrayHasKey('pid', PortalProfileService::validate($registration), 'a saved profile must name its chart');
    }

    public function testSavingARecordUpdatesTheChart(): void
    {
        $record = $this->register(['fname' => 'Portal', 'lname' => 'Saved']);
        $record['phoneHome'] = '555-0142';
        $record['note'] = 'ignored by the chart';

        $saved = $this->profiles->save($record);

        $this->assertSame('555-0142', $saved['phoneHome']);
        $this->assertNull($saved['note']);
        $this->assertIsString($record['id']);
        $reread = $this->profiles->record((int) $record['id']);
        $this->assertIsArray($reread);
        $this->assertSame('555-0142', $reread['phoneHome']);
    }

    public function testColumnsKeyTheFieldsByChartColumn(): void
    {
        $columns = PortalProfileService::columns(['dob' => '1990-01-02', 'providerid' => '5', 'careTeam' => '6', 'note' => 'x']);

        $this->assertSame(array_values(PortalProfileService::FIELDS), array_keys($columns));
        $this->assertSame('1990-01-02', $columns['DOB']);
        $this->assertSame('5', $columns['providerID']);
        $this->assertSame('6', $columns['care_team_provider']);
        $this->assertArrayNotHasKey('note', $columns);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    private function register(array $fields): array
    {
        $record = $this->profiles->register($fields + ['dob' => '1980-01-02', 'sex' => 'Female']);
        $this->assertIsString($record['pid']);
        $this->createdPids[] = (int) $record['pid'];

        return $record;
    }
}
