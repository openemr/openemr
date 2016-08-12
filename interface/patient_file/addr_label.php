<?php
/** 
* interface/patient_file/addr_label.php Displaying a PDF file of Labels for printing. 
* 
* Program for displaying Address Labels 
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
* @author Terry Hill <terry@lillysystems.com>
* @author Scott Wakefield <scott@npclinics.com.au>
* @link http://www.open-emr.org 
*
* this is from the barcode-coder and FPDF website I used the examples and code snippets listed on the sites
* to create this program
*/

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/formatting.inc.php");

//Get the data to place on labels
$patdata = getPatientData($pid, "fname,lname,street,city,state,postal_code");

$pdf = new TCPDF('L', 'mm',array(102,252), true, 'UTF-8'); // set the orentation, unit of measure and size of the page
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont($pdf->font, '', 50);

$text1 = sprintf("%s %s\n",  $patdata['fname'], $patdata['lname']);
$text2 = sprintf("%s \n", $patdata['street']);
$text3 = sprintf("%s, %s, %s \n", $patdata['city'], $patdata['state'], $patdata['postal_code']);

$pdf->setXY(52,5);
$pdf->writeHTML($text1);
$pdf->setX(52);
$pdf->writeHTML($text2);
$pdf->setX(52);
$pdf->writeHTML($text3);
$pdf->Output();