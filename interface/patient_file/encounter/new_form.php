<?php
include_once("../../globals.php");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<!-- outdated
<dl>
<dt><span class="title">New Form</span></dt>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=physical";?>" class="link" target=Main>Physical Examination</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=newpatient";?>" class="link" target=Main>New Patient Form</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=sencounter";?>" class="link" target=Main>Small Patient Encounter</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=reviewofs";?>" class="link" target=Main>Review Of Systems</a></dd>
</dl>
-->
<dl>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");
function myGetRegistered ( $state="1", $limit="unlimited", $offset="0")
{
        $sql = "select category, nickname, name, state, directory, id, sql_run, unpackaged, date from registry where state like \"$state\" order by category,priority";
        if ($limit != "unlimited")
                $sql .= " limit $limit, $offset";
        $res = sqlStatement($sql);
        if ($res)
        for($iter=0; $row=sqlFetchArray($res); $iter++)
        {
                $all[$iter] = $row;
        }
        else
                return false;
        return $all;
}
$reg = myGetRegistered ();
$old_category = '';
echo "<FORM METHOD=POST NAME='choose'>\n";
if ($reg != false)
foreach ($reg as $entry) {
	$new_category = trim($entry['category']);
	$new_nickname = trim($entry['nickname']);
	if ($new_category == '') {$new_category = 'miscellaneous';}
	if ($new_nickname != '') {$nickname = $new_nickname;}
	else {$nickname = $entry['name'];}
	if ($old_category != $new_category) {
		$new_category_ = $new_category;
		$new_category_ = str_replace(' ','_',$new_category_);
		if ($old_category != '') {echo "</select>\n";}
		echo "<select name=".$new_category_." onchange='top.frames[\"Main\"].location.href = document.choose." .$new_category_.".options[document.choose." .$new_category_.".selectedIndex].value'>\n";
		echo "<option value=".$new_category_.">".$new_category."</option>\n";

		echo "<option value='".$rootdir.'/patient_file/encounter/load_form.php?formname='.urlencode($entry['directory'])."'>".$nickname."</option>\n";
		$old_category = $new_category;
	}
	else {
		echo "<option value='".$rootdir.'/patient_file/encounter/load_form.php?formname='.urlencode($entry['directory'])."'>".$nickname."</option>\n";
	}
}
echo "</select>\n";
echo "</FORM>\n";
?>
</dl>

</body>
</html>
