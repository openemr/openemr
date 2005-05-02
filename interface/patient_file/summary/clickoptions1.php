<?php
//CLICKOPTIONS by Mark Leeds 2005, see clickoptions.txt in openemr/custom/ directory for more info
if (is_file("../../../custom/clickoptions.txt")) 
{	
	$showselect = "true";
	$clickoptions = file("../../../custom/clickoptions.txt");
	$clickoptions_screened = array();
	foreach($clickoptions as $var)
	{
		rtrim($var);
		if (substr($var,0,1) != "#") 
		{
			if (strpos($var, $clickoptions_category) !== false)
			{
				array_push($clickoptions_screened,substr($var,strpos($var,"::")+2,strlen($var)-1-strpos($var,"::")+2 ) );
			}
			
		}
	}
}
echo "<script>";
echo "function set_text(){document.new_note.title.value = document.new_note.myscroll.options[document.new_note.myscroll.selectedIndex].text;}";
echo "</script>";
?>
