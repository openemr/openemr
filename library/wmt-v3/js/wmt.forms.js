function clearExamSection()
{
	var prefix = '';
	var client = '';
	var skip   = '';
	if(arguments.length) var client = arguments[0];
	if(arguments.length > 1) var prefix = arguments[1];
	if(arguments.length > 2) var skip = prefix + arguments[2];
	if(prefix == 'ALL') prefix = '';
  var i;
	var f = document.forms[0];
  var l = f.elements.length;
  for (i=0; i<l; i++) {
		if(!skip || f.elements[i].name != skip) {
    	if((prefix == '') || (f.elements[i].name.indexOf(prefix) == 0)) {
    		if(f.elements[i].type.indexOf('select') != -1) {
        	f.elements[i].selectedIndex = '0';
      	}
    		if(f.elements[i].type.indexOf('check') != -1) {
        	f.elements[i].checked=false;
      	}
    		if(f.elements[i].type.indexOf('text') != -1) {
        	if(f.elements[i].name.indexOf('dictate') == -1) {
						f.elements[i].value='';
					}
      	}
    	}
		}
  }
}

function AddMultSelItem(thisSelect, targetField)
{
	var sel = document.getElementById(thisSelect);
	var target = document.getElementById(targetField);
	var desc = '';
	for (var i=0; i<sel.options.length; i++) {
		if(sel.options[i].selected) {
			if(desc != '') desc = desc + ', ';
			desc = desc + sel.options[i].text;
		}
	}
	while( target.firstChild) {
		target.removeChild( target.firstChild );
	}
	target.appendChild( document.createTextNode(desc) );
}

function AddOrRemoveThis(thisTarget, thisData)
{
	var delim = '+';
	if(arguments.length > 2) delim = arguments[2];
	var empty_text = '';
	if(arguments.length > 3) delim = arguments[3];
	// alert('This Target: '+thisTarget+'  This data '+thisData);
	var test = document.getElementById(thisTarget).nodeName.toUpperCase();
	// alert("Test: "+test);
	if(test.indexOf('SPAN') != -1) {
		var val = document.getElementById(thisTarget).innerHTML;
		var new_val = val;
	} else if(test.indexOf('TEXTAREA') != -1) {
		var val = document.getElementById(thisTarget).innerHTML;
		var new_val = val;
	} else {
		var val = document.getElementById(thisTarget).value;
		var new_val = val;
	}
	// alert('Type: '+test+' And the Current Value: '+val);

	// IF IT DOESN'T EXIST WE CAN JUST ADD IT ON THE END
	if(val.indexOf(thisData) == -1) {
		if(new_val != '') new_val += delim;
		new_val += thisData;
	} else if(val.indexOf(delim + thisData + delim) != -1) {
		// THIS IS WHERE WE CLEAR THE VALUE FROM THE LIST IF IT EXISTS
		// FIRST CHECK FOR A MATCH IN THE MIDDLE OF THE EXISTING DATA
		new_val = val.replace((delim + thisData + delim),delim);	
	} else if(val.indexOf(delim + thisData) != -1) {
		// OR IS IT AT THE END?
		new_val = val.replace((delim + thisData), '');	
	} else if(val.indexOf(thisData + delim) != -1) {
		// LAST POSSIBILITY IS AT THE BEGINNING
		new_val = val.replace((thisData + delim), '');	
	} else {
		// IT WAS THE ONLY THING IN THE LIST
		new_val = '';
	}
	if(test.indexOf('SPAN') != -1) {
		var span = document.getElementById(thisTarget);
		while( span.firstChild) {
			span.removeChild( span.firstChild );
		}
		span.appendChild( document.createTextNode(new_val) );
	} else if(test.indexOf('TEXTAREA') != -1) {
		document.getElementById(thisTarget).innerHTML = new_val;
	} else {
		document.getElementById(thisTarget).value = new_val;
	}
	// alert('The New: '+new_val);
}

function UpdateSpanContent(thisSpan, thisContent)
{
	while( thisSpan.firstChild) {
		thisSpan.removeChild( thisSpan.firstChild );
	}
	thisSpan.appendChild( document.createTextNode(thisContent) );
}

function UpdateSelDescription(thisSelect, thisInput, thisSpan)
{
	var delim = '+';
	if(arguments.length > 3) delim = arguments[3];
	var sel = document.getElementById(thisSelect);
	var target = document.getElementById(thisInput);
	for (var i=0; i<sel.options.length; i++) {
		if(sel.options[i].selected) {
			// FIRST TEST IF THE 'REMOVE ALL' OPTION WAS THE CHOICE
			if(sel.options[i].value == '~ra~') {
				document.getElementById(thisInput).value = '';
				UpdateSpanContent(document.getElementById(thisSpan), '');
				sel.options[i].selected=false;
				return true;
			}
			// SECOND - IS THE 'SELECT ALL' OPTION CHOSEN
			if(sel.options[i].value == '~ALL~') {
				document.getElementById(thisInput).value = '~ALL~';
				UpdateSpanContent(document.getElementById(thisSpan), 'ALL');
				sel.options[i].selected=false;
				return true;
			}
			// THIRD - IS THE 'NONE' OPTION WAS CHOSEN
			if(sel.options[i].value == 'none') {
				document.getElementById(thisInput).value = 'none';
				UpdateSpanContent(document.getElementById(thisSpan), 'None');
				sel.options[i].selected=false;
				return true;
			}
			// IF 'NONE' WAS IN OUR LIST WE MUST REMOVE IT NOW
			if(target.value == 'none') {
				document.getElementById(thisInput).value = '';
				UpdateSpanContent(document.getElementById(thisSpan), '');
			}
			// Now we test for this option in our hidden list
			var opt = sel.options[i].value;
			new_val = AddOrRemoveThis(thisInput, opt, delim);
			var txt = sel.options[i].text;
			new_desc = AddOrRemoveThis(thisSpan, txt, ', ');

			sel.options[i].selected=false;
		}
	}
}

function UpdateLinkTextArea(thisSelect, thisInput, thisText)
{
	var sel = document.getElementById(thisSelect);
	// var target = document.getElementById(thisInput);
	// var val = target.value;
	// var new_val = val;
	var ta = document.getElementById(thisText);
	var desc = ta.value;
	var new_desc = desc;
	// alert("Hidden ["+val+"]  And Text ("+desc+")");
	for (var i=0; i<sel.options.length; i++) {
		if(sel.options[i].selected) {
			// First test the 'Remove All' Option
			if(sel.options[i].value == '~ra~') {
				// target.value = '';
				for (var s=0; s<sel.options.length; s++) {
					if(sel.options[s].value != '~ra~' && sel.options[s].value != '') {
						// FIX - Find the text in the textarea and delete it
						new_desc = new_desc.replace(', '+sel.options[s].text, ' ');
						new_desc = new_desc.replace(sel.options[s].text, '');
					}
				}
				ta.value = new_desc;
				sel.options[i].selected = false;
				return true;
			}
			// Second - test the 'Select All' Option
			if(sel.options[i].value == '~ALL~') {
				// target.value = '~ALL~';
				// FIX - WARNING - This could result in strange behavior
				ta.value = '';
				for (var s=0; s<sel.options.length; s++) {
					if(sel.options[s].value != '~ra~' && sel.options[s].value != '') {
						if(ta.value != '') ta.value += ', ';
						ta.value += sel.options[s].text;
					}
				}
				sel.options[i].selected = false;
				return true;
			}
			// Now we test for this option in our hidden list
			var test = desc.indexOf(sel.options[i].text);
			// alert("Is the New Choice in our List: "+test);
			// If it doesn't exist we add the option and description
			if(test == -1) {
				if(new_desc != '') new_desc = new_desc + ', ';
				new_desc = new_desc + sel.options[i].text;
				// if(new_val != '') new_val = new_val + '+';
				// new_val = new_val + sel.options[i].value;	
			} else {
				// Now the description text
				// First check for a match in the middle - 
				var txt = sel.options[i].text;
				test = desc.indexOf(', '+txt+',');
				if(test != -1) {
					new_desc = desc.replace((', '+txt+','),', ');	
				} else {
					// Is it at the end?
					test = desc.indexOf(', '+txt);
					if(test != -1) {
						new_desc = desc.replace((', '+txt),'');	
					} else {
						// Is it at the beginning?
						test = desc.indexOf(txt+',');
						if(test != -1) {
							new_desc = desc.replace((txt+', '),'');	
						} else {
							// It is the only one
							new_desc = '';
						}
					}
				}
			}
			sel.options[i].selected=false;
		}
	}
	// alert("New Hidden Value: "+new_val);
	// target.value = new_val;
	ta.value = new_desc;
}

function addCannedText(chk, field, text) {
	if(chk.checked == false) return false;
	var numargs = arguments.length;
	var label = '';
	if(numargs >= 4) label = arguments[3];
	var existing = document.getElementById(field).value
	if(existing.indexOf(text) != -1) return false;
	if(label != '') {
		if(existing.indexOf(label) != -1) return false;
	}
	if(existing != '') existing = existing + "\n\n";
	if(label != '') existing = existing + "PROCEDURE:  " + label + "\n";
	existing = existing + text;
	document.getElementById(field).value = existing;
	return true;
}

function updateValue(field, text) {
	var numargs = arguments.length;
	var label = '';
	if(numargs > 2) label = arguments[2];
	var existing = document.getElementById(field).value
	if(existing.indexOf(text) != -1) return false;
	if(label != '') {
		if(existing.indexOf(label) != -1) return false;
	}
	// if(existing != '') existing = existing + "\n\n";
	if(label != '') existing = existing + "PROCEDURE:  " + label + "\n";
	existing = existing + text;
	document.getElementById(field).value = existing;
	return true;
}

function findAndSelect(sel_id, val) {
	var sel = document.getElementById(sel_id);
	if(!sel) return false;
	for (var i=0; i<sel.options.length; i++) {
		if(sel.options[i].value == val) {
			sel.options[i].selected = true;
		}
	}
}

function AddMultSelItem(thisSelect, targetField)
{
	var sel = document.getElementById(thisSelect);
	var target = document.getElementById(targetField);
	var desc = '';
	for (var i=0; i<sel.options.length; i++) {
		if(sel.options[i].selected) {
			if(desc != '') desc = desc + ', ';
			desc = desc + sel.options[i].text;
		}
	}
	while( target.firstChild) {
		target.removeChild( target.firstChild );
	}
	target.appendChild( document.createTextNode(desc) );
}

function toggleThroughSelect(base_id) {
	var td = base_id + '_td';
	var cur = document.getElementById(base_id).selectedIndex;
  cur = parseInt(cur + 1);
	if(cur == document.getElementById(base_id).length) cur = 0;
	document.getElementById(base_id).selectedIndex = cur;
}

