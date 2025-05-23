<?php

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 IntegralEMR LLC <kevin.y@integralemr.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Kevin Yeh <kevin.y@integralemr.com>
//
// +------------------------------------------------------------------------------+

$DOMClass = "DOMDocument";
if (!class_exists($DOMClass)) {
    error_log("Creation of month drop down failed:" . PHP_EOL . "Do you have php-xml installed?");
    return;
}

$DOM = new $DOMClass();

$divMonths = $DOM->createElement("div");
$divMonths->setAttribute("id", "monthPicker");
$divMonths->setAttribute("style", "display: none; position: absolute; top: 15px;");
$DOM->appendChild($divMonths);
$tblMonths = $DOM->createElement("table");
$divMonths->appendChild($tblMonths);
$tbodyMonths = $DOM->createElement("tbody");
$tblMonths->appendChild($tbodyMonths);
$pMonth = date("m");
$pYear = date("Y");

$tdClasses = "tdDatePicker tdMonthName-small";
for ($idx = 0; $idx < 13; $idx++) {
    $pDay = $cDay;

    if ($pMonth > 12) {
        $pMonth = $pMonth - 12;
        $pYear = $pYear + 1;
    }

    while (! checkdate($pMonth, $pDay, $pYear)) {
        $pDay = $pDay - 1;
    }

    $pDate = sprintf("%d%02d%02d", $pYear, $pMonth, $pDay);
    $trMonth = $DOM->createElement("tr");
    $tdMonth = $DOM->createElement("td", xl(date("F", strtotime($pDate))) . " " . $pYear);
    $tdMonth->setAttribute("id", $pDate);
    $tdMonth->setAttribute("class", $tdClasses);
    $trMonth->appendChild($tdMonth);
    $tbodyMonths->appendChild($trMonth);
    $pMonth = $pMonth + 1;
}

echo $DOM->saveXML($divMonths);
