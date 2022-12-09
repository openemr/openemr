<?php
if(strtolower($dt{'form_complete'}) == 'a') {
	echo $content;
} else  {
	include($GLOBALS['incdir']."/forms/$frmdir/common_view.php");
}
?>
