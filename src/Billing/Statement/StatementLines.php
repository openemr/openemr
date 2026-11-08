<?php

/**
 * Builds the invoice lines of a patient statement, one encounter at a time.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

final class StatementLines
{
    /**
     * Adds one encounter's invoice lines to the statement being built and
     * carries the encounter's balance into the amount due. The statement array
     * is the one the statement run (interface/billing/sl_eob_search.php) hands
     * to the layouts in the site's statement.inc.php: `lines` is a list of
     * dos / desc / amount / adjust / paid / notice / detail / bill_date, `amount`
     * the total due as a "%.2f" string and `ins_paid` the running insurance total.
     *
     * @param array<string, mixed> $stmt
     * @param int $notice the statement number this will be for the encounter (1 for the first)
     */
    public static function append(array &$stmt, int $pid, int $encounter, string $svcdate, int $notice): void
    {
        // grab last bill date from billing
        $bdrow = QueryUtils::querySingleRow("select bill_date from billing where pid = ? AND encounter = ? limit 1", [$pid, $encounter]);
        $billDate = is_array($bdrow) ? ($bdrow['bill_date'] ?? null) : null;

        $invlines = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);

        $useCustomStatement = OEGlobalsBag::getInstance()->getBoolean('use_custom_statement');
        $lines = is_array($stmt['lines'] ?? null) ? $stmt['lines'] : [];
        $amount = is_numeric($stmt['amount'] ?? null) ? (float) $stmt['amount'] : 0.0;
        $insPaid = is_numeric($stmt['ins_paid'] ?? null) ? $stmt['ins_paid'] + 0 : 0;

        foreach ($invlines as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            $chg = is_numeric($value['chg'] ?? null) ? (float) $value['chg'] : 0.0;
            $bal = is_numeric($value['bal'] ?? null) ? (float) $value['bal'] : 0.0;
            $adj = is_numeric($value['adj'] ?? null) ? (float) $value['adj'] : 0.0;
            $codeText = is_string($value['code_text'] ?? null) ? $value['code_text'] : '';

            $line = [];
            $line['dos'] = $svcdate;
            if ($useCustomStatement) {
                $line['desc'] = ($key == 'CO-PAY') ? "Patient Payment" : $codeText;
            } else {
                $line['desc'] = ($key == 'CO-PAY') ? "Patient Payment" : "Procedure $key";
            }

            $line['amount'] = sprintf("%.2f", $chg);
            $line['adjust'] = sprintf("%.2f", $adj);
            $line['paid'] = sprintf("%.2f", $chg - $bal);
            $line['notice'] = $notice;
            $line['detail'] = is_array($value['dtl'] ?? null) ? $value['dtl'] : [];
            $line['bill_date'] = $billDate;
            $lines[] = $line;
            $amount = (float) sprintf("%.2f", $amount + $bal);
            if (is_numeric($value['ins'] ?? null)) {
                $insPaid += $value['ins'] + 0;
            }
        }

        $stmt['lines'] = $lines;
        $stmt['amount'] = sprintf("%.2f", $amount);
        $stmt['ins_paid'] = $insPaid;
    }
}
