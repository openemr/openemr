<?php



//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("../../library/acl.inc");
require_once("language.inc.php");

//START OUT OUR PAGE....
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">
<form name='translation' id='translation' method='get' action='language.php' onsubmit="return top.restoreSession()">
<input type='hidden' name='m' value='<?php echo htmlspecialchars($_GET['m'], ENT_QUOTES); ?>' />
<input type='hidden' name='edit' value='<?php echo htmlspecialchars($_GET['edit'], ENT_QUOTES); ?>' />
<span class="title"><?php echo htmlspecialchars(xl('Multi Language Tool'), ENT_NOQUOTES); ?></span>
<table>
 <tr>
  <td class="small" colspan='4'>
   <a href="?m=definition" onclick="top.restoreSession()"><?php echo htmlspecialchars(xl('Edit Definitions'), ENT_NOQUOTES); ?></a> |
   <a href="?m=language" onclick="top.restoreSession()"><?php echo htmlspecialchars(xl('Add Language'), ENT_NOQUOTES); ?></a> |
   <a href="?m=constant" onclick="top.restoreSession()"><?php echo htmlspecialchars(xl('Add Constant'), ENT_NOQUOTES); ?></a> |
   <a href="?m=manage" onclick="top.restoreSession()"><?php echo htmlspecialchars(xl('Manage Translations'), ENT_NOQUOTES); ?></a>
  </td>
 </tr>
</table>
</form>

<?php
switch ($_GET['m']) :
    case 'definition':
        include_once('lang_definition.php');
        break;
    case 'constant':
        include_once('lang_constant.php');
        break;
    case 'language':
        include_once('lang_language.php');
        break;
    case 'manage':
        include_once('lang_manage.php');
        break;
endswitch;
?>

<BR><A HREF="lang.info.html" TARGET="_blank"><?php echo htmlspecialchars(xl('Info'), ENT_NOQUOTES); ?></A>
</body>
</html>
