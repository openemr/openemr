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
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>

<script language='JavaScript'>
function editLang(lang_id) {
 var filter = document.forms[0].form_filter.value;
 window.location = '?m=definition&edit=' + lang_id + '&filter=' + encodeURIComponent(filter);
 return false;
}
</script>
</head>

<body class="body_top">
<form name='filterform' id='filterform' method='get' action='language.php'>
<input type='hidden' name='m' value='<?php echo $_GET['m']; ?>' />
<input type='hidden' name='edit' value='<?php echo $_GET['edit']; ?>' />
<span class="title"><?php  xl('Multi Language Tool','e') ?></span>
<span class='text'>&nbsp;&nbsp;&nbsp;<?php xl('Filter for Constants','e','',':'); ?>
<input type='text' name='form_filter' size='8' value='' />
<?php xl('(% matches any string, _ matches any character)','e'); ?>
</form>
</span>

<div class="text">
<br>

<?php
/* menu */
$sql="SELECT * FROM lang_languages ORDER BY lang_id";
$res=SqlStatement($sql);
$string='|';
while ($row=SqlFetchArray($res)){
  $string .= "| <a href='' onclick='return editLang(" . $row['lang_id'] . ")'>" . xl($row['lang_description']) . "</a> |";
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

<BR><A HREF="lang.info.html" TARGET="_blank"><?php xl('Info','e'); ?></A>
</div>
