Calendar=function() {
	return {};
}

Calendar.setup = function (params) {
	$('#'+params.inputField).datetimepicker(datetp);
  	$('#'+params.button).click(function() {
  		$('#'+params.inputField).focus();
  	});
}