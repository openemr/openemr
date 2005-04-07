<!-- program was updated by Nikolai Vitsyn: 2004/02/13 -->

<?
include_once("../../globals.php");
include_once("$srcdir/lists.inc");
?>

<html>

<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>


<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<table ><tr>
<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Medical Problems</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "medical_problem", "id,title,comments", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . "</a><br>\n";
	}
}
?>
</td>


<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Medications</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "medication", "id,title", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . "</a><br>\n";
	}
}
?>
</td>

<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Allergies</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "allergy", "id,title", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . "</a><br>\n";
	}
}

?>
</td>

<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Surgeries</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "surgery", "id,title,comments", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . "</a><br>\n";
	}
}

?>
</td>

<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Immunizations</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "immunization", "id,title,comments", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . ": ".strterm($iter{"comments"},20)."</a><br>\n";
	}	
}

?>
</td>

<td width="20%" valid="top"
<?
$cwd= getcwd();
chdir("../../../");
require_once("library/classes/Controller.class.php");
$c = new Controller();
echo '<font class="title">Prescriptions</font>';
echo $c->act(array("prescription" => "", "block" => "", "patient_id" => $pid));

?>
</td>




</tr>
</table>




</body>
</html>

