<?php
if(!isset($content)) $content = '';
if(strtolower($dt{'form_complete'}) == 'a' && !$create && $content) {
	echo $content;
} else  {
	include($GLOBALS['incdir']."/forms/$frmdir/common_view.php");
}
?>
