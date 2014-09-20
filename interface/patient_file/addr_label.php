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
* @link http://www.open-emr.org 
*
* this is from the barcode-coder and FPDF website I used the examples and code snippets listed on the sites
* to create this program
*/

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/classes/PDF_Label.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/classes/php-barcode.php");

//Get the data to place on labels
//

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.postal_code, p.pid " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($pid));

// re-order the dates
//
$today = oeFormatShortDate($date='today');
$dob = oeFormatShortDate($patdata['DOB']);

$pdf = new PDF_Label('5160'); // used this to get the basic info to the class
$pdf = new eFPDF('P', 'mm',array(102,252)); // set the orentation, unit of measure and size of the page
$pdf->AddPage();
$pdf->SetFont('Arial','',50);


$fontSize = 40;
$marge    = 5;   // between barcode and hri in pixel
$x        = 20;  // barcode center
$y        = 200;  // barcode center
$height   = 40;   // barcode height in 1D ; module size in 2D
$width    = 1;    // barcode height in 1D ; not use in 2D
$angle    = 90;   // rotation in degrees
$black    = '000000'; // color in hexa



$text1 = sprintf("%s %s\n", $patdata['fname'], $patdata['lname']);
$text2 = sprintf("%s \n", $patdata['street']);
$text3 = sprintf("%s , %s\n", $patdata['city'], $patdata['state']);
$text4 = sprintf("%s \n", $patdata['postal_code']);


$pdf->TextWithRotation($x + $xt, $y + $yt, $text1, $angle);
$xt=$xt + 15;
$pdf->TextWithRotation($x + $xt, $y + $yt, $text2, $angle);
$xt=$xt + 15;
$pdf->TextWithRotation($x + $xt, $y + $yt, $text3, $angle);
$xt=$xt + 15;
$y=$y - 100;
$pdf->TextWithRotation($x + $xt, $y + $yt, $text4, $angle);


$pdf->Output();
?>
