<?php

require_once(__DIR__ . "/../../../interface/globals.php");
require_once($GLOBALS['srcdir'] . "/classes/Document.class.php");
require_once(__DIR__ . '/tcpdf/tcpdf.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Get request type
$type = $_REQUEST['type'];

if ($type == 'lc_barcode') {
    $specimen = array();
    $printer = 'file';
    $order = $_REQUEST['order'];
    $specimens = explode(";", $_REQUEST['specimen']);
    $patient = strtoupper($_REQUEST['patient']);
    $client = $_REQUEST['acctid'];
    $pid = $_REQUEST['pid'];

    $count = 1;
    if ($_REQUEST['count']) {
        $count = $_REQUEST['count'];
    }

    $pdf = new TCPDF('L', 'pt', array(54, 144), true, 'UTF-8', false);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 5, 20);
    $pdf->SetAutoPageBreak(false, 35);
    $pdf->setLanguageArray($l);
    $style = array(
        'position' => '',
        'align' => 'L',
        'stretch' => true,
        'fitwidth' => false,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 4,
        'vpadding' => 2,
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false,
        'text' => false,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    do {
        foreach ($specimens as $t) {
            if (empty($t)) {
                continue;
            }
            if ($t == 'none') {
                $ord = $order;
            } else {
                $ord = $order . '-' . $t;
            }
            $pdf->AddPage();
            $pdf->SetFont('times', '', 7);
            $pdf->Cell(0, 5, 'CLIENT #: ' . $client, 0, 1);
            $pdf->Cell(0, 5, 'LAB REF #: ' . $ord, 0, 1);
            $pdf->SetFont('times', 'B', 8);
            $pdf->Cell(0, 0, $patient, 0, 1, '', '', '', 1);

            $pdf->write1DBarcode($client . '-' . $ord, 'C39', '', '', 110, 25, '', $style, 'N');
        }
        $count--;
    } while ($count > 0);

    if ($printer == 'file') {
        $repository = $GLOBALS['oer_config']['documents']['repository'];
        $label_file = $repository . preg_replace("/[^A-Za-z0-9]/", "_", $pid) . "/" . $order . "_LABEL.pdf";

        $pdf->Output($label_file, 'I'); // force display download

        exit;
    }

    $label = $pdf->Output('label.pdf', 'S'); // return as variable
    $CMDLINE = "lpr -P $printer ";
    $pipe = popen("$CMDLINE", 'w');
    if (!$pipe) {
        echo "Label printing failed...";
    } else {
        fputs($pipe, $label);
        pclose($pipe);
        echo "Labels printing at $printer ...";
    }
}

if ($type == 'codedetail') {
    $code = strtoupper($_REQUEST['code']);
    $dos = array();

    $query = "SELECT detail.name, ord.procedure_code AS code, detail.name AS title, detail.description, detail.notes FROM procedure_type det ";
    $query .= "LEFT JOIN procedure_type ord ON ord.procedure_type_id = detail.parent ";
    $query .= "WHERE ord.activity = 1 AND detail.procedure_type = 'det' AND ord.procedure_code  = '" . $code . "' ";
    $query .= "ORDER BY detail.seq ";
    $result = sqlStatement($query);
    echo "<html><head>";
    Header::setupHeader();
    echo "</head><body style='overflow-x: hidden;'>";
    echo "<div class='row'>\n";
    echo "<div class='col-xs-10 col-xs-offset-1'><h4>" . xlt('Test Code Information') . "</h4>\n";
    $none = true;
    while ($data = sqlFetchArray($result)) {
        if (empty($data['notes'])) {
            continue;
        }
        $none = false;
        echo "<div><b><h5 style='margin-bottom:0'>" . $data['name'] . "</h5></b>\n";
        echo "<span class='col-xs-12'>" . nl2br($data['notes']) . "</span>\n";
        echo "</div>\n";
    }

    if ($none) {
        echo "<h4 style='margin-bottom:0'>DETAILS NOT AVAILABLE</h4>\n";
        echo "<div style='padding-right:10px;'>\n";
        echo xlt("Please contact your Quest Diagnostics representative for information") . "\n";
        echo xlt("about this laboratory test. Additional information may be available") . "\n";
        echo xlt("on the ") . "<a href='http://www.questdiagnostics.com/testcenter/TestCenterHome.action' target='_blank'>" . text('http://questdiagnostics.com/testcenter') . xlt('website') . "</a>";
        echo "</div>\n";
    }
    echo "</div></div></body></html>";
}

if ($type == 'label') {
    $specimen = array();
    $printer = 'file';
    $order = $_REQUEST['order'];
    $specimens = explode(";", $_REQUEST['specimen']);
    $patient = strtoupper($_REQUEST['patient']);
    $client = $_REQUEST['acctid'];
    $pid = $_REQUEST['pid'];

    $count = 1;
    if ($_REQUEST['count']) {
        $count = $_REQUEST['count'];
    }

    $pdf = new TCPDF('L', 'pt', array(54,144), true, 'UTF-8', false);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 5, 20);
    $pdf->SetAutoPageBreak(false, 35);
    $pdf->setLanguageArray($l);
    $style = array(
        'position' => '',
        'align' => 'L',
        'stretch' => true,
        'fitwidth' => false,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 4,
        'vpadding' => 2,
        'fgcolor' => array(0,0,0),
        'bgcolor' => false,
        'text' => false,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    do {
        foreach ($specimens as $t) {
            if (empty($t)) {
                continue;
            }
            if ($t == 'none') {
                $ord = $order;
            } else {
                $ord = $order . '-' . $t;
            }
            $pdf->AddPage();
            $pdf->SetFont('times', '', 7);
            $pdf->Cell(0, 5, 'CLIENT #: ' . $client, 0, 1);
            $pdf->Cell(0, 5, 'LAB REF #: ' . $ord, 0, 1);
            $pdf->SetFont('times', 'B', 8);
            $pdf->Cell(0, 0, $patient, 0, 1, '', '', '', 1);

            $pdf->write1DBarcode($client . '-' . $ord, 'C39', '', '', 110, 25, '', $style, 'N');
        }
        $count--;
    } while ($count > 0);

    if ($printer == 'file') {
        $repository = $GLOBALS['oer_config']['documents']['repository'];
        $label_file = $repository . preg_replace("/[^A-Za-z0-9]/", "_", $pid) . "/" . $order . "_LABEL.pdf";

        $pdf->Output($label_file, 'I'); // force display download

        exit;
    }

    $label = $pdf->Output('label.pdf', 'S'); // return as variable
    $CMDLINE = "lpr -P $printer ";
    $pipe = popen("$CMDLINE", 'w');
    if (!$pipe) {
        echo "Label printing failed...";
    } else {
        fwrite($pipe, $label);
        pclose($pipe);
        echo "Labels printing at $printer ...";
    }
}
