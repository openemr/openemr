<?php

/**
 * dictation report.php
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

function dictation_report($pid, $encounter, $cols, $id): void
{
    $cols = 1; // force always 1 column
    $count = 0;
    $data = formFetch("form_dictation", $id);
    if ($data) {
        foreach ($data as $key => $value) {
            if (
                in_array($key, ["id", "pid", "user", "groupname", "authorized", "activity", "date"]) || Utilities::isDateEmpty($value)
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            // @phpstan-ignore argument.type (legacy on-the-fly translation of dynamic value; migration tracked in #11498)
            printf('<h3>%s: </h3><p>%s</p>', xlt($key), nl2br(text($value)));
            $count++;
        }
    }
}
