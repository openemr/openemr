<?php

/**
 * Print postcards for patients currently in the $_SESSION['pidList'] variable.
 *
 * @package MedEx
 * @link    http://www.MedExBank.com
 * @author  MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2017 MedEx <support@MedExBank.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

$pid_list = array();
$pid_list = $_SESSION['pidList'];

$pdf = new FPDF('L', 'mm', array(148, 105));
$last = 1;
$pdf->SetFont('Arial', '', 14);

$sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
$facility = sqlQuery($sql);

$sql = "SELECT * FROM medex_prefs";
$prefs = sqlQuery($sql);
if ($prefs['postcard_top']) {
    $postcard_top = $prefs['postcard_top'];
} else {
    $postcard_top = '';
}

$postcard_message = $postcard_top . "\n" . xl('Please call our office to schedule') . "\n" . xl('your next appointment at') . " " . $facility['phone'] . ".
	\n\n" . $facility['street'] . "\n   
	" . $facility['city'] . ", " . $facility['state'] . "  " . $facility['postal_code'];
$postcard_message = "\n\n" . $postcard_message . "\n\n";

foreach ($pid_list as $pid) {
    $pdf->AddPage();
    $patdata = sqlQuery("SELECT " .
        "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
        "p.street, p.city, p.state, p.postal_code, p.pid " .
        "FROM patient_data AS p " .
        "WHERE p.pid = ? LIMIT 1", array($pid));
    $prov = sqlQuery("SELECT * FROM users WHERE id IN (SELECT r_provider  FROM `medex_recalls` WHERE `r_pid`=?)", array($pid));
    if (isset($prov['fname']) && isset($prov['lname'])) {
        $prov_name = ": " . $prov['fname'] . " " . $prov['lname'];
        if (isset($prov['suffix'])) {
            $prov_name .= ", " . $prov['suffix'];
        }
    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(74, 30, $facility['name'] . $prov_name, 1, 1, 'C');
    $pdf->MultiCell(74, 4, $postcard_message, 'LRTB', 'C', 0);// [, boolean fill]]])
    $pdf->Text(100, 50, $patdata['fname'] . " " . $patdata['lname']);
    $pdf->Text(100, 55, $patdata['street']);
    $pdf->Text(100, 60, $patdata['city'] . " " . $patdata['state'] . "  " . $patdata['postal_code']);
}
$pdf->Output('postcards.pdf', 'D');
//D forces the file download instead of showing it in browser
//isn't there an openEMR global for this?
