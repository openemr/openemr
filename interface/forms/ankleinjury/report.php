<?php

/**
 * ankleinjury report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\Utilities;
use OpenEMR\Core\OEGlobalsBag;

require_once(__DIR__ . '/../../globals.php');
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");

function ankleinjury_report($pid, $encounter, $cols, $id): void
{
    $count = 0;
    $data = formFetch("form_ankleinjury", $id);
    if ($data) {
        print "<table>\n<tr>\n";
        foreach ($data as $key => $value) {
            if (
                in_array($key, ["id", "pid", "user", "groupname", "authorized", "activity", "date"]) ||
                Utilities::isDateEmpty($value)
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            $key = str_replace("Ankle ", "", $key);
            $key = str_replace("Injuary", "Injury", $key);
            // @phpstan-ignore argument.type (legacy on-the-fly translation of dynamic value; migration tracked in #11498)
            printf('<td valign="top"><span class="bold">%s: </span><span class="text">%s</span></td>', xlt($key), text($value));
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr>\n<tr>\n";
            }
        }

        print "</tr>\n</table>\n";
    }
}
