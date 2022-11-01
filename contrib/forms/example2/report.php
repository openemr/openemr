<?php

/**
 * example2 report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

/** CHANGE THIS, the name of the function is significant and  **
 **              must be changed to match the folder name     **/
function example_report($pid, $encounter, $cols, $id)
{

    /** CHANGE THIS - name of the database table associated with this form **/
    $table_name = "form_example";

    $count = 0;
    $data = formFetch($table_name, $id);

    if ($data) {
        print "<table><tr>";

        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" || $key == "user" ||
                $key == "groupname" || $key == "authorized" ||
                $key == "activity" || $key == "date" ||
                $value == "" || $value == "0000-00-00 00:00:00" ||
                $value == "n"
            ) {
                // skip certain fields and blank data
                continue;
            }

            $key = ucwords(str_replace("_", " ", $key));
            print("<tr>\n");
            print("<tr>\n");
            print "<td><span class=bold>" . text($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
