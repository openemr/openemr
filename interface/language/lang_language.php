<?php

if ($_POST['add']){
	//validate	
	$pat="^[a-z]{2}\$";
	if (!check_pattern ($_POST['lang_code'],$pat)) {
		echo htmlspecialchars(xl("Code must be two letter lowercase"),ENT_NOQUOTES).'<br>';
		$err='y';
	}

	$sql="SELECT * FROM lang_languages WHERE lang_code LIKE ? or lang_description LIKE ? limit 1" ;
	$res=SqlQuery($sql, array("%".$_POST['lang_code']."%","%".$_POST['lang_name']) );
	if ( $res ) {
		echo htmlspecialchars(xl("Data Alike is already in database, please change code and/or description"),ENT_NOQUOTES).'<br>';
		$err='y';
	}
	if ($err=='y'){
		$val_lang_code=$_POST['lang_code'];
		$val_lang_name=$_POST['lang_name'];
	} else {
	        //insert into the main table
		$sql="INSERT INTO lang_languages SET lang_code=?, lang_description=?";
		SqlStatement($sql, array($_POST['lang_code'],$_POST['lang_name']) );
	        
		//insert into the log table - to allow persistant customizations
		insert_language_log($_POST['lang_name'],$_POST['lang_code'],'','');
	    
	        echo htmlspecialchars(xl('Language definition added'),ENT_NOQUOTES).'<br>';
	}
}

?>

<TABLE>
<FORM name="lang_form" METHOD=POST ACTION="?m=language" onsubmit="return top.restoreSession()">
<TR>
	<TD><?php  echo htmlspecialchars(xl('Language Code'),ENT_NOQUOTES); ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_code" size="2" maxlength="2" value="<?php echo htmlspecialchars($val_lang_code,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD><?php  echo htmlspecialchars(xl('Language Name'),ENT_NOQUOTES); ?>:</TD>
	<TD><INPUT TYPE="text" NAME="lang_name" size="24" value="<?php echo htmlspecialchars($val_lang_name,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD></TD>
	<TD><INPUT TYPE="submit" name="add" value="<?php echo htmlspecialchars(xl('Add'),ENT_QUOTES); ?>"></TD>
</TR>
</FORM>
</TABLE>
