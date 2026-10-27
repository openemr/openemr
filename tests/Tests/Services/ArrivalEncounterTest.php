<?php

/**
 * The encounter todaysEncounterCheck() creates when a patient arrives (calendar, flow board, appointment status,
 * front payment).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\EncounterService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../library/encounter_events.inc.php';

final class ArrivalEncounterTest extends TestCase
{
    private int $pid;
    private int $userId;
    /** A provider whose id isn't 1, so a provider id stored as `authorized` can't pass for "1". */
    private int $providerId;
    private string $username;
    private ?string $userFacilityName;
    private int $userFacilityId;
    private string $group;
    private int $facilityId;
    private int $categoryId;

    /** @var list<array<mixed>> */
    private array $encounterSaves = [];
    private \Closure $listener;

    /** @var array<string, mixed> */
    private array $previousSession = [];
    /** @var array<string, mixed> */
    private array $previousGlobals = [];

    protected function setUp(): void
    {
        $user = QueryUtils::querySingleRow(
            "SELECT `id`, `username`, `facility`, `facility_id` FROM `users` WHERE `username` = 'admin'"
        );
        $this->assertIsArray($user, 'the admin user is needed as the session user');
        $this->userId = self::intOf($user['id']);
        $this->username = self::stringOf($user['username']);
        $this->userFacilityName = $user['facility'] === null ? null : self::stringOf($user['facility']);
        $this->userFacilityId = self::intOf($user['facility_id']);
        $group = QueryUtils::fetchSingleValue("SELECT `name` FROM `groups` WHERE `user` = 'admin'", 'name');
        $this->group = is_string($group) ? $group : 'Default';

        $providerId = QueryUtils::fetchSingleValue("SELECT `id` FROM `users` WHERE `id` <> 1 ORDER BY `id` LIMIT 1", 'id');
        $this->assertNotNull($providerId, 'a user other than id 1 is needed as the provider');
        $this->providerId = self::intOf($providerId);

        $facilityId = QueryUtils::fetchSingleValue("SELECT `id` FROM `facility` ORDER BY `id` LIMIT 1", 'id');
        $this->assertNotNull($facilityId, 'a facility is needed');
        $this->facilityId = self::intOf($facilityId);
        $categoryId = QueryUtils::fetchSingleValue(
            "SELECT `pc_catid` FROM `openemr_postcalendar_categories` WHERE `pc_cattype` = 0 ORDER BY `pc_catid` LIMIT 1",
            'pc_catid'
        );
        $this->assertNotNull($categoryId, 'a patient appointment category is needed');
        $this->categoryId = self::intOf($categoryId);

        // A new pid, so tearDown() removes only this test's rows (pid is unique; MAX is NULL on an empty table).
        $maxPid = QueryUtils::fetchSingleValue("SELECT MAX(`pid`) AS `max` FROM `patient_data`", 'max');
        $this->pid = ($maxPid === null ? 0 : self::intOf($maxPid)) + 1;
        QueryUtils::sqlInsert(
            "INSERT INTO `patient_data` SET `pid` = ?, `uuid` = ?, `pubpid` = ?, `fname` = 'Arrival', `lname` = 'Test', "
            . "`DOB` = '1980-01-01', `sex` = 'Female'",
            [$this->pid, UuidRegistry::getRegistryForTable('patient_data')->createUuid(), 'test-arrival-' . $this->pid]
        );

        // encounter_events.inc.php sets $today when a page includes it at global scope; PHPUnit includes this file
        // inside a function, so set it the way a page does.
        $GLOBALS['today'] = date('Y-m-d');

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (['authUserID', 'authUser', 'authProvider'] as $key) {
            $this->previousSession[$key] = $session->get($key);
        }
        $session->set('authUserID', $this->userId);
        $session->set('authUser', $this->username);
        $session->set('authProvider', $this->group);

        $globals = OEGlobalsBag::getInstance();
        foreach (['auto_create_new_encounters', 'auto_create_prevent_reason'] as $key) {
            $this->previousGlobals[$key] = $globals->get($key);
        }
        $globals->set('auto_create_new_encounters', 1);
        $globals->set('auto_create_prevent_reason', 0);

        $this->listener = function (ServiceSaveEvent $event): void {
            if ($event->getService() instanceof EncounterService) {
                $this->encounterSaves[] = (array) $event->getSaveData();
            }
        };
        $globals->getKernel()->getEventDispatcher()->addListener(ServiceSaveEvent::EVENT_POST_SAVE, $this->listener);
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->getKernel()->getEventDispatcher()->removeListener(ServiceSaveEvent::EVENT_POST_SAVE, $this->listener);
        foreach ($this->previousGlobals as $key => $value) {
            $globals->set($key, $value);
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach ($this->previousSession as $key => $value) {
            $session->set($key, $value);
        }

        $uuids = QueryUtils::fetchRecords(
            "SELECT `uuid` FROM `form_encounter` WHERE `pid` = ? AND `uuid` IS NOT NULL "
            . "UNION SELECT `uuid` FROM `patient_data` WHERE `pid` = ?",
            [$this->pid, $this->pid]
        );
        foreach ($uuids as $row) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `uuid_registry` WHERE `uuid` = ?", [$row['uuid']]);
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM `forms` WHERE `pid` = ?", [$this->pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `form_encounter` WHERE `pid` = ?", [$this->pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `patient_data` WHERE `pid` = ?", [$this->pid]);
    }

    #[Test]
    public function anArrivalEncounterGetsAUuidAndFiresTheSaveEventOnce(): void
    {
        $encounter = todaysEncounterCheck(
            $this->pid,
            date('Y-m-d'),
            'Follow-up',
            $this->facilityId,
            $this->facilityId,
            $this->providerId,
            $this->categoryId,
            false
        );

        $this->assertGreaterThan(0, $encounter);
        $uuid = QueryUtils::fetchSingleValue(
            "SELECT `uuid` FROM `form_encounter` WHERE `pid` = ? AND `encounter` = ?",
            'uuid',
            [$this->pid, $encounter]
        );
        $this->assertNotEmpty($uuid, 'the arrival encounter has a UUID as soon as it is created');
        $this->assertCount(1, $this->encounterSaves, 'one POST_SAVE from EncounterService');
        $this->assertSame(self::stringOf($encounter), self::stringOf($this->encounterSaves[0]['encounter']));
        $this->assertSame(self::stringOf($this->pid), self::stringOf($this->encounterSaves[0]['pid']));
    }

    #[Test]
    public function todaysEncounterIsReturnedWithoutAnotherInsertOrEvent(): void
    {
        $first = todaysEncounterCheck($this->pid, date('Y-m-d'), 'Follow-up', $this->facilityId, $this->facilityId, $this->providerId, $this->categoryId, false);
        $this->assertGreaterThan(0, $first);
        $saves = count($this->encounterSaves);

        $again = todaysEncounterCheck($this->pid, date('Y-m-d'), 'Follow-up', $this->facilityId, $this->facilityId, $this->providerId, $this->categoryId);
        $notReturned = todaysEncounterCheck($this->pid, date('Y-m-d'), 'Follow-up', $this->facilityId, $this->facilityId, $this->providerId, $this->categoryId, false);

        $this->assertSame(self::intOf($first), self::intOf($again), 'the existing encounter comes back');
        $this->assertSame(0, self::intOf($notReturned), 'with return_existing false, 0 comes back');
        $count = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS `n` FROM `form_encounter` WHERE `pid` = ?", 'n', [$this->pid]);
        $this->assertSame(1, self::intOf($count), 'no second encounter');
        $this->assertCount($saves, $this->encounterSaves, 'no further save event');
    }

    #[Test]
    public function theStoredColumnsAreTheOnesThisPathHasAlwaysStored(): void
    {
        $encounter = todaysEncounterCheck($this->pid, date('Y-m-d'), 'Follow-up', $this->facilityId, '', $this->providerId, $this->categoryId, false);

        $row = QueryUtils::querySingleRow(
            "SELECT `date`, `reason`, `facility`, `facility_id`, `billing_facility`, `provider_id`, `pc_catid`, `pos_code`, `class_code` "
            . "FROM `form_encounter` WHERE `pid` = ? AND `encounter` = ?",
            [$this->pid, $encounter]
        );
        $this->assertIsArray($row);
        $pos = QueryUtils::fetchSingleValue("SELECT `pos_code` FROM `facility` WHERE `id` = ?", 'pos_code', [$this->facilityId]);
        $this->assertSame(date('Y-m-d') . ' 00:00:00', $row['date']);
        $this->assertSame('Follow-up', $row['reason']);
        $this->assertSame($this->userFacilityName, $row['facility'], "the session user's facility name, as before");
        $this->assertSame($this->facilityId, self::intOf($row['facility_id']));
        $this->assertSame($this->userFacilityId, self::intOf($row['billing_facility']), "no billing facility given: the user's");
        $this->assertSame($this->providerId, self::intOf($row['provider_id']));
        $this->assertSame($this->categoryId, self::intOf($row['pc_catid']));
        $this->assertSame(
            $pos === null ? null : self::stringOf($pos),
            $row['pos_code'] === null ? null : self::stringOf($row['pos_code']),
            "the facility's POS code, NULL when it has none"
        );
        $this->assertSame('AMB', $row['class_code']);

        $form = QueryUtils::querySingleRow(
            "SELECT `form_name`, `formdir`, `user`, `groupname`, `authorized`, `deleted` FROM `forms` WHERE `pid` = ? AND `encounter` = ?",
            [$this->pid, $encounter]
        );
        $this->assertIsArray($form);
        $this->assertSame('New Patient Encounter', $form['form_name']);
        $this->assertSame('newpatient', $form['formdir']);
        $this->assertSame($this->username, $form['user']);
        $this->assertSame($this->group, $form['groupname']);
        $this->assertSame(1, self::intOf($form['authorized']), 'stored as authorized, as this path always has');
        $this->assertSame(0, self::intOf($form['deleted']));
    }

    #[Test]
    public function aMissingProviderOrCategoryIsStoredAsZeroAsBefore(): void
    {
        $encounter = todaysEncounterCheck($this->pid, date('Y-m-d'), '', '', '', '', '', false);

        $row = QueryUtils::querySingleRow(
            "SELECT `reason`, `facility_id`, `billing_facility`, `provider_id`, `pc_catid` FROM `form_encounter` "
            . "WHERE `pid` = ? AND `encounter` = ?",
            [$this->pid, $encounter]
        );
        $this->assertIsArray($row);
        $this->assertSame(0, self::intOf($row['provider_id']));
        $this->assertSame(0, self::intOf($row['pc_catid']));
        $this->assertSame($this->userFacilityId, self::intOf($row['facility_id']));
        $this->assertSame($this->userFacilityId, self::intOf($row['billing_facility']));
        $this->assertSame(xl('Please indicate visit reason'), $row['reason']);
        $authorized = QueryUtils::fetchSingleValue(
            "SELECT `authorized` FROM `forms` WHERE `pid` = ? AND `encounter` = ? AND `formdir` = 'newpatient'",
            'authorized',
            [$this->pid, $encounter]
        );
        $this->assertSame(1, self::intOf($authorized), 'authorized with no provider too, so it never waits for sign-off');
    }

    #[Test]
    public function anEncounterInsertedOtherwiseStillStoresTheProviderInAuthorized(): void
    {
        // As the New Encounter form, the REST API, FHIR and AppointmentService call it: no authorized argument.
        $puuid = QueryUtils::fetchSingleValue("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", 'uuid', [$this->pid]);
        $this->assertIsString($puuid);
        $result = (new EncounterService())->insertEncounter(UuidRegistry::uuidToString($puuid), [
            'pc_catid' => $this->categoryId,
            'class_code' => EncounterService::DEFAULT_CLASS_CODE,
            'date' => date('Y-m-d'),
            'reason' => 'Follow-up',
            'facility_id' => $this->facilityId,
            'billing_facility' => $this->facilityId,
            'provider_id' => $this->providerId,
            'user' => $this->username,
            'group' => $this->group,
        ]);
        $this->assertTrue($result->isValid());
        $record = $result->getFirstDataResult();
        $this->assertIsArray($record);

        $authorized = QueryUtils::fetchSingleValue(
            "SELECT `authorized` FROM `forms` WHERE `pid` = ? AND `encounter` = ? AND `formdir` = 'newpatient'",
            'authorized',
            [$this->pid, $record['encounter']]
        );
        $this->assertSame($this->providerId, self::intOf($authorized), 'the provider id, as insertEncounter() has always stored it');
    }

    private static function intOf(mixed $value): int
    {
        if (!is_numeric($value)) {
            throw new \UnexpectedValueException('Expected a number, got ' . get_debug_type($value));
        }
        return (int) $value;
    }

    private static function stringOf(mixed $value): string
    {
        if (!is_scalar($value)) {
            throw new \UnexpectedValueException('Expected a scalar, got ' . get_debug_type($value));
        }
        return (string) $value;
    }
}
