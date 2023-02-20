<?php 

require_once ("../../interface/globals.php");
?>

Calendar=function() {
	return {};
}

Calendar.setup = function (params) {
	$('#'+params.inputField).datetimepicker({
		<?php $datetimepicker_timepicker = false; ?>
	  	<?php $datetimepicker_showseconds = false; ?>
	 	<?php $datetimepicker_formatInput = false; ?>
	  	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
	  	<?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
	});
	
  	$('#'+params.button).click(function() {
  		$('#'+params.inputField).focus();
  	});
}