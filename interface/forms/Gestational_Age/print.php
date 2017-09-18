<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Gestational_Age");
?>
<html><head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/Gestational_Age/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
<h1> Gestational_Age </h1>
<hr>
<input type="submit" name="submit form" value="submit form" /><br>
<br>
<h3>Dates</h3>

<table>

<tr><td>
<span class='text'><?php xl('Lmp (yyyy-mm-dd): ','e') ?></span>
</td><td>
<input type='text' size='10' name='lmp' id='lmp' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event' />
<img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
id='img_lmp' border='0' alt='[?]' style='cursor:pointer'
title='Click here to choose a date'>
<script>
Calendar.setup({inputField:'lmp', ifFormat:'%Y-%m-%d', button:'img_lmp'});
</script>
</td></tr>

</table>

<table>

<tr><td>
<span class='text'><?php xl('Edc (yyyy-mm-dd): ','e') ?></span>
</td><td>
<input type='text' size='10' name='edc' id='edc' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event' />
<img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
id='img_edc' border='0' alt='[?]' style='cursor:pointer'
title='Click here to choose a date'>
<script>
Calendar.setup({inputField:'edc', ifFormat:'%Y-%m-%d', button:'img_edc'});
</script>
</td></tr>

</table>
<br>
<h3>Gestational Age</h3>

<table>

<tr><td>Weeks</td> <td><input type="text" name="weeks"  /></td></tr>

</table>

<table>

<tr><td>Days</td> <td><input type="text" name="days"  /></td></tr>

</table>
<table></table><input type="submit" name="submit form" value="submit form" />
</form>
<?php
formFooter();
?>
