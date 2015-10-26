// --------- Add and remove receivers---------
function addRow(tableID) {

	var table = document.getElementById(tableID);
	var rowCount = table.rows.length;
	if (rowCount < 8) {
		var row = table.insertRow(rowCount);

		var cell0 = row.insertCell(0);
		var element0 = document.createElement("label");
		var textnode = document.createTextNode("Receiver");
		element0.appendChild(textnode);
		cell0.appendChild(element0);
		var element1 = document.createElement("input");
		element1.type = "checkbox";
		cell0.appendChild(element1);

		var cell2 = row.insertCell(1);
		var element2 = document.createElement("input");
		element2.type = "text";
		element2.name = "receiverEmail[]";
		element2.size = "10";
		cell2.appendChild(element2);

		var cell3 = row.insertCell(2);
		var element3 = document.createElement("input");
		element3.type = "text";
		element3.name = "receiverAmount[]";
		element3.className = "smallfield";
		cell3.appendChild(element3);

		var cell4 = row.insertCell(3);
		var sel = document.createElement('select');
		sel.name = 'primaryReceiver[]';
		sel.options[0] = new Option('false', 'false');
		sel.options[1] = new Option('true', 'true');
		sel.className = "smallfield";
		cell4.appendChild(sel);

	}
}

function deleteRow(tableID) {
	try {
		var selectionMade = false;
		var table = document.getElementById(tableID);
		var rowCount = table.rows.length;
		if (rowCount > 1) {
			for ( var i = 2; i < rowCount; i++) {
				var row = table.rows[i];
				var chkbox = row.cells[0].childNodes[0];
				if (null != chkbox && true == chkbox.checked) {
					selectionMade = true;
					table.deleteRow(i);
					rowCount--;
					i--;
				}
			}
		} 
		if(!selectionMade){
			alert("Please select one or more rows to delete");
		}
	} catch (e) {
		alert(e);
	}
}

function cloneRow(tableID, maxRows) {

	var table = document.getElementById(tableID);
	var tBody = table.getElementsByTagName("tbody")[0];
	var rowCount = tBody.childElementCount;
	if (rowCount < maxRows && rowCount > 0) {
		var origRow = tBody.children[rowCount - 1];
		var row = origRow.cloneNode(true);
		row.id = getNextId(origRow.id);
		tBody.appendChild(row);
		var cells = row.getElementsByTagName("td");
		for (i = 0; i < cells.length; i++) {
			var cell = cells[i];
			var inputs = cell.getElementsByTagName("input");
			for (j = 0; j < inputs.length; j++) {
				var input = inputs[j];
				input.id = getNextId(input.id);
				if(input.type == "checkbox") {
					input.disabled = false;
				}
			}
			var inputs = cell.getElementsByTagName("select");
			for (j = 0; j < inputs.length; j++) {
				var input = inputs[j];
				input.id = getNextId(input.id);
			}			
		}
	}
}

function getNextId(id) {
	var parts = id.match(/(\D+)(\d+)$/);
	// create a unique name for the new field by incrementing
	// the number for the previous field by 1
	return parts[1] + ++parts[2];
}

// JQuery functions
function addTableRow(table) {
	// clone the last row in the table
	var $tr = $(table).find("tbody tr:last").clone();

	// get the name attribute for the input and select fields
	$tr.find("input,select").attr("name", getNextId(this.name)).attr("id",
			getNextId(this.id)).removeAttr("disabled");	
	// append the new row to the table
	$(table).find("tbody tr:last").after($tr);
}

qtipConfig = {
	style : {
		color : 'black',
		textAlign : 'left',
		border : {
			width : 7,
			radius : 5
		},
		tip : 'topLeft',
		name : 'blue'
	}
}
