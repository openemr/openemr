function enableAnchors() {
    anchors.options.visible = 'always';
    anchors.add('h3');
}

$(document).ready(function() {
    enableAnchors();

    var table = $('#standard_table').DataTable( {
        lengthChange: false,
		bSort: false,
		bPaginate: false,
		autoWidth: true,
		buttons: [ ]
    } );
 
    table.buttons().container()
        .appendTo('#standard_table_wrapper .col-sm-6:eq(0)' );    	
} );

var codeElement = document.getElementById("sql-script-codemirror");
var editor = null;
if (null != codeElement) {
	editor = CodeMirror.fromTextArea(codeElement, {
		lineNumbers: true,
		mode: 'text/x-sql',
		indentWithTabs: true,
		smartIndent: true,
		lineNumbers: true,
		matchBrackets: true,
		autofocus: true,
		readOnly: true
	});
}
