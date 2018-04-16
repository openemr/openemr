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
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");
if ($GLOBALS['gbl_portal_cms_enable']) {
    require_once($GLOBALS["include_root"] . "/cmsportal/portal.inc.php");
}
require_once("$srcdir/appointments.inc.php");

use ESign\Api;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

// For those who care that this is the patient report.
$GLOBALS['PATIENT_REPORT_ACTIVE'] = true;

$PDF_OUTPUT = empty($_POST['pdf']) ? 0 : intval($_POST['pdf']);

if ($PDF_OUTPUT) {
/*   composer bootstrap loads classes for mPDF */
    $pdf = new mPDF(
        $GLOBALS['pdf_language'], // codepage or language/codepage or language - this can help auto determine many other options such as RTL
        $GLOBALS['pdf_size'], // Globals default is 'letter'
        '9', // default font size (pt)
        '', // default_font. will set explicitly in script.
        $GLOBALS['pdf_left_margin'],
        $GLOBALS['pdf_right_margin'],
        $GLOBALS['pdf_top_margin'],
        $GLOBALS['pdf_bottom_margin'],
        '', // default header margin
        '', // default footer margin
        $GLOBALS['pdf_layout']
    ); // Globals default is 'P'

      $pdf->shrink_tables_to_fit = 1;
      $keep_table_proportions = true;
      $pdf->use_kwt = true;

 // set 'dejavusans' for now. which is supported by a lot of languages - http://dejavu-fonts.org/wiki/Main_Page
 // TODO: can have this selected as setting in globals after we have more experience with this to fully support internationalization. Don't think this is issue here.
       $pdf->setDefaultFont('dejavusans'); // see config_fonts.php/config_lang2fonts.php for OTL font declarations for different languages/fonts. Important for auto font select getting right font for lanaguage.
       $pdf->autoScriptToLang = true; // will sense font based on language used in html i.e if hebrew text is sent the proper font will be selected. IMPORTANT: this affects performance.
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl'); // direction from html will still be honored.
    }

    ob_start();
} // end pdf conditional.

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients', 'med');
$auth_demo     = acl_check('patients', 'demo');

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

        if (substr($content, $i+6, $wrlen) === $web_root &&
        substr($content, $i+6, $wsrlen) !== $webserver_root) {
            $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
        }
    }

    return $content;
}

function postToGet($arin)
{
    $getstring="";
    foreach ($arin as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
            }
        } else {
            $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
        }
    }

    return $getstring;
}
?>

<?php if ($PDF_OUTPUT) { ?>
<link rel="stylesheet" href="<?php echo  $web_root . '/interface/themes/style_pdf.css' ?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $web_root; ?>/library/ESign/css/esign_report.css" />
<?php } else {?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/ESign/css/esign_report.css" />
<?php } ?>

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the
      // encounter listings output, but not in the custom report. ?>
<style>
  div.navigateLink {display:none;}
  .hilite {background-color: #FFFF00;}
  .hilite2 {background-color: transparent;}
  mark {background-color: #FFFF00;}
  .css_button{cursor:pointer;}
  .next {background-color: #FFFF00;}
  #search_options{
    position:fixed;
    left:0px;
    top:0px;
    z-index:10;
    border-bottom: solid thin #6D6D6D;
    padding:0% 2% 0% 2.5%;
  }
  img { max-width:700px; }
</style>

<?php if (!$PDF_OUTPUT) { ?>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/library/js/SearchHighlight.js"></script>
<script type="text/javascript">var $j = jQuery.noConflict();</script>

<?php // if the track_anything form exists, then include the styling
if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css" type="text/css">
<?php  } ?>

</head>
<?php
// remove blank header for printable version to conserve space
// adjust this if you are printing to letterhead to appropriate height
($printable) ? ($style = ''):($style='padding-top:95px;');
?>
<body class="body_top" style="<?php echo $style; ?>">
<?php } ?>
<div id="report_custom" style="width: 100%;">  <!-- large outer DIV -->

<?php
if (sizeof($_GET) > 0) {
    $ar = $_GET;
} else {
    $ar = $_POST;
}

if ($printable) {
  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
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
    echo '<page_header style="text-align:right;" class="custom-tag"> ' . xlt("PATIENT") . ':' . text($titleres['lname']) . ', ' . text($titleres['fname']) . ' - ' . $titleres['DOB_TS'] . '</page_header>    ';
    echo '<page_footer style="text-align:right;" class="custom-tag">' . xlt('Generated on') . ' ' . text(oeFormatShortDate()) . ' - ' . text($facility['name']) . ' ' . text($facility['phone']) . '</page_footer>';

    // Use logo if it exists as 'practice_logo.gif' in the site dir
    // old code used the global custom dir which is no longer a valid
    $practice_logo = "";
    $plogo = glob("$OE_SITE_DIR/images/*");// let's give the user a little say in image format.
    $plogo = preg_grep('~practice_logo\.(gif|png|jpg|jpeg)$~i', $plogo);
    if (! empty($plogo)) {
        $k = current(array_keys($plogo));
        $practice_logo = $plogo[$k];
    }

    echo "<div><table width='795'><tbody><tr><td>";
    if (file_exists($practice_logo)) {
        $logo_path = $GLOBALS['OE_SITE_WEBROOT'] . "/images/". basename($practice_logo);
        echo "<img style='max-width:250px;height:auto;' src='$logo_path' align='left'>"; // keep size within reason
        echo "</td><td>";
    }
    ?>
    <h2><?php echo $facility['name'] ?></h2>
<?php echo $facility['street'] ?><br>
<?php echo $facility['city'] ?>, <?php echo $facility['state'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
<?php echo $facility['phone'] ?><br>

<a href="javascript:window.close();"><span class='title'><?php echo $titleres['fname'] . " " . $titleres['lname']; ?></span></a><br>
<span class='text'><?php xl('Generated on', 'e'); ?>: <?php echo text(oeFormatShortDate()); ?></span>
<?php echo "</td></tr></tbody></table></div>";?>

<?php
} else { // not printable
    ?>

    <a href="patient_report.php" onclick='top.restoreSession()'>
 <span class='title'><?php xl('Patient Report', 'e'); ?></span>
 <span class='back'><?php echo $tback;?></span>
</a><br><br>
<a href="custom_report.php?printable=1&<?php print postToGet($ar); ?>" class='link_submit' target='new' onclick='top.restoreSession()'>
 [<?php xl('Printable Version', 'e'); ?>]
</a><br>
<div class="report_search_bar" style="width: 100%;" id="search_options">
  <table style="width: 100%;">
    <tr>
  <td>
    <input type="text" onKeyUp="clear_last_visit();remove_mark_all();find_all();" name="search_element" id="search_element" style="width: 180px;"/>
  </td>
  <td>
     <a class="css_button" onClick="clear_last_visit();remove_mark_all();find_all();" ><span><?php echo xlt('Find'); ?></span></a>
  </td>
  <td>
     <a class="css_button" onClick="next_prev('prev');" ><span><?php echo xlt('Prev'); ?></span></a>
  </td>
  <td>
     <a class="css_button" onClick="next_prev('next');" ><span><?php echo xlt('Next'); ?></span></a>
  </td>
  <td>
    <input type="checkbox" onClick="clear_last_visit();remove_mark_all();find_all();" name="search_case" id="search_case" />
  </td>
  <td>
    <span><?php echo xlt('Match case'); ?></span>
  </td>
  <td style="padding-left: 10px;">
    <span class="text"><b><?php echo xlt('Search In'); ?>:</b></span>
    <br>
    <?php
    $form_id_arr = array();
    $form_dir_arr = array();
    $last_key ='';
    //ksort($ar);
    foreach ($ar as $key_search => $val_search) {
        if ($key_search == 'pdf' || $key_search == '') {
            continue;
        }

        if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                    preg_match('/^(.*)_(\d+)$/', $key_search, $res_search);
                    $form_id_arr[] = add_escape_custom($res_search[2]);
                     $form_dir_arr[] = add_escape_custom($res_search[1]);
        }
    }

    //echo json_encode(json_encode($array_key_id));
    if (sizeof($form_id_arr)>0) {
        $query = "SELECT DISTINCT(form_name),formdir FROM forms WHERE form_id IN ( '".implode("','", $form_id_arr)."') AND formdir IN ( '".implode("','", $form_dir_arr)."')";
        $arr = sqlStatement($query);
        echo "<select multiple size='4' style='width:300px;' id='forms_to_search' onchange='clear_last_visit();remove_mark_all();find_all();' >";
        while ($res_forms_ids = sqlFetchArray($arr)) {
            echo "<option value='".attr($res_forms_ids['formdir'])."' selected>".text($res_forms_ids['form_name'])."</option>";
        }

        echo "</select>";
    }
    ?>
      </td>
      <td style="padding-left: 10px;; width: 30%;">
    <span id ='alert_msg' style='color: red;'></span>
      </td>
    </tr>
  </table>
    </div>
    <?php
} // end not printable ?>

<?php

// include ALL form's report.php files
$inclookupres = sqlStatement("select distinct formdir from forms where pid = ? AND deleted=0", array($pid));
while ($result = sqlFetchArray($inclookupres)) {
  // include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
    $formdir = $result['formdir'];
    if (substr($formdir, 0, 3) == 'LBF') {
        include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
    } else {
        include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
    }
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
            print "<h1>".xlt('Recurrent Appointments').":</h1>";

            //fetch the data of the recurring days
            $recurrences = fetchRecurrences($pid);

            //print the recurring days to screen
            if (empty($recurrences)) { //if there are no recurrent appointments:
                echo "<div class='text' >";
                echo "<span>" . xlt('None') . "</span>";
                echo "</div>";
                echo "<br>";
            } else {
                foreach ($recurrences as $row) {
                    //checks if there are recurrences and if they are current (git didn't end yet)
                    if (!recurrence_is_current($row['pc_endDate'])) {
                        continue;
                    }

                    echo "<div class='text' >";
                    echo "<span>" . xlt('Appointment Category') . ': ' . xlt($row['pc_catname']) . "</span>";
                    echo "<br>";
                    echo "<span>" . xlt('Recurrence') . ': ' .text($row['pc_recurrspec']) . "</span>";
                    echo "<br>";
                    $red_text = ""; //if ends in a week, make font red
                    if (ends_in_a_week($row['pc_endDate'])) {
                        $red_text = " style=\"color:red;\" ";
                    }

                    echo "<span" . $red_text . ">" . xlt('End Date') . ': ' . text($row['pc_endDate']) . "</span>";
                    echo "</div>";
                    echo "<br>";
                }
            }

            echo "</div><br>";
        } elseif ($val == "demographics") {
            echo "<hr />";
            echo "<div class='text demographics' id='DEM'>\n";
            print "<h1>".xl('Patient Data').":</h1>";
            // printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);
            $result1 = getPatientData($pid);
            $result2 = getEmployerData($pid);
            echo "   <table>\n";
            display_layout_rows('DEM', $result1, $result2);
            echo "   </table>\n";
            echo "</div>\n";
        } elseif ($val == "history") {
            echo "<hr />";
            echo "<div class='text history' id='HIS'>\n";
            if (acl_check('patients', 'med')) {
                print "<h1>".xl('History Data').":</h1>";
                // printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);
                $result1 = getHistoryData($pid);
                echo "   <table>\n";
                display_layout_rows('HIS', $result1);
                echo "   </table>\n";
            }

            echo "</div>";

            // } elseif ($val == "employer") {
            //   print "<br><span class='bold'>".xl('Employer Data').":</span><br>";
            //   printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);
        } elseif ($val == "insurance") {
            echo "<hr />";
            echo "<div class='text insurance'>";
            echo "<h1>".xl('Insurance Data').":</h1>";
            print "<br><span class=bold>".xl('Primary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "primary"), $N);
            print "<span class=bold>".xl('Secondary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "secondary"), $N);
            print "<span class=bold>".xl('Tertiary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "tertiary"), $N);
            echo "</div>";
        } elseif ($val == "billing") {
            echo "<hr />";
            echo "<div class='text billing'>";
            print "<h1>".xl('Billing Information').":</h1>";
            if (!empty($ar['newpatient']) && count($ar['newpatient']) > 0) {
                $billings = array();
                echo "<table>";
                echo "<tr><td width='400' class='bold'>Code</td><td class='bold'>".xl('Fee')."</td></tr>\n";
                $total = 0.00;
                $copays = 0.00;
                foreach ($ar['newpatient'] as $be) {
                    $ta = explode(":", $be);
                    $billing = getPatientBillingEncounter($pid, $ta[1]);
                    $billings[] = $billing;
                    foreach ($billing as $b) {
                        echo "<tr>\n";
                        echo "<td class=text>";
                        echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . htmlspecialchars($b['code_text']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td>\n";
                        echo "<td class=text>";
                        echo oeFormatMoney($b['fee']);
                        echo "</td>\n";
                        echo "</tr>\n";
                        $total += $b['fee'];
                        if ($b['code_type'] == "COPAY") {
                            $copays += $b['fee'];
                        }
                    }
                }

                echo "<tr><td>&nbsp;</td></tr>";
                echo "<tr><td class=bold>".xl('Sub-Total')."</td><td class=text>" . oeFormatMoney($total + abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Paid')."</td><td class=text>" . oeFormatMoney(abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Total')."</td><td class=text>" . oeFormatMoney($total) . "</td></tr>";
                echo "</table>";
                echo "<pre>";
                //print_r($billings);
                echo "</pre>";
            } else {
                printPatientBilling($pid);
            }

            echo "</div>\n"; // end of billing DIV

    /****

        } elseif ($val == "allergies") {

            print "<span class=bold>Patient Allergies:</span><br>";
            printListData($pid, "allergy", "1");

        } elseif ($val == "medications") {

            print "<span class=bold>Patient Medications:</span><br>";
            printListData($pid, "medication", "1");

        } elseif ($val == "medical_problems") {

            print "<span class=bold>Patient Medical Problems:</span><br>";
            printListData($pid, "medical_problem", "1");

    ****/
        } elseif ($val == "immunizations") {
            if (acl_check('patients', 'med')) {
                echo "<hr />";
                echo "<div class='text immunizations'>\n";
                print "<h1>".xl('Patient Immunization').":</h1>";
                $sql = "select i1.immunization_id, i1.administered_date, substring(i1.note,1,20) as immunization_note, c.code_text_short ".
                   " from immunizations i1 ".
                   " left join code_types ct on ct.ct_key = 'CVX' ".
                   " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code ".
                   " where i1.patient_id = '$pid' and i1.added_erroneously = 0 ".
                   " order by administered_date desc";
                $result = sqlStatement($sql);
                while ($row=sqlFetchArray($result)) {
                  // Figure out which name to use (ie. from cvx list or from the custom list)
                    if ($GLOBALS['use_custom_immun_list']) {
                         $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                    } else {
                        if (!empty($row['code_text_short'])) {
                            $vaccine_display = htmlspecialchars(xl($row['code_text_short']), ENT_NOQUOTES);
                        } else {
                            $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                        }
                    }

                    echo $row['administered_date'] . " - " . $vaccine_display;
                    if ($row['immunization_note']) {
                         echo " - " . $row['immunization_note'];
                    }

                    echo "<br>\n";
                }

                echo "</div>\n";
            }

        // communication report
        } elseif ($val == "batchcom") {
            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Communication sent').":</h1>";
            $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
            // echo $sql;
            $result = sqlStatement($sql);
            while ($row=sqlFetchArray($result)) {
                echo $row{'batchcom_data'}.", By: ".$row{'user_name'}."<br>Text:<br> ".$row{'msg_txt'}."<br>\n";
            }

            echo "</div>\n";
        } elseif ($val == "notes") {
            echo "<hr />";
            echo "<div class='text notes'>\n";
            print "<h1>".xl('Patient Notes').":</h1>";
            printPatientNotes($pid);
            echo "</div>";
        } elseif ($val == "transactions") {
            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Transactions').":</h1>";
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
                $fname = basename($d->get_url());
                $couch_docid = $d->get_couch_docid();
                $couch_revid = $d->get_couch_revid();
                //  Extract the extension by the mime/type and not the file name extension
                // -There is an exception. Need to manually see if it a pdf since
                //  the image_type_to_extension() is not working to identify pdf.
                $extension = strtolower(substr($fname, strrpos($fname, ".")));
                if ($extension != '.pdf') { // Will print pdf header within pdf import
                    echo "<h3>" . xl('Document') . " '" . $fname ."'</h3>";
                }

                $notes = $d->get_notes();
                if (!empty($notes)) {
                    echo "<table>";
                }

                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . xl('Note') . ' #' . $note->get_id() . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . xl('Date') . ': ' . text(oeFormatShortDate($note->get_date())) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>'.$note->get_note().'<br><br></td>';
                    echo '</tr>';
                }

                if (!empty($notes)) {
                    echo "</table>";
                }

                $url_file = $d->get_url_filepath();
                if ($couch_docid && $couch_revid) {
                    $url_file = $d->get_couch_url($pid, $encounter);
                }

                // Collect filename and path
                $from_all = explode("/", $url_file);
                $from_filename = array_pop($from_all);
                $from_pathname_array = array();
                for ($i=0; $i<$d->get_path_depth(); $i++) {
                    $from_pathname_array[] = array_pop($from_all);
                }

                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/", $from_pathname_array);

                if ($couch_docid && $couch_revid) {
                    $from_file = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $from_filename;
                    $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                } else {
                    $from_file = $GLOBALS["fileroot"] . "/sites/" . $_SESSION['site_id'] .
                    '/documents/' . $from_pathname . '/' . $from_filename;
                    $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                }
                // adding support for .txt MDM-TXA interface/orders/receive_hl7_results.inc.php
                if ($extension != (".pdf" || ".txt")) {
                    $image_data = getimagesize($from_file);
                    $extension = image_type_to_extension($image_data[2]);
                }

                if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                    if ($PDF_OUTPUT) {
                        // OK to link to the image file because it will be accessed by the
                        // mPDF parser and not the browser.
                        $from_rel = $web_root . substr($from_file, strlen($webserver_root));
                        echo "<img src='$from_rel'";
                        // Flag images with excessive width for possible stylesheet action.
                        $asize = getimagesize($from_file);
                        if ($asize[0] > 750) {
                            echo " class='bigimage'";
                        }

                        echo " /><br><br>";
                    } else {
                        echo "<img src='" . $GLOBALS['webroot'] .
                        "/controller.php?document&retrieve&patient_id=&document_id=" .
                        $document_id . "&as_file=false&original_file=true&disable_exit=false&show_original=true'><br><br>";
                    }
                } else {
                        // Most clinic documents are expected to be PDFs, and in that happy case
                        // we can avoid the lengthy image conversion process.
                    if ($PDF_OUTPUT && $extension == ".pdf") {
                        echo "</div></div>\n"; // HTML to PDF conversion will fail if there are open tags.
                        $content = getContent();
                        $pdf->writeHTML($content, false); // catch up with buffer.
                        $pdf->SetImportUse();
                        $pg_header = "<span>" . xl('Document') . " " . $fname ."</span>";
                        //$pdf->SetHTMLHeader ($pg_header,'left',false); // A header for imported doc, don't think we need but will keep.
                        $pagecount = $pdf->setSourceFile($from_file);
                        for ($i = 0; $i < $pagecount; ++$i) {
                            $pdf->AddPage();
                            $itpl = $pdf->importPage($i+1);
                            $pdf->useTemplate($itpl);
                        }

                        // Make sure whatever follows is on a new page.
                       // $pdf->AddPage(); // Only needed for signature line. Patched out 04/20/2017 sjpadgett.

                        // Resume output buffering and the above-closed tags.
                        ob_start();

                        echo "<div><div class='text documents'>\n";
                    } elseif ($extension == ".txt") {
                        echo "<pre>";
                        readfile($from_file);
                        echo "</pre>";
                    } else {
                        if (! is_file($to_file)) {
                            exec("convert -density 200 \"$from_file\" -append -resize 850 \"$to_file\"");
                        }

                        if (is_file($to_file)) {
                            if ($PDF_OUTPUT) {
                                // OK to link to the image file because it will be accessed by the mPDF parser and not the browser.
                                echo "<img src='$to_file'><br><br>";
                            } else {
                                echo "<img src='" . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . $document_id . "&as_file=false&original_file=false'><br><br>";
                            }
                        } else {
                            echo "<b>NOTE</b>: " . xl('Document') . "'" . $fname . "' " . xl('cannot be converted to JPEG. Perhaps ImageMagick is not installed?') . "<br><br>";
                            if ($couch_docid && $couch_revid) {
                                unlink($from_file);
                            }
                        }
                    }
                } // end if-else
            } // end Documents loop
            echo "</div>";
        } // Procedures is an array of checkboxes whose values are procedure order IDs.
        else if ($key == "procedures") {
            if ($auth_med) {
                echo "<hr />";
                echo "<div class='text documents'>";
                foreach ($val as $valkey => $poid) {
                    echo "<h1>" . xlt('Procedure Order') . ":</h1>";
                    echo "<br />\n";
                    // Need to move the inline styles from this function to the stylesheet, but until
                    // then we do it just for PDFs to avoid breaking anything.
                    generate_order_report($poid, false, !$PDF_OUTPUT);
                    echo "<br />\n";
                }

                echo "</div>";
            }
        } else if (strpos($key, "issue_") === 0) {
                // display patient Issues
            if ($first_issue) {
                $prevIssueType = 'asdf1234!@#$'; // random junk so as to not match anything
                $first_issue = 0;
                echo "<hr />";
                echo "<h1>".xl("Issues")."</h1>";
            }

            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $rowid = $res[2];
            $irow = sqlQuery("SELECT type, title, comments, diagnosis " .
                            "FROM lists WHERE id = '$rowid'");
            $diagnosis = $irow['diagnosis'];
            if ($prevIssueType != $irow['type']) {
                // output a header for each Issue Type we encounter
                $disptype = $ISSUE_TYPES[$irow['type']][0];
                echo "<div class='issue_type'>" . $disptype . ":</div>\n";
                $prevIssueType = $irow['type'];
            }

            echo "<div class='text issue'>";
            echo "<span class='issue_title'>" . $irow['title'] . ":</span>";
            echo "<span class='issue_comments'> " . $irow['comments'] . "</span>\n";
            // Show issue's chief diagnosis and its description:
            if ($diagnosis) {
                echo "<div class='text issue_diag'>";
                echo "<span class='bold'>[".xl('Diagnosis')."]</span><br>";
                $dcodes = explode(";", $diagnosis);
                foreach ($dcodes as $dcode) {
                    echo "<span class='italic'>".$dcode."</span>: ";
                    echo lookup_code_descriptions($dcode)."<br>\n";
                }

                //echo $diagnosis." -- ".lookup_code_descriptions($diagnosis)."\n";
                echo "</div>";
            }

            // Supplemental data for GCAC or Contraception issues.
            if ($irow['type'] == 'ippf_gcac') {
                echo "   <table>\n";
                display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = '$rowid'"));
                echo "   </table>\n";
            } else if ($irow['type'] == 'contraceptive') {
                echo "   <table>\n";
                display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = '$rowid'"));
                echo "   </table>\n";
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
                    echo "<h1>" . xl($formres["form_name"]) . "</h1>";
                } else {
                    echo "<div class='text encounter_form'>";
                    echo "<h1>" . xl_form_title($formres["form_name"]) . "</h1>";
                }

                // show the encounter's date
                echo "(" . oeFormatSDFT(strtotime($dateres["date"])) . ") ";
                if ($res[1] == 'newpatient') {
                    // display the provider info
                    echo ' '. xl('Provider') . ': ' . text(getProviderName(getProviderIdOfEncounter($form_encounter)));
                }

                echo "<br>\n";

                // call the report function for the form
                ?>
                <div name="search_div" id="search_div_<?php echo attr($form_id)?>_<?php echo attr($res[1])?>" class="report_search_div class_<?php echo attr($res[1]); ?>">
                <?php
                if (substr($res[1], 0, 3) == 'LBF') {
                    call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                } else {
                    call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
                }

                $esign = $esignApi->createFormESign($formId, $res[1], $form_encounter);
                if ($esign->isLogViewable("report")) {
                    $esign->renderLog();
                }
                ?>

                </div>
                <?php

                if ($res[1] == 'newpatient') {
                    // display billing info
                    $bres = sqlStatement(
                        "SELECT b.date, b.code, b.code_text " .
                        "FROM billing AS b, code_types AS ct WHERE " .
                        "b.pid = ? AND " .
                        "b.encounter = ? AND " .
                        "b.activity = 1 AND " .
                        "b.code_type = ct.ct_key AND " .
                        "ct.ct_diag = 0 " .
                        "ORDER BY b.date",
                        array($pid, $form_encounter)
                    );
                    while ($brow=sqlFetchArray($bres)) {
                        echo "<div class='bold' style='display: inline-block'>&nbsp;".xl('Procedure').": </div><div class='text' style='display: inline-block'>" .
                            $brow['code'] . " " . htmlspecialchars($brow['code_text']) . "</div><br>\n";
                    }
                }

                print "</div>";
            } // end auth-check for encounter forms
        } // end if('issue_')... else...
    } // end if('include_')... else...
} // end $ar loop

if ($printable && ! $PDF_OUTPUT) {// Patched out of pdf 04/20/2017 sjpadgett
    echo "<br /><br />" . xl('Signature') . ": _______________________________<br />";
}
?>

</div> <!-- end of report_custom DIV -->

<?php
if ($PDF_OUTPUT) {
    $content = getContent();
    $ptd = getPatientData($pid, "fname,lname");
    $fn = strtolower($ptd['fname'] . '_' . $ptd['lname'] . '_' . $pid . '_' . xl('report') . '.pdf');
    $pdf->SetTitle(ucfirst($ptd['fname']) . ' ' . $ptd['lname'] . ' ' . xl('Id') . ':' . $pid . ' ' . xl('Report'));
    $isit_utf8 = preg_match('//u', $content); // quick check for invalid encoding
    if (! $isit_utf8) {
        if (function_exists('iconv')) { // if we can lets save the report
            $content = iconv("UTF-8", "UTF-8//IGNORE", $content);
        } else { // no sense going on.
            $die_str = xlt("Failed UTF8 encoding check! Could not automatically fix.");
            die($die_str);
        }
    }

    try {
        $pdf->writeHTML($content, false); // convert html
    } catch (MpdfException $exception) {
        die($exception);
    }

    if ($PDF_OUTPUT == 1) {
        try {
            $pdf->Output($fn, $GLOBALS['pdf_output']); // D = Download, I = Inline
        } catch (MpdfException $exception) {
            die($exception);
        }
    } else {
        // This is the case of writing the PDF as a message to the CMS portal.
        $ptdata = getPatientData($pid, 'cmsportal_login');
        $contents = $pdf->Output('', true);
        echo "<html><head>\n";
        echo "<link rel='stylesheet' href='$css_header' type='text/css'>\n";
        echo "</head><body class='body_top'>\n";
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

        echo "<p>" . xlt('Report has been sent to the patient.') . "</p>\n";
        echo "</body></html>\n";
    }
} else {
?>
</body>
<?php if (!$printable) { // Set up translated strings for use by interactive search ?>
<script type="text/javascript">
var xl_string = <?php echo json_encode(array(
    'spcl_chars' => xla('Special characters are not allowed').'.',
    'not_found'  => xla('No results found').'.',
    'results'    => xla('Showing result'),
    'literal_of' => xla('of'),
));
?>;
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/interface/patient_file/report/custom_report.js?v=<?php echo $v_js_includes; ?>"></script>
<?php } ?>
</html>
<?php } ?>
