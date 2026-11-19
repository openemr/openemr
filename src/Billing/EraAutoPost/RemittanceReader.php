<?php

/**
 * Reads an 835 file into its checks, with ParseERA.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\ParseERA;

final class RemittanceReader
{
    /** The error for a file with a transaction set that has no payment (BPR) or trace number (TRN) of its own. */
    public const NO_CHECK_HEADER = 'A transaction set has no BPR or TRN segment';

    /** The error for a file with a transaction set that has no SE segment before the next set begins. */
    public const SET_NOT_CLOSED = 'A transaction set does not end with its SE segment';

    public function read(string $path): Remittance
    {
        /** @var list<ClaimPayment> $claims the claim payments of the transaction set being read */
        $claims = [];
        /** @var list<RemittanceCheck> $checks */
        $checks = [];
        $headless = false;
        /** @var list<string> $sets the transaction set each of those claim payments was read in */
        $sets = [];
        $unclosed = false;
        ob_start();
        try {
            $error = ParseERA::parseERA(
                $path,
                static function (array $out) use (&$claims, &$sets): void {
                    $claims[] = ClaimPayment::fromParsedEra($out);
                    $sets[] = self::text($out, 'st_control_number');
                },
                // One check per transaction set, whether or not it has claims: a set can
                // carry only provider-level adjustments.
                static function (array $out) use (&$claims, &$sets, &$checks, &$headless, &$unclosed): void {
                    // A set that never got its SE leaves its claims to be counted in the next one.
                    if (array_diff($sets, [self::text($out, 'st_control_number')]) !== []) {
                        $unclosed = true;
                    }
                    $sets = [];
                    // Without its own BPR and TRN the set would be read as the check before it.
                    if (($out['st_has_bpr'] ?? false) !== true || ($out['st_has_trn'] ?? false) !== true) {
                        $headless = true;
                    }
                    $checks[] = self::check($out, $claims);
                    $claims = [];
                }
            );
        } finally {
            ob_end_clean();
        }
        if ($error === '' && $headless) {
            $error = self::NO_CHECK_HEADER;
        }
        if ($error === '' && ($unclosed || $claims !== [])) {
            $error = self::SET_NOT_CLOSED;
        }
        return new Remittance($checks, $error);
    }

    /**
     * @param array<mixed>       $out    the parser's array at the end of the transaction set
     * @param list<ClaimPayment> $claims
     */
    private static function check(array $out, array $claims): RemittanceCheck
    {
        $plbTotal = 0.0;
        $plbReasons = [];
        $plbs = $out['plb'] ?? [];
        foreach (is_array($plbs) ? $plbs : [] as $plb) {
            if (!is_array($plb)) {
                continue;
            }
            $amount = $plb['amount'] ?? 0.0;
            $plbTotal += is_numeric($amount) ? (float) $amount : 0.0;
            $reason = self::text($plb, 'reason');
            if ($reason !== '' && !in_array($reason, $plbReasons, true)) {
                $plbReasons[] = $reason;
            }
        }
        $amount = $out['check_amount'] ?? 0.0;
        return new RemittanceCheck(
            checkNumber: self::text($out, 'check_number'),
            checkDate: self::text($out, 'check_date'),
            amount: is_numeric($amount) ? (float) $amount : 0.0,
            payerName: self::text($out, 'payer_name'),
            claims: $claims,
            plbTotal: round($plbTotal, 2),
            plbReasons: $plbReasons,
        );
    }

    /**
     * @param array<mixed> $values
     */
    private static function text(array $values, string $key): string
    {
        $value = $values[$key] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }
}
