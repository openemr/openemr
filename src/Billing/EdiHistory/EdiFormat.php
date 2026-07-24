<?php

/**
 * Formatting helpers for the EDI history (edihistory) X12 display code.
 *
 * Holds the canonical implementations of the edih_format_* routines as
 * autoloadable static methods, so namespaced code such as Claim277Renderer
 * can call them without depending on the non-autoloaded procedural include
 * library/edihistory/edih_csv_inc.php. The global edih_format_date() and
 * edih_format_money() functions delegate here as a backfill until every
 * legacy call site is migrated to the class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin McCormick
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Kevin McCormick
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final class EdiFormat
{
    private function __construct()
    {
    }

    /**
     * Order the digits of an X12 date and insert separators.
     *
     * A six-digit value is expanded to eight digits using the current
     * century. US preference yields MM/DD/YYYY; anything else yields the
     * general YYYY-MM-DD.
     *
     * @param string $str_val the date digits (six or eight)
     * @param string $pref    'US' for MM/DD/YYYY, otherwise YYYY-MM-DD
     */
    public static function date(string $str_val, string $pref = 'Y-m-d'): string
    {
        $strdt = preg_replace('/\D/', '', $str_val) ?? '';
        if (strlen($strdt) === 6) {
            $tdy = date('Ymd');
            $strdt = ($pref === 'US')
                ? substr($tdy, 0, 2) . substr($strdt, -2) . substr($strdt, 0, 4)
                : substr($tdy, 0, 2) . $strdt;
        }

        return ($pref === 'US')
            ? substr($strdt, 4, 2) . '/' . substr($strdt, 6) . '/' . substr($strdt, 0, 4)
            : substr($strdt, 0, 4) . '-' . substr($strdt, 4, 2) . '-' . substr($strdt, 6);
    }

    /**
     * Format a monetary amount with a leading $ and two decimal places.
     *
     * The empty string passes through unchanged, matching the legacy helper.
     *
     * @param string $str_val the amount, e.g. '150.5'
     */
    public static function money(string $str_val): string
    {
        return ($str_val !== '') ? sprintf('$%01.2f', (float) $str_val) : $str_val;
    }
}
