<?php /* Smarty version 2.6.30, created on 2017-08-13 22:07:59
         compiled from /Users/alfiecarlisle/Documents/openemr/templates/prescription/general_lookup.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_lookup.html', 51, false),array('function', 'xl', '/Users/alfiecarlisle/Documents/openemr/templates/prescription/general_lookup.html', 56, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];  ?>" type="text/css">
<script language="Javascript">
<?php echo '
		function my_process () {
			// Pass the variable
'; ?>

			parent.iframetopardiv(document.lookup.drug.value);
			parent.opener.document.prescribe.drug.value = document.lookup.drug.value;
			// Close the window
			window.self.close();
<?php echo '
		}
'; ?>

</script>
<?php echo '
 <style type="text/css" title="mystyles" media="all">
<!--
td {
	font-size:8pt;
	font-family:helvetica;
}
input {
	font-size:8pt;
	font-family:helvetica;
}
select {
	font-size:8pt;
	font-family:helvetica;
}
a {
	font-size:8pt;
	font-family:helvetica;
}
textarea {
	font-size:8pt;
	font-family:helvetica;
}
-->
</style>
'; ?>

</head>
<body onload="javascript:document.lookup.drug.focus();">
<div style="" class="drug_lookup" id="newlistitem">
	<form NAME="lookup" ACTION="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" METHOD="POST" onsubmit="return top.restoreSession()" style="margin:0px">

	<?php if ($this->_tpl_vars['drug_options']): ?>
        <div>
        <?php echo smarty_function_html_options(array('name' => 'drug','values' => $this->_tpl_vars['drug_values'],'options' => $this->_tpl_vars['drug_options']), $this);?>
<br/>
        </div>
        <div>
            <a href="javascript:;" onClick="my_process(); return true;">Select</a> |
            <a href="javascript:;" class="button" onClick="parent.cancelParlookup();">Cancel</a> |
            <a href="<?php echo $this->_tpl_vars['CONTROLLER_THIS']; ?>
" onclick="top.restoreSession()"><?php echo smarty_function_xl(array('t' => 'New Search'), $this);?>
</a>
        </div>
	<?php else: ?>
		<?php echo $this->_tpl_vars['NO_RESULTS']; ?>


		<input TYPE="HIDDEN" NAME="varname" VALUE=""/>
		<input TYPE="HIDDEN" NAME="formname" VALUE=""/>
		<input TYPE="HIDDEN" NAME="submitname" VALUE=""/>
		<input TYPE="HIDDEN" NAME="action" VALUE="<?php echo smarty_function_xl(array('t' => 'Search'), $this);?>
">
		<div ALIGN="CENTER" CLASS="infobox">
			<input TYPE="TEXT" NAME="drug" VALUE="<?php echo $this->_tpl_vars['drug']; ?>
"/>
			<input TYPE="SUBMIT" NAME="action" VALUE="<?php echo smarty_function_xl(array('t' => 'Search'), $this);?>
" class="button"/>
			<input TYPE="BUTTON" VALUE="<?php echo smarty_function_xl(array('t' => 'Cancel'), $this);?>
" class="button" onClick="parent.cancelParlookup();"/>
		</div>
		<input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />

	<?php endif; ?></form>
	</div>
</body>
</html>