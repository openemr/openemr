<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Endometriosis_Serology");
?>
<html><head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/Endometriosis_Serology/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
<h1> Endometriosis Serology </h1>
<hr>
<input type="submit" name="submit form" value="submit form" /><br>
<br>
<h3>Lab Analysis</h3>
<br>
<h4>Serology</h4>

<table>

<tr><td>Serum il 1beta</td> <td><input type="text" name="serum_il_1beta"  /></td></tr>

</table>

<table>

<tr><td>Serum il 6</td> <td><input type="text" name="serum_il_6"  /></td></tr>

</table>

<table>

<tr><td>Serum tnf alpha</td> <td><input type="text" name="serum_tnf_alpha"  /></td></tr>

</table>
<br>
<h4>Risk of Endometriosis</h4>

<table>

<tr><td>Probability of endometriosis</td> <td><input type="text" name="probability_of_endometriosis"  /></td></tr>

</table>
<table></table><input type="submit" name="submit form" value="submit form" />
</form>
<?php
formFooter();
?>
