<?php

/**
 * Re-opens the insurance level a payment reversal (835 CLP02 = 22) took the
 * money back from.
 *
 * ERA posting doesn't post reversals; the biller enters them. But the
 * original payment closed its level, so until someone re-opens it the
 * encounter counts as insurance-done, and once the reversal is entered the
 * balance is treated as the patient's: statements bill the whole charge.
 * Re-opening the level when the reversal arrives keeps the balance with the
 * payer. A correction (reversal plus corrected payment) posted from the same
 * check leaves the level closed, in either order: the corrected payment
 * closes it like any payment, and a reversal that finds it posted leaves it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

final readonly class EraReversal
{
    /**
     * @param string $payerId the insurance company the ERA is posted for (InsId)
     */
    public function __construct(private string $payerId)
    {
    }

    /**
     * The insurance level (1-3) at which the ERA's payer covers the claim on its
     * date of service, or 0. A reversal's claim status (22) doesn't say which
     * level it reverses, so the payer decides.
     */
    public function payerLevel(int $pid, string $dateOfService): int
    {
        foreach ([1 => 'primary', 2 => 'secondary', 3 => 'tertiary'] as $level => $type) {
            $provider = QueryUtils::fetchSingleValue(
                'SELECT provider FROM insurance_data WHERE pid = ? AND type = ? '
                . 'AND (date <= ? OR date IS NULL) AND (date_end >= ? OR date_end IS NULL) ORDER BY date DESC LIMIT 1',
                'provider',
                [$pid, $type, $dateOfService, $dateOfService]
            );
            if (is_scalar($provider) && (string) $provider === $this->payerId) {
                return $level;
            }
        }
        return 0;
    }

    /**
     * Re-open $level for the encounter if it is closed and this check posted
     * nothing for the claim at that level (no corrected payment).
     *
     * @param int    $level       from payerLevel()
     * @param string $checkNumber the reversal's check (TRN02)
     * @param string $checkDate   its date, yyyymmdd (BPR16), or ''
     */
    public function reopenLevel(int $pid, int $encounter, int $level, string $checkNumber, string $checkDate): EraReversalOutcome
    {
        if ($level === 0) {
            return EraReversalOutcome::PayerNotOnClaim;
        }
        $closed = QueryUtils::fetchSingleValue(
            'SELECT last_level_closed FROM form_encounter WHERE pid = ? AND encounter = ?',
            'last_level_closed',
            [$pid, $encounter]
        );
        if (!is_numeric($closed) || (int) $closed < $level) {
            return EraReversalOutcome::AlreadyOpen;
        }
        if ($this->postedFromCheck($pid, $encounter, $level, $checkNumber, $checkDate)) {
            return EraReversalOutcome::CorrectionPosted;
        }
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_encounter SET last_level_closed = ? WHERE pid = ? AND encounter = ? AND last_level_closed >= ?',
            [$level - 1, $pid, $encounter, $level]
        );
        return EraReversalOutcome::Reopened;
    }

    /**
     * Whether this check posted anything (payments, adjustments or notes) for
     * the claim at this level: the corrected half of a correction.
     */
    private function postedFromCheck(int $pid, int $encounter, int $level, string $checkNumber, string $checkDate): bool
    {
        [$bare, $era] = EraCheckPosting::references($checkNumber);
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM ar_activity AS a JOIN ar_session AS s ON s.session_id = a.session_id "
            . "WHERE a.pid = ? AND a.encounter = ? AND a.payer_type = ? AND a.deleted IS NULL "
            . "AND s.reference IN (?, ?) AND s.payer_id = ? AND (? = '' OR s.check_date = ?)",
            'n',
            [$pid, $encounter, $level, $bare, $era, $this->payerId, $checkDate, $checkDate]
        );
        return is_numeric($count) && (int) $count > 0;
    }
}
