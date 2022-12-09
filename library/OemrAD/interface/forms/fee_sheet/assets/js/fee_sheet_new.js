function open_justify_form(pid, encounter) {
	var url = js_oemrad_web_lib_path + 'interface/forms/fee_sheet/justify_form.php'+'?pid='+pid+'&encounter='+encounter;
  	dlgopen(url, 'justify_form', 500, 200, '', 'Justify');
}

function setJustifyVal(value) {
	if(value != "") {
		var eleJustify = document.getElementsByClassName("selJustify");
		if(eleJustify.length > 0) {
			for (i = 0; i < eleJustify.length; i++) {
				eleJustify[i].selectedIndex = "0";
				eleJustify[i].options["0"].value = value; 
				eleJustify[i].options["0"].text = value; 
			}
		}

	}
}