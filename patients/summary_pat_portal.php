<?php

// Copyright (C) 2011 by following authors:
//   - Cassian LUP <cassi.lup@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
// (note this is already completed by the script that includes this
//    get_patient_info.php )

//STOP FAKE REGISTER GLOBALS
// (note this is already completed by the script that includes this
//    get_patient_info.php )

//continue session
session_start();

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=".$_SESSION['site_id'];
//

// kick out if patient not authenticated
if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
  $pid = $_SESSION['pid'];
}
else {
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
 require_once("$srcdir/classes/Address.class.php");
 require_once("$srcdir/classes/InsuranceCompany.class.php");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/options.inc.php");
 require_once("../interface/patient_file/history/history.inc.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/edi.inc");
 include_once("$srcdir/lists.inc");

?>
<html>

<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $web_root; ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo $web_root; ?>/library/dynarch_calendar.css">
<script type="text/javascript" src="<?php echo $web_root; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<link rel="stylesheet" href="css/base.css" type="text/css"/>
<link rel="stylesheet" href="css/tables.css" type="text/css"/>
<script type="text/javascript" language="JavaScript">

 function refreshme() {
  location.reload();
 }

 function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        $("#"+div).hide();
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        $("#"+div).show();
    }
 }

function refreshAppointments() {
      $("#appointments_ps_expand").load("get_appointments.php", { 'embeddedScreen' : true }, function() {
          $(".edit_event").fancybox({
              'overlayOpacity' : 0.0,
              'showCloseButton' : true,
              'centerOnScroll' : false,
              'autoscale' : true,
              'hihdeOnContentClick' : false,
              'callbackOnClose' : function()  {
                  refreshAppointments();
              }
          });
      });
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
      $("#labtestresults_ps_expand").load("get_lab_results.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
      $("#problemlist_ps_expand").load("get_problems.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
      $("#medicationlist_ps_expand").load("get_medications.php", { 'embeddedScreen' : true  }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
      $("#medicationallergylist_ps_expand").load("get_allergies.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
	  $("#amendments_ps_expand").load("get_amendments.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
      
      refreshAppointments();

    // fancy box
    enable_modals();

  // modal for dialog boxes
  $(".large_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'frameHeight' : 600,
    'frameWidth' : 1000,
    'centerOnScroll' : false
  });

  // modal for image viewer
  $(".image_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'centerOnScroll' : false,
    'autoscale' : true
  });
  
  $(".add_event").fancybox( {
  	'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'centerOnScroll' : false,
    'autoscale' : true,
    'hideOnContentClick' : false,
    'onClose' : function() {
    	refreshme();
    }
  });

        $(".generateCCR").click(
        function() {
                if(document.getElementById('show_date').checked == true){
                        if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                                alert('<?php echo addslashes( xl('Please select a start date and end date')) ?>');
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
                                alert('<?php echo addslashes( xl('Please select a start date and end date')) ?>');
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
       echo htmlspecialchars(xl('Please enter a valid Direct Address above.'), ENT_QUOTES);?>");
                  $("#ccr_send_result").show();
                } else {
                  $(".viewCCR_transmit").attr('disabled','disabled');
                  $("#ccr_send_message").html("<?php
       echo htmlspecialchars(xl('Working... this may take a minute.'), ENT_QUOTES);?>");
                  $("#ccr_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'generate',raw:'send '+ccrRecipient,requested_by:'patient'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccr_send_message").html("<?php
       echo htmlspecialchars(xl('Your message was submitted for delivery to'), ENT_QUOTES);
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
       echo htmlspecialchars(xl('Please enter a valid Direct Address above.'), ENT_QUOTES);?>");
                  $("#ccd_send_result").show();
                } else {
                  $(".viewCCD_transmit").attr('disabled','disabled');
                  $("#ccd_send_message").html("<?php
       echo htmlspecialchars(xl('Working... this may take a minute.'), ENT_QUOTES);?>");
                  $("#ccd_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'viewccd',raw:'send '+ccdRecipient,requested_by:'patient'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccd_send_message").html("<?php
       echo htmlspecialchars(xl('Your message was submitted for delivery to'), ENT_QUOTES);
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

});

</script>

</head>

<body class="body_top">

<div id="wrapper" class="lefttop" style="width: 700px;">
<h2 class="heading"><?php echo htmlspecialchars( xl('Patient Portal'), ENT_NOQUOTES); ?></h2>

<?php
 $result = getPatientData($pid);
?>
<?php echo htmlspecialchars( xl('Welcome'), ENT_NOQUOTES); ?> <b><?php echo htmlspecialchars($result['fname']." ".$result['lname'],ENT_NOQUOTES); ?></b>

<div style='margin-top:10px'> <!-- start main content div -->
 <table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
   <td align="left" valign="top">
    <!-- start left column div -->
    <div style='float:left; margin-right:20px'>
     <table cellspacing=0 cellpadding=0>
      <?php if ( $GLOBALS['activate_ccr_ccd_report'] ) { // show CCR/CCD reporting options ?>
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
          expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
           $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
           $widgetAuth, $fixedWidth);
          ?>
           <br/>
           <div style='margin-left:3em; margin-right:3em; padding:1em; border:1px solid blue;' class='text'>
            <div id="ccr_report">
             <form name='ccr_form' id='ccr_form' method='post' action='../ccr/createCCR.php?portal_auth=1'>
             <span class='text'><b><?php echo htmlspecialchars( xl('Continuity of Care Record (CCR)'), ENT_NOQUOTES); ?></b></span>&nbsp;&nbsp;
             <br/>
             <span class='text'>(<?php echo htmlspecialchars( xl('Pop ups need to be enabled to see these reports'), ENT_NOQUOTES); ?>)</span>
             <br/>
             <br/>
             <input type='hidden' name='ccrAction'>
             <input type='hidden' name='raw'>
             <input type="checkbox" name="show_date" id="show_date" onchange="show_date_fun();" ><span class='text'><?php echo htmlspecialchars( xl('Use Date Range'), ENT_NOQUOTES); ?>
             <br>
             <div id="date_div" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo htmlspecialchars( xl('Start Date'), ENT_NOQUOTES);?>: </span>
                </td>
                <td>
                 <input type='text' size='10' name='Start' id='Start'
                 onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                 title='<?php echo htmlspecialchars( xl('yyyy-mm-dd'), ENT_QUOTES); ?>' />
                 <img src='../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
                 id='img_start' border='0' alt='[?]' style='cursor:pointer'
                 title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' >
                 <script LANGUAGE="JavaScript">
                  Calendar.setup({inputField:"Start", ifFormat:"%Y-%m-%d", button:"img_start"});
                 </script>
                </td>
                <td>
                 &nbsp;
                 <span class='bold'><?php echo htmlspecialchars( xl('End Date'), ENT_NOQUOTES);?>: </span>
                </td>
                <td>
                 <input type='text' size='10' name='End' id='End'
                 onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
                 title='<?php echo htmlspecialchars( xl('yyyy-mm-dd'), ENT_QUOTES); ?>' />
                 <img src='../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
                 id='img_end' border='0' alt='[?]' style='cursor:pointer'
                 title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' >
                 <script LANGUAGE="JavaScript">
                  Calendar.setup({inputField:"End", ifFormat:"%Y-%m-%d", button:"img_end"});
                 </script>
                </td>
               </tr>
              </table>
             </div>
             <br>
             <input type="button" class="generateCCR" value="<?php echo htmlspecialchars( xl('View/Print'), ENT_QUOTES); ?>" />
             <!-- <input type="button" class="generateCCR_download_h" value="<?php echo htmlspecialchars( xl('Download'), ENT_QUOTES); ?>" /> -->
             <input type="button" class="generateCCR_download_p" value="<?php echo htmlspecialchars( xl('Download'), ENT_QUOTES); ?>" />
             <!-- <input type="button" class="generateCCR_raw" value="<?php echo htmlspecialchars( xl('Raw Report'), ENT_QUOTES); ?>" /> -->
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
             <input type="button" class="viewCCR_send_dialog" value="<?php echo htmlspecialchars( xl('Transmit', ENT_QUOTES)); ?>" />
             <br>
             <div id="ccr_send_dialog" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo htmlspecialchars( xl('Enter Recipient\'s Direct Address'), ENT_NOQUOTES);?>: </span>
                <input type="text" size="64" name="ccr_send_to" id="ccr_send_to" value="">
                <input type="button" class="viewCCR_transmit" value="<?php echo htmlspecialchars( xl('Send', ENT_QUOTES)); ?>" />
                <div id="ccr_send_result" style="display:none" >
                 <span class="text" id="ccr_send_message"></span>
                </div>
                </td>
              </tr>
              </table>
             </div>
<?php } ?>
             <hr/>
             <span class='text'><b><?php echo htmlspecialchars( xl('Continuity of Care Document (CCD)'), ENT_NOQUOTES); ?></b></span>&nbsp;&nbsp;
             <br/>
             <span class='text'>(<?php echo htmlspecialchars( xl('Pop ups need to be enabled to see these reports'), ENT_NOQUOTES); ?>)</span>
             <br/>
             <br/>
             <input type="button" class="viewCCD" value="<?php echo htmlspecialchars( xl('View/Print', ENT_QUOTES)); ?>" />
             <input type="button" class="viewCCD_download" value="<?php echo htmlspecialchars( xl('Download', ENT_QUOTES)); ?>" />
             <!-- <input type="button" class="viewCCD_raw" value="<?php echo htmlspecialchars( xl('Raw Report', ENT_QUOTES)); ?>" /> -->
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
             <input type="button" class="viewCCD_send_dialog" value="<?php echo htmlspecialchars( xl('Transmit', ENT_QUOTES)); ?>" />
             <br>
             <div id="ccd_send_dialog" style="display:none" >
              <br>
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo htmlspecialchars( xl('Enter Recipient\'s Direct Address'), ENT_NOQUOTES);?>: </span>
                <input type="text" size="64" name="ccd_send_to" id="ccd_send_to" value="">
                <input type="button" class="viewCCD_transmit" value="<?php echo htmlspecialchars( xl('Send', ENT_QUOTES)); ?>" />
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
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
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
                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>

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
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
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
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
	
<!-- Amendments -->
<?php if ( $GLOBALS['amendments'] ) { ?>
	<tr>
	<td width='650px'>
<?php
$widgetTitle = xl("Amendments");
$widgetLabel = "amendments";
$widgetButtonLabel = xl(""));
$widgetButtonClass = "hidden";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = false;
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>
                    
<br/>
	<div style='margin-left:10px' class='text'><img src='images/ajax-loader.gif'/></div><br/>
	</td>
	</tr>		

<?php } ?>

	<tr>
		<td>
			
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
	 $res = sqlStatement($query, array($pid) );

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
        }
        else {
            $widgetAuth = false;
        }
	$fixedWidth = false;

	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);

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

<input type="button" style="text-align: right;" value="<?php echo xl('Log Out'); ?>" onclick="window.location = 'logout.php'"/>

<input type="button" style="text-align: right;" value="<?php echo xl('Change Password'); ?>" onclick="window.location = '<?php echo $landingpage."&password_update=1";?>'"/>


</wrapper>

</body>
</html>
