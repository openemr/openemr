<?php
//CLICKOPTIONS by Mark Leeds 2005, see clickoptions.txt in openemr/custom/ directory for more info
if (count($clickoptions_screened) > 0)
{
	echo "<select name='myscroll' size='4' onchange='set_text()'>";
	foreach ($clickoptions_screened as $val)
	{
		rtrim($val);
		echo "<option value='$val'>$val</option>\n";
	}
	echo "</select> <br />";
}
?>
