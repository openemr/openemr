<?php

/**
 * TaskService reads a task and its inputs and outputs in one REPEATABLE READ transaction it asks for
 * itself. Inside a caller's open transaction the database refuses that request, so the caller gets an
 * error and keeps its transaction, where ADODB's BEGIN would otherwise have committed it.
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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\TaskService;
use PHPUnit\Framework\TestCase;

final class TaskServiceSnapshotTest extends TestCase
{
    private const MARK = 'test-fixture task snapshot probe';

    public function testAReadInsideAnOpenTransactionIsRefusedAndTheTransactionKept(): void
    {
        $refused = false;
        try {
            QueryUtils::inTransaction(static function (): void {
                // Written in the caller's transaction: it's gone after the rollback unless something committed it.
                QueryUtils::sqlInsert(
                    "INSERT INTO task (uuid, patient_id, status, intent, description, version) VALUES (?, 0, 'draft', 'order', ?, 1)",
                    [(new UuidRegistry(['disable_tracker' => true]))->createUuid(), self::MARK]
                );
                (new TaskService())->search([]);
            });
        } catch (SqlQueryException) {
            $refused = true;
        }
        $kept = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM task WHERE description = ?', 'n', [self::MARK]);
        QueryUtils::sqlStatementThrowException('DELETE FROM task WHERE description = ?', [self::MARK]);
        $this->assertTrue($refused, 'a Task read inside an open transaction is refused');
        $this->assertSame('0', is_scalar($kept) ? (string)$kept : '', 'the caller\'s transaction was rolled back, not committed by the read');
    }
}
