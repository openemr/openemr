<?php

/**
 * Audit Log Tamper Report.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Anil N <aniln@ensoftek.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

?>
<html>
<head>

<title><?php echo xlt("Audit Log Tamper Report"); ?></title>

<?php Header::setupHeader('datetime-picker'); ?>

<style>
#logview {
    width: 100%;
}
#logview table {
    width:100%;
    border-collapse: collapse;
}
#logview th {
    background-color: #cccccc;
    cursor: pointer; cursor: hand;
    padding: 5px 5px;
    align: left;
    text-align: left;
}

#logview td {
    background-color: var(--white);
    border-bottom: 1px solid #808080;
    cursor: default;
    padding: 5px 5px;
    vertical-align: top;
}
.highlight {
    background-color: #336699;
    color: #336699;
}
.tamperColor{
    color:red;
}
</style>
<script>
//function to disable the event type field if the event name is disclosure
function eventTypeChange(eventname)
{
         if (eventname == "disclosure") {
            document.theform.type_event.disabled = true;
          }
         else {
            document.theform.type_event.disabled = false;
         }
}

// VicarePlus :: This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
 }

// VicarePlus :: This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.theform;
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;
 }

</script>
</head>
<body class="body_top">
<font class="title"><?php echo xlt('Audit Log Tamper Report'); ?></font>
<br />
<?php
$err_message = 0;

$start_date = (!empty($_GET["start_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["start_date"]) : date("Y-m-d") . " 00:00:00";
$end_date = (!empty($_GET["end_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["end_date"]) : date("Y-m-d") . " 23:59:59";
/*
 * Start date should not be greater than end date - Date Validation
 */
if ($start_date > $end_date) {
    echo "<table><tr class='alert'><td colspan=7>";
    echo xlt('Start Date should not be greater than End Date');
    echo "</td></tr></table>";
    $err_message = 1;
}

if (!empty($_GET["form_patient"])) {
    $form_patient = $_GET['form_patient'];
}

?>
<?php
$form_user = $_GET['form_user'] ?? null;
$form_pid = $_GET['form_pid'] ?? null;
if (empty($form_patient)) {
    $form_pid = null;
}

?>
<br />
<FORM METHOD="GET" name="theform" id="theform" onSubmit='top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<?php

$sortby = $_GET['sortby'] ?? null;
?>
<input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>">
<input type=hidden name=csum value="">
<table>
<tr><td>
<span class="text"><?php echo xlt('Start Date'); ?>: </span>
</td><td>
<input type="text" size="18" class="datetimepicker" name="start_date" id="start_date" value="<?php echo attr(oeFormatDateTime($start_date, 0, true)); ?>" title="<?php echo xla('Start date'); ?>" />
</td>
<td>
<span class="text"><?php echo xlt('End Date'); ?>: </span>
</td><td>
<input type="text" size="18" class="datetimepicker" name="end_date" id="end_date" value="<?php echo attr(oeFormatDateTime($end_date, 0, true)); ?>" title="<?php echo xla('End date'); ?>" />
</td>

<td>
&nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
</td>
<td>
<input type='text' size='20' name='form_patient' style='width:100%;cursor:pointer;cursor:hand' value='<?php echo (!empty($form_patient)) ? attr($form_patient) : '' ?>' placeholder= '<?php echo xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
<input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
</td>
</tr>

<tr><td>
<span class='text'><?php echo xlt('Include Checksum'); ?>: </span>
</td><td>
<?php

$check_sum = isset($_GET['check_sum']);
?>
<input type="checkbox" name="check_sum" <?php echo ($check_sum) ? "checked" : ""; ?>>
</td>
<td>
<input type=hidden name="event" value="<?php echo attr($event ?? '') ; ?>">
<a href="javascript:document.theform.submit();" class='link_submit'>[<?php echo xlt('Refresh'); ?>]</a>
</td>
</tr>
</table>
</FORM>


<?php if ($start_date && $end_date && $err_message != 1) { ?>
<div id="logview">
<span class="text" id="display_tamper" style="display:none;"><?php echo xlt('Following rows in the audit log have been tampered'); ?></span>
<table>
 <tr>
  <th id="sortby_date" class="text" title="<?php echo xla('Sort by date/time'); ?>"><?php echo xlt('Log Types'); ?></th>
  <th id="sortby_date" class="text" title="<?php echo xla('Sort by date/time'); ?>"><?php echo xlt('ID'); ?></th>
  <th id="sortby_date" class="text" title="<?php echo xla('Sort by date/time'); ?>"><?php echo xlt('Date'); ?></th>
  <th id="sortby_user" class="text" title="<?php echo xla('Sort by User'); ?>"><?php echo xlt('User'); ?></th>
  <th id="sortby_pid" class="text" title="<?php echo xla('Sort by PatientID'); ?>"><?php echo xlt('PatientID'); ?></th>
  <th id="sortby_comments" class="text" title="<?php echo xla('Sort by Comments'); ?>"><?php echo xlt('Comments'); ?></th>
    <?php  if ($check_sum) {?>
  <th id="sortby_newchecksum" class="text" title="<?php xla('Sort by New Checksum'); ?>"><?php echo xlt('Tampered Checksum'); ?></th>
  <th id="sortby_oldchecksum" class="text" title="<?php xla('Sort by Old Checksum'); ?>"><?php echo xlt('Original Checksum'); ?></th>
    <?php } ?>
 </tr>
    <?php

    $eventname = $_GET['eventname'] ?? null;
    $type_event = $_GET['type_event'] ?? null;
    ?>
<input type="hidden" name="event" value="<?php echo attr($eventname) . "-" . attr($type_event) ?>">
    <?php
    $type_event = "";
    $tevent = "";
    $gev = "";
    if ($eventname != "" && $type_event != "") {
        $getevent = $eventname . "-" . $type_event;
    }

    if (($eventname == "") && ($type_event != "")) {
        $tevent = $type_event;
    } elseif ($type_event == "" && $eventname != "") {
        $gev = $eventname;
    } elseif ($eventname == "") {
        $gev = "";
    } else {
        $gev = $getevent;
    }

    $dispArr = array();
    $icnt = 1;
    if ($ret = EventAuditLogger::instance()->getEvents(array('sdate' => $start_date,'edate' => $end_date, 'user' => $form_user, 'patient' => $form_pid, 'sortby' => ($_GET['sortby'] ?? null), 'levent' => $gev, 'tevent' => $tevent))) {
        // Set up crypto object (object will increase performance since caches used keys)
        $cryptoGen = new CryptoGen();

        while ($iter = sqlFetchArray($ret)) {
            if (empty($iter["id"])) {
                // Log item is missing; it has been deleted.
                echo "<tr><td colspan='6' class='text tamperColor''>" . xlt("The log entry with following id has been deleted") . ": " . $iter['log_id_hash'] . "</td></tr>";
                continue;
            }

            //translate comments
            $patterns = array ('/^success/','/^failure/','/ encounter/');
            $replace = array ( xl('success'), xl('failure'), xl('encounter', '', ' '));

            $checkSumOld = $iter['checksum'];
            if (empty($checkSumOld)) {
                // no checksum, so skip
                continue;
            } elseif (strlen($checkSumOld) < 50) {
                // for backward compatibility (for log checksums created in the sha1 days)
                $checkSumNew = sha1($iter['date'] . $iter['event'] . $iter['user'] . $iter['groupname'] . $iter['comments'] . $iter['patient_id'] . $iter['success'] . $iter['checksum'] . $iter['crt_user']);
            } else {
                $checkSumNew = hash('sha3-512', $iter['date'] . $iter['event'] . $iter['category'] . $iter['user'] . $iter['groupname'] . $iter['comments'] . $iter['user_notes'] . $iter['patient_id'] . $iter['success'] . $iter['crt_user'] . $iter['log_from'] . $iter['menu_item_id'] . $iter['ccda_doc_id']);
            }

            $checkSumOldApi = $iter['checksum_api'];
            if (!empty($checkSumOldApi)) {
                $checkSumNewApi = hash('sha3-512', $iter['log_id_api'] . $iter['user_id'] . $iter['patient_id_api'] . $iter['ip_address'] . $iter['method'] . $iter['request'] . $iter['request_url'] . $iter['request_body'] . $iter['response'] . $iter['created_time']);
            }

            $dispCheck = false;
            $mainFail = false;
            $apiFail = false;
            if ($checkSumOld != $checkSumNew) {
                $dispCheck = true;
                $mainFail = true;
            }
            if (!empty($checkSumOldApi) && ($checkSumOldApi != $checkSumNewApi)) {
                $dispCheck = true;
                $apiFail = true;
            }
            if (!$dispCheck) {
                continue;
            }

            $logType = '';
            if (!empty($mainFail) && !empty($apiFail)) {
                $logType = xl('Main and API');
            } elseif (!empty($mainFail)) {
                $logType = xl('Main');
            } else { // !empty($apiFail)
                $logType = xl('API');
            }

            if (!empty($iter['encrypt'])) {
                $commentEncrStatus = $iter['encrypt'];
            } else {
                $commentEncrStatus = "No";
            }
            if (!empty($iter['version'])) {
                $encryptVersion = $iter['version'];
            } else {
                $encryptVersion = 0;
            }

            if ($commentEncrStatus == "Yes") {
                if ($encryptVersion >= 3) {
                    // Use new openssl method
                    if (extension_loaded('openssl')) {
                        $trans_comments = $cryptoGen->decryptStandard($iter["comments"]);
                        if ($trans_comments !== false) {
                            $trans_comments = preg_replace($patterns, $replace, trim($trans_comments));
                        } else {
                            $trans_comments = xl("Unable to decrypt these comments since decryption failed.");
                        }
                    } else {
                        $trans_comments = xl("Unable to decrypt these comments since the PHP openssl module is not installed.");
                    }
                } elseif ($encryptVersion == 2) {
                    // Use new openssl method
                    if (extension_loaded('openssl')) {
                        $trans_comments = $cryptoGen->aes256DecryptTwo($iter["comments"]);
                        if ($trans_comments !== false) {
                            $trans_comments = preg_replace($patterns, $replace, trim($trans_comments));
                        } else {
                            $trans_comments = xl("Unable to decrypt these comments since decryption failed.");
                        }
                    } else {
                        $trans_comments = xl("Unable to decrypt these comments since the PHP openssl module is not installed.");
                    }
                } elseif ($encryptVersion == 1) {
                    // Use new openssl method
                    if (extension_loaded('openssl')) {
                        $trans_comments = preg_replace($patterns, $replace, trim($cryptoGen->aes256DecryptOne($iter["comments"])));
                    } else {
                        $trans_comments = xl("Unable to decrypt these comments since the PHP openssl module is not installed.");
                    }
                } else { //$encryptVersion == 0
                    // Use old mcrypt method
                    if (extension_loaded('mcrypt')) {
                        $trans_comments = preg_replace($patterns, $replace, trim($cryptoGen->aes256Decrypt_mycrypt($iter["comments"])));
                    } else {
                        $trans_comments = xl("Unable to decrypt these comments since the PHP mycrypt module is not installed.");
                    }
                }
            } else {
                // base64 decode if applicable (note the $encryptVersion is a misnomer here, we have added in base64 encoding
                //  of comments in OpenEMR 6.0.0 and greater when the comments are not encrypted since they hold binary (uuid) elements)
                if ($encryptVersion >= 4) {
                    $iter["comments"] = base64_decode($iter["comments"]);
                }
                $trans_comments = preg_replace($patterns, $replace, trim($iter["comments"]));
            }

            //Alter Checksum value records only display here
            if ($dispCheck) {
                $dispArr[] = $icnt++;
                ?>
     <TR class="oneresult">
          <TD class="text tamperColor"><?php echo text($logType); ?></TD>
          <TD class="text tamperColor"><?php echo text($iter["id"]); ?></TD>
          <TD class="text tamperColor"><?php echo text(oeFormatDateTime($iter["date"], "global", true)); ?></TD>
          <TD class="text tamperColor"><?php echo text($iter["user"]); ?></TD>
          <TD class="text tamperColor"><?php echo text($iter["patient_id"]);?></TD>
                <?php // Using mb_convert_encoding to change binary stuff (uuid) to just be '?' characters ?>
          <TD class="text tamperColor"><?php echo text(mb_convert_encoding($trans_comments, 'UTF-8', 'UTF-8'));?></TD>
                <?php
                if ($check_sum) {
                    if (!empty($mainFail) && !empty($apiFail)) {
                        echo '<TD class="text tamperColor">' . text($checkSumNew) . '<br>' . text($checkSumNewApi) . '</TD>';
                        echo '<TD class="text tamperColor">' . text($checkSumOld) . '<br>' . text($checkSumOldApi) . '</TD>';
                    } elseif (!empty($mainFail)) {
                        echo '<TD class="text tamperColor">' . text($checkSumNew) . '</TD>';
                        echo '<TD class="text tamperColor">' . text($checkSumOld) . '</TD>';
                    } else { // !empty($apiFail)
                        echo '<TD class="text tamperColor">' . text($checkSumNewApi) . '</TD>';
                        echo '<TD class="text tamperColor">' . text($checkSumOldApi) . '</TD>';
                    }
                }
                ?>
     </TR>
                <?php
            }
        }
    }

    if (count($dispArr) == 0) {?>
     <TR class="oneresult">
                <?php
                $colspan = 4;
                if ($check_sum) {
                    $colspan = 6;
                }
                ?>
        <TD class="text" colspan="<?php echo attr($colspan);?>" align="center"><?php echo xlt('No audit log tampering detected in the selected date range.'); ?></TD>
     </TR>
        <?php
    } else {?>
    <script>$('#display_tamper').css('display', 'block');</script>
        <?php
    }

    ?>
</table>
</div>
<?php } ?>
</body>
<script>

// jQuery stuff to make the page a little easier to use
$(function () {
    // funny thing here... good learning experience
    // the TR has TD children which have their own background and text color
    // toggling the TR color doesn't change the TD color
    // so we need to change all the TR's children (the TD's) just as we did the TR
    // thus we have two calls to toggleClass:
    // 1 - for the parent (the TR)
    // 2 - for each of the children (the TDs)
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });

    // click-able column headers to sort the list
    $("#sortby_date").click(function() { $("#sortby").val("date"); $("#theform").submit(); });
    $("#sortby_event").click(function() { $("#sortby").val("event"); $("#theform").submit(); });
    $("#sortby_user").click(function() { $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_cuser").click(function() { $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_group").click(function() { $("#sortby").val("groupname"); $("#theform").submit(); });
    $("#sortby_pid").click(function() { $("#sortby").val("patient_id"); $("#theform").submit(); });
    $("#sortby_success").click(function() { $("#sortby").val("success"); $("#theform").submit(); });
    $("#sortby_comments").click(function() { $("#sortby").val("comments"); $("#theform").submit(); });
    $("#sortby_oldchecksum").click(function() { $("#sortby").val("checksum"); $("#theform").submit(); });
    $("#sortby_newchecksum").click(function() { $("#sortby").val("checksum"); $("#theform").submit(); });

    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = true; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</html>
