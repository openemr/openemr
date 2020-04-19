<?php

/**
* interface/patient_file/addr_label.php Displaying a PDF file of Labels for printing.
*
* Program for displaying Address Labels
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    Terry Hill <terry@lillysystems.com>
* @author    Daniel Pflieger <growlingflea@gmail.com>
* @copyright Copyright (c) 2014 Terry Hill <terry@lillysystems.com>
* @copyright Copyright (c) 2017 Daniel Pflieger <growlingflea@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../globals.php");

//Get the data to place on labels
//

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.postal_code, p.pid " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($pid));

// re-order the dates
//
$today = oeFormatShortDate($date = 'today');
$dob = oeFormatShortDate($patdata['DOB']);

//Keep in mind the envelope is shifted by 90 degrees.
// Changes made by Daniel Pflieger, daniel@mi-squared.com growlingflea@gmail.com

$x_width =  $GLOBALS['env_x_width'];
$y_height = $GLOBALS['env_y_height'];

//printed text details
$font_size = $GLOBALS['env_font_size'];
$x         = $GLOBALS['env_x_dist'];  // Distance from the 'top' of the envelope in portrait position
$y         = $GLOBALS['env_y_dist']; // Distance from the right most edge of the envelope in portrait position
$angle    = 90;   // rotation in degrees
$black    = '000000'; // color in hexa

//Format of the address
//This number increases the spacing between the line printed on the envelope
$xt       = .2 * $font_size;

//ymargin of printed text. The smaller the number, the further from the left edge edge the address is printed
$yt       = 0;

$text1 = sprintf("%s %s\n", $patdata['fname'], $patdata['lname']);
$text2 = sprintf("%s \n", $patdata['street']);
$text3 = sprintf("%s , %s %s", $patdata['city'], $patdata['state'], $patdata['postal_code']);

$pdf = new eFPDF('P', 'mm', array($x_width, $y_height)); // set the orentation, unit of measure and size of the page
$pdf->AddPage();
$pdf->SetFont('Arial', '', $font_size);
$pdf->TextWithRotation($x, $y + $yt, $text1, $angle);
$xt += $xt;
$pdf->TextWithRotation($x + $xt, $y + $yt, $text2, $angle);
$xt += $xt;
$pdf->TextWithRotation($x + $xt, $y + $yt, $text3, $angle);
$xt += $xt;

$pdf->Output();
