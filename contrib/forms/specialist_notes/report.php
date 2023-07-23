<?php

// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function specialist_notes_report($pid, $encounter, $cols, $id)
{
    $cols = 1; // force always 1 column
    $count = 0;
    $data = sqlQuery("SELECT * " .
    "FROM form_specialist_notes WHERE " .
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
                $value = 'Yes';
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
