<?php

/**
 * ankleinjury report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function ankleinjury_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_ankleinjury", $id);
    if ($data) {
        print "<table>\n<tr>\n";
        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" || $key == "user" || $key == "groupname" ||
                $key == "authorized" || $key == "activity" || $key == "date" ||
                $value == "" || $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            $key = str_replace("Ankle ", "", $key);
            $key = str_replace("Injuary", "Injury", $key);
            print "<td valign='top'><span class='bold'>" . xlt($key) . ": </span><span class='text'>" . text($value) . "</span></td>\n";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr>\n<tr>\n";
            }
        }

        print "</tr>\n</table>\n";
    }
}
