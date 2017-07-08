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

require_once("../../globals.php");
require_once("$srcdir/fpdf/fpdf.php");

# This is based on session array. 
$pid_list = array();
$pid_list = $_SESSION['pidList'];

$pdf = new FPDF('L', 'mm', array(148,105));
$last = 1;
$pdf->SetFont('Arial','',14);

$sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
$facility = sqlQuery($sql);

$sql = "select * from medex_prefs";
$prefs =  sqlQuery($sql);
if ($prefs['postcard_top']) {
	$postcard_top = $prefs['postcard_top'];
} else {
	$postcard_top ='';
}

$postcard_message = $postcard_top."\n".xlt('Please call our office to schedule')."\n".xlt('your next appointment at')." ".text($facility['phone']).".
	\n\n   ".text($facility['street'])."\n   
	".text($facility['city']).", ".text($facility['state'])."  ".text($facility['postal_code']);
$postcard_message = "\n\n".$postcard_message."\n\n";

foreach ($pid_list as $pid) {
	$pdf->AddPage();
	$patdata = sqlQuery("SELECT " .
	  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
	  "p.street, p.city, p.state, p.postal_code, p.pid " .
	  "FROM patient_data AS p " .
	  "WHERE p.pid = ? LIMIT 1", array($pid));
	$prov = sqlQuery("select * from users where id in (SELECT r_provider  FROM `medex_recalls` WHERE `r_pid`=?)",array($pid));
	if (isset($prov['fname']) && isset($prov['lname'])) {
		$prov_name = ": ".$prov['fname']." ".$prov['lname'];
		if (isset($prov['suffix'])) $prov_name .= ", ".$prov['suffix'];
	}
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(74,30,$facility['name'].$prov_name,1,1,'C');
	$pdf->MultiCell(74, 4, $postcard_message,'LRTB', 'C', 0);// [, boolean fill]]])
	$pdf->Text(100,50,$patdata['fname']." ".$patdata['lname']);
	$pdf->Text(100,55,$patdata['street']);
	$pdf->Text(100,60,$patdata['city']." ".$patdata['state']."  ".$patdata['postal_code']);
}
$pdf->Output('postcards.pdf','D'); 
//D forces the file download instead of showing it in browser
//isn't there an openEMR global for this?

?>