<?php

require_once("../../../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;

if(!isset($_REQUEST['eid'])) $_REQUEST['eid'] = '';
if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';

$patData = array();
$email_direct = '';
$pat_phone = '';

if(!empty($_REQUEST['pid'])) {
	$pat_data = getPatientData($_REQUEST['pid']);

	$email_direct = $GLOBALS['wmt::use_email_direct'] ? $pat_data['email_direct'] : $pat_data['email'];
	$pat_phone = isset($pat_data['phone_cell']) && !empty($pat_data['phone_cell']) ? preg_replace('/[^0-9]/', '', $pat_data['phone_cell']) : "";
}

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Communication Type'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme']); ?>
	<style type="text/css">
  		.body_top{
  			margin: 10px;
  		}
  	</style>
</head>
<body class="body_top">
	<form id="custom_form">
		<input type="hidden" name="eid" value="<?php echo $_REQUEST['eid']; ?>">
		<?php if(!empty($email_direct)) { ?>
			<div>
				<input type="checkbox" id="email" name="email" value="1">
				<label for="email">Email - (<?php echo $email_direct; ?>)</label>
			</div>
		<?php } ?>
		<?php if(!empty($pat_phone)) { ?>
			<div>
				<input type="checkbox" id="sms" name="sms" value="1">
				<label for="sms">SMS - (<?php echo $pat_phone; ?>)</label>
			</div>
		<?php } ?>

		<?php if(empty($pat_phone) && empty($email_direct)) { ?>
			<div>
				<span>No communication type available to for this patient.</span>
			</div>
		<?php } ?>

		<?php if(!empty($pat_phone) || !empty($email_direct)) { ?>
		<footer style="border:none; margin-top: 80px;">
			<button id="submit_type" type="button">Submit</button>
		</footer>
		<?php } ?>
	</form>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#submit_type').click(async function(){
				var formData = $('#custom_form').serializeObject();

				if(formData && (formData.email == true || formData.sms == true)) {
					callCommunicationType(formData);
				} else {
					alert("Please select communication type.");
				}
			});

			function callCommunicationType(formData) {
				if (opener.closed || ! opener.setCommunicationType)
				alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
				else
				opener.setCommunicationType(formData);
				window.close();
				return false;
			 }

			$.fn.serializeObject = function() {
		        var o = {};
		        var a = this.serializeArray();
		        $.each(a, function() {
		            if (o[this.name]) {
		                if (!o[this.name].push) {
		                    o[this.name] = [o[this.name]];
		                }
		                o[this.name].push(this.value || '');
		            } else {
		                o[this.name] = this.value || '';
		            }
		        });
		        return o;
		    };
		});
	</script>
</body>
</html>