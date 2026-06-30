<?php

/**
 * Review of Systems Checks form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\Utilities;
use OpenEMR\Core\OEGlobalsBag;

require_once(__DIR__ . '/../../globals.php');
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");

function reviewofs_report($pid, $encounter, $cols, $id): void
{
    $count = 0;
    $data = formFetch("form_reviewofs", $id);
    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if (in_array($key, ["id", "pid", "user", "groupname", "authorized", "activity", "date"]) || Utilities::isDateEmpty($value)) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));

            //modified by BM 07-2009 for internationalization
            $valueText = is_string($value) ? $value : '';
            // @phpstan-ignore argument.type (legacy on-the-fly translation of dynamic value; migration tracked in #11498)
            $keyLabel = xlt($key);
            // @phpstan-ignore argument.type (legacy on-the-fly translation of dynamic value; migration tracked in #11498)
            $valueLabel = xlt($valueText);
            $valueOutput = $key == 'Additional Notes' ? text($valueText) : $valueLabel;
            printf('<td><span class="bold">%s: </span><span class="text">%s</span></td>', $keyLabel, $valueOutput);

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
