<?php
/** 
 * interface/patient_file/label.php Displaying a PDF file of Labels for printing. 
 * 
 * Program for displaying Chart Labels 
 * via the popups on the left nav screen
 * 
 * Copyright (C) 2014 Terry Hill <terry@lillysystems.com> 
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
 * @author Terry Hill <terry@lillysystems.com>.
 * @author Scott Wakefield <scott@npclinics.com.au> 
 * @link http://www.open-emr.org 
 */
// I used the program example supplied with the Avery Label Print Class to produce this program

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/classes/PDF_Label.php");
require_once("$srcdir/formatting.inc.php");

//Get the data to place on labels
$patdata = getPatientData($pid, "fname,lname,street,city,state,postal_code,pubpid,DOB,pid");

// re-order the dates
$today = oeFormatShortDate($date='today');
$dob = oeFormatShortDate($patdata['DOB']);

$labeltype = $GLOBALS['chart_label_type'];

switch($labeltype){
            case '1':
                $label     		= '5160';
                $labelsPerPage	= '30';
                break;
            case '2':
                $label     		= '5161';
                $labelsPerPage	= '20'; 
                break;
            case '3':
                $label     		= '5162';
                $labelsPerPage	= '14'; 
                break;
            }

$pdf = new PDF_Label($label);
$pdf->AddPage();

// Added spaces to the sprintf for Fire Fox it was having a problem with alignment 
$text = sprintf("  %s %s\n  %s\n  %s\n  %s", $patdata['fname'], $patdata['lname'], $dob, $today, $patdata['pid']);

// For loop for printing the labels 
for($i=1; $i<=$labelsPerPage; $i++) {
	$pdf->Add_Label($text);
}

$pdf->Output();