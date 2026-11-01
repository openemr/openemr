<?php

/**
 * Copies onsite_portal_activity rows into the review queue tables.
 *
 * Runs from the SQL upgrade (#IfPatientReviewMigrationNeeded). Each legacy row is copied in its
 * own transaction and remembered in patient_review_request.legacy_activity_id, so the
 * migration can be run again and only copies rows it has not copied yet. The legacy table is
 * left as it is; it is dropped in a later release.
 *
 * Rows are written directly rather than through ReviewQueueService: they carry their original
 * dates, statuses and reviewers, which the service (correctly) does not let a caller choose.
 * No audit log entries are written for them; their history is in patient_review_event.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Migration;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidFactoryInterface;

final readonly class PortalActivityMigrator
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private LegacyActivityMapping $mapping,
        private ClockInterface $clock,
        private UuidFactoryInterface $uuidFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Whether any legacy row is still to be copied.
     */
    public function isNeeded(): bool
    {
        if (!$this->tablesExist()) {
            return false;
        }
        $row = QueryUtils::querySingleRow(
            'SELECT 1 AS `found` FROM `onsite_portal_activity` AS `a`'
            . ' WHERE NOT EXISTS (SELECT 1 FROM `patient_review_request` AS `r` WHERE `r`.`legacy_activity_id` = `a`.`id`)'
            . ' LIMIT 1'
        );

        return is_array($row) && $row !== [];
    }

    public function migrate(): MigrationReport
    {
        $report = new MigrationReport();
        if (!$this->tablesExist()) {
            return $report;
        }

        $rows = QueryUtils::fetchRecords(
            'SELECT `a`.* FROM `onsite_portal_activity` AS `a`'
            . ' WHERE NOT EXISTS (SELECT 1 FROM `patient_review_request` AS `r` WHERE `r`.`legacy_activity_id` = `a`.`id`)'
            . ' ORDER BY `a`.`id` ASC'
        );
        $now = $this->clock->now();
        foreach ($rows as $row) {
            $plan = $this->mapping->plan($row, $now);
            if ($plan === null) {
                $report->skipped[] = is_numeric($row['id'] ?? null) ? intval($row['id']) : 0;
                continue;
            }
            QueryUtils::inTransaction(fn(): int => $this->write($plan));
            $report->migrated++;
            if ($plan->unrecognisedStatus !== null) {
                $report->unrecognised[$plan->legacyId] = $plan->unrecognisedStatus;
            }
        }

        if ($report->skipped !== []) {
            $this->logger->warning('Portal activity rows not migrated to the review queue: no patient or an unknown activity', [
                'legacy_ids' => $report->skipped,
            ]);
        }
        if ($report->unrecognised !== []) {
            $this->logger->warning('Portal activity rows with an unrecognised status were migrated as pending', [
                'statuses' => $report->unrecognised,
            ]);
        }

        return $report;
    }

    private function write(LegacyActivityPlan $plan): int
    {
        $id = QueryUtils::sqlInsert(
            'INSERT INTO `patient_review_request` (`uuid`, `pid`, `type`, `source`, `status`, `summary`, `payload`,'
            . ' `payload_format`, `target_table`, `target_id`, `created_at`, `updated_at`, `reviewed_by`, `reviewed_at`,'
            . ' `review_note`, `legacy_activity_id`) VALUES (?, ?, ?, \'portal\', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $this->uuidFactory->uuid4()->getBytes(),
                $plan->pid,
                $plan->type->value,
                $plan->status->value,
                $plan->summary,
                $plan->payload,
                $plan->payloadFormat->value,
                $plan->targetTable,
                $plan->targetId,
                $plan->createdAt->format(self::DATETIME_FORMAT),
                $plan->updatedAt->format(self::DATETIME_FORMAT),
                $plan->reviewedBy,
                $plan->reviewedAt?->format(self::DATETIME_FORMAT),
                $plan->reviewNote,
                $plan->legacyId,
            ]
        );
        foreach ($plan->events as $event) {
            QueryUtils::sqlInsert(
                'INSERT INTO `patient_review_event` (`request_id`, `from_status`, `to_status`, `actor_type`, `actor_id`, `created_at`, `note`)'
                . ' VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    $event->from?->value,
                    $event->to->value,
                    $event->actor->type->value,
                    $event->actor->id,
                    $event->at->format(self::DATETIME_FORMAT),
                    $event->note,
                ]
            );
        }
        if ($plan->cardCiphertext !== null) {
            // Already encrypted by paylib.php with the same CryptoInterface::encryptForDatabase().
            QueryUtils::sqlInsert(
                'INSERT INTO `patient_review_secret` (`request_id`, `kind`, `ciphertext`, `created_at`) VALUES (?, ?, ?, ?)',
                [$id, ReviewQueueService::SECRET_PAYMENT_CARD, $plan->cardCiphertext, $plan->createdAt->format(self::DATETIME_FORMAT)]
            );
        }

        return $id;
    }

    private function tablesExist(): bool
    {
        return QueryUtils::existsTable('onsite_portal_activity') && QueryUtils::existsTable('patient_review_request');
    }
}
