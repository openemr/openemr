<?php

/**
 * Immutable result of an appointment-reminder run.
 *
 * Shared vocabulary for any module that scans upcoming appointments and
 * dispatches reminders (SMS, email, third-party messaging, etc.). Lives in
 * `src/` rather than inside a single module so modules can report results
 * in a consistent shape without each one inventing its own counts array.
 *
 * The bodies of the individual per-module runners stay in their modules;
 * only this result type is promoted to core. See issue #11827 for the
 * original refactor and the follow-up issue for a core
 * AppointmentReminderJob interface.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\Reminder;

final readonly class ReminderRunResult
{
    /**
     * @param int $scanned         Candidate appointments returned by the module's scan query.
     * @param int $inWindow        Subset of candidates inside the send-now window.
     * @param int $sent            Messages the transport reported as sent.
     * @param int $skippedInvalid  Rows skipped because the recipient (phone/email) was invalid or ineligible.
     * @param int $failed          Rows that hit a transport/provider error or caught exception.
     */
    public function __construct(
        public int $scanned,
        public int $inWindow,
        public int $sent,
        public int $skippedInvalid,
        public int $failed,
    ) {
    }

    public function hasFailures(): bool
    {
        return $this->failed > 0;
    }

    /**
     * @return array{scanned: int, in_window: int, sent: int, skipped_invalid: int, failed: int}
     */
    public function toArray(): array
    {
        return [
            'scanned' => $this->scanned,
            'in_window' => $this->inWindow,
            'sent' => $this->sent,
            'skipped_invalid' => $this->skippedInvalid,
            'failed' => $this->failed,
        ];
    }
}
