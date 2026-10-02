<?php

/**
 * PatientTransactionServiceTest.php
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
use OpenEMR\Services\PatientTransactionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PatientTransactionServiceTest extends TestCase
{
    // Pid picked to not collide with any fixture. Id picked to not collide with any
    // autoincrement `transactions.id` a fresh dev database will ever reach.
    private const PID = 987654321;
    private const UNKNOWN_ID = 999999999999;

    private const SESSION_USER = 'trans_test_user';
    private const SESSION_GROUPNAME = 'trans_test_group';

    private static bool $hadPreviousSession = false;
    private static ?SessionInterface $previousSession = null;

    /**
     * Injects a deterministic session (authUser/authProvider) for the shim's newTransaction()
     * to read, and remembers whatever session was active before so it can be restored.
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
     * Removes every lbt_data row for a fixture transaction, then the fixture transactions
     * themselves.
     */
    private function deleteFixtureRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `lbt_data` WHERE `form_id` IN (SELECT `id` FROM `transactions` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException("DELETE FROM `transactions` WHERE `pid` = ?", [self::PID]);
    }

    /**
     * Writes a transactions row directly, bypassing the code under test.
     */
    private function insertTransactionRow(string $title, string $date, string $user, string $groupname, int $authorized): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO `transactions` (`date`, `title`, `pid`, `user`, `groupname`, `authorized`) VALUES (?, ?, ?, ?, ?, ?)",
            [$date, $title, self::PID, $user, $groupname, $authorized]
        );
    }

    /**
     * Writes an lbt_data row directly, bypassing the code under test.
     */
    private function insertLbtField(int $formId, string $fieldId, string $fieldValue): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `lbt_data` (`form_id`, `field_id`, `field_value`) VALUES (?, ?, ?)",
            [$formId, $fieldId, $fieldValue]
        );
    }

    /**
     * Reads a transactions row directly, bypassing the code under test.
     *
     * @return array<mixed>|null
     */
    private function fetchTransactionRow(int $id): ?array
    {
        $row = QueryUtils::querySingleRow("SELECT * FROM `transactions` WHERE `id` = ?", [$id]);

        return is_array($row) ? $row : null;
    }

    /**
     * Reads a single lbt_data field value directly, bypassing the code under test.
     */
    private function fetchLbtValue(int $formId, string $fieldId): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT `field_value` FROM `lbt_data` WHERE `form_id` = ? AND `field_id` = ?",
            [$formId, $fieldId]
        );
        $value = is_array($row) ? ($row['field_value'] ?? null) : null;

        return is_string($value) ? $value : null;
    }

    /**
     * Casts a raw DB column value to string for assertion comparisons. id/pid/authorized are
     * never non-scalar in practice; empty string is the fallback for anything that is.
     */
    private function asString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    #[Test]
    public function testGetTransByIdMergesTransactionColumnsAndLbtFields(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 1);
        $this->insertLbtField($id, 'body', 'note body');
        $this->insertLbtField($id, 'refer_diag', 'diagnosis code');

        $row = getTransById($id);

        $this->assertIsArray($row);
        $this->assertSame((string) $id, $this->asString($row['id']));
        $this->assertSame('Referral', $row['title']);
        $this->assertSame((string) self::PID, $this->asString($row['pid']));
        $this->assertSame('drA', $row['user']);
        $this->assertSame('groupA', $row['groupname']);
        $this->assertSame('note body', $row['body']);
        $this->assertSame('diagnosis code', $row['refer_diag']);
    }

    #[Test]
    public function testGetTransByIdRespectsColsParameter(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 1);

        $row = getTransById($id, 'id, title');

        $this->assertIsArray($row);
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('title', $row);
        $this->assertArrayNotHasKey('pid', $row);
        $this->assertArrayNotHasKey('user', $row);
    }

    /**
     * Characterization: the pre-refactor function returned false (sqlQuery's miss value) for an
     * unknown id. The new contract returns null. Expected to FAIL against the untouched
     * library/transactions.inc.php.
     */
    #[Test]
    public function testGetTransByIdReturnsNullForUnknownId(): void
    {
        $this->assertNull(getTransById(self::UNKNOWN_ID));
    }

    #[Test]
    public function testGetTransByPidReturnsEmptyArrayForUnknownPid(): void
    {
        $this->assertSame([], getTransByPid(self::PID));
    }

    #[Test]
    public function testGetTransByPidOrdersByDateDescAndMergesLbtFields(): void
    {
        $idOld = $this->insertTransactionRow('Older', '2026-01-01 08:00:00', 'drA', 'groupA', 0);
        $idNew = $this->insertTransactionRow('Newer', '2026-02-01 08:00:00', 'drB', 'groupB', 1);
        $this->insertLbtField($idOld, 'body', 'old body');
        $this->insertLbtField($idNew, 'body', 'new body');

        $rows = getTransByPid(self::PID);

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $row0 = $rows[0];
        $row1 = $rows[1];
        $this->assertIsArray($row0);
        $this->assertIsArray($row1);
        $this->assertSame((string) $idNew, $this->asString($row0['id']));
        $this->assertSame('new body', $row0['body']);
        $this->assertSame((string) $idOld, $this->asString($row1['id']));
        $this->assertSame('old body', $row1['body']);
    }

    #[Test]
    public function testGetTransByPidRespectsColsParameter(): void
    {
        $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 1);

        $rows = getTransByPid(self::PID, 'id, title');

        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $row0 = $rows[0];
        $this->assertIsArray($row0);
        $this->assertArrayHasKey('id', $row0);
        $this->assertArrayHasKey('title', $row0);
        $this->assertArrayNotHasKey('pid', $row0);
    }

    #[Test]
    public function testServiceNewTransactionStoresRowAndVerbatimBody(): void
    {
        $body = "it's C:\\tmp";
        $id = PatientTransactionService::newTransaction(self::PID, $body, 'Referral title', 'svc_user', 'svc_group', 1);

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame('Referral title', $row['title']);
        $this->assertSame((string) self::PID, $this->asString($row['pid']));
        $this->assertSame('svc_user', $row['user']);
        $this->assertSame('svc_group', $row['groupname']);
        $this->assertSame('1', $this->asString($row['authorized']));
        $this->assertNotNull($row['date']);

        // Characterization: the pre-refactor function ran the body through add_escape_custom()
        // before binding it, double-escaping quotes/backslashes. Expected to FAIL against the
        // untouched library/transactions.inc.php.
        $this->assertSame($body, $this->fetchLbtValue($id, 'body'));
    }

    #[Test]
    public function testShimNewTransactionUsesSessionUserAndGroupname(): void
    {
        $id = newTransaction(self::PID, 'shim body', 'Shim title', '1');

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame(self::SESSION_USER, $row['user']);
        $this->assertSame(self::SESSION_GROUPNAME, $row['groupname']);
        $this->assertSame('Shim title', $row['title']);
        $this->assertSame('1', $this->asString($row['authorized']));
        $this->assertSame('shim body', $this->fetchLbtValue($id, 'body'));
    }

    #[Test]
    public function testAuthorizeTransactionSetsFlagWithDefault(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 0);

        authorizeTransaction($id);

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame('1', $this->asString($row['authorized']));
    }

    #[Test]
    public function testAuthorizeTransactionSetsExplicitZero(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 1);

        authorizeTransaction($id, '0');

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame('0', $this->asString($row['authorized']));
    }

    #[Test]
    public function testAuthorizeTransactionOnUnknownIdChangesNothing(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 0);

        authorizeTransaction(self::UNKNOWN_ID, '1');

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame('0', $this->asString($row['authorized']));
    }

    #[Test]
    public function testShimGetTransByIdGuardRejectsArrayId(): void
    {
        $this->assertNull(getTransById([self::PID]));
    }

    #[Test]
    public function testShimGetTransByPidGuardRejectsArrayPid(): void
    {
        $this->assertSame([], getTransByPid([self::PID]));
    }

    #[Test]
    public function testShimNewTransactionGuardRejectsArrayPidAndWritesNothing(): void
    {
        $result = newTransaction([self::PID], 'body', 'title');

        $this->assertSame(0, $result);
        $this->assertSame([], getTransByPid(self::PID));
    }

    #[Test]
    public function testShimAuthorizeTransactionGuardRejectsArrayIdAndLeavesRowUnchanged(): void
    {
        $id = $this->insertTransactionRow('Referral', '2026-01-05 10:00:00', 'drA', 'groupA', 0);

        authorizeTransaction([$id], '1');

        $row = $this->fetchTransactionRow($id);
        $this->assertIsArray($row);
        $this->assertSame('0', $this->asString($row['authorized']));
    }
}
