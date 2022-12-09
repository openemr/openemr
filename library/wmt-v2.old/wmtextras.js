// This file has extras for some nifty but usually 
// client specific functionality

// Allows tab to be used in a text area
function insertTab(o,e)
{
	var Kval = e.keyCode ? e.keyCode : e.charCode ? e.charCode : e.which;
	if(Kval == 9 && !e.shiftKey && !e.ctrlKey && !e.altKey) {
		var Sc_top = o.scrollTop;
		if(o.setSelectionRange) {
			var ss = o.selectionStart;
			var se = o.selectionEnd;
			o.value = o.value.substring(0,ss) + "\t" + o.value.substr(se);
			o.setSelectionRange(ss + 1, ss + 1);
			o.focus();
		} else if(o.createTextRange) {
			document.selection.creatRange().text = "\t";
			e.returnValue=false;
		}
		o.scrollTop = Sc_top;
		if(e.preventDefault) {
			e.preventDefault();
		}
		return false;
	}
	return true;
}
