<?php

/**
 * Patient custom report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Tony McCormick <tony@mi-squared.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");
require_once("$srcdir/appointments.inc.php");
require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

use ESign\Api;
use Mpdf\Mpdf;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\MedicalDevice\MedicalDevice;
use OpenEMR\Services\FacilityService;

if (!AclMain::aclCheckCore('patients', 'pat_rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Custom Report")]);
    exit;
}

$facilityService = new FacilityService();

$staged_docs = array();
$archive_name = '';

// For those who care that this is the patient report.
$GLOBALS['PATIENT_REPORT_ACTIVE'] = true;

$PDF_OUTPUT = empty($_POST['pdf']) ? 0 : intval($_POST['pdf']);
$PDF_FAX = empty($_POST['fax']) ? 0 : intval($_POST['fax']);
if ($PDF_FAX) {
    $PDF_OUTPUT = 1;
}

if ($PDF_OUTPUT) {
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => $GLOBALS['pdf_size'],
        'default_font_size' => '9',
        'default_font' => 'dejavusans',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => $GLOBALS['pdf_layout'],
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'autoScriptToLang' => true,
        'keep_table_proportions' => true
    );
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
} // end pdf conditional.

// get various authorization levels
$auth_notes_a = AclMain::aclCheckCore('encounters', 'notes_a');
$auth_notes = AclMain::aclCheckCore('encounters', 'notes');
$auth_coding_a = AclMain::aclCheckCore('encounters', 'coding_a');
$auth_coding = AclMain::aclCheckCore('encounters', 'coding');
$auth_relaxed = AclMain::aclCheckCore('encounters', 'relaxed');
$auth_med = AclMain::aclCheckCore('patients', 'med');
$auth_demo = AclMain::aclCheckCore('patients', 'demo');

$esignApi = new Api();

$printable = empty($_GET['printable']) ? false : true;
if ($PDF_OUTPUT) {
    $printable = true;
}

unset($_GET['printable']);

// Number of columns in tables for insurance and encounter forms.
$N = $PDF_OUTPUT ? 4 : 6;

$first_issue = 1;

function getContent()
{
    global $web_root, $webserver_root;
    $content = ob_get_clean();
    // Fix a nasty mPDF bug - it ignores document root!
    $i = 0;
    $wrlen = strlen($web_root);
    $wsrlen = strlen($webserver_root);
    while (true) {
        $i = stripos($content, " src='/", $i + 1);
        if ($i === false) {
            break;
        }

        if (
            substr($content, $i + 6, $wrlen) === $web_root &&
            substr($content, $i + 6, $wsrlen) !== $webserver_root
        ) {
            $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
        }
    }

    return $content;
}

function postToGet($arin)
{
    $getstring = "";
    foreach ($arin as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $getstring .= attr_url($key . "[]") . "=" . attr_url($v) . "&";
            }
        } else {
            $getstring .= attr_url($key) . "=" . attr_url($val) . "&";
        }
    }

    return $getstring;
}

function report_basename($pid)
{
    $ptd = getPatientData($pid, "fname,lname");
    // escape names for pesky periods hyphen etc.
    $esc = $ptd['fname'] . '_' . $ptd['lname'];
    $esc = str_replace(array('.', ',', ' '), '', $esc);
    $fn = basename_international(strtolower($esc . '_' . $pid . '_' . xl('report')));

    return array('base' => $fn, 'fname' => $ptd['fname'], 'lname' => $ptd['lname']);
}

function zip_content($source, $destination, $content = '', $create = true)
{
    if (!extension_loaded('zip')) {
        return false;
    }

    $zip = new ZipArchive();
    if ($create) {
        if (!$zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }
    } else {
        if (!$zip->open($destination, ZipArchive::OVERWRITE)) {
            return false;
        }
    }

    if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    } elseif (!empty($content)) {
        $zip->addFromString(basename($source), $content);
    }

    return $zip->close();
}

?>

<?php if ($PDF_OUTPUT) { ?>
    <?php Header::setupAssets(['pdf-style', 'esign-theme-only']); ?>
<?php } else { ?>
<html>
<head>
    <?php Header::setupHeader(['esign-theme-only', 'search-highlight']); ?>
    <?php } ?>

    <?php // do not show stuff from report.php in forms that is encaspulated
    // by div of navigateLink class. Specifically used for CAMOS, but
    // can also be used by other forms that require output in the
    // encounter listings output, but not in the custom report. ?>

    <style>
      div.navigateLink {
        display: none;
      }

      .hilite2 {
        background-color: transparent;
      }

      .hilite, mark, .next {
        background-color: var(--yellow);
      }

      img {
        max-width: 700px;
      }
    </style>

    <?php if (!$PDF_OUTPUT) { ?>
        <?php // if the track_anything form exists, then include the styling
        if (file_exists(__DIR__ . "/../../forms/track_anything/style.css")) { ?>
            <?php Header::setupAssets('track-anything'); ?>
        <?php } ?>

</head>
<?php } ?>

<body>
    <div class="container">
        <div id="report_custom w-100">  <!-- large outer DIV -->
            <?php
            if (sizeof($_GET) > 0) {
                $ar = $_GET;
            } else {
                $ar = $_POST;
            }

            if ($printable) {
                /*******************************************************************
                 * $titleres = getPatientData($pid, "fname,lname,providerID");
                 * $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
                 *******************************************************************/
                $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
                $facility = null;
                if ($_SESSION['pc_facility']) {
                    $facility = $facilityService->getById($_SESSION['pc_facility']);
                } else {
                    $facility = $facilityService->getPrimaryBillingLocation();
                }

                /******************************************************************/
                // Setup Headers and Footers for mPDF only Download
                // in HTML view it's just one line at the top of page 1
                echo '<page_header class="custom-tag text-right"> ' . xlt("PATIENT") . ':' . text($titleres['lname']) . ', ' . text($titleres['fname']) . ' - ' . text($titleres['DOB_TS']) . '</page_header>    ';
                echo '<page_footer class="custom-tag text-right">' . xlt('Generated on') . ' ' . text(oeFormatShortDate()) . ' - ' . text($facility['name']) . ' ' . text($facility['phone']) . '</page_footer>';

                // Use logo if it exists as 'practice_logo.gif' in the site dir
                // old code used the global custom dir which is no longer a valid
                $practice_logo = "";
                $plogo = glob("$OE_SITE_DIR/images/*");// let's give the user a little say in image format.
                $plogo = preg_grep('~practice_logo\.(gif|png|jpg|jpeg)$~i', $plogo);
                if (!empty($plogo)) {
                    $k = current(array_keys($plogo));
                    $practice_logo = $plogo[$k];
                }

                echo "<div class='table-responsive'><table class='table'><tbody><tr><td>";
                if (file_exists($practice_logo)) {
                    $logo_path = $GLOBALS['OE_SITE_WEBROOT'] . "/images/" . basename($practice_logo);
                    echo "<img class='h-auto' style='max-width:250px;' src='$logo_path'>"; // keep size within reason
                    echo "</td><td>";
                }
                ?>
                <h5><?php echo text($facility['name']); ?></h5>
                <?php echo text($facility['street']); ?><br />
                <?php echo text($facility['city']); ?>, <?php echo text($facility['state']); ?><?php echo text($facility['postal_code']); ?><br clear='all'>
                <?php echo text($facility['phone']); ?><br />

                <a href="javascript:window.close();"><span class='title'><?php echo xlt('Patient') . ": " . text($titleres['fname']) . " " . text($titleres['lname']); ?></span></a><br />
                <span class='text'><?php echo xlt('Generated on'); ?>: <?php echo text(oeFormatShortDate()); ?></span>
                <?php echo "</td></tr></tbody></table></div>"; ?>

            <?php } else { // not printable
                ?>
                <div class="border-bottom fixed-top px-5 pt-4 report_search_bar">
                    <div class="row">
                        <div class="col-md">
                            <input type="text" class="form-control" onkeyup="clear_last_visit();remove_mark_all();find_all();" name="search_element" id="search_element" />
                        </div>
                        <div class="col-md">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-search" onClick="clear_last_visit();remove_mark_all();find_all();"><?php echo xlt('Find'); ?></button>
                                <button type="button" class="btn btn-primary" onClick="next_prev('prev');"><?php echo xlt('Prev'); ?></button>
                                <button type="button" class="btn btn-primary" onClick="next_prev('next');"><?php echo xlt('Next'); ?></button>
                            </div>
                        </div>
                        <div class="col-md">
                            <span><?php echo xlt('Match case'); ?></span>
                            <input type="checkbox" onClick="clear_last_visit();remove_mark_all();find_all();" name="search_case" id="search_case" />
                        </div>
                        <div class="col-md mb-2">
                            <span class="text font-weight-bold"><?php echo xlt('Search In'); ?>:</span>
                            <br />
                            <?php
                            $form_id_arr = array();
                            $form_dir_arr = array();
                            $last_key = '';
                            //ksort($ar);
                            foreach ($ar as $key_search => $val_search) {
                                if ($key_search == 'pdf' || $key_search == '') {
                                    continue;
                                }

                                if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                                    preg_match('/^(.*)_(\d+)$/', $key_search, $res_search);
                                    $form_id_arr[] = add_escape_custom($res_search[2] ?? '');
                                    $form_dir_arr[] = add_escape_custom($res_search[1] ?? '');
                                }
                            }

                            //echo json_encode(json_encode($array_key_id));
                            if (sizeof($form_id_arr) > 0) {
                                $query = "SELECT DISTINCT(form_name),formdir FROM forms WHERE form_id IN ( '" . implode("','", $form_id_arr) . "') AND formdir IN ( '" . implode("','", $form_dir_arr) . "')";
                                $arr = sqlStatement($query);
                                echo "<select multiple size='4' class='form-control' id='forms_to_search' onchange='clear_last_visit();remove_mark_all();find_all();' >";
                                while ($res_forms_ids = sqlFetchArray($arr)) {
                                    echo "<option value='" . attr($res_forms_ids['formdir']) . "' selected>" . text($res_forms_ids['form_name']) . "</option>";
                                }
                                echo "</select>";
                            }
                            ?>
                        </div>
                        <div class="col-md">
                            <span id='alert_msg' class='text-danger'></span>
                        </div>
                    </div>
                </div>
                <div id="backLink">
                    <a href="patient_report.php" onclick='top.restoreSession()'>
                        <span class='title'><?php echo xlt('Patient Report'); ?></span>
                        <span class='back'><?php echo text($tback); ?></span>
                    </a>
                </div>
                <br />
                <br />
                <a href="custom_report.php?printable=1&<?php print postToGet($ar); ?>" class='link_submit' target='new' onclick='top.restoreSession()'>
                    [<?php echo xlt('Printable Version'); ?>]
                </a>
            <?php } // end not printable ?>

            <?php

            // include ALL form's report.php files
            $inclookupres = sqlStatement("select distinct formdir from forms where pid = ? AND deleted=0", array($pid));
            while ($result = sqlFetchArray($inclookupres)) {
                // include_once("{$GLOBALS['incdir']}/forms/" . $result["formdir"] . "/report.php");
                $formdir = $result['formdir'];
                if (substr($formdir, 0, 3) == 'LBF') {
                    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
                } else {
                    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
                }
            }

            if ($PDF_OUTPUT) {
                $tmp_files_remove = array();
            }

            // For each form field from patient_report.php...
            //
            foreach ($ar as $key => $val) {
                if ($key == 'pdf') {
                    continue;
                }

                // These are the top checkboxes (demographics, allergies, etc.).
                //
                if (stristr($key, "include_")) {
                    if ($val == "recurring_days") {
                        /// label/header for recurring days
                        echo "<hr />";
                        echo "<div class='text' id='appointments'>\n";
                        print "<h4>" . xlt('Recurrent Appointments') . ":</h4>";

                        //fetch the data of the recurring days
                        $recurrences = fetchRecurrences($pid);

                        //print the recurring days to screen
                        if (empty($recurrences)) { //if there are no recurrent appointments:
                            echo "<div class='text' >";
                            echo "<span>" . xlt('None{{Appointment}}') . "</span>";
                            echo "</div>";
                            echo "<br />";
                        } else {
                            foreach ($recurrences as $row) {
                                //checks if there are recurrences and if they are current (git didn't end yet)
                                if (!recurrence_is_current($row['pc_endDate'])) {
                                    continue;
                                }

                                echo "<div class='text' >";
                                echo "<span>" . xlt('Appointment Category') . ': ' . xlt($row['pc_catname']) . "</span>";
                                echo "<br />";
                                echo "<span>" . xlt('Recurrence') . ': ' . text($row['pc_recurrspec']) . "</span>";
                                echo "<br />";

                                if (ends_in_a_week($row['pc_endDate'])) {
                                    echo "<span class='text-danger'>" . xlt('End Date') . ': ' . text($row['pc_endDate']) . "</span>";
                                } else {
                                    echo "<span>" . xlt('End Date') . ': ' . text($row['pc_endDate']) . "</span>";
                                }

                                echo "</div>";
                                echo "<br />";
                            }
                        }

                        echo "</div><br />";
                    } elseif ($val == "demographics") {
                        echo "<hr />";
                        echo "<div class='text demographics' id='DEM'>\n";
                        print "<h4>" . xlt('Patient Data') . ":</h4>";
                        // printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);
                        $result1 = getPatientData($pid);
                        $result2 = getEmployerData($pid);
                        echo "   <div class='table-responsive'><table class='table'>\n";
                        display_layout_rows('DEM', $result1, $result2);
                        echo "   </table></div>\n";
                        echo "</div>\n";
                    } elseif ($val == "history") {
                        echo "<hr />";
                        echo "<div class='text history' id='HIS'>\n";
                        if (AclMain::aclCheckCore('patients', 'med')) {
                            print "<h4>" . xlt('History Data') . ":</h4>";
                            // printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);
                            $result1 = getHistoryData($pid);
                            echo "   <table>\n";
                            display_layout_rows('HIS', $result1);
                            echo "   </table>\n";
                        }

                        echo "</div>";

                        // } elseif ($val == "employer") {
                        //   print "<br /><span class='bold'>".xl('Employer Data').":</span><br />";
                        //   printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);
                    } elseif ($val == "insurance") {
                        echo "<hr />";
                        echo "<div class='text insurance'>";
                        echo "<h4>" . xlt('Insurance Data') . ":</h4>";
                        print "<br /><span class='font-weight-bold'>" . xlt('Primary Insurance Data') . ":</span><br />";
                        printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "primary"), $N);
                        print "<span class='font-weight-bold'>" . xlt('Secondary Insurance Data') . ":</span><br />";
                        printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "secondary"), $N);
                        print "<span class='font-weight-bold'>" . xlt('Tertiary Insurance Data') . ":</span><br />";
                        printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "tertiary"), $N);
                        echo "</div>";
                    } elseif ($val == "billing") {
                        echo "<hr />";
                        echo "<div class='text billing'>";
                        print "<h4>" . xlt('Billing Information') . ":</h4>";
                        if (!empty($ar['newpatient']) && count($ar['newpatient']) > 0) {
                            $billings = array();
                            echo "<div class='table-responsive'><table class='table'>";
                            echo "<tr><td class='font-weight-bold'>" . xlt('Code') . "</td><td class='font-weight-bold'>" . xlt('Fee') . "</td></tr>\n";
                            $total = 0.00;
                            $copays = 0.00;
                            foreach ($ar['newpatient'] as $be) {
                                $ta = explode(":", $be);
                                $billing = getPatientBillingEncounter($pid, $ta[1]);
                                $billings[] = $billing;
                                foreach ($billing as $b) {
                                    echo "<tr>\n";
                                    echo "<td class='text'>";
                                    echo text($b['code_type']) . ":\t" . text($b['code']) . "&nbsp;" . text($b['modifier']) . "&nbsp;&nbsp;&nbsp;" . text($b['code_text']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                    echo "</td>\n";
                                    echo "<td class='text'>";
                                    echo text(oeFormatMoney($b['fee']));
                                    echo "</td>\n";
                                    echo "</tr>\n";
                                    $total += $b['fee'];
                                    if ($b['code_type'] == "COPAY") {
                                        $copays += $b['fee'];
                                    }
                                }
                            }

                            echo "<tr><td>&nbsp;</td></tr>";
                            echo "<tr><td class='font-weight-bold'>" . xlt('Sub-Total') . "</td><td class='text'>" . text(oeFormatMoney($total + abs($copays))) . "</td></tr>";
                            echo "<tr><td class='font-weight-bold'>" . xlt('Paid') . "</td><td class='text'>" . text(oeFormatMoney(abs($copays))) . "</td></tr>";
                            echo "<tr><td class='font-weight-bold'>" . xlt('Total') . "</td><td class='text'>" . text(oeFormatMoney($total)) . "</td></tr>";
                            echo "</table></div>";
                            echo "<pre>";
                            //print_r($billings);
                            echo "</pre>";
                        } else {
                            printPatientBilling($pid);
                        }

                        echo "</div>\n"; // end of billing DIV
                    } elseif ($val == "immunizations") {
                        if (AclMain::aclCheckCore('patients', 'med')) {
                            echo "<hr />";
                            echo "<div class='text immunizations'>\n";
                            print "<h4>" . xlt('Patient Immunization') . ":</h4>";
                            $sql = "select i1.immunization_id, i1.administered_date, substring(i1.note,1,20) as immunization_note, c.code_text_short " .
                                " from immunizations i1 " .
                                " left join code_types ct on ct.ct_key = 'CVX' " .
                                " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code " .
                                " where i1.patient_id = ? and i1.added_erroneously = 0 " .
                                " order by administered_date desc";
                            $result = sqlStatement($sql, array($pid));
                            while ($row = sqlFetchArray($result)) {
                                // Figure out which name to use (ie. from cvx list or from the custom list)
                                if ($GLOBALS['use_custom_immun_list']) {
                                    $vaccine_display = generate_display_field(array('data_type' => '1', 'list_id' => 'immunizations'), $row['immunization_id']);
                                } else {
                                    if (!empty($row['code_text_short'])) {
                                        $vaccine_display = xlt($row['code_text_short']);
                                    } else {
                                        $vaccine_display = generate_display_field(array('data_type' => '1', 'list_id' => 'immunizations'), $row['immunization_id']);
                                    }
                                }

                                echo text($row['administered_date']) . " - " . $vaccine_display;
                                if ($row['immunization_note']) {
                                    echo " - " . text($row['immunization_note']);
                                }

                                echo "<br />\n";
                            }

                            echo "</div>\n";
                        }

                        // communication report
                    } elseif ($val == "batchcom") {
                        echo "<hr />";
                        echo "<div class='text transactions'>\n";
                        print "<h4>" . xlt('Patient Communication sent') . ":</h4>";
                        $sql = "SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id=?";
                        // echo $sql;
                        $result = sqlStatement($sql, array($pid));
                        while ($row = sqlFetchArray($result)) {
                            echo text($row['batchcom_data']) . ", By: " . text($row['user_name']) . "<br />Text:<br /> " . text($row['msg_txt']) . "<br />\n";
                        }

                        echo "</div>\n";
                    } elseif ($val == "notes") {
                        echo "<hr />";
                        echo "<div class='text notes'>\n";
                        print "<h4>" . xlt('Patient Notes') . ":</h4>";
                        printPatientNotes($pid);
                        echo "</div>";
                    } elseif ($val == "transactions") {
                        echo "<hr />";
                        echo "<div class='text transactions'>\n";
                        print "<h4>" . xlt('Patient Transactions') . ":</h4>";
                        printPatientTransactions($pid);
                        echo "</div>";
                    }
                } else {
                    // Documents is an array of checkboxes whose values are document IDs.
                    //
                    if ($key == "documents") {
                        echo "<hr />";
                        echo "<div class='text documents'>";
                        foreach ($val as $valkey => $valvalue) {
                            $document_id = $valvalue;
                            if (!is_numeric($document_id)) {
                                continue;
                            }

                            $d = new Document($document_id);
                            $fname = basename($d->get_name());
                            //  Extract the extension by the mime/type and not the file name extension
                            // -There is an exception. Need to manually see if it a pdf since
                            //  the image_type_to_extension() is not working to identify pdf.
                            $extension = strtolower(substr($fname, strrpos($fname, ".")));
                            if ($extension != '.pdf') { // Will print pdf header within pdf import
                                echo "<h5>" . xlt('Document') . " '" . text($fname) . "-" . text($d->get_id()) . "'</h5>";
                            }

                            $notes = $d->get_notes();
                            if (!empty($notes)) {
                                echo "<div class='table-responsive'><table class='table'>";
                            }

                            foreach ($notes as $note) {
                                echo '<tr>';
                                echo '<td>' . xlt('Note') . ' #' . text($note->get_id()) . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . xlt('Date') . ': ' . text(oeFormatShortDate($note->get_date())) . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . text($note->get_note()) . '<br /><br /></td>';
                                echo '</tr>';
                            }

                            if (!empty($notes)) {
                                echo "</table></div>";
                            }

                            // adding support for .txt MDM-TXA interface/orders/receive_hl7_results.inc.php
                            if ($extension != (".pdf" || ".txt")) {
                                $tempCDoc = new C_Document();
                                $tempFile = $tempCDoc->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                                // tmp file in temporary_files_dir
                                $tempFileName = tempnam($GLOBALS['temporary_files_dir'], "oer");
                                file_put_contents($tempFileName, $tempFile);
                                $image_data = getimagesize($tempFileName);
                                $extension = image_type_to_extension($image_data[2]);
                                unlink($tempFileName);
                            }

                            if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                                if ($PDF_OUTPUT) {
                                    // OK to link to the image file because it will be accessed by the
                                    // mPDF parser and not the browser.
                                    $tempDocC = new C_Document();
                                    $fileTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                                    // tmp file in ../documents/temp since need to be available via webroot
                                    $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'] . '/documents/temp', "oer");
                                    file_put_contents($from_file_tmp_web_name, $fileTemp);
                                    echo "<img src='$from_file_tmp_web_name'";
                                    // Flag images with excessive width for possible stylesheet action.
                                    $asize = getimagesize($from_file_tmp_web_name);
                                    if ($asize[0] > 750) {
                                        echo " class='bigimage'";
                                    }
                                    $tmp_files_remove[] = $from_file_tmp_web_name;
                                    echo " /><br /><br />";
                                } else {
                                    echo "<img src='" . $GLOBALS['webroot'] .
                                        "/controller.php?document&retrieve&patient_id=&document_id=" .
                                        attr_url($document_id) . "&as_file=false&original_file=true&disable_exit=false&show_original=true'><br /><br />";
                                }
                            } else {
                                // Most clinic documents are expected to be PDFs, and in that happy case
                                // we can avoid the lengthy image conversion process.
                                if ($PDF_OUTPUT && $extension == ".pdf") {
                                    echo "</div></div>\n"; // HTML to PDF conversion will fail if there are open tags.
                                    $content = getContent();
                                    $pdf->writeHTML($content); // catch up with buffer.
                                    $err = '';
                                    try {
                                        // below header isn't being used. missed maybe!
                                        $pg_header = "<span>" . xlt('Document') . " " . text($fname) . "-" . text($d->get_id()) . "</span>";
                                        $tempDocC = new C_Document();
                                        $pdfTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                                        // tmp file in temporary_files_dir
                                        $from_file_tmp_name = tempnam($GLOBALS['temporary_files_dir'], "oer");
                                        file_put_contents($from_file_tmp_name, $pdfTemp);

                                        $pagecount = $pdf->setSourceFile($from_file_tmp_name);
                                        for ($i = 0; $i < $pagecount; ++$i) {
                                            $pdf->AddPage();
                                            $itpl = $pdf->importPage($i + 1);
                                            $pdf->useTemplate($itpl);
                                        }
                                    } catch (Exception $e) {
                                        // chances are PDF is > v1.4 and compression level not supported.
                                        // regardless, we're here so lets dispose in different way.
                                        //
                                        unlink($from_file_tmp_name);
                                        $archive_name = ($GLOBALS['temporary_files_dir'] . '/' . report_basename($pid)['base'] . ".zip");
                                        $rtn = zip_content(basename($d->url), $archive_name, $pdfTemp);
                                        $err = "<span>" . xlt('PDF Document Parse Error and not included. Check if included in archive.') . " : " . text($fname) . "</span>";
                                        $pdf->writeHTML($err);
                                        $staged_docs[] = array('path' => $d->url, 'fname' => $fname);
                                    } finally {
                                        unlink($from_file_tmp_name);
                                        // Make sure whatever follows is on a new page. Maybe!
                                        // okay if not a series of pdfs so if so need @todo
                                        if (empty($err)) {
                                            $pdf->AddPage();
                                        }
                                        // Resume output buffering and the above-closed tags.
                                        ob_start();
                                        echo "<div><div class='text documents'>\n";
                                    }
                                } elseif ($extension == ".txt") {
                                    echo "<pre>";
                                    $tempDocC = new C_Document();
                                    $textTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                                    echo text($textTemp);
                                    echo "</pre>";
                                } else {
                                    if ($PDF_OUTPUT) {
                                        // OK to link to the image file because it will be accessed by the mPDF parser and not the browser.
                                        $tempDocC = new C_Document();
                                        $fileTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, false, true, true);
                                        // tmp file in ../documents/temp since need to be available via webroot
                                        $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'] . '/documents/temp', "oer");
                                        file_put_contents($from_file_tmp_web_name, $fileTemp);
                                        echo "<img src='$from_file_tmp_web_name'><br /><br />";
                                        $tmp_files_remove[] = $from_file_tmp_web_name;
                                    } else {
                                        if ($extension === '.pdf' || $extension === '.zip') {
                                            echo "<strong>" . xlt('Available Document') . ":</strong><em> " . text($fname) . "</em><br />";
                                        } else {
                                            echo "<img src='" . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . attr_url($document_id) . "&as_file=false&original_file=false'><br /><br />";
                                        }
                                    }
                                }
                            } // end if-else
                        } // end Documents loop
                        echo "</div>";
                    } elseif ($key == "procedures") { // Procedures is an array of checkboxes whose values are procedure order IDs.
                        if ($auth_med) {
                            echo "<hr />";
                            echo "<div class='text documents'>";
                            foreach ($val as $valkey => $poid) {
                                if (empty($GLOBALS['esign_report_show_only_signed'])) {
                                    echo '<h4>' . xlt('Procedure Order') . ':</h4>';
                                    echo "<br />\n";
                                    generate_order_report($poid, false, !$PDF_OUTPUT);
                                    echo "<br />\n";
                                }
                            }
                            echo "</div>";
                        }
                    } elseif (strpos($key, "issue_") === 0) {
                        // display patient Issues
                        if ($first_issue) {
                            $prevIssueType = 'asdf1234!@#$'; // random junk so as to not match anything
                            $first_issue = 0;
                            echo "<hr />";
                            echo "<h4>" . xlt("Issues") . "</h4>";
                        }

                        preg_match('/^(.*)_(\d+)$/', $key, $res);
                        $rowid = $res[2];
                        $irow = sqlQuery("SELECT type, title, comments, diagnosis, udi_data " .
                            "FROM lists WHERE id = ?", array($rowid));
                        $diagnosis = $irow['diagnosis'];
                        if ($prevIssueType != $irow['type']) {
                            // output a header for each Issue Type we encounter
                            $disptype = $ISSUE_TYPES[$irow['type']][0];
                            echo "<div class='issue_type'>" . text($disptype) . ":</div>\n";
                            $prevIssueType = $irow['type'];
                        }

                        echo "<div class='text issue'>";
                        if ($prevIssueType == "medical_device") {
                            echo "<span class='issue_title'><span class='font-weight-bold'>" . xlt('Title') . ": </span>" . text($irow['title']) . "</span><br>";
                            echo "<span class='issue_title'>" . (new MedicalDevice($irow['udi_data']))->fullOutputHtml() . "</span>";
                            echo "<span class='issue_comments'> " . text($irow['comments']) . "</span><br><br>\n";
                        } else {
                            echo "<span class='issue_title'>" . text($irow['title']) . ":</span>";
                            echo "<span class='issue_comments'> " . text($irow['comments']) . "</span>\n";
                        }

                        // Show issue's chief diagnosis and its description:
                        if ($diagnosis) {
                            echo "<div class='text issue_diag'>";
                            echo "<span class='font-weight-bold'>[" . xlt('Diagnosis') . "]</span><br />";
                            $dcodes = explode(";", $diagnosis);
                            foreach ($dcodes as $dcode) {
                                echo "<span class='italic'>" . text($dcode) . "</span>: ";
                                echo text(lookup_code_descriptions($dcode)) . "<br />\n";
                            }

                            //echo $diagnosis." -- ".lookup_code_descriptions($diagnosis)."\n";
                            echo "</div>";
                        }

                        // Supplemental data for GCAC or Contraception issues.
                        if ($irow['type'] == 'ippf_gcac') {
                            echo "   <div class='table-responsive'><table class='table'>\n";
                            display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = ?", array($rowid)));
                            echo "   </table></div>\n";
                        } elseif ($irow['type'] == 'contraceptive') {
                            echo "   <div class='table-responsive'><table class='table'>\n";
                            display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = ?", array($rowid)));
                            echo "   </table></div>\n";
                        }

                        echo "</div>\n"; //end the issue DIV
                    } else {
                        // we have an "encounter form" form field whose name is like
                        // dirname_formid, with a value which is the encounter ID.
                        //
                        // display encounter forms, encoded as a POST variable
                        // in the format: <formdirname_formid>=<encounterID>

                        if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                            $form_encounter = $val;
                            preg_match('/^(.*)_(\d+)$/', $key, $res);
                            $form_id = $res[2];
                            $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
                            $dateres = getEncounterDateByEncounter($form_encounter);
                            $formId = getFormIdByFormdirAndFormid($res[1], $form_id);

                            if ($res[1] == 'newpatient') {
                                echo "<div class='text encounter'>\n";
                                echo "<h4>" . xlt($formres["form_name"]) . "</h4>";
                            } else {
                                echo "<div class='text encounter_form'>";
                                echo "<h4>" . text(xl_form_title($formres["form_name"])) . "</h4>";
                            }

                            // show the encounter's date
                            echo "(" . text(oeFormatSDFT(strtotime($dateres["date"]))) . ") ";
                            if ($res[1] == 'newpatient') {
                                // display the provider info
                                echo ' ' . xlt('Provider') . ': ' . text(getProviderName(getProviderIdOfEncounter($form_encounter)));
                            }

                            echo "<br />\n";

                            // call the report function for the form
                            ?>
                            <div name="search_div" id="search_div_<?php echo attr($form_id) ?>_<?php echo attr($res[1]) ?>" class="report_search_div class_<?php echo attr($res[1]); ?>">
                                <?php
                                $esign = $esignApi->createFormESign($formId, $res[1], $form_encounter);
                                if ($esign->isSigned('report') && !empty($GLOBALS['esign_report_show_only_signed'])) {
                                    if (substr($res[1], 0, 3) == 'LBF') {
                                        call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                                    } else {
                                        call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
                                    }
                                } elseif (empty($GLOBALS['esign_report_show_only_signed'])) {
                                    if (substr($res[1], 0, 3) == 'LBF') {
                                        call_user_func('lbf_report', $pid, $form_encounter, $N, $form_id, $res[1]);
                                    } else {
                                        call_user_func($res[1] . '_report', $pid, $form_encounter, $N, $form_id);
                                    }
                                } else {
                                    echo "<h6>" . xlt("Not signed.") . "</h6>";
                                }
                                if ($esign->isLogViewable("report")) {
                                    $esign->renderLog();
                                }
                                ?>

                            </div>
                            <?php

                            if ($res[1] == 'newpatient') {
                                // display billing info
                                $bres = sqlStatement(
                                    "SELECT b.date, b.code, b.code_text, b.modifier " .
                                    "FROM billing AS b, code_types AS ct WHERE " .
                                    "b.pid = ? AND " .
                                    "b.encounter = ? AND " .
                                    "b.activity = 1 AND " .
                                    "b.code_type = ct.ct_key AND " .
                                    "ct.ct_diag = 0 " .
                                    "ORDER BY b.date",
                                    array($pid, $form_encounter)
                                );
                                while ($brow = sqlFetchArray($bres)) {
                                    echo "<div class='font-weight-bold d-inline-block'>&nbsp;" . xlt('Procedure') . ": </div><div class='text d-inline-block'>" .
                                        text($brow['code']) . ":" . text($brow['modifier']) . " " . text($brow['code_text']) . "</div><br />\n";
                                }
                            }

                            print "</div>";
                        } // end auth-check for encounter forms
                    } // end if('issue_')... else...
                } // end if('include_')... else...
            } // end $ar loop

            if ($printable && !$PDF_OUTPUT) {// Patched out of pdf 04/20/2017 sjpadgett
                echo "<br /><br />" . xlt('Signature') . ": _______________________________<br />";
            }
            ?>

        </div> <!-- end of report_custom DIV -->
    </div>

    <?php
    if ($PDF_OUTPUT) {
        $content = getContent();
        $ptd = report_basename($pid);
        $fn = $ptd['base'] . ".pdf";
        $pdf->SetTitle(ucfirst($ptd['fname']) . ' ' . $ptd['lname'] . ' ' . xl('Id') . ':' . $pid . ' ' . xl('Report'));
        $isit_utf8 = preg_match('//u', $content); // quick check for invalid encoding
        if (!$isit_utf8) {
            if (function_exists('iconv')) { // if we can lets save the report
                $content = iconv("UTF-8", "UTF-8//IGNORE", $content);
            } else { // no sense going on.
                $die_str = xlt("Failed UTF8 encoding check! Could not automatically fix.");
                die($die_str);
            }
        }

        try {
            $pdf->writeHTML($content); // convert html
        } catch (MpdfException $exception) {
            die(text($exception));
        }

        if ($PDF_OUTPUT == 1) {
            try {
                if ($PDF_FAX === 1) {
                    $fax_pdf = $pdf->Output($fn, 'S');
                    $tmp_file = $GLOBALS['temporary_files_dir'] . '/' . $fn; // is deleted in sendFax...
                    file_put_contents($tmp_file, $fax_pdf);
                    echo $tmp_file;
                    exit();
                } else {
                    if (!empty($archive_name) && sizeof($staged_docs) > 0) {
                        $rtn = zip_content(basename($fn), $archive_name, $pdf->Output($fn, 'S'));
                        header('Content-Description: File Transfer');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header("Cache-control: private");
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header("Content-Type: application/zip; charset=utf-8");
                        header("Content-Length: " . filesize($archive_name));
                        header('Content-Disposition: attachment; filename="' . basename($archive_name) . '"');

                        ob_end_clean();
                        @readfile($archive_name) or error_log("Archive temp file not found: " . $archive_name);

                        unlink($archive_name);
                    } else {
                        $pdf->Output($fn, $GLOBALS['pdf_output']); // D = Download, I = Inline
                    }
                }
            } catch (MpdfException $exception) {
                die(text($exception));
            }
        } else {
            // This is the case of writing the PDF as a message to the CMS portal.
            $ptdata = getPatientData($pid, 'cmsportal_login');
            $contents = $pdf->Output('', true);
            echo "<html><head>\n";
            Header::setupHeader();
            echo "</head><body>\n";
            $result = cms_portal_call(array(
                'action' => 'putmessage',
                'user' => $ptdata['cmsportal_login'],
                'title' => xl('Your Clinical Report'),
                'message' => xl('Please see the attached PDF.'),
                'filename' => 'report.pdf',
                'mimetype' => 'application/pdf',
                'contents' => base64_encode($contents)
            ));
            if ($result['errmsg']) {
                die(text($result['errmsg']));
            }

            echo "<p class='mt-3'>" . xlt('Report has been sent to the patient.') . "</p>\n";
            echo "</body></html>\n";
        }
        foreach ($tmp_files_remove as $tmp_file) {
            // Remove the tmp files that were created
            unlink($tmp_file);
        }
    } else {
        ?>
        <?php if (!$printable) { ?>
        <script src="<?php echo $GLOBALS['web_root'] ?>/interface/patient_file/report/custom_report.js?v=<?php echo $v_js_includes; ?>"></script>
        <script>
            const searchBarHeight = document.querySelectorAll('.report_search_bar')[0].clientHeight;
            document.getElementById('backLink').style.marginTop = `${searchBarHeight}px`;
        </script>
    <?php } ?>

</body>
</html>
<?php } ?>
