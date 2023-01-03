<?php

/**
 * body_composition report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function body_composition_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = sqlQuery("SELECT * " .
    "FROM form_body_composition WHERE " .
    "id = ? AND activity = '1'", array($id));
    if ($data) {
        print "<table cellpadding='0' cellspacing='0'>\n<tr>\n";
        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" || $key == "user" || $key == "groupname" ||
                $key == "authorized" || $key == "activity" || $key == "date" ||
                $value == "" || $value == "0" || $value == "0.00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            print "<td valign='top'><span class='bold'>" . text($key) . ": </span><span class='text'>" . text($value) . " &nbsp;</span></td>\n";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr>\n<tr>\n";
            }
        }

        print "</tr>\n</table>\n";
    }
}
