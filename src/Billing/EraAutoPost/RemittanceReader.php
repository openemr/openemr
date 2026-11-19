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
        /** @var array<string, array{out: array<mixed>, claims: list<ClaimPayment>}> $found */
        $found = [];
        ob_start();
        try {
            $error = ParseERA::parseERA($path, static function (array $out) use (&$found): void {
                // One check per transaction set; the group number keeps sets of two groups apart.
                $key = self::text($out, 'gs_control_number') . '/' . self::text($out, 'st_control_number');
                $claims = $found[$key]['claims'] ?? [];
                $claims[] = ClaimPayment::fromParsedEra($out);
                // The last claim's copy is kept: provider-level adjustments follow the claims.
                $found[$key] = ['out' => $out, 'claims' => $claims];
            });
        } finally {
            ob_end_clean();
        }

        $checks = [];
        foreach ($found as $check) {
            $out = $check['out'];
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
            $checks[] = new RemittanceCheck(
                checkNumber: self::text($out, 'check_number'),
                checkDate: self::text($out, 'check_date'),
                amount: is_numeric($amount) ? (float) $amount : 0.0,
                payerName: self::text($out, 'payer_name'),
                claims: $check['claims'],
                plbTotal: round($plbTotal, 2),
                plbReasons: $plbReasons,
            );
        }
        return new Remittance($checks, $error);
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
