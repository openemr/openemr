<html>
<head>
<title>Form Plugin Test</title>
<style>
body, table, tr, th, td {
	font-family: Verdana;
	font-size: 9pt;
	background-color: aliceblue;
}

div.Savant-Form {
	margin: 8px;
}

fieldset.Savant-Form {
	margin: 8px;
	border-top: 1px solid silver;
	border-left: 1px solid silver;
	border-bottom: 1px solid gray;
	border-right: 1px solid gray;
	padding: 4px;
}

legend.Savant-Form {
	padding: 2px 4px;
	color: #036;
	font-weight: bold;
	font-size: 120%;
}

table.Savant-Form {
	border-spacing: 0px;
	margin: 0px;
	spacing: 0px;
	padding: 0px;
}

tr.Savant-Form {
	
}

th.Savant-Form {
	padding: 4px;
	spacing: 0px;
	border: 0px;
	text-align: right;
	vertical-align: top;
}

td.Savant-Form {
	padding: 4px;
	spacing: 0px;
	border: 0px;
	text-align: left;
	vertical-align: top;
}

label.Savant-Form {
	font-weight: bold;
}

input[type="text"] {
	font-family: monospace;
	font-size: 9pt;
}

textarea {
	font-family: monospace;
	font-size: 9pt;
}
</style>
</head>
<body>
		<?php
		// start a form
		$this->form ( 'set', 'class', 'Savant-Form' );
		
		echo $this->form ( 'start' );
		
		// add a hidden value before the layout
		echo $this->form ( 'hidden', 'hideme', 'hidden & valued' );
		
		// start a block
		echo $this->form ( 'block', 'start', 'First Section', 'row' );
		
		// text field
		// type, name, value, label, attribs, require, message
		echo $this->form ( 'text', 'mytext', $this->mytext, 'Enter some text here:', array (
				'size' => '20' 
		), true, $this->valid ['mytext'] );
		
		// checkbox with default value (array(checked, not-checked))
		echo $this->form ( 'checkbox', 'xbox', $this->xbox, 'Check this:', array (
				1,
				0 
		), 'style="text-align: center;"' );
		
		// single select
		echo $this->form ( 'select', 'picker', $this->picker, 'Pick one:', $this->opts );
		
		// NEW BLOCK
		echo $this->form ( 'block', 'start', "Second Section", 'row' );
		
		// multi-select with note
		echo $this->form ( 'group', 'start', 'Pick many:' );
		echo $this->form ( 'select', 'picker2', $this->picker2, 'Pick many:', $this->opts, 'multiple="multiple"' );
		echo $this->form ( 'note', "<br />Pick as many as you like; use the Ctrl key on Windows, or the Cmd key on Macintosh." );
		echo $this->form ( 'group', 'end' );
		
		// radio buttons
		echo $this->form ( 'radio', 'chooser', $this->chooser, 'Choose one:', $this->opts );
		
		// NEW BLOCK
		echo $this->form ( 'block', 'start', null, 'row' );
		
		// text area
		echo $this->form ( 'textarea', 'myarea', $this->myarea, 'Long text:', array (
				'rows' => 12,
				'cols' => 40 
		) );
		echo $this->form ( 'block', 'end' );
		
		// NEW BLOCK (clears floats)
		echo $this->form ( 'block', 'start', null, 'row' );
		echo $this->form ( 'submit', 'op', 'Save' );
		echo $this->form ( 'reset', 'op', 'Reset' );
		echo $this->form ( 'button', '', 'Click Me!', null, array (
				'onClick' => 'return alert("hello!")' 
		) );
		
		// end the form
		echo $this->form ( 'block', 'end' );
		echo $this->form ( 'note', '<span style="color: red;">* Indicates a required field.</span>' );
		echo $this->form ( 'end' );
		?>
	</body>
</html>


