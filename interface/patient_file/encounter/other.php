<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");

//the number of rows to display before resetting and starting a new column:
$N=10
?>

<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?php echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>

<td valign=top>

<dl>

<form method='post' name='other_form' action="diagnosis.php?mode=add&type=OTHER"
 target='Diagnosis' onsubmit='return top.restoreSession()'>

<dt><span class=title><?php xl('Other','e'); ?></span></dt>

<br>
<table>
<tr>
<td class="text"><?php xl('code','e'); ?></td>
<td class="text"><?php xl('description','e'); ?></td>
<td class="text">&nbsp;&nbsp;&nbsp;<?php xl('fee','e'); ?></td>
<td></td>
</tr>
<tr>
  <td class="text"><input type=entry name=code size=4>&nbsp;&nbsp;</td>
  <td class="text"><input type=entry name=text size=15>&nbsp;&nbsp;</td>
  <td class="text">$ </span><input type=entry name=fee size=5></td>
  <td>&nbsp;<a class='text' href="javascript:top.restoreSession();document.other_form.submit();">
   <?php xl('Save','e'); ?></a>
  </td>
</tr>
</table>

</form>

</dl>

</td>
</tr>
</table>

</body>
</html>
