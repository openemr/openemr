<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once("$srcdir/formatting.inc.php");
require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$printable = empty($_GET['printable']) ? false : true;
unset($_GET['printable']);

$N = 6;
$first_issue = 1;

function postToGet($arin) {
  $getstring="";
  foreach ($arin as $key => $val) {
    if (is_array($val)) {
      foreach ($val as $k => $v) {
        $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
      }
    }
    else {
      $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
    }
  }
  return $getstring;
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the 
      // encounter listings output, but not in the custom report. ?>
<style> div.navigateLink {display:none;} </style>

</head>

<body class="body_top">
<div id="report_custom">  <!-- large outer DIV -->

<?php
if (sizeof($_GET) > 0) { $ar = $_GET; }
else { $ar = $_POST; }

if ($printable) {
  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  *******************************************************************/
  $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
  if ($_SESSION['pc_facility']) {
    $sql = "select * from facility where id=" . $_SESSION['pc_facility'];
  } else {
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  }
  /******************************************************************/
  $db = $GLOBALS['adodb']['db'];
  $results = $db->Execute($sql);
  $facility = array();
  if (!$results->EOF) {
    $facility = $results->fields;
  }
  $practice_logo = "../../../custom/practice_logo.gif";
  if (file_exists($practice_logo)) {
    echo "<img src='$practice_logo' align='left'>\n";
  }
?>
<h2><?php echo $facility['name'] ?></h2>
<?php echo $facility['street'] ?><br>
<?php echo $facility['city'] ?>, <?php echo $facility['state'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
<?php echo $facility['phone'] ?><br>
</p>
<a href="javascript:window.close();"><span class='title'><?php echo $titleres['fname'] . " " . $titleres['lname']; ?></span></a><br>
<span class='text'><?php xl('Generated on','e'); ?>: <?php echo oeFormatShortDate(); ?></span>
<br><br>

<?php

} 
else { // not printable

?>

<a href="patient_report.php">
 <span class='title'><?php xl('Patient Report','e'); ?></span>
 <span class='back'><?php echo $tback;?></span>
</a><br><br>
<a href="custom_report.php?printable=1&<?php print postToGet($ar); ?>" class='link_submit' target='new'>
 [<?php xl('Printable Version','e'); ?>]
</a><br>

<?php } // end not printable ?>

<?php

// include ALL form's report.php files
$inclookupres = sqlStatement("select distinct formdir from forms where pid = '$pid' AND deleted=0");
while($result = sqlFetchArray($inclookupres)) {
  // include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
  $formdir = $result['formdir'];
  if (substr($formdir,0,3) == 'LBF')
    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
  else
    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
}

// For each form field from patient_report.php...
//
foreach ($ar as $key => $val) {

    // These are the top checkboxes (demographics, allergies, etc.).
    //
    if (stristr($key,"include_")) {

        if ($val == "demographics") {
            
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
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);		
            print "<span class=bold>".xl('Secondary Insurance Data').":</span><br>";	
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
            print "<span class=bold>".xl('Tertiary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
            echo "</div>";

        } elseif ($val == "billing") {

            echo "<hr />";
            echo "<div class='text billing'>";
            print "<h1>".xl('Billing Information').":</h1>";
            if (count($ar['newpatient']) > 0) {
                $billings = array();
                echo "<table>";
                echo "<tr><td width='400' class='bold'>Code</td><td class='bold'>".xl('Fee')."</td></tr>\n";
                $total = 0.00;
                $copays = 0.00;
                foreach ($ar['newpatient'] as $be) {
                    $ta = split(":",$be);
                    $billing = getPatientBillingEncounter($pid,$ta[1]);
                    $billings[] = $billing;
                    foreach ($billing as $b) {
                        echo "<tr>\n";
                        echo "<td class=text>";
                        echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
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
                   " where i1.patient_id = '$pid' ".
                   " order by administered_date desc";
                $result = sqlStatement($sql);
                while ($row=sqlFetchArray($result)) {
                  // Figure out which name to use (ie. from cvx list or from the custom list)
                  if ($GLOBALS['use_custom_immun_list']) {
                     $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                  }
                  else {
                     if (!empty($row['code_text_short'])) {
                        $vaccine_display = htmlspecialchars( xl($row['code_text_short']), ENT_NOQUOTES);
                     }
                     else {
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
            foreach($val as $valkey => $valvalue) {
                $document_id = $valvalue;
                if (!is_numeric($document_id)) continue;
                $d = new Document($document_id);
                $fname = basename($d->get_url());
				$couch_docid = $d->get_couch_docid();
				$couch_revid = $d->get_couch_revid();
                $extension = substr($fname, strrpos($fname,"."));
                echo "<h1>" . xl('Document') . " '" . $fname ."'</h1>";
                $notes = Note::notes_factory($d->get_id());
                echo "<table>";
                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . xl('Note') . ' #' . $note->get_id() . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . xl('Date') . ': ' . oeFormatShortDate($note->get_date()) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>'.$note->get_note().'<br><br></td>';
                    echo '</tr>';
                }
                echo "</table>";
                if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                    echo "<img src='" . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . $document_id . "&as_file=false'><br><br>";
                }
                else {
                    // echo "<b>NOTE</b>: ".xl('Document')."'" . $fname ."' ".xl('cannot be displayed inline because its type is not supported by the browser.')."<br><br>";	
                    // This requires ImageMagick to be installed.
                    $url_file = $d->get_url_filepath();
                    if($couch_docid && $couch_revid){
                      $url_file = $d->get_couch_url($pid,$encounter);
                    }
                    // just grab the last two levels, which contain filename and patientid
                    $from_all = explode("/",$url_file);
                    $from_filename = array_pop($from_all);
                    $from_patientid = array_pop($from_all);
					if($couch_docid && $couch_revid){
					$from_file = $GLOBALS['OE_SITE_DIR'].'/documents/temp/'.$from_filename;
                    $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
		            }
					else{
                    $from_file = $GLOBALS["fileroot"] . "/sites/" . $_SESSION['site_id'] .'/documents/'. $from_patientid.'/'.$from_filename;
                    $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
					}
					if (! is_file($to_file)) exec("convert -density 200 \"$from_file\" -append -resize 850 \"$to_file\"");
                    if (is_file($to_file)) {
                        echo "<img src='" . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . $document_id . "&as_file=false&original_file=false'><br><br>";
                    } else {
                        echo "<b>NOTE</b>: " . xl('Document') . "'" . $fname . "' " .
                        xl('cannot be converted to JPEG. Perhaps ImageMagick is not installed?') . "<br><br>";
						if($couch_docid && $couch_revid){
						unlink($from_file);
						}
                    }
                    
                } // end if-else
            } // end Documents loop
            echo "</div>";

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
            }
            else if ($irow['type'] == 'contraceptive') {
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
                $formres = getFormNameByFormdirAndFormid($res[1],$form_id);
                $dateres = getEncounterDateByEncounter($form_encounter);

                if ($res[1] == 'newpatient') {
                    echo "<div class='text encounter'>\n";
                    echo "<h1>" . xl($formres["form_name"]) . "</h1>";
                }
                else {
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
                if (substr($res[1],0,3) == 'LBF')
                  call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                else
                  call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);

                if ($res[1] == 'newpatient') {
                    // display billing info
                    $bres = sqlStatement("SELECT b.date, b.code, b.code_text " .
                      "FROM billing AS b, code_types AS ct WHERE " .
                      "b.pid = ? AND " .
                      "b.encounter = ? AND " .
                      "b.activity = 1 AND " .
                      "b.code_type = ct.ct_key AND " .
                      "ct.ct_diag = 0 " .
                      "ORDER BY b.date",
                      array($pid, $form_encounter));
                    while ($brow=sqlFetchArray($bres)) {
                        echo "<span class='bold'>&nbsp;".xl('Procedure').": </span><span class='text'>" .
                            $brow['code'] . " " . $brow['code_text'] . "</span><br>\n";
                    }
                }

                print "</div>";
            
            } // end auth-check for encounter forms

        } // end if('issue_')... else...

    } // end if('include_')... else...

} // end $ar loop

if ($printable)
  echo "</br></br>" . xl('Signature') . ": _______________________________</br>";
?>

</div> <!-- end of report_custom DIV -->
</body>
</html>
