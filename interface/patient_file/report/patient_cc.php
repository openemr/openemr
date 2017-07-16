<?php
// mdsupport - CCR/CCD requests

require_once("../../globals.php");

if (!$GLOBALS['activate_ccr_ccd_report'] ) { // Config error
	// Suppress exit if the caller needs to be notified.
	exit;
}
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">
<?php 
if (!$GLOBALS['activate_ccr_ccd_report'] ) { // Config error
	printf('%s/%s %s.', attr(xl('CCR')), attr(xl('CCD')), 
		attr(xl('feature has not been enabled')));
	exit;
}
if (!isset($_SESSION['pid'])) {
	printf('%s/%s %s %s.', attr(xl('CCR')), attr(xl('CCD')), attr(xl('patient selection')),
			attr(xl('feature has not been enabled')));
	exit;
}
?>
<div class="container">
	<div id="send_result" class="alert" style="display:none" >
		<span class="text" id="send_message"></span>
	</div>

  <div><small><?php echo xl('Pop ups need to be enabled to see these reports'); ?></small></div>
  <form name='ccr_form' id='ccr_form' method='post' action='../../../ccr/createCCR.php'>
  <input type='hidden' id='ccrAction' name='ccrAction' value="">
  <input type='hidden' id='raw' name='raw' value="">
  <input type="hidden" name="send_type" id="send_type" value="">
  <input type="hidden" name="ccr_sent_by" id="ccr_sent_by" value="user">
  <div class="row">
    <div id="ccr_report" class="panel panel-default col-md-6">
      <div class="panel-heading"><?php xl('Continuity of Care Record (CCR)','e'); ?></div>
      <div class="panel-body">
          <div class="row">
              <div class='col-md-3'><?php xl('Optional Date Range','e'); ?></div>
              <div class='col-md-4'>
                  <label for='Start'><?php xl('Start Date','e');?></label>
                  <input class="oemr_date" type='text' size='10' name='Start' id='Start'>
			  </div>  
              <div class='col-md-4'>
                  <label for='End'><?php xl('End Date','e');?></label>
                  <input class="oemr_date"  type='text' size='10' name='End' id='End'>
              </div>
          </div>
		  <div class="row">
			<div class='col-md-3'><label for="ccr_send_to"><?php echo htmlspecialchars( xl("Recipient's Direct Address"), ENT_NOQUOTES);?></label></div>
			<div class='col-md-8'><input type="email" size="40" name="ccr_send_to" id="ccr_send_to" value=""></div>
		  </div>
	  </div>
      <div class="panel-footer">
		<input type="button" class="generateCCR submit1" value="<?php echo xla('Generate Report'); ?>"  
			data-ccraction="generate" data-raw="no" data-target="_blank" />
		<!-- 
		<input type="button" class="generateCCR_download_h submit1" value="<?php echo xl('Download')." (Hybrid)"; ?>" 
			data-ccraction="generate" data-raw="hybrid" />
		<input type="button" class="generateCCR_raw submit1" value="<?php xl('Raw Report','e'); ?>" 
			data-ccraction="generate" data-raw="yes" data-target="_blank" />
		-->
		<input type="button" class="generateCCR_download_p submit1" value="<?php echo xl('Download'); ?>" 
			data-ccraction="generate" data-raw="pure" />
		<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
		<input type="button" class="viewCCR_send_dialog submit2" value="<?php echo htmlspecialchars( xl('Transmit', ENT_QUOTES)); ?>" 
			 data-ccraction="generate" data-send="ccr" />
		<?php } ?>
      </div>
          
    </div>
    <div class="panel panel-default col-md-6">
      <div class="panel-heading"><?php xl('Continuity of Care Document (CCD)','e'); ?></div>
      <div class="panel-body">
			<div class='col-md-4'><label for="ccd_send_to"><?php echo htmlspecialchars( xl("Recipient's Direct Address"), ENT_NOQUOTES);?></label></div>
			<div class='col-md-8'><input type="email" size="40" name="ccd_send_to" id="ccd_send_to" value=""></div>
      </div>
      <div class="panel-footer">
      <input type="button" class="viewCCD submit1" value="<?php echo xla('Generate Report'); ?>" 
			data-ccraction="viewCCD" data-raw="no" data-target="_blank" />
	  <input type="button" class="viewCCD_download submit1" value="<?php echo htmlspecialchars( xl('Download', ENT_QUOTES)); ?>" 
			data-ccraction="viewCCD" data-raw="pure" />
	  <!--
	  <input type="button" class="viewCCD_raw submit1" value="<?php xl('Raw Report','e'); ?>" 
			data-ccraction="viewCCD" data-raw="yes" data-target="_blank" /> 
	  -->
	  <?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
	  <input type="button" class="viewCCD_send_dialog submit2" value="<?php echo htmlspecialchars( xl('Transmit', ENT_QUOTES))?>" 
			data-ccraction="viewCCD" data-send="ccd" />
	  <?php } ?>
      </div>
    </div>
  </div>
</div>

<div id="ccd_send_dialog" class="collapse">
 <br>
 <table border="0" cellpadding="0" cellspacing="0" >
  <tr>
   <td>
    <span class='bold'><?php echo htmlspecialchars( xl('Enter Recipient\'s Direct Address'), ENT_NOQUOTES);?>: </span>
   <input type="text" size="64" name="ccd_send_to" id="ccd_send_to" value="">
   <input type="hidden" name="ccd_sent_by" id="ccd_sent_by" value="user">
   <input type="button" class="viewCCD_transmit" value="<?php echo htmlspecialchars( xl('Send', ENT_QUOTES)); ?>" />
   <div id="ccd_send_result" style="display:none" >
    <span class="text" id="ccd_send_message"></span>
   </div>
   </td>
 </tr>
 </table>
</div>

</form>
<hr/>
<hr/>

</div>
</body>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
<?php require_once ("$srcdir/asset_datetimepicker.inc.php"); ?>
<script language="javascript">
function disp_msg(msg, msg_class) {
	$msgbox = $("#send_result");
	if (msg == "") { 
		$msgbox.collapse("hide");
		return;
	}
	$msgbox.attr("class","alert fade in "+msg_class);
	$("#send_message").html(msg);
	$msgbox.show();
}
$("input.submit1").click(function() {
	var $this = $(this);
	if ($this.hasClass(".generateCCR") || $this.hasClass(".generateCCR_download_p")) {
		// Check date entries : No longer needed	
		//if(document.getElementById('show_date').checked == true){
        //    if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
        //            alert('<?php echo addslashes( xl('Please select a start date and end date')) ?>');
        //            return false;
        //    }
		//}
		// return false;
	}
	if (typeof($this.data("ccraction")) !== "undefined") { $("#ccrAction").val($this.data("ccraction")); } 
	if (typeof($this.data("raw")) !== "undefined") { $("#raw").val($this.data("raw")); } 
	if (typeof($this.data("target")) !== "undefined") { $("#ccr_form").attr("target", $this.data("target")); } 
	$("#ccr_form").submit();
	$("#ccr_form").attr("target", "");
});
$("input.submit2").click( function() {
	var $this = $(this);
	var send = $this.data("send");
	var ccrRecipient = $('#'+send+'_send_to').val();
	if(ccrRecipient == "") {
	  disp_msg("<?php echo htmlspecialchars(xl('Please enter a valid Direct email address'), ENT_QUOTES);?>", "alert-danger");
	  return false;
	}
	$this.attr('disabled','disabled');
	if (typeof($this.data("ccraction")) !== "undefined") { $("#ccraction").val($this.data("ccraction")); }
	if (typeof($this.data("raw")) !== "undefined") { $("#raw").val('send '+ccrRecipient); } 
    $("#send_message").html("<?php echo htmlspecialchars(xl('This may take a minute.  Sending message to '), ENT_QUOTES);?>"+ccrRecipient);
    $("#send_result").show();
	var action=$("#ccr_form").attr('action');
	$.post(action, {ccrAction:'generate',raw:'send '+ccrRecipient,requested_by:'user'})
	.done(function() {
		disp_msg("<?php echo htmlspecialchars(xl('Your message was submitted for delivery to '), ENT_QUOTES);?>"+ccrRecipient, "alert-success");
		$('#'+send+'_send_to').val("");
	})
	.fail(function() {
		disp_msg("<?php echo htmlspecialchars(xl('Message transmission error.'), ENT_QUOTES);?>", "alert-danger");
	})
	.always(function() {
		$this.removeAttr('disabled');
	});
});
</script>

</html>
