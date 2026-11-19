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
    public function read(string $path): Remittance
    {
        /** @var list<ClaimPayment> $claims the claim payments of the transaction set being read */
        $claims = [];
        /** @var list<RemittanceCheck> $checks */
        $checks = [];
        ob_start();
        try {
            $error = ParseERA::parseERA(
                $path,
                static function (array $out) use (&$claims): void {
                    $claims[] = ClaimPayment::fromParsedEra($out);
                },
                // One check per transaction set, whether or not it has claims: a set can
                // carry only provider-level adjustments.
                static function (array $out) use (&$claims, &$checks): void {
                    $checks[] = self::check($out, $claims);
                    $claims = [];
                }
            );
        } finally {
            ob_end_clean();
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
