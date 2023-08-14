<?php

require_once("../../../globals.php");

use OpenEMR\Core\Header;

$selId = isset($_GET['id']) ? $_GET['id'] : "";
$selData = array();

if(isset($selId) && !empty($selId)) {
	$selData = sqlQuery("SELECT * from vh_predefined_lbf_selector_details vplsd where id = ? ", array($selId));
}

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Add Predefined LBF Selection'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery']); ?>
	<style type="text/css">
		.mainContainer {
			margin-top: 0px;
			margin-bottom: 10px;
			margin-left: 10px;
			margin-right: 10px;
		}

		.modal-body {
		    padding: 2px;
		    height: 61.7621vh;
		    max-height: 94vh;
		    overflow-y: auto;
		}

		.modal-footer {
			text-align: right;
		    padding: 10px;
		    border-top: 1px solid rgb(229, 229, 229);
		}
	</style>
</head>
<body>
<div class="mainContainer modal-body">
	<form>
		<div class="form-group">
		    <label><?php echo  xlt('Predefined Selector Name'); ?></label>
		    <input type="text" class="form-control" id="selector_name" aria-describedby="emailHelp" placeholder="Enter Selector Name" value="<?php echo isset($selData['title']) ? $selData['title'] : '' ?>">
		</div>

		<div class="form-group">
		    <input type="checkbox" class="" id="is_global" <?php echo isset($selData['is_global']) && $selData['is_global'] == "1" ? 'checked="checked"'  : '' ?>>
		<label class="form-check-label"><?php echo  xlt('Is Global'); ?></label>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-primary btn-messagesaveBtn" data-dismiss="modal" onClick="handleSubmit()">Submit</button>
</div>
<script type="text/javascript">
	function handleSubmit() {
		let title = $('#selector_name').val();
		let isGlobal = $('#is_global').prop('checked')==true ? '1' : '0';

		if(title == '') {
			alert('Please Enter Predefined Selector Name');
			return false;
		}

		selCallbackFun(title, isGlobal);
	}

	function selCallbackFun(title, isGlobal) {
		if (opener.closed || ! opener.setPredefinedLBFSelection)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setPredefinedLBFSelection('<?php echo $selId ?>', title, isGlobal);
		window.close();
		return false;
	}
	</script>
</body>
</html>