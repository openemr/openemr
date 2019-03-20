<?php
/**
 *
 * Patient summary screen.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("../history/history.inc.php");
require_once("$srcdir/edi.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/options.js.php");
require_once("$srcdir/group.inc");
require_once(dirname(__FILE__)."/../../../library/appointments.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Reminder\BirthdayReminder;
use OpenEMR\OeUI\OemrUI;

if (isset($_GET['set_pid'])) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

$active_reminders = false;
$all_allergy_alerts = false;
if ($GLOBALS['enable_cdr']) {
    //CDR Engine stuff
    if ($GLOBALS['enable_allergy_check'] && $GLOBALS['enable_alert_log']) {
        //Check for new allergies conflicts and throw popup if any exist(note need alert logging to support this)
        $new_allergy_alerts = allergy_conflict($pid, 'new', $_SESSION['authUser']);
        if (!empty($new_allergy_alerts)) {
            $pod_warnings = '';
            foreach ($new_allergy_alerts as $new_allergy_alert) {
                $pod_warnings .= js_escape($new_allergy_alert) . ' + "\n"';
            }
            echo '<script type="text/javascript">alert(' . xlj('WARNING - FOLLOWING ACTIVE MEDICATIONS ARE ALLERGIES') . ' + "\n" + ' . $pod_warnings . ')</script>';
        }
    }

    if ((!isset($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && $GLOBALS['enable_cdr_crp']) {
        // showing a new patient, so check for active reminders and allergy conflicts, which use in active reminder popup
        $active_reminders = active_alert_summary($pid, "reminders-due", '', 'default', $_SESSION['authUser'], true);
        if ($GLOBALS['enable_allergy_check']) {
            $all_allergy_alerts = allergy_conflict($pid, 'all', $_SESSION['authUser'], true);
        }
    }
}

function print_as_money($money)
{
    preg_match("/(\d*)\.?(\d*)/", $money, $moneymatches);
    $tmp = wordwrap(strrev($moneymatches[1]), 3, ",", 1);
    $ccheck = strrev($tmp);
    if ($ccheck[0] == ",") {
        $tmp = substr($ccheck, 1, strlen($ccheck)-1);
    }

    if ($moneymatches[2] != "") {
        return "$ " . strrev($tmp) . "." . $moneymatches[2];
    } else {
        return "$ " . strrev($tmp);
    }
}

// get an array from Photos category
function pic_array($pid, $picture_directory)
{
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
                 "on documents.id = categories_to_documents.document_id " .
                 "join categories on categories.id = categories_to_documents.category_id " .
                 "where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
        while ($results = sqlFetchArray($query)) {
            array_push($pics, $results['id']);
        }
    }

    return ($pics);
}
// Get the document ID of the first document in a specific catg.
function get_document_by_catg($pid, $doc_catg)
{

    $result = array();

    if ($pid and $doc_catg) {
        $result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
        "documents AS d, categories_to_documents AS cd, categories AS c " .
        "WHERE d.foreign_id = ? " .
        "AND cd.document_id = d.id " .
        "AND c.id = cd.category_id " .
        "AND c.name LIKE ? " .
        "ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg));
    }

    return($result['id']);
}

// Display image in 'widget style'
function image_widget($doc_id, $doc_catg)
{
    global $pid, $web_root;
    $docobj = new Document($doc_id);
    $image_file = $docobj->get_url_file();
    $image_width = $GLOBALS['generate_doc_thumb'] == 1 ? '' : 'width=100';
    $extension = substr($image_file, strrpos($image_file, "."));
    $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp','.PNG','.JPG','.JPEG','.PNG','.BMP');
    if (in_array($extension, $viewable_types)) { // extension matches list
        $to_url = "<td> <a href = '$web_root" .
        "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false&original_file=true&disable_exit=false&show_original=true'" .
        " onclick='top.restoreSession();' class='image_modal'>" .
        " <img src = '$web_root" .
        "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "&as_file=false'" .
        " $image_width alt='" . attr($doc_catg) . ":" . attr($image_file) . "'>  </a> </td> <td valign='center'>" .
        text($doc_catg) . '<br />&nbsp;' . text($image_file) .
        "</td>";
    } else {
        $to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
            "&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc_id) . "'" .
            " onclick='top.restoreSession()' class='css_button_small'>" .
            "<span>" .
            xlt("View") . "</a> &nbsp;" .
            text("$doc_catg - $image_file") .
            "</span> </td>";
    }

        echo "<table><tr>";
        echo $to_url;
        echo "</tr></table>";
}

// Determine if the Vitals form is in use for this site.
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
  "directory = 'vitals' AND state = 1");
$vitals_is_registered = $tmp['count'];

// Get patient/employer/insurance information.
//
$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$result2 = getEmployerData($pid);
$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
$insco_name = "";
if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
    $insco_name = getInsuranceProvider($result3['provider']);
}
?>
<html>

<head>

    <?php Header::setupHeader(['common']); ?>

<script type="text/javascript" language="JavaScript">

 function oldEvt(apptdate, eventid) {
   let title = <?php echo xlj('Appointments'); ?>;
   dlgopen('../../main/calendar/add_edit_event.php?date=' + encodeURIComponent(apptdate) + '&eid=' + encodeURIComponent(eventid), '_blank', 725, 500, '', title);
 }

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 400, 500);
  }

 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() { // @todo don't think this is used any longer!!
  dlgopen('../deleter.php?patient=' + <?php echo js_url($pid); ?> + '&csrf_token_form=' + <?php echo js_url(collectCsrfToken()); ?>, '_blank', 500, 450, '', '',{
      allowResize: false,
      allowDrag: false,
      dialogId: 'patdel',
      type: 'iframe'
  });
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
   top.clearPatient();
    <?php } else { ?>
   parent.left_nav.clearPatient();
    <?php } ?>
 }

 function newEvt() {
     let title = <?php echo xlj('Appointments'); ?>;
     let url = '../../main/calendar/add_edit_event.php?patientid=' + <?php echo js_url($pid); ?>;
     dlgopen(url, '_blank', 725, 500, '', title);
     return false;
 }

</script>

<script type="text/javascript">

function toggleIndicator(target,div) {
// <i id="show_hide" class="fa fa-lg small fa-eye-slash" title="Click to Hide"></i>
    $mode = $(target).find(".indicator").text();
    if ( $mode == <?php echo xlj('collapse'); ?> ) {
        $(target).find(".indicator").text(<?php echo xlj('expand'); ?>);
        $("#"+div).hide();
        $.post( "../../../library/ajax/user_settings.php",
            {
                target: div,
                mode: 0,
                csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
            }
        );
    } else {
        $(target).find(".indicator").text(<?php echo xlj('collapse'); ?>);
        $("#"+div).show();
        $.post( "../../../library/ajax/user_settings.php",
            {
                target: div,
                mode: 1,
                csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
            }
        );
    }
}

// edit prescriptions dialog.
// called from stats.php.
//
function editScripts(url) {
    var AddScript = function () {

        var __this=$(this);
        __this.find("#clearButton").css("display", "");
        __this.find("#backButton").css("display", "");
        __this.find("#addButton").css("display", "none");

        var iam = top.tab_mode ? top.frames.editScripts : window[1];
        iam.location.href = '<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=0&pid=' + <?php echo js_url($pid); ?>;
    };
    var ListScripts = function () {

        var __this=$(this);
        __this.find("#clearButton").css("display", "none");
        __this.find("#backButton").css("display", "none");
        __this.find("#addButton").css("display", "");
        var iam = top.tab_mode ? top.frames.editScripts : window[1];
        iam.location.href = '<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=' + <?php echo js_url($pid); ?>;
    };

    let title = <?php echo xlj('Prescriptions'); ?>;
    let w = 910; // for weno width

    dlgopen(url, 'editScripts', w, 300, '', '', {
        buttons: [
            {text: <?php echo xlj('Add'); ?>, close: false,  id: 'addButton', class: 'btn-primary btn-sm', click: AddScript},
            {text: <?php echo xlj('Clear'); ?>, close: false,id: 'clearButton', style: 'display:none;', class: 'btn-primary btn-sm', click: AddScript},
            {text: <?php echo xlj('Back'); ?>, close: false, id: 'backButton', style: 'display:none;', class: 'btn-primary btn-sm', click: ListScripts},
            {text: <?php echo xlj('Done'); ?>, close: true, id: 'doneButton', class: 'btn-default btn-sm'}
        ],
        onClosed: 'refreshme',
        allowResize: true,
        allowDrag: true,
        dialogId: 'editscripts',
        type: 'iframe'
    });
}

function doPublish() {
    let title = <?php echo xlj('Publish Patient to FHIR Server'); ?>;
    let url = top.webroot_url + '/phpfhir/providerPublishUI.php?patient_id=' + <?php echo js_url($pid); ?>;

    dlgopen(url, 'publish', 'modal-mlg', 750, '', '', {
        buttons: [
            {text: <?php echo xlj('Done'); ?>, close: true, style: 'default btn-sm'}
        ],
        allowResize: true,
        allowDrag: true,
        dialogId: '',
        type: 'iframe'
    });
}

$(document).ready(function(){
  var msg_updation='';
    <?php
    if ($GLOBALS['erx_enable']) {
        //$soap_status=sqlQuery("select soap_import_status from patient_data where pid=?",array($pid));
        $soap_status=sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')", array($pid));
        while ($row_soapstatus=sqlFetchArray($soap_status)) {
            //if($soap_status['soap_import_status']=='1' || $soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_patientfullmedication.php",
                dataType: "html",
                data: {
                    patient:<?php echo js_escape($row_soapstatus['pid']); ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            //}
            //elseif($soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_allergy.php",
                dataType: "html",
                data: {
                    patient:<?php echo js_escape($row_soapstatus['pid']); ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            if ($GLOBALS['erx_import_status_message']) { ?>
            if(msg_updation)
              alert(msg_updation);
            <?php
            }

            //}
        }
    }
    ?>
    // load divs
    $("#stats_div").load("stats.php",
        {
            embeddedScreen : true,
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        },
        function() {}
    );
    $("#pnotes_ps_expand").load("pnotes_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );
    $("#disclosures_ps_expand").load("disc_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      top.restoreSession();
      $("#clinical_reminders_ps_expand").load("clinical_reminders_fragment.php",
          {
              embeddedScreen : true,
              csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
          },
          function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").on('click', function(e) {
              e.preventDefault();e.stopPropagation();
              dlgopen('', '', 800, 200, '', '', {
                  buttons: [
                      {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                  ],
                  onClosed: 'refreshme',
                  allowResize: false,
                  allowDrag: true,
                  dialogId: 'demreminder',
                  type: 'iframe',
                  url: $(this).attr('href')
              });
          });
      });
    <?php } // end crw?>

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
      top.restoreSession();
      $("#patient_reminders_ps_expand").load("patient_reminders_fragment.php",
          {
              csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
          }
      );
    <?php } // end prw?>

<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
    // Initialize the Vitals form if it is registered and user is authorized.
    $("#vitals_ps_expand").load("vitals_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );
<?php } ?>

    // Initialize track_anything
    $("#track_anything_ps_expand").load("track_anything_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );


    // Initialize labdata
    $("#labdata_ps_expand").load("labdata_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );
<?php
// Initialize for each applicable LBF form.
$gfres = sqlStatement("SELECT grp_form_id FROM layout_group_properties WHERE " .
  "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_repeats > 0 AND grp_activity = 1 " .
  "ORDER BY grp_seq, grp_title");
while ($gfrow = sqlFetchArray($gfres)) {
?>
    $(<?php echo js_escape("#".$gfrow['grp_form_id']."_ps_expand"); ?>).load("lbf_fragment.php?formname=" + <?php echo js_url($gfrow['grp_form_id']); ?>,
        {
            csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
        }
    );
<?php
}
?>
    tabbify();

// modal for dialog boxes
    $(".large_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 1000, 600, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".rx_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        var AddAmendment = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/add_edit_amendments.php"
        };
        var ListAmendments = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/list_amendments.php"
        };
        var title = <?php echo xlj('Amendments'); ?>;
        dlgopen('', 'editAmendments', 800, 300, '', title, {
            buttons: [
                {text: <?php echo xlj('Add'); ?>, close: false, style: 'primary  btn-sm', click: AddAmendment},
                {text: <?php echo xlj('List'); ?>, close: false, style: 'primary  btn-sm', click: ListAmendments},
                {text: <?php echo xlj('Done'); ?>, close: true, style: 'default btn-sm'}
            ],
            onClosed: 'refreshme',
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

// modal for image viewer
    $(".image_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 400, 300, '', <?php echo xlj('Patient Images'); ?>, {
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".deleter").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 600, 360, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            //onClosed: 'imdeleted',
            allowResize: false,
            allowDrag: false,
            dialogId: 'patdel',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".iframe1").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 350, 300, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
// for patient portal
  $(".small_modal").on('click', function(e) {
      e.preventDefault();e.stopPropagation();
      dlgopen('', '', 380, 200, '', '', {
          buttons: [
              {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
          ],
          allowResize: true,
          allowDrag: true,
          dialogId: '',
          type: 'iframe',
          url: $(this).attr('href')
      });
  });

  function openReminderPopup() {
      top.restoreSession()
      dlgopen('', 'reminders', 500, 250, '', '', {
          buttons: [
              {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
          ],
          allowResize: true,
          allowDrag: true,
          dialogId: '',
          type: 'iframe',
          url: $("#reminder_popup_link").attr('href')
      });
  }


<?php if ($GLOBALS['patient_birthday_alert']) {
    // To display the birthday alert:
    //  1. The patient is not deceased
    //  2. The birthday is today (or in the past depending on global selection)
    //  3. The notification has not been turned off (or shown depending on global selection) for this year
    $birthdayAlert = new BirthdayReminder($pid, $_SESSION['authId']);
    if ($birthdayAlert->isDisplayBirthdayAlert()) {
    ?>
    // show the active reminder modal
    dlgopen('', 'bdayreminder', 300, 170, '', false, {
        allowResize: false,
        allowDrag: true,
        dialogId: '',
        type: 'iframe',
        url: $("#birthday_popup").attr('href')
    });

    <?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
    openReminderPopup();
    <?php }?>
<?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
    openReminderPopup();
<?php }?>

});

// JavaScript stuff to do when a new patient is set.
//
function setMyPatient() {
 // Avoid race conditions with loading of the left_nav or Title frame.
 if (!parent.allFramesLoaded()) {
  setTimeout("setMyPatient()", 500);
  return;
 }
<?php
if (isset($_GET['set_pid'])) {
    $date_of_death = is_patient_deceased($pid)['date_deceased']; ?>
    parent.left_nav.setPatient(<?php echo js_escape($result['fname'] . " " . $result['lname']) .
    "," . js_escape($pid) . "," . js_escape($result['pubpid']) . ",'',";
    if (empty($date_of_death)) {
        echo js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($result['DOB_YMD']));
    } else {
        echo js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age at death') . ": " . oeFormatAge($result['DOB_YMD'], $date_of_death));
    }?>);
    var EncounterDateArray = new Array;
    var CalendarCategoryArray = new Array;
    var EncounterIdArray = new Array;
    var Count = 0;
<?php
   //Encounter details are stored to javacript as array.
   $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
if (sqlNumRows($result4)>0) {
    while ($rowresult4 = sqlFetchArray($result4)) {?>
            EncounterIdArray[Count] = <?php echo js_escape($rowresult4['encounter']); ?>;
            EncounterDateArray[Count] = <?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>;
            CalendarCategoryArray[Count] = <?php echo js_escape(xl_appt_category($rowresult4['pc_catname'])); ?>;
            Count++;
        <?php
    }
}
?>
    parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
<?php
} // end setting new pid ?>
 parent.left_nav.syncRadios();
<?php if ((isset($_GET['set_pid']) ) && (isset($_GET['set_encounterid'])) && ( intval($_GET['set_encounterid']) > 0 )) {
    $encounter = intval($_GET['set_encounterid']);
    $_SESSION['encounter'] = $encounter;
    $query_result = sqlQuery("SELECT `date` FROM `form_encounter` WHERE `encounter` = ?", array($encounter)); ?>
 encurl = 'encounter/encounter_top.php?set_encounter=' + <?php echo js_url($encounter);?> + '&pid=' + <?php echo js_url($pid);?>;
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
  parent.left_nav.setEncounter(<?php echo js_escape(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date'])))); ?>, <?php echo js_escape($encounter); ?>, 'enc');
    top.restoreSession();
  parent.left_nav.loadFrame('enc2', 'enc', 'patient_file/' + encurl);
    <?php } else { ?>
  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.setEncounter(<?php echo js_escape(attr(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date']))))); ?>, <?php echo js_escape($encounter); ?>, othername);
    top.restoreSession();
  parent.frames[othername].location.href = '../' + encurl;
    <?php } ?>
<?php } // end setting new encounter id (only if new pid is also set) ?>
}

$(window).on('load', function() {
 setMyPatient();
});

</script>

<style type="css/text">

#pnotes_ps_expand {
  height:auto;
  width:100%;
}

<?php
// This is for layout font size override.
$grparr = array();
getLayoutProperties('DEM', $grparr, 'grp_size');
if (!empty($grparr['']['grp_size'])) {
    $FONTSIZE = $grparr['']['grp_size'];
?>
/* Override font sizes in the theme. */
#DEM .groupname {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .label {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data td {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
<?php } ?>

</style>
<title><?php echo xlt("Dashboard{{patient file}}"); ?></title>

<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Medical Record Dashboard'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "medical_dashboard_help.php"
    );
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>

<body class="body_top patient-demographics">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <a href='../reminder/active_reminder_popup.php' id='reminder_popup_link' style='display: none;' onclick='top.restoreSession()'></a>

        <a href='../birthday_alert/birthday_pop.php?pid=<?php echo attr_url($pid); ?>&user_id=<?php echo attr_url($_SESSION['authId']); ?>' id='birthday_popup' style='display: none;' onclick='top.restoreSession()'></a>
        <?php
        $thisauth = acl_check('patients', 'demo');
        if ($thisauth) {
            if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
                $thisauth = 0;
            }
        }

        if (!$thisauth) {
            echo "<p>(" . xlt('Demographics not authorized') . ")</p>\n";
            echo "</body>\n</html>\n";
            exit();
        }?>

            <?php
            if ($thisauth) {?>
                <div class="row">
                    <div class="col-sm-12">
                        <?php require_once("$include_root/patient_file/summary/dashboard_header.php"); ?>
                    </div>
                </div>
            <?php
            } // $thisauth
            ?>

        <div class="row" >
            <div class="col-sm-12">
                <?php
                    $list_id = "dashboard"; // to indicate nav item is active, count and give correct id
                    // Collect the patient menu then build it
                    $menuPatient = new PatientMenuRole();
                    $menuPatient->displayHorizNavBarMenu();
                     // Get the document ID of the patient ID card if access to it is wanted here.
                    $idcard_doc_id = false;
                if ($GLOBALS['patient_id_category_name']) {
                    $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
                }
                ?>
            </div>
        </div>

        <div style='margin-top:10px' class="main"> <!-- start main content div -->
            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="demographics-box" align="left" valign="top">
                        <!-- start left column div -->
                        <div style='float:left; margin-right:20px'>

                            <table cellspacing=0 cellpadding=0>
                                <?php
                                if (!$GLOBALS['hide_billing_widget']) { ?>
                                <tr>
                                    <td>
                                        <?php
                                        // Billing expand collapse widget
                                        $widgetTitle = xl("Billing");
                                        $widgetLabel = "billing";
                                        $widgetButtonLabel = xl("Edit");
                                        $widgetButtonLink = "return newEvt();";
                                        $widgetButtonClass = "";
                                        $linkMethod = "javascript";
                                        $bodyClass = "notab";
                                        $widgetAuth = false;
                                        $fixedWidth = true;
                                        if ($GLOBALS['force_billing_widget_open']) {
                                            $forceExpandAlways = true;
                                        } else {
                                            $forceExpandAlways = false;
                                        }

                                        expand_collapse_widget(
                                            $widgetTitle,
                                            $widgetLabel,
                                            $widgetButtonLabel,
                                            $widgetButtonLink,
                                            $widgetButtonClass,
                                            $linkMethod,
                                            $bodyClass,
                                            $widgetAuth,
                                            $fixedWidth,
                                            $forceExpandAlways
                                        );
                                        ?>
                                    <br>
                                <?php
                                //PATIENT BALANCE,INS BALANCE naina@capminds.com
                                $patientbalance = get_patient_balance($pid, false);
                                //Debit the patient balance from insurance balance
                                $insurancebalance = get_patient_balance($pid, true) - $patientbalance;
                                $totalbalance=$patientbalance + $insurancebalance;

                                // Show current balance and billing note, if any.
                                echo "<table border='0'><tr><td>" .
                                "<table ><tr><td><span class='bold'><font color='red'>" .
                                xlt('Patient Balance Due') .
                                " : " . text(oeFormatMoney($patientbalance)) .
                                "</font></span></td></tr>".
                                "<tr><td><span class='bold'><font color='red'>" .
                                xlt('Insurance Balance Due') .
                                " : " . text(oeFormatMoney($insurancebalance)) .
                                "</font></span></td></tr>".
                                "<tr><td><span class='bold'><font color='red'>" .
                                xlt('Total Balance Due').
                                " : " . text(oeFormatMoney($totalbalance)) .
                                "</font></span></td></td></tr>";
                                if (!empty($result['billing_note'])) {
                                    echo "<tr><td><span class='bold'><font color='red'>" .
                                    xlt('Billing Note') . ":" .
                                    text($result['billing_note']) .
                                    "</font></span></td></tr>";
                                }

                                if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
                                    echo "<tr><td><span class='bold'>" .
                                    xlt('Primary Insurance') . ': ' . text($insco_name) .
                                    "</span>&nbsp;&nbsp;&nbsp;";
                                    if ($result3['copay'] > 0) {
                                        echo "<span class='bold'>" .
                                        xlt('Copay') . ': ' .  text($result3['copay']) .
                                        "</span>&nbsp;&nbsp;&nbsp;";
                                    }
                                    echo "<span class='bold'>" .
                                    xlt('Effective Date') . ': ' .  text(oeFormatShortDate($result3['effdate'])) .
                                    "</span></td></tr>";
                                }

                                echo "</table></td></tr></td></tr></table><br>";

                        ?>
                                        </div> <!-- required for expand_collapse_widget -->
                                    </td>
                                </tr>
                                <?php } ?>

                        <?php if (acl_check('patients', 'demo')) { ?>
                              <tr>
                               <td>
                        <?php
                        // Demographics expand collapse widget
                        $widgetTitle = xl("Demographics");
                        $widgetLabel = "demographics";
                        $widgetButtonLabel = xl("Edit");
                        $widgetButtonLink = "demographics_full.php";
                        $widgetButtonClass = "";
                        $linkMethod = "html";
                        $bodyClass = "";
                        $widgetAuth = acl_check('patients', 'demo', '', 'write');
                        $fixedWidth = true;
                        expand_collapse_widget(
                            $widgetTitle,
                            $widgetLabel,
                            $widgetButtonLabel,
                            $widgetButtonLink,
                            $widgetButtonClass,
                            $linkMethod,
                            $bodyClass,
                            $widgetAuth,
                            $fixedWidth
                        );
                        ?>
                                 <div id="DEM" >
                                  <ul class="tabNav">
                                    <?php display_layout_tabs('DEM', $result, $result2); ?>
                                  </ul>
                                  <div class="tabContainer">
                                    <?php display_layout_tabs_data('DEM', $result, $result2); ?>
                                  </div>
                                 </div>
                                <!--</div>  required for expand_collapse_widget -->
                               </td>
                              </tr>

                              <tr>
                               <td>
                        <?php
                        $insurance_count = 0;
                        foreach (array('primary','secondary','tertiary') as $instype) {
                            $enddate = 'Present';
                            $query = "SELECT * FROM insurance_data WHERE " .
                            "pid = ? AND type = ? " .
                            "ORDER BY date DESC";
                            $res = sqlStatement($query, array($pid, $instype));
                            while ($row = sqlFetchArray($res)) {
                                if ($row['provider']) {
                                    $insurance_count++;
                                }
                            }
                        }

                        if ($insurance_count > 0) {
                          // Insurance expand collapse widget
                            $widgetTitle = xl("Insurance");
                            $widgetLabel = "insurance";
                            $widgetButtonLabel = xl("Edit");
                            $widgetButtonLink = "demographics_full.php";
                            $widgetButtonClass = "";
                            $linkMethod = "html";
                            $bodyClass = "";
                            $widgetAuth = acl_check('patients', 'demo', '', 'write');
                            $fixedWidth = true;
                            expand_collapse_widget(
                                $widgetTitle,
                                $widgetLabel,
                                $widgetButtonLabel,
                                $widgetButtonLink,
                                $widgetButtonClass,
                                $linkMethod,
                                $bodyClass,
                                $widgetAuth,
                                $fixedWidth
                            );

                            if ($insurance_count > 0) {
                            ?>

                                <ul class="tabNav"><?php
                                            ///////////////////////////////// INSURANCE SECTION
                                            $first = true;
                                foreach (array('primary','secondary','tertiary') as $instype) {
                                    $query = "SELECT * FROM insurance_data WHERE " .
                                    "pid = ? AND type = ? " .
                                    "ORDER BY date DESC";
                                    $res = sqlStatement($query, array($pid, $instype));

                                    $enddate = 'Present';

                                    while ($row = sqlFetchArray($res)) {
                                        if ($row['provider']) {
                                            $ins_description = ucfirst($instype);
                                            $ins_description = xl($ins_description);
                                            $ins_description .= strcmp($enddate, 'Present') != 0 ? " (".xl('Old').")" : "";
                                            ?>
                                            <li <?php echo $first ? 'class="current"' : '' ?>><a href="#">
                                                        <?php echo text($ins_description); ?></a></li>
                                                        <?php
                                                        $first = false;
                                        }

                                        $enddate = $row['date'];
                                    }
                                }

                                            // Display the eligibility tab
                                            echo "<li><a id='eligibility' href='#'>" . xlt('Eligibility') . "</a></li>";

                                            ?></ul><?php
                            } ?>

                                        <div class="tabContainer">
                                            <?php
                                            $first = true;
                                            foreach (array('primary','secondary','tertiary') as $instype) {
                                                $enddate = 'Present';

                                                $query = "SELECT * FROM insurance_data WHERE " .
                                                "pid = ? AND type = ? " .
                                                "ORDER BY date DESC";
                                                $res = sqlStatement($query, array($pid, $instype));
                                                while ($row = sqlFetchArray($res)) {
                                                    if ($row['provider']) {
                                                        ?>
                                                        <div class="tab <?php echo $first ? 'current' : '' ?>">
                                                        <table border='0' cellpadding='0' width='100%'>
                                                        <?php
                                                        $icobj = new InsuranceCompany($row['provider']);
                                                        $adobj = $icobj->get_address();
                                                        $insco_name = trim($icobj->get_name());
                                                        ?>
                                                        <tr>
                                                         <td valign='top' colspan='3'>
                                                          <span class='text'>
                                                            <?php
                                                            if (strcmp($enddate, 'Present') != 0) {
                                                                echo xlt("Old") . " ";
                                                            }
                                                            ?>
                                                            <?php $tempinstype=ucfirst($instype);
                                                            echo xlt($tempinstype.' Insurance'); ?>
                                                            <?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
                                                            <?php echo ' ' . xlt('from') . ' ' . $row['date']; ?>
                                                            <?php } ?>
                                                                    <?php echo ' ' . xlt('until') . ' ';
                                                                    echo (strcmp($enddate, 'Present') != 0) ? text($enddate) : xlt('Present'); ?>:</span>
                                                             </td>
                                                            </tr>
                                                            <tr>
                                                             <td valign='top'>
                                                              <span class='text'>
                                                                <?php
                                                                if ($insco_name) {
                                                                    echo text($insco_name) . '<br>';
                                                                    if (trim($adobj->get_line1())) {
                                                                        echo text($adobj->get_line1()) . '<br>';
                                                                        echo text($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip());
                                                                    }
                                                                } else {
                                                                    echo "<font color='red'><b>" . xlt('Unassigned') . "</b></font>";
                                                                }
                                                                ?>
                                                              <br>
                                                                <?php echo xlt('Policy Number'); ?>:
                                                                <?php echo text($row['policy_number']) ?><br>
                                                                <?php echo xlt('Plan Name'); ?>:
                                                                <?php echo text($row['plan_name']); ?><br>
                                                                <?php echo xlt('Group Number'); ?>:
                                                                <?php echo text($row['group_number']); ?></span>
                                                             </td>
                                                             <td valign='top'>
                                                                <span class='bold'><?php echo xlt('Subscriber'); ?>: </span><br>
                                                                <span class='text'><?php echo text($row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname']); ?>
                                                            <?php
                                                            if ($row['subscriber_relationship'] != "") {
                                                                echo "(" . text($row['subscriber_relationship']) . ")";
                                                            }
                                                            ?>
                                                          <br>
                                                            <?php echo xlt('S.S.'); ?>:
                                                            <?php echo text($row['subscriber_ss']); ?><br>
                                                            <?php echo xlt('D.O.B.'); ?>:
                                                            <?php
                                                            if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") {
                                                                echo text($row['subscriber_DOB']);
                                                            }
                                                            ?><br>
                                                            <?php echo xlt('Phone'); ?>:
                                                            <?php echo text($row['subscriber_phone']); ?>
                                                          </span>
                                                         </td>
                                                         <td valign='top'>
                                                          <span class='bold'><?php echo xlt('Subscriber Address'); ?>: </span><br>
                                                          <span class='text'><?php echo text($row['subscriber_street']); ?><br>
                                                            <?php echo text($row['subscriber_city']); ?>
                                                            <?php
                                                            if ($row['subscriber_state'] != "") {
                                                                echo ", ";
                                                            }

                                                            echo text($row['subscriber_state']); ?>
                                                            <?php
                                                            if ($row['subscriber_country'] != "") {
                                                                echo ", ";
                                                            }

                                                            echo text($row['subscriber_country']); ?>
                                                            <?php echo " " . text($row['subscriber_postal_code']); ?></span>

                                                        <?php if (trim($row['subscriber_employer'])) { ?>
                                                          <br><span class='bold'><?php echo xlt('Subscriber Employer'); ?>: </span><br>
                                                          <span class='text'><?php echo text($row['subscriber_employer']); ?><br>
                                                            <?php echo text($row['subscriber_employer_street']); ?><br>
                                                            <?php echo text($row['subscriber_employer_city']); ?>
                                                            <?php
                                                            if ($row['subscriber_employer_city'] != "") {
                                                                echo ", ";
                                                            }

                                                            echo text($row['subscriber_employer_state']); ?>
                                                            <?php
                                                            if ($row['subscriber_employer_country'] != "") {
                                                                echo ", ";
                                                            }

                                                            echo text($row['subscriber_employer_country']); ?>
                                                            <?php echo " " . text($row['subscriber_employer_postal_code']); ?>
                                                          </span>
                                                        <?php } ?>

                                                         </td>
                                                        </tr>
                                                        <tr>
                                                         <td>
                                                        <?php if ($row['copay'] != "") { ?>
                                                          <span class='bold'><?php echo xlt('CoPay'); ?>: </span>
                                                          <span class='text'><?php echo text($row['copay']); ?></span>
                                          <br />
                                                        <?php } ?>
                                                          <span class='bold'><?php echo xlt('Accept Assignment'); ?>:</span>
                                                          <span class='text'>
                                                        <?php
                                                        if ($row['accept_assignment'] == "TRUE") {
                                                            echo xl("YES");
                                                        }
                                                        if ($row['accept_assignment'] == "FALSE") {
                                                            echo xl("NO");
                                                        }
                                                        ?>
                                                          </span>
                                                        <?php if (!empty($row['policy_type'])) { ?>
                                          <br />
                                                          <span class='bold'><?php echo xlt('Secondary Medicare Type'); ?>: </span>
                                                          <span class='text'><?php echo text($policy_types[$row['policy_type']]); ?></span>
                                                        <?php } ?>
                                                         </td>
                                                         <td valign='top'></td>
                                                         <td valign='top'></td>
                                                       </tr>

                                                    </table>
                                                    </div>
                                                        <?php
                                                    } // end if ($row['provider'])
                                                    $enddate = $row['date'];
                                                    $first = false;
                                                } // end while
                                            } // end foreach

                                            // Display the eligibility information
                                            echo "<div class='tab'>\n" .
                                                "<div class='tab-content pre-scrollable' style='width:695px;overflow-x: hidden;'>\n";

                                            if ($GLOBALS['enable_oa']) {
                                                echo "<form method='post' action='./demographics.php'>\n";
                                                echo "<div class='col col-sm-12'>";
                                                echo "<button class='btn btn-success btn-xs btn-transmit pull-right' name='status_update' value='true'>" .
                                                    xlt("Update Status") . "</button>";
                                                echo "</div><br>\n";
                                                if ($_POST['status_update'] === 'true') {
                                                    unset($_POST['status_update']);
                                                    $showEligibility = true;
                                                    $ok = requestEligibleTransaction($pid);
                                                    if ($ok === true) {
                                                        show_eligibility_information($pid, false);
                                                    } else {
                                                        echo $ok;
                                                    }
                                                } else {
                                                    show_eligibility_information($pid, true);
                                                }
                                                echo "</form>";
                                            } else {
                                                show_eligibility_information($pid, true);
                                            }
                                            echo "</div></div>";

                                    ///////////////////////////////// END INSURANCE SECTION
                                    ?>
                                    </div>

                        <?php } // ?>

                                    </td>
                                </tr>
                        <?php } // end if demographics authorized ?>

                        <?php if (acl_check('patients', 'notes')) { ?>
                                <tr>
                                    <td width='650px'>
                        <?php
                        // Notes expand collapse widget
                        $widgetTitle = xl("Messages");
                        $widgetLabel = "pnotes";
                        $widgetButtonLabel = xl("Edit");
                        $widgetButtonLink = "pnotes_full.php?form_active=1";
                        $widgetButtonClass = "";
                        $linkMethod = "html";
                        $bodyClass = "notab";
                        $widgetAuth = acl_check('patients', 'notes', '', 'write');
                        $fixedWidth = true;
                        expand_collapse_widget(
                            $widgetTitle,
                            $widgetLabel,
                            $widgetButtonLabel,
                            $widgetButtonLink,
                            $widgetButtonClass,
                            $linkMethod,
                            $bodyClass,
                            $widgetAuth,
                            $fixedWidth
                        );
                        ?>
                                            <br/>
                                            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                                        </div>
                                    </td>
                                </tr>
                        <?php } // end if notes authorized ?>

                        <?php if (acl_check('patients', 'reminder') && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) {
                                        echo "<tr><td width='650px'>";
                                        // patient reminders collapse widget
                                        $widgetTitle = xl("Patient Reminders");
                                        $widgetLabel = "patient_reminders";
                                        $widgetButtonLabel = xl("Edit");
                                        $widgetButtonLink = "../reminder/patient_reminders.php?mode=simple&patient_id=" . attr_url($pid);
                                        $widgetButtonClass = "";
                                        $linkMethod = "html";
                                        $bodyClass = "notab";
                                        $widgetAuth = acl_check('patients', 'reminder', '', 'write');
                                        $fixedWidth = true;
                                        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>
                                            <br/>
                                            <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
                                        </div>
                                                </td>
                                        </tr>
                        <?php } //end if prw is activated  ?>

                        <?php if (acl_check('patients', 'disclosure')) { ?>
                               <tr>
                               <td width='650px'>
                        <?php
                        // disclosures expand collapse widget
                        $widgetTitle = xl("Disclosures");
                        $widgetLabel = "disclosures";
                        $widgetButtonLabel = xl("Edit");
                        $widgetButtonLink = "disclosure_full.php";
                        $widgetButtonClass = "";
                        $linkMethod = "html";
                        $bodyClass = "notab";
                        $widgetAuth = acl_check('patients', 'disclosure', '', 'write');
                        $fixedWidth = true;
                        expand_collapse_widget(
                            $widgetTitle,
                            $widgetLabel,
                            $widgetButtonLabel,
                            $widgetButtonLink,
                            $widgetButtonClass,
                            $linkMethod,
                            $bodyClass,
                            $widgetAuth,
                            $fixedWidth
                        );
                        ?>
                                            <br/>
                                            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                                        </div>
                             </td>
                            </tr>
                        <?php } // end if disclosures authorized ?>

                        <?php if ($GLOBALS['amendments'] && acl_check('patients', 'amendment')) { ?>
                          <tr>
                               <td width='650px'>
                                <?php // Amendments widget
                                $widgetTitle = xlt('Amendments');
                                $widgetLabel = "amendments";
                                $widgetButtonLabel = xlt("Edit");
                                $widgetButtonLink = $GLOBALS['webroot'] . "/interface/patient_file/summary/list_amendments.php?id=" . attr_url($pid);
                                $widgetButtonClass = "rx_modal";
                                $linkMethod = "html";
                                $bodyClass = "summary_item small";
                                $widgetAuth = acl_check('patients', 'amendment', '', 'write');
                                $fixedWidth = false;
                                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                                $sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
                                $result = sqlStatement($sql, array($pid));

                                if (sqlNumRows($result) == 0) {
                                        echo " <table><tr>\n";
                                        echo "  <td colspan='" . attr($numcols) . "' class='text'>&nbsp;&nbsp;" . xlt('None') . "</td>\n";
                                        echo " </tr></table>\n";
                                }

                                while ($row=sqlFetchArray($result)) {
                                        echo "&nbsp;&nbsp;";
                                        echo "<a class= '" . attr($widgetButtonClass) . "' href='" . $GLOBALS['webroot'] . "/interface/patient_file/summary/add_edit_amendments.php?id=" . attr_url($row['amendment_id']) . "' onclick='top.restoreSession()'>" . text($row['amendment_date']);
                                        echo "&nbsp; " . text($row['amendment_desc']);

                                        echo "</a><br>\n";
                                } ?>
                          </td>
                            </tr>
                        <?php } // end amendments authorized ?>

                        <?php if (acl_check('patients', 'lab')) { ?>
                            <tr>
                             <td width='650px'>
                        <?php // labdata expand collapse widget
                          $widgetTitle = xl("Labs");
                          $widgetLabel = "labdata";
                          $widgetButtonLabel = xl("Trend");
                          $widgetButtonLink = "../summary/labdata.php";#"../encounter/trend_form.php?formname=labdata";
                          $widgetButtonClass = "";
                          $linkMethod = "html";
                          $bodyClass = "notab";
                          // check to see if any labdata exist
                          $spruch = "SELECT procedure_report.date_collected AS date " .
                                    "FROM procedure_report " .
                                    "JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " .
                                    "WHERE procedure_order.patient_id = ? " .
                                    "ORDER BY procedure_report.date_collected DESC ";
                          $existLabdata = sqlQuery($spruch, array($pid));
                        if ($existLabdata) {
                            $widgetAuth = true;
                        } else {
                            $widgetAuth = false;
                        }

                          $fixedWidth = true;
                          expand_collapse_widget(
                              $widgetTitle,
                              $widgetLabel,
                              $widgetButtonLabel,
                              $widgetButtonLink,
                              $widgetButtonClass,
                              $linkMethod,
                              $bodyClass,
                              $widgetAuth,
                              $fixedWidth
                          );
                        ?>
                              <br/>
                              <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                              </div>
                             </td>
                            </tr>
                        <?php } // end labs authorized ?>

                        <?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
                            <tr>
                             <td width='650px'>
                        <?php // vitals expand collapse widget
                          $widgetTitle = xl("Vitals");
                          $widgetLabel = "vitals";
                          $widgetButtonLabel = xl("Trend");
                          $widgetButtonLink = "../encounter/trend_form.php?formname=vitals";
                          $widgetButtonClass = "";
                          $linkMethod = "html";
                          $bodyClass = "notab";
                          // check to see if any vitals exist
                          $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid));
                        if ($existVitals) {
                            $widgetAuth = true;
                        } else {
                            $widgetAuth = false;
                        }

                          $fixedWidth = true;
                          expand_collapse_widget(
                              $widgetTitle,
                              $widgetLabel,
                              $widgetButtonLabel,
                              $widgetButtonLink,
                              $widgetButtonClass,
                              $linkMethod,
                              $bodyClass,
                              $widgetAuth,
                              $fixedWidth
                          );
                        ?>
                              <br/>
                              <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                              </div>
                             </td>
                            </tr>
                        <?php } // end if ($vitals_is_registered && acl_check('patients', 'med')) ?>

                        <?php
                        // This generates a section similar to Vitals for each LBF form that
                        // supports charting.  The form ID is used as the "widget label".
                        //
                        $gfres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
                          "FROM layout_group_properties WHERE " .
                          "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_repeats > 0 AND grp_activity = 1 " .
                          "ORDER BY grp_seq, grp_title");
                        while ($gfrow = sqlFetchArray($gfres)) {
                            // $jobj = json_decode($gfrow['notes'], true);
                            $LBF_ACO = empty($gfrow['grp_aco_spec']) ? false : explode('|', $gfrow['grp_aco_spec']);
                            if ($LBF_ACO && !acl_check($LBF_ACO[0], $LBF_ACO[1])) {
                                continue;
                            } ?>
                            <tr>
                            <td width='650px'>
                            <?php // vitals expand collapse widget
                            $vitals_form_id = $gfrow['option_id'];
                            $widgetTitle = $gfrow['title'];
                            $widgetLabel = $vitals_form_id;
                            $widgetButtonLabel = xl("Trend");
                            $widgetButtonLink = "../encounter/trend_form.php?formname=" . attr_url($vitals_form_id);
                            $widgetButtonClass = "";
                            $linkMethod = "html";
                            $bodyClass = "notab";
                            $widgetAuth = false;
                            if (!$LBF_ACO || acl_check($LBF_ACO[0], $LBF_ACO[1], '', 'write')) {
                                // check to see if any instances exist for this patient
                                $existVitals = sqlQuery(
                                    "SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
                                    array($pid, $vitals_form_id)
                                );
                                $widgetAuth = $existVitals;
                            }

                            $fixedWidth = true;
                            expand_collapse_widget(
                                $widgetTitle,
                                $widgetLabel,
                                $widgetButtonLabel,
                                $widgetButtonLink,
                                $widgetButtonClass,
                                $linkMethod,
                                $bodyClass,
                                $widgetAuth,
                                $fixedWidth
                            ); ?>
                               <br/>
                               <div style='margin-left:10px' class='text'>
                                <image src='../../pic/ajax-loader.gif'/>
                               </div>
                               <br/>
                              </div> <!-- This is required by expand_collapse_widget(). -->
                             </td>
                            </tr>
                        <?php
                        } // end while
                        ?>

                            </table>

              </div>
                <!-- end left column div -->

                <!-- start right column div -->
                <div>
                    <table>
                    <tr>
                    <td>

                <div>
                    <?php

                    // If there is an ID Card or any Photos show the widget
                    $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
                    if ($photos or $idcard_doc_id) {
                        $widgetTitle = xl("ID Card") . '/' . xl("Photos");
                        $widgetLabel = "photos";
                        $linkMethod = "javascript";
                        $bodyClass = "notab-right";
                        $widgetAuth = false;
                        $fixedWidth = false;
                        expand_collapse_widget(
                            $widgetTitle,
                            $widgetLabel,
                            $widgetButtonLabel,
                            $widgetButtonLink,
                            $widgetButtonClass,
                            $linkMethod,
                            $bodyClass,
                            $widgetAuth,
                            $fixedWidth
                        );
                ?>
                <br />
                <?php
                if ($idcard_doc_id) {
                    image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
                }

                foreach ($photos as $photo_doc_id) {
                    image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
                }
                    }
                ?>

                <br />
                </div>
                <div>
                    <?php
                    // Advance Directives
                    if ($GLOBALS['advance_directives_warning']) {
                    // advance directives expand collapse widget
                        $widgetTitle = xl("Advance Directives");
                        $widgetLabel = "directives";
                        $widgetButtonLabel = xl("Edit");
                        $widgetButtonLink = "return advdirconfigure();";
                        $widgetButtonClass = "";
                        $linkMethod = "javascript";
                        $bodyClass = "summary_item small";
                        $widgetAuth = true;
                        $fixedWidth = false;
                        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                          $counterFlag = false; //flag to record whether any categories contain ad records
                          $query = "SELECT id FROM categories WHERE name='Advance Directive'";
                          $myrow2 = sqlQuery($query);
                        if ($myrow2) {
                            $parentId = $myrow2['id'];
                            $query = "SELECT id, name FROM categories WHERE parent=?";
                            $resNew1 = sqlStatement($query, array($parentId));
                            while ($myrows3 = sqlFetchArray($resNew1)) {
                                $categoryId = $myrows3['id'];
                                $nameDoc = $myrows3['name'];
                                $query = "SELECT documents.date, documents.id " .
                                   "FROM documents " .
                                   "INNER JOIN categories_to_documents " .
                                   "ON categories_to_documents.document_id=documents.id " .
                                   "WHERE categories_to_documents.category_id=? " .
                                   "AND documents.foreign_id=? " .
                                   "ORDER BY documents.date DESC";
                                $resNew2 = sqlStatement($query, array($categoryId, $pid));
                                $limitCounter = 0; // limit to one entry per category
                                while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                                    $dateTimeDoc = $myrows4['date'];
                                // remove time from datetime stamp
                                    $tempParse = explode(" ", $dateTimeDoc);
                                    $dateDoc = $tempParse[0];
                                    $idDoc = $myrows4['id'];
                                    echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
                                    attr_url($pid) . "&document_id=" .
                                    attr_url($idDoc) . "&as_file=true' onclick='top.restoreSession()'>" .
                                    text(xl_document_category($nameDoc)) . "</a> " .
                                    text($dateDoc);
                                    echo "<br>";
                                    $limitCounter = $limitCounter + 1;
                                    $counterFlag = true;
                                }
                            }
                        }

                        if (!$counterFlag) {
                            echo "&nbsp;&nbsp;" . xlt('None');
                        } ?>
                      </div>
                    <?php
                    }  // close advanced dir block

                    // Show Clinical Reminders for any user that has rules that are permitted.
                    $clin_rem_check = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
                    if (!empty($clin_rem_check) && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw'] &&
                        acl_check('patients', 'alert')) {
                        // clinical summary expand collapse widget
                        $widgetTitle = xl("Clinical Reminders");
                        $widgetLabel = "clinical_reminders";
                        $widgetButtonLabel = xl("Edit");
                        $widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=" . attr_url($pid);
                        ;
                        $widgetButtonClass = "";
                        $linkMethod = "html";
                        $bodyClass = "summary_item small";
                        $widgetAuth = acl_check('patients', 'alert', '', 'write');
                        $fixedWidth = false;
                        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                        echo "<br/>";
                        echo "<div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>";
                        echo "</div>";
                    } // end if crw

                      // Show current and upcoming appointments.
                      //
                      // Recurring appointment support and Appointment Display Sets
                      // added to Appointments by Ian Jardine ( epsdky ).
                      //
                    if (isset($pid) && !$GLOBALS['disable_calendar'] && acl_check('patients', 'appt')) {
                      //
                        $current_date2 = date('Y-m-d');
                        $events = array();
                        $apptNum = (int)$GLOBALS['number_of_appts_to_show'];
                        if ($apptNum != 0) {
                            $apptNum2 = abs($apptNum);
                        } else {
                            $apptNum2 = 10;
                        }

                        //
                        $mode1 = !$GLOBALS['appt_display_sets_option'];
                        $colorSet1 = $GLOBALS['appt_display_sets_color_1'];
                        $colorSet2 = $GLOBALS['appt_display_sets_color_2'];
                        $colorSet3 = $GLOBALS['appt_display_sets_color_3'];
                        $colorSet4 = $GLOBALS['appt_display_sets_color_4'];
                        //
                        if ($mode1) {
                            $extraAppts = 1;
                        } else {
                            $extraAppts = 6;
                        }

                        $events = fetchNextXAppts($current_date2, $pid, $apptNum2 + $extraAppts, true);
                        //////
                        if ($events) {
                            $selectNum = 0;
                            $apptNumber = count($events);
                          //
                            if ($apptNumber <= $apptNum2) {
                                $extraApptDate = '';
                                //
                            } elseif ($mode1 && $apptNumber == $apptNum2 + 1) {
                                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                                array_pop($events);
                                --$apptNumber;
                                $selectNum = 1;
                                //
                            } elseif ($apptNumber == $apptNum2 + 6) {
                                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                                array_pop($events);
                                --$apptNumber;
                                $selectNum = 2;
                                //
                            } else { // mode 2 - $apptNum2 < $apptNumber < $apptNum2 + 6
                                $extraApptDate = '';
                                $selectNum = 2;
                                //
                            }

                          //
                            $limitApptIndx = $apptNum2 - 1;
                            $limitApptDate = $events[$limitApptIndx]['pc_eventDate'];
                          //
                            switch ($selectNum) {
                                //
                                case 2:
                                    $lastApptIndx = $apptNumber - 1;
                                    $thisNumber = $lastApptIndx - $limitApptIndx;
                                    for ($i = 1; $i <= $thisNumber; ++$i) {
                                        if ($events[$limitApptIndx + $i]['pc_eventDate'] != $limitApptDate) {
                                            $extraApptDate = $events[$limitApptIndx + $i]['pc_eventDate'];
                                            $events = array_slice($events, 0, $limitApptIndx + $i);
                                            break;
                                        }
                                    }

                                    //
                                case 1:
                                    $firstApptIndx = 0;
                                    for ($i = 1; $i <= $limitApptIndx; ++$i) {
                                        if ($events[$limitApptIndx - $i]['pc_eventDate'] != $limitApptDate) {
                                            $firstApptIndx = $apptNum2 - $i;
                                            break;
                                        }
                                    }

                                    //
                            }

                          //
                            if ($extraApptDate) {
                                if ($extraApptDate != $limitApptDate) {
                                    $apptStyle2 = " style='background-color:" . attr($colorSet3) . ";'";
                                } else {
                                    $apptStyle2 = " style='background-color:" . attr($colorSet4) . ";'";
                                }
                            }
                        }

                        //////

                        // appointments expand collapse widget
                        $widgetTitle = xl("Appointments");
                        $widgetLabel = "appointments";
                        $widgetButtonLabel = xl("Add");
                        $widgetButtonLink = "return newEvt();";
                        $widgetButtonClass = "";
                        $linkMethod = "javascript";
                        $bodyClass = "summary_item small";
                        $widgetAuth = $resNotNull // $resNotNull reflects state of query in fetchAppointments
                        && (acl_check('patients', 'appt', '', 'write') || acl_check('patients', 'appt', '', 'addonly'));
                        $fixedWidth = false;
                        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                        $count = 0;
                        //
                        $toggleSet = true;
                        $priorDate = "";
                        $therapyGroupCategories = array();
                        $query = sqlStatement("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_cattype = 3 AND pc_active = 1");
                        while ($result = sqlFetchArray($query)) {
                            $therapyGroupCategories[] = $result['pc_catid'];
                        }

                        //
                        foreach ($events as $row) { //////
                            $count++;
                            $dayname = date("l", strtotime($row['pc_eventDate'])); //////
                            $dispampm = "am";
                            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
                            $dispmin  = substr($row['pc_startTime'], 3, 2);
                            if ($disphour >= 12) {
                                $dispampm = "pm";
                                if ($disphour > 12) {
                                    $disphour -= 12;
                                }
                            }

                            $etitle = xl('(Click to edit)');
                            if ($row['pc_hometext'] != "") {
                                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
                            }

                            //////
                            if ($extraApptDate && $count > $firstApptIndx) {
                                $apptStyle = $apptStyle2;
                            } else {
                                if ($row['pc_eventDate'] != $priorDate) {
                                    $priorDate = $row['pc_eventDate'];
                                    $toggleSet = !$toggleSet;
                                }

                                if ($toggleSet) {
                                    $apptStyle = " style='background-color:" . attr($colorSet2) . ";'";
                                } else {
                                    $apptStyle = " style='background-color:" . attr($colorSet1) . ";'";
                                }
                            }

                            //////
                            echo "<div " . $apptStyle . ">";
                            if (!in_array($row['pc_catid'], $therapyGroupCategories)) {
                                echo "<a href='javascript:oldEvt(" . attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']) . ")' title='" . attr($etitle) . "'>";
                            } else {
                                echo "<span title='" . attr($etitle) . "'>";
                            }

                            echo "<b>" . text(oeFormatShortDate($row['pc_eventDate'])) . ", ";
                            echo text(sprintf("%02d", $disphour) .":$dispmin " . xl($dispampm) . " (" . xl($dayname))  . ")</b> ";
                            if ($row['pc_recurrtype']) {
                                echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . xla("Repeating event") . "' alt='" . xla("Repeating event") . "'>";
                            }

                            echo "<span title='" . generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . "'>";
                            echo "<br>" . xlt('Status') . "( " . text($row['pc_apptstatus']) . " ) </span>";
                            echo text(xl_appt_category($row['pc_catname'])) . "\n";
                            if (in_array($row['pc_catid'], $therapyGroupCategories)) {
                                echo "<br><span>" . xlt('Group name') .": " . text(getGroup($row['pc_gid'])['group_name']) . "</span>\n";
                            }

                            if ($row['pc_hometext']) {
                                echo " <span style='color:green'> Com</span>";
                            }

                            echo "<br>" . text($row['ufname'] . " " . $row['ulname']);
                            echo !in_array($row['pc_catid'], $therapyGroupCategories) ? '</a>' : '<span>';
                            echo "</div>\n";
                            //////
                        }

                        if ($resNotNull) { //////
                            if ($count < 1) {
                                echo "&nbsp;&nbsp;" . xlt('No Appointments');
                            } else { //////
                                if ($extraApptDate) {
                                    echo "<div style='color:#0000cc;'><b>" . text($extraApptDate) . " ( + ) </b></div>";
                                }
                            }
                            // Show Recall if one exists
                            $query = sqlStatement("SELECT * FROM medex_recalls WHERE r_pid = ?", array($pid));

                            while ($result2 = sqlFetchArray($query)) {
                                //tabYourIt('recall', 'main/messages/messages.php?go=' + choice);
                                //parent.left_nav.loadFrame('1', tabNAME, url);
                                echo "&nbsp;&nbsp<b>Recall: <a onclick=\"top.left_nav.loadFrame('1', 'rcb', '../interface/main/messages/messages.php?go=addRecall');\">" . text(oeFormatShortDate($result2['r_eventDate'])). " (". text($result2['r_reason']).") </a></b>";
                                $count2++;
                            }
                            //if there is no appt and no recall
                            if (($count < 1) && ($count2 < 1)) {
                                echo "<br /><br />&nbsp;&nbsp;<a onclick=\"top.left_nav.loadFrame('1', 'rcb', '../interface/main/messages/messages.php?go=addRecall');\">" . xlt('No Recall') . "</a>";
                            }
                            $count =0;
                            echo "</div>";
                        }
                    } // End of Appointments Widget.


                    /* Widget that shows recurrences for appointments. */
                    if (isset($pid) && !$GLOBALS['disable_calendar'] && $GLOBALS['appt_recurrences_widget'] &&
                        acl_check('patients', 'appt')) {
                         $widgetTitle = xl("Recurrent Appointments");
                         $widgetLabel = "recurrent_appointments";
                         $widgetButtonLabel = xl("Add");
                         $widgetButtonLink = "return newEvt();";
                         $widgetButtonClass = "";
                         $linkMethod = "javascript";
                         $bodyClass = "summary_item small";
                         $widgetAuth = false;
                         $fixedWidth = false;
                         expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                         $count = 0;
                         $toggleSet = true;
                         $priorDate = "";

                         //Fetch patient's recurrences. Function returns array with recurrence appointments' category, recurrence pattern (interpreted), and end date.
                         $recurrences = fetchRecurrences($pid);
                        if (empty($recurrences)) { //if there are no recurrent appointments:
                            echo "<div>";
                            echo "<span>" . "&nbsp;&nbsp;" . xlt('None') . "</span>";
                            echo "</div></div>";
                        } else {
                            foreach ($recurrences as $row) {
                                //checks if there are recurrences and if they are current (git didn't end yet)
                                if (!recurrence_is_current($row['pc_endDate'])) {
                                    continue;
                                }

                                echo "<div>";
                                echo "<span>" . xlt('Appointment Category') . ": <b>" . xlt($row['pc_catname']) . "</b></span>";
                                echo "<br>";
                                echo "<span>" . xlt('Recurrence') . ': ' . text($row['pc_recurrspec']) . "</span>";
                                echo "<br>";
                                $red_text = ""; //if ends in a week, make font red
                                if (ends_in_a_week($row['pc_endDate'])) {
                                    $red_text = " style=\"color:red;\" ";
                                }

                                echo "<span" . $red_text . ">" . xlt('End Date') . ': ' . text(oeFormatShortDate($row['pc_endDate'])) . "</span>";
                                echo "</div>";
                            }

                            echo "</div>";
                        }
                    }

                     /* End of recurrence widget */

                    // Show PAST appointments.
                    // added by Terry Hill to allow reverse sorting of the appointments
                    $direction = "ASC";
                    if ($GLOBALS['num_past_appointments_to_show'] < 0) {
                        $direction = "DESC";
                        ($showpast = -1 * $GLOBALS['num_past_appointments_to_show']);
                    } else {
                        $showpast = $GLOBALS['num_past_appointments_to_show'];
                    }

                    if (isset($pid) && !$GLOBALS['disable_calendar'] && $showpast > 0 &&
                      acl_check('patients', 'appt')) {
                        $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
                        "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
                        "c.pc_catname, e.pc_apptstatus " .
                        "FROM openemr_postcalendar_events AS e, users AS u, " .
                        "openemr_postcalendar_categories AS c WHERE " .
                        "e.pc_pid = ? AND e.pc_eventDate < CURRENT_DATE AND " .
                        "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
                        "ORDER BY e.pc_eventDate " . escape_sort_order($direction) . " , e.pc_startTime DESC " .
                        "LIMIT " . escape_limit($showpast);

                        $pres = sqlStatement($query, array($pid));

                      // appointments expand collapse widget
                        $widgetTitle = xl("Past Appointments");
                        $widgetLabel = "past_appointments";
                        $widgetButtonLabel = '';
                        $widgetButtonLink = '';
                        $widgetButtonClass = '';
                        $linkMethod = "javascript";
                        $bodyClass = "summary_item small";
                        $widgetAuth = false; //no button
                        $fixedWidth = false;
                        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
                        $count = 0;
                        while ($row = sqlFetchArray($pres)) {
                            $count++;
                            $dayname = date("l", strtotime($row['pc_eventDate']));
                            $dispampm = "am";
                            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
                            $dispmin  = substr($row['pc_startTime'], 3, 2);
                            if ($disphour >= 12) {
                                $dispampm = "pm";
                                if ($disphour > 12) {
                                    $disphour -= 12;
                                }
                            }

                            if ($row['pc_hometext'] != "") {
                                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
                            }

                            echo "<a href='javascript:oldEvt(" . attr_js(preg_replace("/-/", "", $row['pc_eventDate'])) . ', ' . attr_js($row['pc_eid']) . ")' title='" . attr($etitle) . "'>";
                            echo "<b>" . text(xl($dayname) . ", " . oeFormatShortDate($row['pc_eventDate'])) . "</b> " . xlt("Status") .  "(";
                            echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
                            echo text("$disphour:$dispmin ") . xlt($dispampm) . " ";
                            echo text($row['fname'] . " " . $row['lname']) . "</a><br>\n";
                        }

                        if (isset($pres) && $res != null) {
                            if ($count < 1) {
                                echo "&nbsp;&nbsp;" . xlt('None');
                            }

                            echo "</div>";
                        }
                    }

                    // END of past appointments
                ?>
                        </div>

                        <div id='stats_div'>
                            <br/>
                            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                        </div>
                    </td>
                    </tr>

                            <?php // TRACK ANYTHING -----

                        // Determine if track_anything form is in use for this site.
                            $tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
                                        "directory = 'track_anything' AND state = 1");
                            $track_is_registered = $tmp['count'];
                            if ($track_is_registered) {
                                echo "<tr> <td>";
                                // track_anything expand collapse widget
                                $widgetTitle = xl("Tracks");
                                $widgetLabel = "track_anything";
                                $widgetButtonLabel = xl("Tracks");
                                $widgetButtonLink = "../../forms/track_anything/create.php";
                                $widgetButtonClass = "";
                                $widgetAuth = "";  // don't show the button
                                $linkMethod = "html";
                                $bodyClass = "notab";
                                // check to see if any tracks exist
                                $spruch = "SELECT id " .
                                "FROM forms " .
                                "WHERE pid = ? " .
                                "AND formdir = ? ";
                                $existTracks = sqlQuery($spruch, array($pid, "track_anything"));

                                $fixedWidth = false;
                                expand_collapse_widget(
                                    $widgetTitle,
                                    $widgetLabel,
                                    $widgetButtonLabel,
                                    $widgetButtonLink,
                                    $widgetButtonClass,
                                    $linkMethod,
                                    $bodyClass,
                                    $widgetAuth,
                                    $fixedWidth
                                );
                        ?>
                      <br/>
                      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                      <!--</div>-->
                     </td>
                    </tr><?php
                            }  // end track_anything ?>
                    </table>

                </div> <!-- end right column div -->

              </td>

             </tr>
            </table>

        </div> <!-- end main content div -->
    </div><!-- end container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
<script language='JavaScript'>
// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];
checkSkipConditions();

var isPost = <?php echo js_escape($showEligibility); ?>;
var listId = '#' + <?php echo js_escape($list_id); ?>;
$(document).ready(function(){
    $(listId).addClass("active");
    if(isPost === true) {
        $("#eligibility").click();
        $("#eligibility").get(0).scrollIntoView();
    }
});

</script>

</body>
</html>
