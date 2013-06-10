<?php
/**
 * Viewing of office notes.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

include_once("../../globals.php");
include_once("$srcdir/onotes.inc");

//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 10;
?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<div id="officenotes_list">
<a href="office_comments_full.php" <?php if (!$GLOBALS['concurrent_layout']) echo 'target="Main"'; ?>>
<font class="title"><?php echo xlt('Office Notes'); ?></font>
<font class="more"><?php echo text($tmore);?></font></a>

<br>

<table border=0 width=100%>

<?php

//retrieve all active notes
if($result = getOnoteByDate("", 1, "date,body,user","all",0)) {

$notes_count = 0;//number of notes so far displayed
foreach ($result as $iter) {
    if ($notes_count >= $N) {
        //we have more active notes to print, but we've reached our display maximum (defined at top of this file)
        print "<tr><td colspan=3 align=center><a target=Main href='office_comments_full.php?active=1' class='alert'>".xlt("Some office notes were not displayed. Click here to view all.")."</a></td></tr>\n";
        break;
    }
    
    
    if (getdate() == strtotime($iter{"date"})) {
        $date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
    } else {
        $date_string = date( "D F dS" ,strtotime($iter{"date"}));
    }
    
    print "<tr><td width=20% valign=top><font class='bold'>".text($date_string)."</font> <font class='bold'>(".text($iter{"user"}).")</font><br>" . "<font class='text'>" . text($iter{"body"}) . "</font></td></tr>\n";
    
    
    $notes_count++;
}

}
?>

</table>
</div>

</body>
</html>
