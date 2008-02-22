<?php
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
include_once("../../library/acl.inc");
require_once("language.inc.php");


//START OUT OUR PAGE....
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<span class="title"><?php  xl('Multi Language Tool','e') ?></span>
<div class="text">
<br>

<?php
/* menu */
$sql="SELECT * FROM lang_languages ORDER BY lang_id";
$res=SqlStatement($sql);
$string='|';
while ($row=SqlFetchArray($res)){
	$string.='| <a href=?m=definition&edit='.$row['lang_id'].'>'.$row['lang_description'].'</a> |';
}
$string.='|';

echo ("<a href=\"?m=language&language=add\">".xl('Add Language')."</a> or ");
echo ("<a href=\"?m=constant&constant=add\">".xl('Add Constant')."</a> or ");
echo (xl('Edit definitions').": $string <br><br>");

switch ($_GET['m']):
	case 'definition':
		include_once('lang_definition.php');
	break;
	case 'constant':
		include_once('lang_constant.php');
	break;
		case 'language':
		include_once('lang_language.php');
	break;
endswitch;


?>

<A HREF="lang.info.html"><?php xl('Info','e'); ?></A>
</div>