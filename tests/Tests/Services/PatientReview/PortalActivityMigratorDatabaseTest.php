<?php

/**
 * The onsite_portal_activity migration against the database: what it writes, that it runs
 * once per row, and that the staff pending counts match before and after.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PatientReview;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\Migration\LegacyActivityMapping;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\TestCase;

class PortalActivityMigratorDatabaseTest extends TestCase
{
    /** A patient id no fixture uses. */
    private const PID = 990081;

    protected function setUp(): void
    {
        $this->removeTestRows();
    }

    protected function tearDown(): void
    {
        $this->removeTestRows();
    }

    private function removeTestRows(): void
    {
        foreach (['patient_review_secret', 'patient_review_event'] as $table) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM ' . QueryUtils::escapeTableName($table)
                . ' WHERE `request_id` IN (SELECT `id` FROM `patient_review_request` WHERE `pid` = ?)',
                [self::PID]
            );
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_review_request` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_portal_activity` WHERE `patient_id` = ?', [self::PID]);
    }

    private function legacy(
        string $activity,
        string $status,
        int $requireAudit = 1,
        string $tableArgs = '',
        string $checksum = '0',
        string $actionTaken = '',
        int $actionUser = 0,
    ): int {
        return QueryUtils::sqlInsert(
            'INSERT INTO `onsite_portal_activity` (`date`, `patient_id`, `activity`, `require_audit`, `pending_action`,'
            . ' `action_taken`, `status`, `narrative`, `table_action`, `table_args`, `action_user`, `action_taken_time`, `checksum`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                '2026-09-01 08:00:00',
                self::PID,
                $activity,
                $requireAudit,
                'review',
                $actionTaken,
                $status,
                'Migration test ' . $activity,
                '',
                $tableArgs,
                $actionUser,
                $actionUser > 0 ? '2026-09-02 10:00:00' : null,
                $checksum,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function migratedRow(int $legacyId): array
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_review_request` WHERE `legacy_activity_id` = ?', [$legacyId]);
        $this->assertIsArray($row, 'legacy row ' . $legacyId . ' was migrated');
        $columns = [];
        foreach ($row as $column => $value) {
            if (is_string($column)) {
                $columns[$column] = $value;
            }
        }
        return $columns;
    }

    private function intOf(mixed $value): int
    {
        $this->assertIsNumeric($value);
        return intval($value);
    }

    private function pendingFor(?ReviewType $type = null): int
    {
        $sql = 'SELECT COUNT(*) AS `total` FROM `patient_review_request` WHERE `pid` = ? AND `status` = ?';
        $binds = [self::PID, ReviewStatus::Pending->value];
        if ($type !== null) {
            $sql .= ' AND `type` = ?';
            $binds[] = $type->value;
        }
        $total = QueryUtils::querySingleRow($sql, $binds)['total'] ?? 0;
        $this->assertIsNumeric($total);
        return intval($total);
    }

    private function legacyWaitingFor(?string $activity = null): int
    {
        // The query GetPortalAlertCounts() ran against onsite_portal_activity.
        $sql = 'SELECT COUNT(*) AS `total` FROM `onsite_portal_activity` WHERE `patient_id` = ? AND `status` = ? AND `require_audit` = ?';
        $binds = [self::PID, 'waiting', 1];
        if ($activity !== null) {
            $sql .= ' AND `activity` = ?';
            $binds[] = $activity;
        }
        $total = QueryUtils::querySingleRow($sql, $binds)['total'] ?? 0;
        $this->assertIsNumeric($total);
        return intval($total);
    }

    public function testMigratesEachWorkflowWithItsHistoryAndRunsOnce(): void
    {
        $profile = $this->legacy('profile', 'waiting', tableArgs: serialize(['fname' => 'Ann', 'pid' => (string) self::PID]));
        $payment = $this->legacy('payment', 'waiting', tableArgs: '{"1001":"10.00"}', checksum: 'v6:card-ciphertext');
        $posted = $this->legacy('payment', 'closed', tableArgs: '{"1001":"5.00"}', checksum: 'v6:old-card', actionTaken: 'payment posted', actionUser: 1);
        $document = $this->legacy('document', 'waiting', requireAudit: 1, tableArgs: '77');
        $editing = $this->legacy('document', 'editing', tableArgs: '78');
        $invoice = $this->legacy('invoice', 'waiting transaction', requireAudit: 0, tableArgs: '[{"encounter":5}]');
        $odd = $this->legacy('profile', 'new');
        $unknown = $this->legacy('message', 'waiting');

        $migrator = ReviewQueueFactory::portalActivityMigrator();
        $this->assertTrue($migrator->isNeeded());
        $report = $migrator->migrate();

        $this->assertContains($unknown, $report->skipped);
        $this->assertSame('new', $report->unrecognised[$odd] ?? null);
        $this->assertFalse($migrator->isNeeded(), 'a skipped row does not make every upgrade pass run the migration again');

        $queue = ReviewQueueFactory::create();
        $profileRequest = $queue->get($this->intOf($this->migratedRow($profile)['id']));
        $this->assertSame(ReviewType::Profile, $profileRequest->type);
        $this->assertSame(ReviewStatus::Pending, $profileRequest->status);
        $this->assertSame('Ann', $profileRequest->payload['fname'] ?? null, 'the serialized profile payload is readable');

        $paymentId = $this->intOf($this->migratedRow($payment)['id']);
        $this->assertSame(['value' => '{"1001":"10.00"}'], $queue->get($paymentId)->payload);
        $secret = QueryUtils::querySingleRow(
            'SELECT `ciphertext` FROM `patient_review_secret` WHERE `request_id` = ? AND `kind` = ?',
            [$paymentId, ReviewQueueService::SECRET_PAYMENT_CARD]
        );
        $this->assertSame('v6:card-ciphertext', is_array($secret) ? $secret['ciphertext'] : null, 'open payment keeps its card data');

        $postedRow = $this->migratedRow($posted);
        $this->assertSame(ReviewStatus::Completed->value, $postedRow['status']);
        $this->assertSame('payment posted', $postedRow['review_note']);
        $this->assertSame(1, $this->intOf($postedRow['reviewed_by']));
        $this->assertFalse(
            is_array(QueryUtils::querySingleRow('SELECT 1 AS `found` FROM `patient_review_secret` WHERE `request_id` = ?', [$this->intOf($postedRow['id'])])),
            'a posted payment does not keep card data'
        );
        $this->assertCount(2, $queue->history($this->intOf($postedRow['id'])));

        $documentRow = $this->migratedRow($document);
        $this->assertSame(['onsite_documents', '77'], [$documentRow['target_table'], $documentRow['target_id']]);
        $this->assertSame(ReviewStatus::Cancelled->value, $this->migratedRow($editing)['status']);
        $this->assertSame(ReviewStatus::AwaitingPayment->value, $this->migratedRow($invoice)['status']);

        $oddHistory = $queue->history($this->intOf($this->migratedRow($odd)['id']));
        $this->assertStringStartsWith(LegacyActivityMapping::NOTE_UNRECOGNISED, (string) $oddHistory[0]->note);

        // A second run copies nothing new.
        $again = $migrator->migrate();
        $this->assertSame(0, $again->migrated);
        $copies = QueryUtils::querySingleRow('SELECT COUNT(*) AS `total` FROM `patient_review_request` WHERE `pid` = ?', [self::PID])['total'] ?? 0;
        $this->assertSame(7, $this->intOf($copies));
    }

    public function testPendingCountsMatchTheLegacyCounts(): void
    {
        $this->legacy('profile', 'waiting', tableArgs: serialize(['fname' => 'Ann']));
        $this->legacy('payment', 'waiting', tableArgs: '{}');
        $this->legacy('payment', 'waiting', tableArgs: '{}');
        $this->legacy('payment', 'closed', tableArgs: '{}');
        $this->legacy('document', 'waiting', tableArgs: '81');
        $this->legacy('invoice', 'waiting transaction', requireAudit: 0, tableArgs: '[]');

        $legacyAudits = $this->legacyWaitingFor();
        $legacyPayments = $this->legacyWaitingFor('payment');

        ReviewQueueFactory::portalActivityMigrator()->migrate();

        $this->assertSame($legacyAudits, $this->pendingFor(), 'audit badge count');
        $this->assertSame($legacyPayments, $this->pendingFor(ReviewType::Payment), 'payment badge count');
    }
}
