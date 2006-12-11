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
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_note", $_GET["id"]);

?>

<form method=post action="<?echo $rootdir?>/forms/note/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Work/School Note</span><br></br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link">[Don't Save Changes]</a>
</br>

<tr>
<td>
<input type=entry name="note_type" value="<?echo
stripslashes($obj{"note_type"});?>" size="50"> 
</td>
</tr>

<span class="text">Message:</span></br>
<textarea name="message" cols ="67" rows="4"  wrap="virtual name">
<?echo stripslashes($obj{"message"});?></textarea>
<br></br>

<span class=text>Doctor: </span><input type=entry name="doctor" value="<?echo stripslashes($obj{"doctor"});?>">
<br></br>


<span class=text>Date: </span><input type=entry name="date_of_signature" value="<?echo stripslashes($obj{"date_of_signature"});?>" >
<br></br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
