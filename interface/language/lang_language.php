<?php
require_once("language.inc.php");

if ($_POST['add']){
	//validate	
	$pat="^[a-z]{2}\$";
	if (!check_pattern (strip_escape_custom($_POST['lang_code']),$pat)) {
		xl ("Code must be two letter lowercase",'e','','<br>');
		$err='y';
	}

	$sql="SELECT * FROM lang_languages WHERE lang_code LIKE '%".formData('lang_code')."%' or lang_description LIKE '%".formData('lang_name')."' limit 1" ;
	$res=SqlQuery($sql);
	if ( $res ) {
		xl ("Data Alike is already in database, please change code and/or description",'e','','<br>');
		$err='y';
	}
	if ($err=='y'){
		$val_lang_code=strip_escape_custom($_POST['lang_code']);
		$val_lang_name=strip_escape_custom($_POST['lang_name']);
	} else {
		$sql="INSERT INTO lang_languages SET lang_code='".formData('lang_code')."', lang_description='".formData('lang_name')."'";
		SqlStatement ($sql);
		xl('Language definition added','e','','<br>');
	}
}

?>

<TABLE>
<FORM name="lang_form" METHOD=POST ACTION="?m=language" onsubmit="return top.restoreSession()">
<TR>
	<TD><?php  xl('Language Code','e') ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_code" size="2" maxlength="2" value="<?php echo htmlspecialchars($val_lang_code,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD><?php  xl('Language Name','e') ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_name" size="24" value="<?php echo htmlspecialchars($val_lang_name,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD></TD>
	<TD><INPUT TYPE="submit" name="add" value="<?php xl('Add','e'); ?>"></TD>
</TR>
</FORM>
</TABLE>
