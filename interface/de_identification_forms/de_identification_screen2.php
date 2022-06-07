<?php

/**
 * de_identification script 2
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("De Identification")]);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

/*executes the De Identification process, using the parameters chosen from the
de_identification_screen1.php  */
$begin_date = $_POST["begin_date"];
$end_date = $_POST["end_date"];

if ($_POST["unstructured"]) {
    $include_unstructured = 1;
} else {
    $include_unstructured = 0;
}

if ($_POST["all"]) {
    $include_tables = "all";
} else {
    if ($_POST["history_data"]) {
        $include_tables = $include_tables . $_POST["history_data"] . "#";
    }

    if ($_POST["prescriptions"]) {
        $include_tables = $include_tables . $_POST["prescriptions"] . "#";
    }

    if ($_POST["immunization"]) {
        $include_tables = $include_tables . $_POST["immunization"] . "#";
    }

    if ($_POST["lists"]) {
        $include_tables = $include_tables . $_POST["lists"] . "#";
    }

    if ($_POST["transactions"]) {
        $include_tables = $include_tables . $_POST["transactions"] . "#";
    }

    if ($_POST["insurance_data"]) {
        $include_tables = $include_tables . $_POST["insurance_data"] . "#";
    }

    if ($_POST["billing_data"]) {
        $include_tables = $include_tables . "billing#payments";
    }
}

$diagnosis_text = $_POST["diagnosis_text"];
$drug_text = $_POST["drug_text"];
$immunization_text = $_POST["immunization_text"];

$query = "select status from de_identification_status";
$res = sqlStatement($query);
if ($row = sqlFetchArray($res)) {
    $deIdentificationStatus = $row['status'];
 /* $deIdentificationStatus:
 *  0 - There is no De Identification in progress. (start new De Identification process)
 *  1 - A De Identification process is currently in progress.
 *  2 - The De Identification process completed and xls file is ready to download
 *  3 - The De Identification process completed with error
 */
}

if ($deIdentificationStatus == 0) {
 //0 - There is no De Identification in progress. (start new De Identification process)
    ?>
<html>
<head>
<title>De Identification</title>

    <?php Header::setupHeader(); ?>

<style>
.style1 {
    text-align: center;
}
</style>
</head>
<body class="body_top">
<strong>De Identification</strong>
<form name="De Identification1" id="De Identification1" method="post"><br />
    <?php

    $query = "SELECT count(*) as count FROM metadata_de_identification";
    $res = sqlStatement($query);
    if ($row = sqlFetchArray($res)) {
        $no_of_items = $row['count'];
        if ($no_of_items == 0) {
            $cmd = "cp " . escapeshellarg($GLOBALS['fileroot'] . "/sql/metadata_de_identification.txt") . " " . escapeshellarg($GLOBALS['temporary_files_dir'] . "/metadata_de_identification.txt");
            $output3 = shell_exec($cmd);
            $query = "LOAD DATA INFILE '" . add_escape_custom($GLOBALS['temporary_files_dir']) . "/metadata_de_identification.txt' INTO TABLE metadata_de_identification FIELDS TERMINATED BY ','  LINES TERMINATED BY '\n'";
            $res = sqlStatement($query);
        }
    }

    //create transaction tables
    $query = "call create_transaction_tables()";
    $res = sqlStatement($query);

    //write input to data base
    $query = "delete from param_include_tables";
    $res = sqlStatement($query);

    $query = "insert into param_include_tables values (?, ?)";
    $res = sqlStatement($query, array($include_tables, $include_unstructured));

    $query = "delete from param_filter_pid";
    $res = sqlStatement($query);

    $query = "insert into param_filter_pid values (?, ?, ?, ?, ?)";
    $res = sqlStatement($query, array($begin_date, $end_date, $diagnosis_text, $drug_text, $immunization_text));

    //process running
    $query = "update de_identification_status set status = 1";
    $res = sqlStatement($query);

    try {
        //call procedure - execute in background
        $sh_cmd = './de_identification_procedure.sh ' . escapeshellarg($sqlconf["host"]) . ' ' . escapeshellarg($sqlconf["login"]) . ' ' . escapeshellarg($sqlconf["pass"]) . ' ' . escapeshellarg($sqlconf["dbase"]) . ' &';
        system($sh_cmd);


        $query = "SELECT status FROM de_identification_status ";
        $res = sqlStatement($query);
        if ($row = sqlFetchArray($res)) {
            $de_identification_status = $row['status'];
            if ($de_identification_status == 2 || $de_identification_status == 3) {
             //2 - The De Identification process completed and xls file is ready to download
             //3 - The De Identification process completed with error
                $query = "SELECT count(*) as count FROM de_identified_data ";
                $res = sqlStatement($query);
                if ($row = sqlFetchArray($res)) {
                    $no_of_items = $row['count'];
                    if ($no_of_items <= 1) {
                        ?>
    <table>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table class="de_identification_status_message" align="center">
    <tr valign="top">

        <td>&nbsp;</td>
        <td rowspan="3"><br />
                        <?php echo xlt('No Patient record found for given Selection criteria');
                        echo "<br /><br />";
                        echo xlt('Please start new De Identification process');
                        echo "<br />";   ?> <br />
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table align="center">
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>

                        <?php
                    } else {   //delete old de_identified_data.xls file
                        $timestamp = 0;
                        $query = "select now() as timestamp";
                        $res = sqlStatement($query);
                        if ($row = sqlFetchArray($res)) {
                            $timestamp = $row['timestamp'];
                        }

                        $timestamp = str_replace(" ", "_", $timestamp);
                        $de_identified_file = $GLOBALS['temporary_files_dir'] . "/de_identified_data" . $timestamp . ".xls";
                        $query = "update de_identification_status set last_available_de_identified_data_file = ?";
                        $res = sqlStatement($query, array($de_identified_file));
                        $query = "select * from de_identified_data into outfile '" . add_escape_custom($de_identified_file) . "' ";
                        $res = sqlStatement($query);
                        ?>
    <table>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table class="de_identification_status_message" align="center">
    <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3"><br />
                        <?php echo xlt('De Identification Process is ongoing');
                        echo "<br /><br />";
                        echo xlt('Please visit De Identification screen after some time');
                        echo "<br />";   ?> <br />
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <table align="center">
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
                        <?php
                    }
                }
            }
        }
    } catch (Exception $e) {
        //error status
        $query = "update de_identification_status set status = 3";
        $res = sqlStatement($query);
    }
} elseif ($deIdentificationStatus == 2 or $deIdentificationStatus == 3) {
 //2 - The De Identification process completed and xls file is ready to download
 //3 - The De Identification process completed with error
    $query = "select last_available_de_identified_data_file from de_identification_status";
    $res = sqlStatement($query);
    if ($row = sqlFetchArray($res)) {
        $filename = $row['last_available_de_identified_data_file'];
    }

    ob_end_clean();
    if (file_exists($filename)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
        header("Content-type: application/x-msexcel");                    // This should work for the rest
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
    }

    //xls file downloaded complete
    $query = "update de_identification_status set status = 0";
    $res = sqlStatement($query);
}
?>
</body>
</html>

