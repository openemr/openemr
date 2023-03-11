<?php 

require_once ("../../interface/globals.php");
?>

Calendar=function() {
	return {};
}

Calendar.getParam = function (params) {
	var param = {
		<?php $datetimepicker_timepicker = false; ?>
	  	<?php $datetimepicker_showseconds = false; ?>
	 	<?php $datetimepicker_formatInput = false; ?>
	  	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
	  	<?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
	};

	if(params && params.ifFormat && params.ifFormat != '') {
		param['format'] = params.ifFormat.replaceAll("%", "");
	}

	return param;
}

Calendar.setup = function (params) {
	var param = Calendar.getParam(params);
	$('#'+params.inputField).datetimepicker(param);
	
  	$('#'+params.button).click(function() {
  		$('#'+params.inputField).datetimepicker('show');;
  	});
}