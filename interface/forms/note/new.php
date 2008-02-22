<!-- Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30 
     Copyright (C) Open Source Medical Software 

     This program is free software; you can redistribute it and/or
     modify it under the terms of the GNU General Public License
     as published by the Free Software Foundation; either version 2
     of the License, or (at your option) any later version.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. -->

<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: note");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>

<html><head>
<? html_header_show();?>
<SCRIPT language="JavaScript"><!--
/******************************************
   Today's Date           
*******************************************/

Style = 1; //pick a style from below

/*------------------------------
Style 1: March 17, 2000
Style 2: Mar 17, 2000
Style 3: Saturday March 17, 2000
Style 4: Sat Mar 17, 2000
Style 5: Sat March 17, 2000
Style 6: 17 March 2000
Style 7: 17 Mar 2000
Style 8: 17 Mar 00
Style 9: 3/17/00
Style 10: 3-17-00
Style 11: Saturday March 17
--------------------------------*/

months = new Array();
months[1] = "January";  months[7] = "July";
months[2] = "February"; months[8] = "August";
months[3] = "March";    months[9] = "September";
months[4] = "April";    months[10] = "October";
months[5] = "May";      months[11] = "November";
months[6] = "June";     months[12] = "December";

months2 = new Array();
months2[1] = "Jan"; months2[7] = "Jul";
months2[2] = "Feb"; months2[8] = "Aug";
months2[3] = "Mar"; months2[9] = "Sep";
months2[4] = "Apr"; months2[10] = "Oct";
months2[5] = "May"; months2[11] = "Nov";
months2[6] = "Jun"; months2[12] = "Dec";

days = new Array();
days[1] = "Sunday";    days[5] = "Thursday";
days[2] = "Monday";    days[6] = "Friday";
days[3] = "Tuesday";   days[7] = "Saturday";
days[4] = "Wednesday";

days2 = new Array();
days2[1] = "Sun"; days2[5] = "Thu";
days2[2] = "Mon"; days2[6] = "Fri";
days2[3] = "Tue"; days2[7] = "Sat";
days2[4] = "Wed";

todaysdate = new Date();
date  = todaysdate.getDate();
day  = todaysdate.getDay() + 1;
month = todaysdate.getMonth() + 1;
yy = todaysdate.getYear();
year = (yy < 1000) ? yy + 1900 : yy;
year2 = 2000 - year; year2 = (year2 < 10) ? "0" + year2 : year2;

dateline = new Array();
dateline[1] = months[month] + " " + date + ", " + year;
dateline[2] = months2[month] + " " + date + ", " + year;
dateline[3] = days[day] + " " + months[month] + " " + date + ", " + year;
dateline[4] = days2[day] + " " + months2[month] + " " + date + ", " + year;
dateline[5] = days2[day] + " " + months[month] + " " + date + ", " + year;
dateline[6] = date + " " + months[month] + " " + year;
dateline[7] = date + " " + months2[month] + " " + year;
dateline[8] = date + " " + months2[month] + " " + year2;
dateline[9] = month + "/" + date + "/" + year2;
dateline[10] = month + "-" + date + "-" + year2;
dateline[11] = days[day] + " " + months[month] + " " + date;
document.write(dateline[Style]);

//--></SCRIPT>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>


<body <?echo $top_bg_line;?>
topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/note/save.php?mode=new" name="my_form">
<span class="title"><?php xl('Work/School Note','e'); ?></span><br></br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 style="color: #483D8B" onclick="top.restoreSession()">[<?php xl('Don\'t Save','e'); ?>]</a>
<br></br>

<?
//print "$name";
//print "Date:<input type=entry name=$date value=".date("Y-m-d")."> ";
?>

<tr>
  <td>
   <select name="note_type">
     <option value="WORK NOTE"><?php xl('WORK NOTE','e'); ?></option>
     <option value="SCHOOL NOTE"><?php xl('SCHOOL NOTE','e'); ?></option>
   <br></select>
   </td>
</tr>

<tr>
<td colspan="2" bgcolor="#ffffff"></td>
</tr>
<tr align="left" valign="top">
<td colspan="2" bgcolor="#e0e0e0"><b><?php xl('MESSAGE:','e'); ?></b>
</td>
</tr>
<br>
<tr><td align="right"></td>
<td><textarea name="message" rows="7" cols="47" wrap="virtual name"></textarea></td>
</tr>
<br></br>

<tr>
<td colspan="2" bgcolor="#ffffff"></td>
</tr>
<tr align="left" valign="top">
<td colspan="2" bgcolor="#e0e0e0"><b><?php xl('Signature:','e'); ?></b>
</td>
</tr>
<br>

<tr>
 <td align="right"><?php xl('Doctor:','e'); ?></td>
 <td>
   <select name="doctor">
      <option value="Dr. #1">Dr. #1</option>
      <option value="Dr. #2">Dr. #2</option>
      <option value="Dr. #3">Dr. #3</option>
    <br><br>
   </select>
  </td>
</tr>
<br><br>

<?
//global $date;
//$date = date("Y-m-d");
//print "Date:$encounter";
//print "Date:<input type=entry name=$date value=".date("Y-m-d")."> ";
?>


<span class="text"><?php xl('Date','e'); ?></span><input type="entry" name="date_of_signature" 
value="<?php echo date("Y-m-d") ?>">
</input>
<br></br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 style="color: #483D8B" onclick="top.restoreSession()">[<?php xl('Don\'t Save','e'); ?>]</a>
</form>
<?php
formFooter();
?>
