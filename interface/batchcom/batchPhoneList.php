<?php
/*    
    batch list processor, included from batchcom 
*/

// create a list for phone calls
// menu for fields could be added in the future

?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
</head>
<body class="body_top">
<span class="title"><?php xl('Batch Communication Tool','e')?></span>
<br><br>
<span class="pclist" ><?php xl('Phone Call List report','e')?></span>
<br><br>

<?php

echo ("<table class='batchcom'>"); //will do css

echo ("<thead bgcolor='#ECECEC'><th width='22%'>".xl('Name') ."</th>");
echo ("<th align='Center' width='15%'>".xl('DOB') ."</th>");
echo ("<th align='Center' width='15%'>".xl('Home')."</th>");
echo ("<th align='Center' width='15%'>".xl('Work') ."</th>");
echo ("<th align='Center' width='15%'>".xl('Contact') ."</th>");
echo ("<th align='Center' width='15%'>".xl('Cell') ."</th></thead>\n");

while ($row=sqlFetchArray($res)) {

    echo ("<tr><td width='22%'>${row['title']} ");
    echo ("${row['fname']} ");
    echo ("${row['lname']} </td>");
    echo ("<td align='Center'>${row['DOB']} </td>");
    echo ("<td align='right'>${row['phone_home']} </td>");
    echo ("<td align='right'>${row['phone_biz']} </td>");
    echo ("<td align='right'>${row['phone_contact']} </td>");
    echo ("<td align='right'>${row['phone_cell']} </td></tr>\n");
}

echo ("</table>"); 

?>
