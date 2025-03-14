<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
function hpi_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_hpi", $id);
    $width = 100 / $cols;
    if ($data) {
        print "<table cellspacing=3px><tr>";
        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" || $key == "user" ||
                $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "complaint" ||
                $key == "date" || $value == "" || $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            print "<td width='" . attr($width) . "%' valign='top'><span class=bold>" . text($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
