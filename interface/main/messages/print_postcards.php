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

use OpenEMR\Common\Database\QueryUtils;
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
if (!is_array($facility)) {
    die(xl('No facility found'));
}

// Extract typed strings from facility result
$f_name   = is_string($facility['name']        ?? null) ? $facility['name']        : '';
$f_phone  = is_string($facility['phone']       ?? null) ? $facility['phone']       : '';
$f_street = is_string($facility['street']      ?? null) ? $facility['street']      : '';
$f_city   = is_string($facility['city']        ?? null) ? $facility['city']        : '';
$f_state  = is_string($facility['state']       ?? null) ? $facility['state']       : '';
$f_postal = is_string($facility['postal_code'] ?? null) ? $facility['postal_code'] : '';

// Postcard top message — saved via Recall Board template editor
$ptValue = QueryUtils::fetchSingleValue(
    "SELECT gl_value FROM globals WHERE gl_name = 'recall_board_postcard_top' LIMIT 1",
    'gl_value',
    []
);
$postcard_top = is_string($ptValue) ? $ptValue : '';

if ($postcard_top === '') {
    $postcard_top = xl('Please call our office to schedule') . "\n" . xl('your next appointment at') . " " . $f_phone . ".\n\n" . $f_street . "\n" . $f_city . ", " . $f_state . "  " . $f_postal;
}

// Replace practice-level template variables
$postcard_top = str_replace('{{practice_name}}', $f_name, $postcard_top);
$postcard_top = str_replace('{{practice_phone}}', $f_phone, $postcard_top);
$practice_addr = $f_street . "\n" . $f_city . ", " . $f_state . " " . $f_postal;
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
    if ($patdata === false) {
        continue;
    }

    // Extract typed strings from patient data
    $p_fname  = is_string($patdata['fname']        ?? null) ? $patdata['fname']        : '';
    $p_lname  = is_string($patdata['lname']        ?? null) ? $patdata['lname']        : '';
    $p_street = is_string($patdata['street']       ?? null) ? $patdata['street']       : '';
    $p_city   = is_string($patdata['city']         ?? null) ? $patdata['city']         : '';
    $p_state  = is_string($patdata['state']        ?? null) ? $patdata['state']        : '';
    $p_postal = is_string($patdata['postal_code']  ?? null) ? $patdata['postal_code']  : '';
    $p_cell   = is_string($patdata['phone_cell']   ?? null) ? $patdata['phone_cell']   : '';
    $p_home   = is_string($patdata['phone_home']   ?? null) ? $patdata['phone_home']   : '';
    $p_dob    = is_string($patdata['DOB']          ?? null) ? $patdata['DOB']          : '';

    // Replace patient-level template variables per patient
    $msg = $postcard_top;
    $patient_name  = trim($p_fname . ' ' . $p_lname);
    $patient_addr  = $p_street . "\n" . $p_city . ", " . $p_state . " " . $p_postal;
    $patient_phone = $p_cell !== '' ? $p_cell : $p_home;
    $dob_formatted = oeFormatShortDate($p_dob);
    $patient_dob   = is_string($dob_formatted) ? $dob_formatted : '';
    $msg = str_replace('{{patient_name}}', $patient_name, $msg);
    $msg = str_replace('{{patient_address}}', $patient_addr, $msg);
    $msg = str_replace('{{patient_phone}}', $patient_phone, $msg);
    $msg = str_replace('{{patient_dob}}', $patient_dob, $msg);

    // --- Left side: return address + message (10..72mm) ---
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY(10, 12);
    $pdf->Cell(62, 6, $f_name . $prov_name, 0, 2, 'C');
    $pdf->Line(10, 19, 72, 19);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetXY(10, 22);
    $pdf->MultiCell(62, 4.5, trim($msg), 0, 'C', 0);

    // --- Vertical divider ---
    $pdf->Line(75, 8, 75, 97);

    // --- Right side: recipient address (80..140mm, vertically centered) ---
    $pdf->SetXY(82, 42);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->MultiCell(56, 6, $p_fname . " " . $p_lname, 0, 'L');
    $pdf->SetX(82);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(56, 5, $p_street, 0, 'L');
    $pdf->SetX(82);
    $pdf->MultiCell(56, 5, $p_city . ", " . $p_state . "  " . $p_postal, 0, 'L');
}
$pdf->Output('postcards.pdf', 'D');
