<?php

/**
 * clinic_note report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");

function clinic_note_report($pid, $encounter, $cols, $id)
{
    $cols = 1; // force always 1 column
    $count = 0;
    $data = sqlQuery("SELECT * " .
    "FROM form_clinic_note WHERE " .
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

            if ($key == 'followup_required') {
                switch ($value) {
                    case '1':
                        $value = 'Yes';
                        break;
                    case '2':
                        $value = 'Pending investigation';
                        break;
                }
            }

            $key = ucwords(str_replace("_", " ", $key));
            print "<td valign='top'><span class='bold'>" . xlt($key) . ": </span><span class='text'>" . text($value) . "&nbsp;</span></td>\n";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr>\n<tr>\n";
            }
        }

        print "</tr>\n</table>\n";
    }
}
