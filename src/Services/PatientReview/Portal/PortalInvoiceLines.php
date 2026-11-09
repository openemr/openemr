<?php

/**
 * The invoice lines the portal payment page offers a patient: one per encounter of theirs
 * (portal_payment.php renders every form_encounter row). A payment may name only these.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

use OpenEMR\Common\Database\QueryUtils;

final class PortalInvoiceLines
{
    /**
     * The patient's encounter numbers.
     *
     * @return list<int>
     */
    public function encounters(int $pid): array
    {
        $encounters = [];
        foreach (QueryUtils::fetchTableColumn('SELECT `encounter` FROM `form_encounter` WHERE `pid` = ?', 'encounter', [$pid]) as $encounter) {
            if (is_numeric($encounter)) {
                $encounters[] = (int) $encounter;
            }
        }

        return $encounters;
    }
}
