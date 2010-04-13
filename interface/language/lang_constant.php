<?php
require_once("language.inc.php");


if ($_POST['add']){
	//validate	
	if (strip_escape_custom($_POST['constant_name']) == "") {
	        echo xl('Constant name is blank','','','<br>');
	        $err='y';
	}
	$sql="SELECT * FROM lang_constants WHERE constant_name='".formData('constant_name')."' limit 1" ;
	$res=SqlQuery($sql);
	if ( $res ) {
		echo xl('Data Alike is already in database, please change constant name','','','<br>');
		$err='y';
	}
	if ($err=='y'){
		$val_constant=strip_escape_custom($_POST['constant_name']);
	} else {
	        //insert into the main table
		$sql="INSERT INTO lang_constants SET constant_name='".formData('constant_name')."'"; 
		SqlStatement ($sql);
	    
                //insert into the log table - to allow persistant customizations
	      	insert_language_log('','',formData('constant_name'),'');
	    
		echo xl('Constant','','',' ') . strip_escape_custom($_POST['constant_name']) . xl('added','',' ','<br>');
	}
	


// echo "$sql here ";
}

?>

<TABLE>
<FORM name="cons_form" METHOD=POST ACTION="?m=constant" onsubmit="return top.restoreSession()">
<TR>
	<TD><? xl ('constant name','e'); ?></TD>
	<TD><INPUT TYPE="text" NAME="constant_name" size="100" value="<? echo htmlspecialchars($val_constant,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD></TD>
	<TD><INPUT TYPE="submit" name="add" value="<?php xl('Add','e'); ?>"></TD>
</TR>
</FORM>
</TABLE>
<?php xl('Please Note: constants are case sensitive and any string is allowed.','e'); ?>
