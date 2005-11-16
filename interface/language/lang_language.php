<?php
require_once("language.inc.php");


if ($_POST['Submit']=='Add'){
	//validate	
	$pat="^[a-z]{2}\$";
	if (!check_pattern ($_POST['lang_code'],$pat)) {
		xl ("Code must be two letter lowercase",'e','','<br>');
		$err='y';
	}
/*	
	$pat="^[a-Z]+\$";
	if (!check_pattern ($_POST['lang_name'],$pat)) {
		echo ("Only letters accepted for description<br>");
		$err='y';
	}
*/
	$sql="SELECT * FROM lang_languages WHERE lang_code LIKE '%".$_POST['lang_code']."%' or lang_description LIKE '%".$_POST['lang_name']."' limit 1" ;
	$res=SqlQuery($sql);
	if ( $res ) {
		xl ("Data Alike is already in database, please change code and/or description",'e','','<br>');
		$err='y';
	}
	if ($err=='y'){
		$val_lang_code=$_POST['lang_code'];
		$val_lang_name=$_POST['lang_name'];
	} else {
		$sql="INSERT INTO lang_languages SET lang_code='".$_POST['lang_code']."', lang_description='".$_POST['lang_name']."'";
		SqlStatement ($sql);
		xl('Language definition added','e','','<br>');
	}
}

?>

<TABLE>
<FORM name="lang_form" METHOD=POST ACTION="?m=language">
<TR>
	<TD><? xl('Language Code','e') ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_code" size="2" maxlength="2" value="<? echo $val_lang_code; ?>"></TD>
</TR>
<TR>
	<TD><? xl('Language Name','e') ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_name" size="24" value="<? echo $val_lang_name; ?>"></TD>
</TR>
<TR>
	<TD></TD>
	<TD><INPUT TYPE="submit" name="Submit" value="Add"></TD>
</TR>
</FORM>
</TABLE>
