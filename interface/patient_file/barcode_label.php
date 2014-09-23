<?php
/** 
 * interface/patient_file/barcode_label.php Displaying a PDF file of Labels for printing. 
 * 
 * Program for displaying Barcode Label
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
 *
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;


require_once("../globals.php");
require_once("$srcdir/classes/PDF_Label.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/classes/php-barcode.php");
  
//Get the data to place on labels
 
$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.postal_code, p.pid " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($pid));
  
  
  
$today = date('m/d/Y');
$dob   = substr($patdata['DOB'],5,2) ."/". Substr($patdata['DOB'],8,2) ."/". Substr($patdata['DOB'],0,4);



// -------------------------------------------------- //
//            BARCODE DATA AND TYPE
// -------------------------------------------------- //
  
$code     = $patdata['pubpid']; // what is wanted as the barcode
$bartype = $GLOBALS['barcode_label_type'] ; // Get barcode type

 switch($bartype){
            case '1':
			    $type     = 'std25';
				break;
            case '2':
			    $type     = 'int25';
                break;
            case '3':
			    $type     = 'ean8';
                break;
            case '4':
 			    $type     = 'ean13';
                break;
            case '5':
 			    $type     = 'upc';
                break;
            case '6':
 			    $type     = 'code11';
                break;
            case '7':
 			    $type     = 'code39';
                break;
            case '8':
 			    $type     = 'code93';
                break;
            case '9':
 			    $type     = 'code128';
                break;
            case '10':
 			    $type     = 'codabar';
                break;
            case '11':
 			    $type     = 'msi';
                break;
            case '12':
 			    $type     = 'datamatrix';
                break;
        }

// -------------------------------------------------- //
//                  PROPERTIES
// -------------------------------------------------- //
$fontSize = 28;
$angle    = 90;   // rotation in degrees
$black    = '000000'; // color in hexa

if ($GLOBALS['barcode_label_type'] == '12') {   // datamatrix
$marge    = 0;   // between barcode and hri in pixel
$x        = 35;  // barcode center
$y        = 120;  // barcode center
$height   = 40;   // barcode height in 1D ; module size in 2D
$width    = 4;    // barcode height in 1D ; not use in 2D
}
ELSE
{
$marge    = 5;   // between barcode and hri in pixel
$x        = 30;  // barcode center
$y        = 120;  // barcode center
$height   = 40;   // barcode height in 1D ; module size in 2D
$width    = 1;    // barcode height in 1D ; not use in 2D
}

// -------------------------------------------------- //
//            ALLOCATE FPDF RESSOURCE
// -------------------------------------------------- //

$pdf = new eFPDF('P', 'mm',array(102,252)); // set the orentation, unit of measure and size of the page
$pdf->AddPage();
 
// -------------------------------------------------- //
//                      BARCODE
// -------------------------------------------------- //
  
$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
$pdf->SetFont('Arial','B',$fontSize);
$pdf->SetTextColor(0, 0, 0);
$len = $pdf->GetStringWidth($data['hri']);
Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, $angle, $xt, $yt);
 
// -------------------------------------------------- //
//                      OUTPUT
// -------------------------------------------------- //
 
$pdf->TextWithRotation($x + $xt, $y + $yt, $data['hri'], $angle);
$pdf->Output();
?>