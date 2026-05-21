<?php

/**
 * aftercare_plan report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\Utilities;
use OpenEMR\Core\OEGlobalsBag;

require_once(__DIR__ . '/../../globals.php');
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");

function aftercare_plan_report($pid, $encounter, $cols, $id): void
{
    $count = 0;
    $data = formFetch("form_aftercare_plan", $id);
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
            // @phpstan-ignore argument.type (legacy on-the-fly translation of dynamic value; migration tracked in #11498)
            printf('<td><span class="bold">%s: </span><span class="text">%s</span></td>', xlt($key), text($value));
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
