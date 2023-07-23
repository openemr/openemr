<?php

/**
 * labs_ajax.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../interface/globals.php");

use Mpdf\Mpdf;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$action = $_GET['action'];

if ($action === 'code_detail') {
    $code = strtoupper($_GET['code']);
    $dos = array();

    $query = "SELECT detail.name, ord.procedure_code AS code, detail.name AS title, detail.description, detail.notes FROM procedure_type det ";
    $query .= "LEFT JOIN procedure_type ord ON ord.procedure_type_id = detail.parent ";
    $query .= "WHERE ord.activity = 1 AND detail.procedure_type = 'det' AND ord.procedure_code  = ? ";
    $query .= "ORDER BY detail.seq ";
    $result = sqlStatement($query, [$code]);
    echo "<html><head>";
    Header::setupHeader();
    echo "</head><body style='overflow-x: hidden;'>";
    echo "<div class='row'>\n";
    echo "<div class='col-10 col-sm-offset-1'><h4>" . xlt('Test Code Information') . "</h4>\n";
    $none = true;
    while ($data = sqlFetchArray($result)) {
        if (empty($data['notes'])) {
            continue;
        }
        $none = false;
        echo "<div><b><h5 style='margin-bottom:0'>" . text($data['name']) . "</h5></b>\n";
        echo "<span class='col-12'>" . nl2br(text($data['notes'])) . "</span>\n";
        echo "</div>\n";
    }
    if ($none) {
        echo "<h4 style='margin-bottom:0'>" . xlt("Details Not Available") . "</h4>\n";
        echo "<div class='pr-5'>\n";
        echo xlt("Contact your Lab representative.") . "\n";
        echo xlt("Additional information may be available");
        echo "</div>\n";
    }
    echo "</div></div></body></html>";
}

if ($action === 'print_labels') {
    $client = $_GET['acctid'];
    $pid = $_GET['pid'];
    $order = $_GET['order'];
    $specimen = array();
    $specimens = explode(";", $_GET['specimen']);
    $patient = strtoupper($_GET['patient']);
    $count = 1;
    if ($_GET['count']) {
        $count = (int)$_GET['count'];
    }

    $pdf = new mPDF(array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => 'utf-8',
        'format' => [45, 19],
        'default_font_size' => '9',
        'default_font' => 'courier',
        'margin_left' => '0',
        'margin_right' => '0',
        'margin_top' => '1mm',
        'margin_bottom' => '0',
        'margin_header' => '0',
        'margin_footer' => '0'
    ));
    $pdf->text_input_as_HTML = true;

    while ($count > 0) {
        foreach ($specimens as $t) {
            if (empty($t)) {
                continue;
            }
            if ($t === 'none') {
                $ord = $order;
            } else {
                $ord = $order . '-' . $t;
            }

            $pdf->AddPage();
            $barcode = '<div style="text-align: center;vertical-align: bottom;">';
            $pdf->SetFont('', '', 7);
            $pdf->writeCell(0, 3, 'CLIENT #: ' . $client, 0, 1, 'C');
            $pdf->writeCell(0, 3, 'LAB REF #: ' . $ord, 0, 1, 'C');
            $pdf->SetFont('', 'B', 8);
            $pdf->writeCell(0, 3, $patient, 0, 1, 'C');
            $code_info = $client . '-' . $ord;
            $barcode .= '<barcode size=".8" pr=".4" code="' . attr($code_info) . '" type="C39" /></div>';
            $pdf->writeHTML($barcode);
        }
        $count--;
    }

    $label_file = $patient . "-" . $client . "_" . $order . "_LABEL.pdf";
    // send to display where user decides to print etc...
    try {
        $pdf->Output($label_file, 'I');
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}
