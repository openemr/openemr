<?php
/*	
	batch list processor, included from batchcom 
*/

// create a list for phone calls
// menu for fields could be added in the future

?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="batchcom.css" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<span class="title">Batch Communication Tool</span>
<br><br>

<?

echo ("<table>"); //will do css

echo ("<tr><td>Name </td>");
echo ("<td>DOB </td>");
echo ("<td>Home</td>");
echo ("<td>Work </td>");
echo ("<td>Contact </td>");
echo ("<td>$Cell </td></tr>\n");


while ($row=sqlFetchArray($res)) {

	echo ("<tr><td>${row['title']} ");
	echo ("${row['fname']} ");
	echo ("${row['lname']} </td>");
	echo ("<td>${row['DOB']} </td>");
	echo ("<td>${row['phone_home']} </td>");
	echo ("<td>${row['phone_biz']} </td>");
	echo ("<td>${row['phone_contact']} </td>");
	echo ("<td>${row['phone_cell']} </td></tr>\n");
}

echo ("</table>"); 

?>