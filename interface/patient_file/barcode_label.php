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
 * @author Scott Wakefield <scott@npclinics.com.au> 
 * @link http://www.open-emr.org 
 *
 *
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/formatting.inc.php");
  
//Get the data to place on labels
 $patdata = getPatientData($pid, "pubpid");

// BARCODE DATA AND TYPE
  
$code    = $patdata['pubpid']; // what is wanted as the barcode
$bartype = $GLOBALS['barcode_label_type'] ; // Get barcode type

 switch($bartype){
            case '1':
                $type     = 'S25'; // Standard 2 of 5
                break;
            case '2':
                $type     = 'I25'; // Interleaved 2 of 5
                break;
            case '3':
                $type     = 'EAN8'; // EAN 8
                break;
            case '4':
                $type     = 'EAN13'; // EAN 13
                break;
            case '5':
                $type     = 'UPCA'; // UPC-A
                break;
            case '6':
                $type     = 'CODE11'; // CODE 11
                break;
            case '7':
                $type     = 'C39'; // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
                break;
            case '8':
                $type     = 'C93'; // CODE 93 - USS-93
                break;
            case '9':
                $type     = 'C128'; // CODE 128
                break;
            case '10':
                $type     = 'CODABAR'; // CODABAR
                break;
            case '11':
                $type     = 'MSI'; // MSI (Variation of Plessey code)
                break;
        }

// PROPERTIES

$fontSize = 28;
$z        = 5;   
$y        = 120;  
$h        = 40;   
$w        = 60;
$xres     = 1;    // width of the smallest bar in user units

// ALLOCATE TCPDF RESSOURCE

$pdf = new TCPDF('L', 'mm',array(102,252), true, 'UTF-8'); // set the orentation, unit of measure and size of the page
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage(); 

// BARCODE
  
$pdf->SetFont($pdf->font,'B',$fontSize);
$pdf->SetTextColor(0, 0, 0);

// define barcode style
$style = array(
    'position' => 'C',
    'align' => 'C',
    'stretch' => true,
    'fitwidth' => false,
    'cellfitalign' => 'C',
    'border' => false,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false //array(255,255,255),

);

$x = $pdf->GetX();
$pdf->write1DBarcode($code, $type, '', '', $w, $h, 1, $style, 'N');
$pdf->SetXY($x, (($h/2) + $fontSize + $z));
$pdf->Cell(0, 0, $code, 0, 0, 'C', FALSE, '', 0, FALSE, 'C', 'B');
$pdf->Output();