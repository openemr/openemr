<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\PostalLetter;

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
$street = isset($_REQUEST['street']) ? $_REQUEST['street'] : "";
$street1 = isset($_REQUEST['street1']) ? $_REQUEST['street1'] : "";
$city = isset($_REQUEST['city']) ? $_REQUEST['city'] : "";
$state = isset($_REQUEST['state']) ? $_REQUEST['state'] : "";
$postal_code = isset($_REQUEST['postal_code']) ? $_REQUEST['postal_code'] : "";
$country = isset($_REQUEST['country']) ? $_REQUEST['country'] : "";

// Option lists
$state_list = new wmt\Options('State');

if($mode == "ajax") {
	$addressObj = PostalLetter::generatePostalAddress($_REQUEST, "\n");
	echo json_encode($addressObj);
	exit();
}

?><!DOCTYPE html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<title>Edit Address</title>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
  	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme']); ?>

  	<style type="text/css">
  		.fieldLabel {
  			text-align: left;
  			width: 100px;
  		}
  		.formContainer {
  			width:100%;
        	max-width:500px;
  		}
  		.body_top{
  			margin: 8px;
  		}
  	</style>
</head>
<body class="body_top">
	<form id="custom_address_form">
		<div style="width:95vw;margin-top:20px">
			<table class="formContainer">
				<?php
					if(isset($_REQUEST['reply']) && $_REQUEST['reply'] == true) {
						?>
						<tr>
							<td class="fieldLabel"><b><?php echo xlt('Name'); ?>:&nbsp;</b></td>
							<td>
								<input type='text' class='form-control' id="name" name="name" value='<?php echo $name ?>' />
							</td>
						</tr>
						<?php
					}
				?>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('Address Line1'); ?>:&nbsp;</b></td>
					<td>
						<input type='text' class='form-control' id="street" name="street" value='<?php echo $street ?>' />
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('Address Line2'); ?>:&nbsp;</b></td>
					<td>
						<input type='text' class='form-control' id="street1" name="street1" value='<?php echo $street1 ?>' />
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('City'); ?>:&nbsp;</b></td>
					<td>
						<input type='text' class='form-control' id="city" name="city" value='<?php echo $city ?>' />
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('State'); ?>:&nbsp;</b></td>
					<td>
						<select id="state" name="state" class='form-control formControlSelect'>
								<?php $state_list->showOptions($state) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('Zip code'); ?>:&nbsp;</b></td>
					<td>
						<input type='text' class='form-control' id="postal_code" name="postal_code" value='<?php echo $postal_code ?>' />
					</td>
				</tr>
				<tr>
					<td class="fieldLabel"><b><?php echo xlt('Country'); ?>:&nbsp;</b></td>
					<td>
						<input type='text' class='form-control' id="country" name="country" value='<?php echo $country ?>' />
					</td>
				</tr>
			</table>
		</div>
		<br/>
		<footer style="border:none">
			<button id="submit_address" class="btn btn-primary" type="button">Submit</button>
		</footer>
	</form>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#submit_address').click(async function(){
				var formData = $('#custom_address_form').serializeObject();
				var result = await validateForm(formData);

				if(result && result['status'] === true) {
					return callCustomAddress(result);
				} else if(result && result['status'] === false) {
					alert(result['errors']);
					return false;
				}
			});

			async function validateForm(address) {
				const result = await $.ajax({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/attachment/custom_address.php?mode=ajax'; ?>",
					datatype: "json",
					data: address
				});

				return JSON.parse(result);
			}

			function callCustomAddress(address) {
				if (opener.closed || ! opener.setCustomAddress)
				alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
				else
				opener.setCustomAddress(address);
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