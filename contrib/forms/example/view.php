<?
include("../../../library/api.inc");
formHeader("View my form");
?>

<form method=post action=save.php name=new_encounter>
<input type=hidden name=id value='<?echo $_GET["id"];?>'>
<span class=title>New Patient Encounter Form</span>
<br>

<span class=text>Reason for Visit:</span><br>
<textarea name=reason cols=40 rows=6 wrap=virtual><?
$result = formFetch('newer_patient', "7");

echo $result["reason"];

?></textarea>

<br>
<?php
echo "<input type='text' name='cats' value='{$result['cats']}'>";
?>
<br>
<a href="javascript:top.restoreSession();document.new_encounter.submit();" class="link_submit">[Save]</a>
<br>
</form>

<?php
formFooter();
?>
