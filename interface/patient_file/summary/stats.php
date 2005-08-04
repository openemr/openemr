<?
 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/acl.inc");
?>
<html>

<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.link {
 font-family: sans-serif;
 text-decoration: none;
 color: #000000;
 font-size: 8pt;
}
</style>
</head>

<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<?
 $thisauth = acl_check('patients', 'med');
 if (!$thisauth) {
  echo "<p>(Issues not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

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
</tr>
<tr>

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
</tr>
<tr>
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
</tr>
<tr>
<td width="20%" valign="top">
<a href="stats_full.php?active=all" target="Main"><font class="title">Surgeries</font><font class=more><?echo $tmore;?></font></a><br>
<?
if ($result = getListByType($pid, "surgery", "id,title,comments", 1, "all", 0)){
	foreach ($result as $iter) {
		echo "<a class=link target=Main href='stats_full.php?active=1'>" . $iter{"title"} . ": ".strterm($iter{"comments"},20)."</a><br>\n";
	}	
}

?>
</td>
</tr>
<tr>
<td width="20%" valign="top">
<a href="immunizations.php" target="Main"><font class="title">Immunizations</font><font class=more><?echo $tmore;?></font></a><br>
<?$sql = "select if(i1.administered_date
                      ,concat(i1.administered_date,' - ',i2.name)
                      ,substring(i1.note,1,20)
                      ) as immunization_data
            from immunizations i1
                   left join immunization i2
                     on i1.immunization_id = i2.id
           where i1.patient_id = $pid
          order by administered_date desc";

$result = sqlStatement($sql);
 
while ($row=sqlFetchArray($result)){
  echo "<a class=link target=Main href='immunizations.php'>" . $row{'immunization_data'} . "</a><br>\n";
}
?>
</td>
</tr>
<tr>
<td width="20%" valign="top">
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
