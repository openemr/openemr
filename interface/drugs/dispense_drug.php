<?php

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\FacilityService;
use PHPMailer\PHPMailer\PHPMailer;

$facilityService = new FacilityService();

function send_email($subject, $body)
{
    $recipient = $GLOBALS['practice_return_email_path'];
    if (empty($recipient)) {
        return;
    }

    $mail = new PHPMailer();
    $mail->From = $recipient;
    $mail->FromName = 'In-House Pharmacy';
    $mail->isMail();
    $mail->Host = "localhost";
    $mail->Mailer = "mail";
    $mail->Body = $body;
    $mail->Subject = $subject;
    $mail->AddAddress($recipient);
    if (!$mail->Send()) {
        error_log('There has been a mail error sending to' . " " . errorLogEscape($recipient .
        " " . $mail->ErrorInfo));
    }
}

$sale_id         = $_REQUEST['sale_id'];
$drug_id         = $_REQUEST['drug_id'];
$prescription_id = $_REQUEST['prescription'];
$quantity        = $_REQUEST['quantity'];
$fee             = $_REQUEST['fee'];
$user            = $_SESSION['authUser'];

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Dispense Drug")]);
    exit;
}

if (!$drug_id) {
    $drug_id = 0;
}

if (!$prescription_id) {
    $prescription_id = 0;
}

if (!$quantity) {
    $quantity = 0;
}

if (!$fee) {
    $fee = 0.00;
}

$inventory_id = 0;
$bad_lot_list = '';
$today = date('Y-m-d');

// If there is no sale_id then this is a new dispensation.
//
if (! $sale_id) {
  // Post the order and update inventory, deal with errors.
  //
    if ($drug_id) {
        $sale_id = sellDrug($drug_id, $quantity, $fee, $pid, 0, $prescription_id, $today, $user);
        if (!$sale_id) {
            die(xlt('Inventory is not available for this order.'));
        }

    /******************************************************************
    $res = sqlStatement("SELECT * FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND on_hand > 0 AND destroy_date IS NULL " .
    "ORDER BY expiration, inventory_id");
    while ($row = sqlFetchArray($res)) {
    if ($row['expiration'] > $today && $row['on_hand'] >= $quantity) {
    break;
    }
    $tmp = $row['lot_number'];
    if (! $tmp) $tmp = '[missing lot number]';
    if ($bad_lot_list) $bad_lot_list .= ', ';
    $bad_lot_list .= $tmp;
    }

    if ($bad_lot_list) {
    send_email("Lot destruction needed",
    "The following lot(s) are expired or too small to fill prescription " .
    "$prescription_id and should be destroyed: $bad_lot_list\n");
    }

    if (! $row) {
    die("Inventory is not available for this order.");
    }

    $inventory_id = $row['inventory_id'];

    sqlStatement("UPDATE drug_inventory SET " .
    "on_hand = on_hand - $quantity " .
    "WHERE inventory_id = $inventory_id");

    $rowsum = sqlQuery("SELECT sum(on_hand) AS sum FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND on_hand > '$quantity' AND expiration > CURRENT_DATE");
    $rowdrug = sqlQuery("SELECT * FROM drugs WHERE " .
    "drug_id = '$drug_id'");
    if ($rowsum['sum'] <= $rowdrug['reorder_point']) {
    send_email("Drug re-order required",
    "Drug '" . $rowdrug['name'] . "' has reached its reorder point.\n");
    }

    // TBD: Set and check a reorder notification date so we don't
    // send zillions of redundant emails.
    ******************************************************************/
    } // end if $drug_id

    /*******************************************************************
    $sale_id = sqlInsert("INSERT INTO drug_sales ( " .
    "drug_id, inventory_id, prescription_id, pid, user, sale_date, quantity, fee " .
    ") VALUES ( " .
    "'$drug_id', '$inventory_id', '$prescription_id', '$pid', '$user', '$today',
    '$quantity', '$fee' "  .
    ")");
    *******************************************************************/

    if (!$sale_id) {
        die(xlt('Internal error, no drug ID specified!'));
    }
} // end if not $sale_id

// Generate the bottle label for the sale identified by $sale_id.

// Get details for what we guess is the primary facility.
$frow = $facilityService->getPrimaryBusinessEntity(array("useLegacyImplementation" => true));

// Get everything else.
$row = sqlQuery("SELECT " .
    "s.pid, s.quantity, s.prescription_id, " .
    "i.manufacturer, i.lot_number, i.expiration, " .
    "d.name, d.ndc_number, d.form, d.size, d.unit, " .
    "r.date_modified, r.dosage, r.route, r.interval, r.substitute, r.refills, " .
    "p.fname, p.lname, p.mname, " .
    "u.fname AS ufname, u.mname AS umname, u.lname AS ulname " .
    "FROM drug_sales AS s, drug_inventory AS i, drugs AS d, " .
    "prescriptions AS r, patient_data AS p, users AS u WHERE " .
    "s.sale_id = ? AND " .
    "i.inventory_id = s.inventory_id AND " .
    "d.drug_id = i.drug_id AND " .
    "r.id = s.prescription_id AND " .
    "p.pid = s.pid AND " .
    "u.id = r.provider_id", array($sale_id));

$dconfig = $GLOBALS['oer_config']['druglabels'];

$header_text = $row['ufname'] . ' ' . $row['umname'] . ' ' . $row['ulname'] . "\n" .
$frow['street'] . "\n" .
$frow['city'] . ', ' . $frow['state'] . ' ' . $frow['postal_code'] .
'  ' . $frow['phone'] . "\n";
if ($dconfig['disclaimer']) {
    $header_text .= $dconfig['disclaimer'] . "\n";
}

$label_text = $row['fname'] . ' ' . $row['lname'] . ' ' . $row['date_modified'] .
' RX#' . sprintf('%06u', $row['prescription_id']) . "\n" .
$row['name'] . ' ' . $row['size'] . ' ' .
generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row['unit']) . ' ' .
xl('QTY') . ' ' . $row['quantity'] . "\n" .
xl('Take') . ' ' . $row['dosage'] . ' ' .
generate_display_field(array('data_type' => '1','list_id' => 'drug_form'), $row['form']) .
($row['dosage'] > 1 ? 's ' : ' ') .
generate_display_field(array('data_type' => '1','list_id' => 'drug_interval'), $row['interval']) .
' ' .
generate_display_field(array('data_type' => '1','list_id' => 'drug_route'), $row['route']) .
"\n" . xl('Lot', '', '', ' ') . $row['lot_number'] . xl('Exp', '', ' ', ' ') . $row['expiration'] . "\n" .
xl('NDC', '', '', ' ') . $row['ndc_number'] . ' ' . $row['manufacturer'];

// if ($row['refills']) {
//  // Find out how many times this prescription has been filled/refilled.
//  $refills_row = sqlQuery("SELECT count(*) AS count FROM drug_sales " .
//   "WHERE prescription_id = '" . $row['prescription_id'] .
//   "' AND quantity > 0");
//  $label_text .= ($refills_row['count'] - 1) . ' of ' . $row['refills'] . ' refills';
// }

// We originally went for PDF output on the theory that output formatting
// would be more controlled.  However the clumisness of invoking a PDF
// viewer from the browser becomes intolerable in a POS environment, and
// printing HTML is much faster and easier if the browser's page setup is
// configured properly.
//
if (false) { // if PDF output is desired
    $pdf = new Cezpdf($dconfig['paper_size']);
    $pdf->ezSetMargins($dconfig['top'], $dconfig['bottom'], $dconfig['left'], $dconfig['right']);
    $pdf->selectFont('Helvetica');
    $pdf->ezSetDy(20); // dunno why we have to do this...
    $pdf->ezText($header_text, 7, array('justification' => 'center'));
    if (!empty($dconfig['logo'])) {
        $pdf->ezSetDy(-5); // add space (move down) before the image
        $pdf->ezImage($dconfig['logo'], 0, 180, '', 'left');
        $pdf->ezSetDy(8);  // reduce space (move up) after the image
    }

    $pdf->ezText($label_text, 9, array('justification' => 'center'));
    $pdf->ezStream();
} else { // HTML output
    ?>
<html>
    <script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<head>
<style>
body {
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
}
.labtop {
    color: #000000;
    font-family: sans-serif;
    font-size: 7pt;
    font-weight: normal;
    text-align: center;
    padding-bottom: 1pt;
}
.labbot {
    color: #000000;
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
    text-align: center;
    padding-top: 2pt;
}
</style>
   <title><?php echo xlt('Prescription Label') ; ?></title>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>
<table border='0' cellpadding='0' cellspacing='0' style='width: 200pt'>
 <tr><td class="labtop" nowrap>
        <?php echo nl2br(text($header_text)); ?>
 </td></tr>
 <tr><td style='background-color: #000000; height: 5pt;'></td></tr>
 <tr><td class="labbot" nowrap>
        <?php echo nl2br(text($label_text)); ?>
 </td></tr>
</table>
</center>
<script>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
</script>
</body>
</html>
    <?php
}
?>
