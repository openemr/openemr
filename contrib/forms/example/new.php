<?
include("../../../library/api.inc");
//use the suggested header
formHeader("newerpatient form");
?>

<--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->
<form method=post action="./save.php" name='newerpatient' target=Main>
<span class=title>Newer Patient Encounter Form</span>
<br>

<span class=text>Why this new visit?</span><br>
<textarea name=reason cols=40 rows=6 wrap=virtual></textarea>

<br>

<span class=text>do you like cats?</span> <input type=text name=cats>

<br>

<--REM note our nifty jscript submit
<a href="javascript:document.newerpatient.submit();" class="link_submit">[Save]</a>
<br>
</form>


<br><br>
<hr>

<?php
formFooter();
?>
