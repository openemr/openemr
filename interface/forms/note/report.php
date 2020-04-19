<?php

/*
 * Work/School Note Form report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");

function note_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_note", $id);
    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if (
                $key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "date" ||
                $value == "" ||
                $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            print("<tr>\n");
            print("<tr>\n");
            if ($key == "Note Type") {
                print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . xlt($value) . "</span></td>";
            } else {
                print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            }

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
