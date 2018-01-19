<?php
/**
 * summary_pat_portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//continue session
session_start();

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=".$_SESSION['site_id'];
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite'])) {
    $pid = $_SESSION['pid'];
} else {
    session_destroy();
    header('Location: '.$landingpage.'&w');
    exit;
}

//

$ignoreAuth = true;
global $ignoreAuth;

require_once("../interface/globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("../interface/patient_file/history/history.inc.php");
require_once("$srcdir/edi.inc");
require_once("$srcdir/lists.inc");
?>
<html>
<head>

<title><?php echo xlt('Patient Information'); ?></title>

<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<script type="text/javascript" src="<?php echo $web_root; ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/common.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>


<link rel="stylesheet" href="css/base.css" type="text/css"/>
<link rel="stylesheet" href="css/tables.css" type="text/css"/>

<script type="text/javascript" language="JavaScript">

 function refreshme() {
  location.reload();
 }

 function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo xla('collapse'); ?>" ) {
        $(target).find(".indicator").text( "<?php echo xla('expand'); ?>" );
        $("#"+div).hide();
    } else {
        $(target).find(".indicator").text( "<?php echo xla('collapse'); ?>" );
        $("#"+div).show();
    }
 }

function show_date_fun(){
  if(document.getElementById('show_date').checked == true){
    document.getElementById('date_div').style.display = '';
  }else{
    document.getElementById('date_div').style.display = 'none';
  }
  return;
}

$(document).ready(function(){

        // load divs
        $("#labtestresults_ps_expand").load("get_lab_results.php");
        $("#problemlist_ps_expand").load("get_problems.php");
        $("#medicationlist_ps_expand").load("get_medications.php");
        $("#medicationallergylist_ps_expand").load("get_allergies.php");
        $("#amendments_ps_expand").load("get_amendments.php");
        $("#appointments_ps_expand").load("get_appointments.php");

        $(".generateCCR").click(
        function() {
                if(document.getElementById('show_date').checked == true){
                        if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                                alert('<?php echo xls('Please select a start date and end date') ?>');
                                return false;
                        }
                }
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'no';
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".generateCCR_raw").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'yes';
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".generateCCR_download_h").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'hybrid';
                $("#ccr_form").submit();
        });
        $(".generateCCR_download_p").click(
        function() {
                if(document.getElementById('show_date').checked == true){
                        if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                                alert('<?php echo xls('Please select a start date and end date') ?>');
                                return false;
                        }
                }
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'pure';
                $("#ccr_form").submit();
        });
        $(".viewCCD").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'no';
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".viewCCD_raw").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'yes';
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".viewCCD_download").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'pure';
                $("#ccr_form").submit();
        });
        $(".generateDoc_download").click(
        function() {
                $("#doc_form").submit();
        });
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
        $(".viewCCR_send_dialog").click(
        function() {
                $("#ccr_send_dialog").toggle();
        });
        $(".viewCCR_transmit").click(
        function() {
                $(".viewCCR_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var ccrRecipient = $("#ccr_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccrRecipient;
                if(ccrRecipient=="") {
                  $("#ccr_send_message").html("<?php
                    echo xla('Please enter a valid Direct Address above.');?>");
                  $("#ccr_send_result").show();
                } else {
                  $(".viewCCR_transmit").attr('disabled','disabled');
                  $("#ccr_send_message").html("<?php
                    echo xla('Working... this may take a minute.');?>");
                  $("#ccr_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'generate',raw:'send '+ccrRecipient,requested_by:'patient'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccr_send_message").html("<?php
                            echo xla('Your message was submitted for delivery to');
                            ?> "+ccrRecipient);
                         $("#ccr_send_to").val("");
                       } else {
                         $("#ccr_send_message").html(data);
                       }
                       $(".viewCCR_transmit").removeAttr('disabled');
                  });
                }
        });
<?php }

if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
        $(".viewCCD_send_dialog").click(
        function() {
                $("#ccd_send_dialog").toggle();
        });
        $(".viewCCD_transmit").click(
        function() {
                $(".viewCCD_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var ccdRecipient = $("#ccd_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccdRecipient;
                if(ccdRecipient=="") {
                  $("#ccd_send_message").html("<?php
                    echo xla('Please enter a valid Direct Address above.');?>");
                  $("#ccd_send_result").show();
                } else {
                  $(".viewCCD_transmit").attr('disabled','disabled');
                  $("#ccd_send_message").html("<?php
                    echo xla('Working... this may take a minute.');?>");
                  $("#ccd_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'viewccd',raw:'send '+ccdRecipient,requested_by:'patient'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccd_send_message").html("<?php
                            echo xla('Your message was submitted for delivery to');
                            ?> "+ccdRecipient);
                         $("#ccd_send_to").val("");
                       } else {
                         $("#ccd_send_message").html(data);
                       }
                       $(".viewCCD_transmit").removeAttr('disabled');
                  });
                }
        });
<?php } ?>

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</head>

<body class="body_top">

<div id="wrapper" class="lefttop" style="width: 700px;">
<h2 class="heading"><?php echo xlt("Patient Portal"); ?></h2>

<?php
 $result = getPatientData($pid);
?>
<?php echo xlt('Welcome'); ?> <b><?php echo text($result['fname']." ".$result['lname']); ?></b>

<div style='margin-top:10px'> <!-- start main content div -->
 <table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
   <td align="left" valign="top">
    <!-- start left column div -->
    <div style='float:left; margin-right:20px'>
     <table cellspacing=0 cellpadding=0>
        <?php if ($GLOBALS['activate_ccr_ccd_report']) { // show CCR/CCD reporting options ?>
       <tr>
        <td width='650px'>
            <?php
          // Reports widget
            $widgetTitle = xl("Reports");
            $widgetLabel = "reports";
            $widgetButtonLabel = xl("");
            $widgetButtonClass = "hidden";
            $linkMethod = "html";
            $bodyClass = "notab";
            $widgetAuth = false;
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
           <div style='margin-left:3em; margin-right:3em; padding:1em; border:1px solid blue;' class='text'>
            <div id="ccr_report">
             <form name='ccr_form' id='ccr_form' method='post' action='../ccr/createCCR.php?portal_auth=1'>
             <span class='text'><b><?php echo xlt('Continuity of Care Record (CCR)'); ?></b></span>&nbsp;&nbsp;
             <br/>
             <span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
             <br/>
             <br/>
             <input type='hidden' name='ccrAction'>
             <input type='hidden' name='raw'>
             <input type="checkbox" name="show_date" id="show_date" onchange="show_date_fun();" ><span class='text'><?php echo xlt('Use Date Range'); ?>
             <br>
             <div id="date_div" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo xlt('Start Date');?>: </span>
                </td>
                <td>
                 <input type='text' size='10' class='datepicker' name='Start' id='Start'
                 title='<?php echo xla('yyyy-mm-dd'); ?>' />
                </td>
                <td>
                 &nbsp;
                 <span class='bold'><?php echo xlt('End Date');?>: </span>
                </td>
                <td>
                 <input type='text' class='datepicker' size='10' name='End' id='End'
                 title='<?php echo xla('yyyy-mm-dd'); ?>' />
                </td>
               </tr>
              </table>
             </div>
             <br>
             <input type="button" class="generateCCR" value="<?php echo xla('View/Print'); ?>" />
             <!-- <input type="button" class="generateCCR_download_h" value="<?php echo xla('Download'); ?>" /> -->
             <input type="button" class="generateCCR_download_p" value="<?php echo xla('Download'); ?>" />
             <!-- <input type="button" class="generateCCR_raw" value="<?php echo xla('Raw Report'); ?>" /> -->
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
             <input type="button" class="viewCCR_send_dialog" value="<?php echo xla('Transmit'); ?>" />
             <br>
             <div id="ccr_send_dialog" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo xlt('Enter Recipient\'s Direct Address');?>: </span>
                <input type="text" size="64" name="ccr_send_to" id="ccr_send_to" value="">
                <input type="button" class="viewCCR_transmit" value="<?php echo xla('Send'); ?>" />
                <div id="ccr_send_result" style="display:none" >
                 <span class="text" id="ccr_send_message"></span>
                </div>
                </td>
              </tr>
              </table>
             </div>
<?php } ?>
             <hr/>
             <span class='text'><b><?php echo xlt('Continuity of Care Document (CCD)'); ?></b></span>&nbsp;&nbsp;
             <br/>
             <span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
             <br/>
             <br/>
             <input type="button" class="viewCCD" value="<?php echo xla('View/Print'); ?>" />
             <input type="button" class="viewCCD_download" value="<?php echo xla('Download'); ?>" />
             <!-- <input type="button" class="viewCCD_raw" value="<?php echo xla('Raw Report'); ?>" /> -->
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
             <input type="button" class="viewCCD_send_dialog" value="<?php echo xla('Transmit'); ?>" />
             <br>
             <div id="ccd_send_dialog" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo xlt('Enter Recipient\'s Direct Address');?>: </span>
                <input type="text" size="64" name="ccd_send_to" id="ccd_send_to" value="">
                <input type="button" class="viewCCD_transmit" value="<?php echo xla('Send'); ?>" />
                <div id="ccd_send_result" style="display:none" >
                 <span class="text" id="ccd_send_message"></span>
                </div>
                </td>
              </tr>
              </table>
             </div>
<?php } ?>
            </form>
           </div>
          </div>
          <br/>

         </div>
        </td>
       </tr>
        <?php } // end CCR/CCD reporting options ?>
<?php if ($GLOBALS['portal_onsite_document_download']) { ?>
<?php echo "<tr><td width='650px'>";
$widgetTitle = xl('Documents');
$widgetLabel = "documents";
$widgetButtonLabel = xl('Download');
$widgetButtonClass = "hidden";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = false;
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
<span class="text"><?php echo xlt('Download all patient documents');?></span>
<br /><br />
<form name='doc_form' id='doc_form' action='get_patient_documents.php' method='post'>
    <input type="button" class="generateDoc_download" value="<?php echo xla('Download'); ?>" />
</form>
</div>
</td>
</tr>
<?php } ?>
<?php echo "<tr><td width='650px'>";
// Lab tests results expand collapse widget
$widgetTitle = xl("Lab Test Results");
$widgetLabel = "labtestresults";
$widgetButtonLabel = xl("");
$widgetButtonClass = "hidden";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = false;
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
                    <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
                  </div>

            </td>
        </tr>
                <?php echo "<tr><td width='650px'>";
                // problem list collapse widget
                $widgetTitle = xl("Problem List");
                $widgetLabel = "problemlist";
                $widgetButtonLabel = xl("");
                $widgetButtonClass = "hidden";
                $linkMethod = "html";
                $bodyClass = "notab";
                $widgetAuth = false;
                $fixedWidth = true;
                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>

                </div>


                        </td>
                </tr>

       <tr>
       <td width='650px'>
<?php
// medication list expand collapse widget
$widgetTitle = xl("Medication List");
$widgetLabel = "medicationlist";
$widgetButtonLabel = xl("");
$widgetButtonClass = "hidden";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = false;
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
                    <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
                </div>

     </td>
    </tr>

    <tr>
     <td width='650px'>
<?php // medication allergy expand collapse widget
  $widgetTitle = xl("Medication Allergy List");
  $widgetLabel = "medicationallergylist";
  $widgetButtonLabel = xl("");
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  $widgetAuth = false;
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
      <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
      </div>

     </td>
    </tr>

<!-- Amendments -->
<?php if ($GLOBALS['amendments']) { ?>
    <tr>
    <td width='650px'>
<?php
$widgetTitle = xl("Amendments");
$widgetLabel = "amendments";
$widgetButtonLabel = xl("");
$widgetButtonClass = "hidden";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = false;
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
    <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
    </td>
    </tr>
<?php } ?>
    <tr>
      <td width='650px'>
<?php
    // Show current and upcoming appointments.
     $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
      "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
      "c.pc_catname " .
      "FROM openemr_postcalendar_events AS e, users AS u, " .
      "openemr_postcalendar_categories AS c WHERE " .
      "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " .
      "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
      "ORDER BY e.pc_eventDate, e.pc_startTime";
      //echo $query;
     $res = sqlStatement($query, array($pid));

    // appointments expand collapse widget
    $widgetTitle = xl("Appointments");
    $widgetLabel = "appointments";
    $widgetButtonLabel = xl("Add");
        $widgetButtonLink = "add_edit_event_user.php?pid=".htmlspecialchars($pid, ENT_QUOTES);
        $widgetButtonClass = "edit_event iframe";
    $linkMethod = "";
    $bodyClass = "summary_item small";
if ($GLOBALS['portal_onsite_appt_modify']) {
    $widgetAuth = true;
} else {
    $widgetAuth = false;
}

    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
             $count = 0;
?>
            <div id='stats_div' style="display:none">
                <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div>
            </div>
        </td>
    </tr>
   </table>

   </div>

  </td>

 </tr>

</table>

</div> <!-- end main content div -->

<div id="portal-buttons-bottom"> <!-- buttons bottom div -->
    <input type="button" style="text-align: right;" value="<?php echo xla('Log Out'); ?>" onclick="window.location = 'logout.php'"/>

    <input type="button" style="text-align: right;" value="<?php echo xla('Change Password'); ?>" onclick="window.location = '<?php echo $landingpage."&password_update=1";?>'"/>
</div><!-- end buttons bottom div -->

</div>
</body>
</html>
