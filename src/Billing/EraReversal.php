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
     * @param string $payerId the insurance company the ERA is posted for (InsId);
     *                        '' when the ERA was opened from EOB Posting - Search,
     *                        which doesn't ask for one
     */
    public function __construct(private string $payerId)
    {
    }

    /**
     * The insurance level (1-3) the reversal is for, or 0 when it can't be told.
     * A reversal's claim status (22) doesn't say which level it reverses.
     * - With a payer chosen: the level at which that payer covers the claim on
     *   its date of service.
     * - Without one: the level of the payment being reversed, found by the
     *   payer's claim number (CLP07) that ERA posting stores on its rows; or,
     *   if the patient had one insurance on the date of service, that one.
     */
    public function reversedLevel(int $pid, int $encounter, string $dateOfService, string $payerClaimNumber): int
    {
        $providers = $this->providers($pid, $dateOfService);
        if ($this->payerId !== '') {
            $level = array_search($this->payerId, $providers, true);
            return is_int($level) ? $level : 0;
        }
        if ($payerClaimNumber !== '') {
            $levels = QueryUtils::fetchTableColumn(
                'SELECT DISTINCT payer_type FROM ar_activity WHERE pid = ? AND encounter = ? '
                . 'AND payer_claim_number = ? AND payer_type > 0 AND deleted IS NULL',
                'payer_type',
                [$pid, $encounter, $payerClaimNumber]
            );
            if (count($levels) === 1 && is_numeric($levels[0])) {
                return (int) $levels[0];
            }
        }
        return count($providers) === 1 && isset($providers[1]) ? 1 : 0;
    }

    /**
     * Re-open $level for the encounter if it is closed and this check posted
     * nothing for the claim at that level (no corrected payment).
     *
     * @param int    $level       from reversedLevel()
     * @param string $checkNumber the reversal's check (TRN02)
     * @param string $checkDate   its date, yyyymmdd (BPR16), or ''
     */
    public function reopenLevel(int $pid, int $encounter, int $level, string $checkNumber, string $checkDate): EraReversalOutcome
    {
        if ($level === 0) {
            return $this->payerId === '' ? EraReversalOutcome::LevelUnknown : EraReversalOutcome::PayerNotOnClaim;
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
     * The insurance companies covering the patient on the date of service, by
     * level, as SLEOB::arGetPayerID() finds them (the newest policy of each type
     * in effect). Levels stop at the first one with no insurance.
     *
     * @return array<int, string>
     */
    private function providers(int $pid, string $dateOfService): array
    {
        $providers = [];
        foreach ([1 => 'primary', 2 => 'secondary', 3 => 'tertiary'] as $level => $type) {
            $provider = QueryUtils::fetchSingleValue(
                'SELECT provider FROM insurance_data WHERE pid = ? AND type = ? '
                . 'AND (date <= ? OR date IS NULL) AND (date_end >= ? OR date_end IS NULL) ORDER BY date DESC LIMIT 1',
                'provider',
                [$pid, $type, $dateOfService, $dateOfService]
            );
            if (!is_scalar($provider) || in_array((string) $provider, ['', '0'], true)) {
                break;
            }
            $providers[$level] = (string) $provider;
        }
        return $providers;
    }

    /**
     * Whether this check posted anything (payments, adjustments or notes) for
     * the claim at this level: the corrected half of a correction. Without a
     * payer chosen the check's sessions have none either, so the payer isn't
     * compared.
     */
    private function postedFromCheck(int $pid, int $encounter, int $level, string $checkNumber, string $checkDate): bool
    {
        [$bare, $era] = EraCheckPosting::references($checkNumber);
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM ar_activity AS a JOIN ar_session AS s ON s.session_id = a.session_id "
            . "WHERE a.pid = ? AND a.encounter = ? AND a.payer_type = ? AND a.deleted IS NULL "
            . "AND s.reference IN (?, ?) AND (? = '' OR s.payer_id = ?) AND (? = '' OR s.check_date = ?)",
            'n',
            [$pid, $encounter, $level, $bare, $era, $this->payerId, $this->payerId, $checkDate, $checkDate]
        );
        return is_numeric($count) && (int) $count > 0;
    }
}
