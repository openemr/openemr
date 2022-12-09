<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<?php
$frmdir = 'cases';
$frmn = 'form_'.$frmdir;
include_once('../../globals.php');
include($GLOBALS['srcdir'].'/wmt-v2/print_setup.inc.php');

include($GLOBALS['srcdir'].'/wmt-v2/form_views/food_handler_print.php');
?>
<html>
<head>
<title>Food Handler Certificate For <?php echo $patient->full_name; ?></title>

<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.css" type="text/css">

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">

<?php include($GLOBALS['srcdir'].'/wmt-v2/ajax/init_ajax.inc.js'); ?>

// I AM WONDERING IF THIS IS A GOOD METHOD TO IMPLEMENT LOGGING?
function markPrinted(id) {
	var output = 'error';
	var id_array = [];
	id_array.push(id);
	if(!id_array.length) {
		alert('No Certificate ID Was Selected, Nothing will Print');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/mark_certificates.ajax.php",
		data: {
			id: id_array
		},
		success: function(result) {
			if(result['error']) {
				output = false;
				alert('There was a problem marking the sheets as printed\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: true 
	});
	return output;
}
</script>
</head>

<body style="padding: 20px 30px 15px 30px">
<?php 
// $print_action = " markPrinted($id);";
include($GLOBALS['srcdir'].'/wmt-v2/print_buttons.inc.php');
?>
</body>
</html>
