<?php
/**
 * interface/patient_file/addr_appt_label.php
 * Displaying a PDF file of Appointment Labels for printing.
 *
 * Program for displaying Address Labels
 * from the appointment report or the Recall Board
 *
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lillysystems.com>
 * @link http://www.open-emr.org
 */
# I used the program example supplied with the Avery Label Print Class to produce this program


require_once("../globals.php");


# This is based on session array.
$pid_list = array();
$pid_list = $_SESSION['pidList'];

#get label type and number of labels on sheet
#

if ($GLOBALS['chart_label_type'] == '1') {
    $pdf = new PDF_Label('5160');
    $last = 30;
} elseif ($GLOBALS['chart_label_type'] == '2') {
    $pdf = new PDF_Label('5161');
    $last = 20;
} elseif ($GLOBALS['chart_label_type'] == '3') {
    $pdf = new PDF_Label('5162');
    $last = 14;
} elseif ($GLOBALS['chart_label_type'] == '4') {
    $pdf = new PDF_Label('5163');
    $last = 14; //not sure about $last from here on down
} elseif ($GLOBALS['chart_label_type'] == '5') {
    $pdf = new PDF_Label('5164');
    $last = 14;
} elseif ($GLOBALS['chart_label_type'] == '6') {
    $pdf = new PDF_Label('8600');
    $last = 14;
} elseif ($GLOBALS['chart_label_type'] == '7') {
    $pdf = new PDF_Label('L7163');
    $last = 14;
} elseif ($GLOBALS['chart_label_type'] == '8') {
    $pdf = new PDF_Label('3422');
    $last = 14;
} else {
    $pdf = new PDF_Label('5160');
    $last = 30;
}
$pdf->AddPage();

#Get the data to place on labels
#and output each label
foreach ($pid_list as $pid) {
    $patdata = sqlQuery("SELECT " .
    "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
    "p.street, p.city, p.state, p.postal_code, p.pid " .
    "FROM patient_data AS p " .
    "WHERE p.pid = ? LIMIT 1", array($pid));

# sprintf to print data
    $text = sprintf("  %s %s\n  %s\n  %s %s %s\n ", $patdata['fname'], $patdata['lname'], $patdata['street'], $patdata['city'], $patdata['state'], $patdata['postal_code']);
    $pdf->Add_Label($text);
}

$pdf->Output();
