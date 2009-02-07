/*
 * phpGACL - Generic Access Control List
 * Copyright (C) 2002,2003 Mike Benoit
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * phpGACL mailing list. http://sourceforge.net/mail/?group_id=57103
 *
 * You may contact the author of phpGACL by e-mail at:
 * ipso@snappymail.ca
 *
 * The latest version of phpGACL can be obtained from:
 * http://phpgacl.sourceforge.net/
 *
 */

var selectedTab = null;

//Function to totally clear a select box.
function depopulate(form_element) {
	if (form_element.options.length > 0) {
		form_element.innerHTML = '';
	}
}

//Populates a select box based off the value of "parent" select box. 
function populate(parent_form_element, child_form_element, src_array) {
	//alert('Parent: ' + parent_form_element);
	//alert('Child: ' + child_form_element);
	
	if (parent_form_element.selectedIndex >= 0) {
		//Grab the current selected value from the parent
		parent_id = parent_form_element.options[parent_form_element.selectedIndex].value;
		
		//Clear the child form element
		depopulate(child_form_element);
		
		//Populate child form element
		if (options[src_array][parent_id]) {
			for (i=0; i < options[src_array][parent_id].length; i++) {
				child_form_element.options[i] = new Option(options[src_array][parent_id][i][1], options[src_array][parent_id][i][0]);
			}
		}
	}
}

//Select an item by "copying" it from one select box to another
function select_item(parent_form_element, src_form_element, dst_form_element) {
	//alert('Src: ' + src_form_element);
	//alert('Dst: ' + dst_form_element);
	found_dup=false;
	//Copy it over to the dst element
	for (i=0; i < src_form_element.options.length; i++) {
		if (src_form_element.options[i].selected) {
			//Check to see if duplicate entries exist.
			for (n=0; n < dst_form_element.options.length; n++) {
				if ( parent_form_element.options[parent_form_element.selectedIndex].value + '^' + src_form_element.options[i].value == dst_form_element.options[n].value) {
					found_dup=true;
				}
			}
			
			//Only add if its not a duplicate entry.
			if (!found_dup) {
				//Grab the current selected value from the parent
				src_id = src_form_element.options[i].value;
				src_text = src_form_element.options[i].text;
				
				src_section_id = parent_form_element.options[parent_form_element.selectedIndex].value;
				src_section_text = parent_form_element.options[parent_form_element.selectedIndex].text;
				
				options_length = dst_form_element.options.length;
				dst_form_element.options[options_length] = new Option(src_section_text + ' > ' + src_text, src_section_id + '^' + src_id);
				dst_form_element.options[options_length].selected = true;
			}
		}
		
		found_dup=false;
	}
}

//Used for moving items to and from the selected combo box.
function deselect_item(form_element) {
	//alert('Src: ' + src_form_element);
	//alert('Dst: ' + dst_form_element);
	
	//Copy it over to the dst element
	for (i=0; i < form_element.options.length; i++) {
		if (form_element.options[i].selected) {
			form_element.options[i] = null;
			i=i - 1;
		}
	}
}

//Used to unselect all items in a combo box
function unselect_all(form_element) {
	for (i=0; i < form_element.options.length; i++) {
		form_element.options[i].selected = false;
	}
}

function select_all(select_box) {
	for (i=0; i < select_box.options.length; i++) {
		select_box.options[i].selected = true;
	}
}

function edit_link(link, parent_id) {
    alert('edit_aco.php?section_id=' + parent_id + '&return_page={$return_page}')
}

function toggleObject(objectID) {
	if(document.getElementById) {
		if(document.getElementById(objectID).className == 'hide') {
			showObject(objectID);
		} else {
			hideObject(objectID);
		}
	}
}

function showObject(objectID) {
	if(document.getElementById) {
		document.getElementById(objectID).className = 'show';
	}
}

function hideObject(objectID) {
	if(document.getElementById) {
		document.getElementById(objectID).className = 'hide';
	}
}

function showTab(objectID) {
	if(document.getElementById) {
		if(selectedObject != objectID) {
			document.getElementById(objectID).className = 'tabon';
			selectedTab = objectID;
		}
	}
}

function hideTab() {
	if(document.getElementById) {
		if(selectedTab) {
			document.getElementById(selectedTab).className = 'taboff';
		}
	}
}

function checkAll(checkbox) {
	for (i=0; i<checkbox.form.elements.length; i++) {
		if (checkbox.form.elements[i].type == checkbox.type) {
			checkbox.form.elements[i].checked = checkbox.checked;
		}
	}
	return true;
}

/**
 * Sets a Cookie with the given name and value.
 *
 * name       Name of the cookie
 * value      Value of the cookie
 * [expires]  Expiration date of the cookie (default: end of current session)
 * [path]     Path where the cookie is valid (default: path of calling document)
 * [domain]   Domain where the cookie is valid
 *              (default: domain of calling document)
 * [secure]   Boolean value indicating if the cookie transmission requires a
 *              secure transmission
 */
function setCookie(name, value, expires, path, domain, secure) {
	document.cookie= name + "=" + escape(value) +
		((expires) ? "; expires=" + expires.toGMTString() : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
}

/**
 * Gets the value of the specified cookie.
 *
 * name  Name of the desired cookie.
 *
 * Returns a string containing value of specified cookie,
 *   or null if cookie does not exist.
 */
function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
		begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	} else {
		begin += 2;
	}
	var end = document.cookie.indexOf(";", begin);
	if (end == -1) {
		end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}

/**
 * Deletes the specified cookie.
 *
 * name      name of the cookie
 * [path]    path of the cookie (must be same as path used to create cookie)
 * [domain]  domain of the cookie (must be same as domain used to create cookie)
 */
function deleteCookie(name, path, domain) {
	if (getCookie(name)) {
		document.cookie = name + "=" + 
			((path) ? "; path=" + path : "") +
			((domain) ? "; domain=" + domain : "") +
			"; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}
}
