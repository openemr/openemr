<?php
require_once("language.inc.php");


if ($_POST['add']){
	//validate	
	if ($_POST['constant_name'] == "") {
	        echo htmlspecialchars(xl('Constant name is blank'),ENT_NOQUOTES).'<br>';
	        $err='y';
	}
	$sql="SELECT * FROM lang_constants WHERE constant_name=? limit 1" ;
	$res=SqlQuery($sql, array($_POST['constant_name']) );
	if ( $res ) {
		echo htmlspecialchars(xl('Data Alike is already in database, please change constant name'),ENT_NOQUOTES).'<br>';
		$err='y';
	}
	if ($err=='y'){
		$val_constant=$_POST['constant_name'];
	} else {
	        //insert into the main table
		$sql="INSERT INTO lang_constants SET constant_name=?"; 
		SqlStatement($sql, array($_POST['constant_name']) );
	    
                //insert into the log table - to allow persistant customizations
	      	insert_language_log('','',$_POST['constant_name'],'');
	    
		echo htmlspecialchars(xl('Constant','','',' ') . $_POST['constant_name'] . xl('added','',' '),ENT_NOQUOTES).'<br>';
	}
	


// echo "$sql here ";
}

?>

<TABLE>
<FORM name="cons_form" METHOD=POST ACTION="?m=constant" onsubmit="return top.restoreSession()">
<TR>
	<TD><? echo htmlspecialchars(xl('constant name'),ENT_NOQUOTES); ?></TD>
	<TD><INPUT TYPE="text" NAME="constant_name" size="100" value="<? echo htmlspecialchars($val_constant,ENT_QUOTES); ?>"></TD>
</TR>
<TR>
	<TD></TD>
	<TD><INPUT TYPE="submit" name="add" value="<?php echo htmlspecialchars(xl('Add'),ENT_QUOTES); ?>"></TD>
</TR>
</FORM>
</TABLE>
<span class="text"><?php echo htmlspecialchars(xl('Please Note: constants are case sensitive and any string is allowed.'),ENT_NOQUOTES); ?></span>
