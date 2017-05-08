<?php
/**
 * /library/MedEx/print_postcards.php
 *
 * This file is executed as a background service
 * either through ajax or cron.
 *
 * Copyright (C) 2017 MedEx <magauran@MedExBank.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Portions of this were developed using Terry Hill's addr_label code.
 *
 * @package OpenEMR
 * @author MedEx <support@MedExBank.com>
 * @link http://www.open-emr.org
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../globals.php");
require_once("$srcdir/fpdf/fpdf.php");
require_once("$srcdir/formatting.inc.php");

# This is based on session array. 
$pid_list = array();
$pid_list = $_SESSION['pidList'];

//$pdf = new FPDF('L', 'mm', array(297.638,  419.528));
$pdf = new FPDF('L', 'mm', array(148,105));
$last = 1;
$pdf->SetFont('Arial','',14);
//var_dump($_SESSION['pidList']);exit;

#Get the data to place on labels
#and output each label
foreach ($pid_list as $pid) {
$pdf->AddPage();
$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.postal_code, p.pid " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($pid));

# sprintf to print data 
$text = sprintf("  %s %s\n  %s\n  %s %s %s\n ", $patdata['fname'], $patdata['lname'], $patdata['street'], $patdata['city'], $patdata['state'], $patdata['postal_code']);

$postcard_message = "It's time to get your EYES checked!
Please call our office to schedule\nyour eye exam  at (413) 276-4543.
Our office is now located at:\n\n   55 St. George Road\n   Springfield, MA 01104\n\n\n";

//$text = $postcard_message;

//$text = "hello";
//$pdf->Cell(40,10,'Hello World !',1);
//$pdf->Write(5,$text);
$pdf->SetFont('Arial','',9);
$pdf->Cell(74,10,'Oculoplastics, LLC:  Raymond Magauran, MD',1,1,'C');
$pdf->MultiCell(74, 55, '', 1 ,'C');// [, boolean fill]]])


$pdf->Text(22,30,"It's time to get your EYES checked!");
$pdf->Text(23,35,"Please call our office to schedule");
$pdf->Text(25,40,"your eye exam  at (413) 276-4543.");
$pdf->Text(25,45,"Our office is now located at");
$pdf->Text(25,50,"   55 St. George Road");
$pdf->Text(25,55,"   Springfield, MA 01104");

$pdf->Text(100,40,$patdata['fname']." ".$patdata['lname']);
$pdf->Text(100,50,$patdata['street']);
$pdf->Text(100,60,$patdata['city']." ".$patdata['state']."  ".$patdata['postal_code']);
$pdf->SetFont('Arial','',8);
$pdf->Text(15,80," St. George Road is at the bottom of Carew Street,");

$pdf->Text(18,85,"where it intersects with Main Street.");
$pdf->Text(15,90,"We are across from the Greek Orthodox Church");
$pdf->Text(18,95,"and next to the Surgery Center of New England.");

}
$pdf->Output('postcards.pdf','D'); 
//D forces the file download instead of showing it in browser
//isn't there an openEMR global for this?

?>
