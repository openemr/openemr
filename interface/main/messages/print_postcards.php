<?php

/**
 * Print postcards for patients currently in the $_SESSION['pidList'] variable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2017 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionWrapperFactory;

require_once("../../globals.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid_list = $session->get('pidList') ?? [];

if (empty($pid_list)) {
    die(xl('No patients selected'));
}

$pdf = new FPDF('L', 'mm', [148, 105]);
$last = 1;
$pdf->SetFont('Arial', '', 14);

$sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
$facility = sqlQuery($sql);
if (empty($facility)) {
    die(xl('No facility found'));
}

// Postcard top message — saved via Recall Board template editor
$postcard_top = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'recall_board_postcard_top' LIMIT 1");
$postcard_top = $postcard_top['gl_value'] ?? '';

if (empty($postcard_top)) {
    $postcard_top = xl('Please call our office to schedule') . "\n" . xl('your next appointment at') . " " . $facility['phone'] . ".\n\n" . $facility['street'] . "\n" . $facility['city'] . ", " . $facility['state'] . "  " . $facility['postal_code'];
}

// Replace practice-level template variables
$postcard_top = str_replace('{{practice_name}}', $facility['name'] ?? '', $postcard_top);
$postcard_top = str_replace('{{practice_phone}}', $facility['phone'] ?? '', $postcard_top);
$practice_addr = ($facility['street'] ?? '') . "\n" . ($facility['city'] ?? '') . ", " . ($facility['state'] ?? '') . " " . ($facility['postal_code'] ?? '');
$postcard_top = str_replace('{{practice_address}}', $practice_addr, $postcard_top);

foreach ($pid_list as $pid) {
    $pdf->AddPage();
    $patdata = sqlQuery("SELECT " .
        "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
        "p.street, p.city, p.state, p.postal_code, p.pid, p.phone_home, p.phone_cell " .
        "FROM patient_data AS p " .
        "WHERE p.pid = ? LIMIT 1", [$pid]);
    $prov = sqlQuery("SELECT * FROM users WHERE id IN (SELECT r_provider FROM `patient_recalls` WHERE `r_pid`=?)", [$pid]);
    $prov_name = '';
    if (isset($prov['fname']) && isset($prov['lname'])) {
        $prov_name = ": " . $prov['fname'] . " " . $prov['lname'];
        if (isset($prov['suffix'])) {
            $prov_name .= ", " . $prov['suffix'];
        }
    }
    if (empty($patdata)) {
        continue;
    }

    // Replace patient-level template variables per patient
    $msg = $postcard_top;
    $patient_name = trim(($patdata['fname'] ?? '') . ' ' . ($patdata['lname'] ?? ''));
    $patient_addr = ($patdata['street'] ?? '') . "\n" . ($patdata['city'] ?? '') . ", " . ($patdata['state'] ?? '') . " " . ($patdata['postal_code'] ?? '');
    $patient_phone = $patdata['phone_cell'] ?: ($patdata['phone_home'] ?? '');
    $patient_dob = !empty($patdata['DOB']) ? oeFormatShortDate($patdata['DOB']) : '';
    $msg = str_replace('{{patient_name}}', $patient_name, $msg);
    $msg = str_replace('{{patient_address}}', $patient_addr, $msg);
    $msg = str_replace('{{patient_phone}}', $patient_phone, $msg);
    $msg = str_replace('{{patient_dob}}', $patient_dob, $msg);

    $postcard_message = "\n\n" . $msg . "\n\n";

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
