<?php

/**
 * PatientIssuesServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientIssuesService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PatientIssuesServiceTest extends TestCase
{
    // Pid picked to not collide with any fixture. Id picked to not collide with any
    // autoincrement `lists.id` a fresh dev database will ever reach.
    private const PID = 987654322;
    private const UNKNOWN_ID = 999999999999;

    private const TOUCH_TYPE = 'lists_test_type';

    private const SESSION_USER = 'lists_test_user';
    private const SESSION_GROUPNAME = 'lists_test_group';

    private static bool $hadPreviousSession = false;
    private static ?SessionInterface $previousSession = null;

    /**
     * Injects a deterministic session (authUser/authProvider) for the shim's addList() to
     * read, and remembers whatever session was active before so it can be restored.
     *
     * @codeCoverageIgnore PHPUnit runs setUpBeforeClass before coverage instrumentation starts.
     */
    public static function setUpBeforeClass(): void
    {
        $factory = SessionWrapperFactory::getInstance();
        self::$hadPreviousSession = $factory->isSessionActive();
        self::$previousSession = self::$hadPreviousSession ? $factory->getActiveSession() : null;

        $session = new Session(new MockArraySessionStorage());
        $session->set('authUser', self::SESSION_USER);
        $session->set('authProvider', self::SESSION_GROUPNAME);
        $factory->setActiveSession($session);
    }

    /**
     * Restores the session SessionWrapperFactory held before this class ran. It has no public
     * API for clearing $activeSession back to null, so when none was active before, reset it
     * via reflection (same technique as FieldRenderingSnapshotTest).
     *
     * @codeCoverageIgnore PHPUnit stops coverage instrumentation before this runs.
     */
    public static function tearDownAfterClass(): void
    {
        $factory = SessionWrapperFactory::getInstance();
        if (self::$hadPreviousSession && self::$previousSession !== null) {
            $factory->setActiveSession(self::$previousSession);
        } else {
            (new ReflectionProperty(SessionWrapperFactory::class, 'activeSession'))->setValue($factory, null);
        }
    }

    protected function setUp(): void
    {
        $this->deleteFixtureRows();
    }

    protected function tearDown(): void
    {
        $this->deleteFixtureRows();
    }

    /**
     * Removes every `lists` and `lists_touch` row for the fixture pid.
     */
    private function deleteFixtureRows(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `lists` WHERE `pid` = ?", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `lists_touch` WHERE `pid` = ?", [self::PID]);
    }

    /**
     * Writes a `lists` row directly, bypassing the code under test.
     */
    private function insertListRow(string $type, string $title, string $comments, string $user, string $groupname, string $activity): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO `lists` (`date`, `pid`, `type`, `title`, `activity`, `comments`, `user`, `groupname`) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)",
            [self::PID, $type, $title, $activity, $comments, $user, $groupname]
        );
    }

    /**
     * Reads a `lists` row directly, bypassing the code under test.
     *
     * @return array<mixed>|null
     */
    private function fetchListRow(int $id): ?array
    {
        $row = QueryUtils::querySingleRow("SELECT * FROM `lists` WHERE `id` = ?", [$id]);

        return is_array($row) ? $row : null;
    }

    /**
     * Counts `lists_touch` rows for the fixture pid/type directly, bypassing the code under test.
     */
    private function countTouchRows(): int
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `date` FROM `lists_touch` WHERE `pid` = ? AND `type` = ?",
            [self::PID, self::TOUCH_TYPE]
        );

        return count($rows);
    }

    /**
     * Casts a raw DB column value to string for assertion comparisons. id/pid/activity are
     * never non-scalar in practice; empty string is the fallback for anything that is.
     */
    private function asString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    #[Test]
    public function testShimAddListStoresRowWithSessionUserAndDefaults(): void
    {
        $id = addList(self::PID, 'lists_test_type', 'Title one', 'Some comments');

        $this->assertGreaterThan(0, $id);

        $row = $this->fetchListRow($id);
        $this->assertIsArray($row);
        $this->assertSame((string) self::PID, $this->asString($row['pid']));
        $this->assertSame('lists_test_type', $row['type']);
        $this->assertSame('Title one', $row['title']);
        $this->assertSame('Some comments', $row['comments']);
        $this->assertSame('1', $this->asString($row['activity']));
        $this->assertSame(self::SESSION_USER, $row['user']);
        $this->assertSame(self::SESSION_GROUPNAME, $row['groupname']);
        $this->assertNotNull($row['date']);
    }

    #[Test]
    public function testServiceAddListStoresExplicitUserGroupnameAndActivity(): void
    {
        $id = PatientIssuesService::addList(self::PID, 'lists_test_type', 'Title two', 'More comments', 'svc_user', 'svc_group', '0');

        $row = $this->fetchListRow($id);
        $this->assertIsArray($row);
        $this->assertSame('svc_user', $row['user']);
        $this->assertSame('svc_group', $row['groupname']);
        $this->assertSame('0', $this->asString($row['activity']));
    }

    #[Test]
    public function testGetListByIdReturnsFullRowForStar(): void
    {
        $id = $this->insertListRow('lists_test_type', 'Full row', 'c', 'u', 'g', '1');

        $shimRow = getListById($id);
        $this->assertIsArray($shimRow);
        $this->assertSame('Full row', $shimRow['title']);
        $this->assertSame((string) self::PID, $this->asString($shimRow['pid']));

        $svcRow = PatientIssuesService::getListById($id);
        $this->assertIsArray($svcRow);
        $this->assertSame('Full row', $svcRow['title']);
    }

    #[Test]
    public function testGetListByIdRespectsColsParameter(): void
    {
        $id = $this->insertListRow('lists_test_type', 'Cols row', 'c', 'u', 'g', '1');

        $row = getListById($id, 'id, title');

        $this->assertIsArray($row);
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('title', $row);
        $this->assertArrayNotHasKey('pid', $row);
        $this->assertArrayNotHasKey('comments', $row);
    }

    #[Test]
    public function testGetListByIdUnknownIdShimReturnsFalseServiceReturnsNull(): void
    {
        $this->assertFalse(getListById(self::UNKNOWN_ID));
        $this->assertNull(PatientIssuesService::getListById(self::UNKNOWN_ID));
    }

    #[Test]
    public function testDisappearListSetsActivityZeroAndReturnsTrue(): void
    {
        $id = $this->insertListRow('lists_test_type', 'Row', 'c', 'u', 'g', '1');

        $result = disappearList($id);

        $this->assertTrue($result);
        $row = $this->fetchListRow($id);
        $this->assertIsArray($row);
        $this->assertSame('0', $this->asString($row['activity']));
    }

    #[Test]
    public function testReappearListSetsActivityOneAndReturnsTrue(): void
    {
        $id = $this->insertListRow('lists_test_type', 'Row', 'c', 'u', 'g', '0');

        $result = reappearList($id);

        $this->assertTrue($result);
        $row = $this->fetchListRow($id);
        $this->assertIsArray($row);
        $this->assertSame('1', $this->asString($row['activity']));
    }

    #[Test]
    public function testDisappearAndReappearListOnUnknownIdReturnTrueWithoutError(): void
    {
        $this->assertTrue(disappearList(self::UNKNOWN_ID));
        $this->assertTrue(reappearList(self::UNKNOWN_ID));
    }

    #[Test]
    public function testGetListTouchBeforeAnyTouch(): void
    {
        $this->assertFalse(getListTouch(self::PID, self::TOUCH_TYPE));
        $this->assertNull(PatientIssuesService::getListTouch(self::PID, self::TOUCH_TYPE));
    }

    #[Test]
    public function testSetListTouchThenGetListTouchReturnsNonEmptyDatetimeString(): void
    {
        setListTouch(self::PID, self::TOUCH_TYPE);

        $shimResult = getListTouch(self::PID, self::TOUCH_TYPE);
        $this->assertIsString($shimResult);
        $this->assertNotSame('', $shimResult);

        $svcResult = PatientIssuesService::getListTouch(self::PID, self::TOUCH_TYPE);
        $this->assertIsString($svcResult);
        $this->assertNotSame('', $svcResult);
    }

    #[Test]
    public function testSetListTouchIsANoOpWhenAlreadyTouched(): void
    {
        setListTouch(self::PID, self::TOUCH_TYPE);
        $this->assertSame(1, $this->countTouchRows());

        QueryUtils::sqlStatementThrowException(
            "UPDATE `lists_touch` SET `date` = ? WHERE `pid` = ? AND `type` = ?",
            ['2001-02-03 04:05:06', self::PID, self::TOUCH_TYPE]
        );

        setListTouch(self::PID, self::TOUCH_TYPE);

        $this->assertSame(1, $this->countTouchRows());
        $touch = getListTouch(self::PID, self::TOUCH_TYPE);
        $this->assertSame('2001-02-03 04:05:06', $touch);
    }

    #[Test]
    public function testShimGetListByIdGuardRejectsArrayId(): void
    {
        $this->assertFalse(getListById([self::UNKNOWN_ID]));
    }

    #[Test]
    public function testShimAddListGuardRejectsArrayPidAndWritesNothing(): void
    {
        $result = addList([self::PID], 'lists_test_type', 'T', 'C');

        $this->assertSame(0, $result);
        $this->assertNull(PatientIssuesService::getListTouch(self::PID, self::TOUCH_TYPE));
        $rows = QueryUtils::fetchRecords("SELECT `id` FROM `lists` WHERE `pid` = ?", [self::PID]);
        $this->assertSame([], $rows);
    }

    #[Test]
    public function testShimDisappearAndReappearListGuardRejectArrayIdAndLeaveActivityUnchanged(): void
    {
        $id = $this->insertListRow('lists_test_type', 'Row', 'c', 'u', 'g', '1');

        $this->assertFalse(disappearList([$id]));
        $this->assertFalse(reappearList([$id]));

        $row = $this->fetchListRow($id);
        $this->assertIsArray($row);
        $this->assertSame('1', $this->asString($row['activity']));
    }

    #[Test]
    public function testShimGetListTouchGuardRejectsArrayPatientId(): void
    {
        $this->assertFalse(getListTouch([self::PID], self::TOUCH_TYPE));
    }

    #[Test]
    public function testShimSetListTouchGuardRejectsArrayPatientIdAndWritesNothing(): void
    {
        setListTouch([self::PID], self::TOUCH_TYPE);

        $this->assertSame(0, $this->countTouchRows());
    }
}
