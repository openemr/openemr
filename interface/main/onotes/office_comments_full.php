<?php
/**
 * Viewing and modification/creation of office notes.
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

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once("$srcdir/onotes.inc");

//the number of records to display per screen
$N = 10;

$offset = (isset($_REQUEST['offset'])) ? $_REQUEST['offset'] : 0;
$active = (isset($_REQUEST['active'])) ? $_REQUEST['active'] : "all";

//this code handles changing the state of activity tags when the user updates them through the interface
if (isset($_POST['mode'])) {
    if ($_POST['mode'] == "update") {
        foreach ($_POST as $var => $val) {
            if ($val == "true" || $val == "false") {
                $id = str_replace("act","",$var);
                if ($val == "true") {reappearOnote($id);}elseif($val=="false"){disappearOnote($id);};
            }
        }
    } elseif ($_POST['mode'] == "new") {
        addOnote($_POST["note"]);
    }
}
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<div id="officenotes_edit">

<form method="post" name="new_note" action="office_comments_full.php">

<?php
/* BACK should go to the main Office Notes screen */
if ($userauthorized) { $backurl="office_comments.php"; }
else { $backurl="../main_info.php"; }
?>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="office_comments.php">
<?php } else { ?>
<a href="<?php echo $backurl; ?>" target="Main">
<?php } ?>

<span class="title"><?php echo xlt('Office Notes'); ?></span>
<span class="back"><?php echo text($tback); ?></span></a>

<br>
<input type="hidden" name="mode" value="new">
<input type="hidden" name="offset" value="<?php echo attr($offset); ?>">
<input type="hidden" name="active" value="<?php echo attr($active); ?>">

<textarea name="note" rows="6" cols="40" wrap="virtual"></textarea>
<br>
<a href="javascript:document.new_note.submit();" class="link_submit">[<?php echo xlt('Add New Note'); ?>]</a>
</form>

<br/>

<form method="post" name="update_activity" action="office_comments_full.php">

<?php //change the view on the current mode, whether all, active, or inactive
$all_class="link"; $active_class="link"; $inactive_class="link";
if ($active=="all") { $all_class="link_selected"; }
elseif ($active==1) { $active_class="link_selected"; }
elseif ($active==0) { $inactive_class="link_selected"; }
?>

<span class="text"><?php echo xlt('View:'); ?> </span> 
<a href="office_comments_full.php?offset=0&active=all" class="<?php echo attr($all_class);?>">[<?php echo xlt('All'); ?>]</a>
<a href="office_comments_full.php?offset=0&active=1" class="<?php echo attr($active_class);?>">[<?php echo xlt('Only Active'); ?>]</a>
<a href="office_comments_full.php?offset=0&active=0" class="<?php echo attr($inactive_class);?>">[<?php echo xlt('Only Inactive'); ?>]</a>

<input type="hidden" name="mode" value="update">
<input type="hidden" name="offset" value="<?php echo attr($offset);?>">
<input type="hidden" name="active" value="<?php echo attr($active);?>">
<br/>
<a href="javascript:document.update_activity.submit();" class="link_submit">[<?php echo xlt('Change Activity'); ?>]</a>

<table border="0" class="existingnotes">
<?php
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N

//retrieve all notes
if ($result = getOnoteByDate("", $active, "id,date,body,user,activity",$N,$offset)) {
$result_count = 0;
foreach ($result as $iter) {
    $result_count++;
    
    if (getdate() == strtotime($iter{"date"})) {
        $date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
    } else {
        $date_string = date( "D F dS" ,strtotime($iter{"date"}));
    }
    
    if ($iter{"activity"}) { $checked = "checked"; }
    else { $checked = ""; }

    print "<tr><td><input type=hidden value='' name='act".attr($iter{"id"})."' id='act".attr($iter{"id"})."'>";
    print "<input name='box".attr($iter{"id"})."' id='box".attr($iter{"id"})."' onClick='javascript:document.update_activity.act".attr($iter{"id"}).".value=this.checked' type=checkbox $checked></td>";
    print "<td><label for='box".attr($iter{"id"})."' class='bold'>".text($date_string) . "</label>";
    print " <label for='box".attr($iter{"id"})."' class='bold'>(". text($iter{"user"}).")</label></td>";
    print "<td><label for='box".attr($iter{"id"})."' class='text'>" . text($iter{"body"}) . "&nbsp;</label></td></tr>\n";
    
    
    $notes_count++;
}
}else{
//no results
print "<tr><td></td><td></td><td></td></tr>\n";
}

?>
</table>

<a href="javascript:document.update_activity.submit();" class="link_submit">[<?php echo xlt('Change Activity'); ?>]</a>
</form>

<hr>
<table width="400" border="0" cellpadding="0" cellspacing="0">
<tr><td>
<?php
if ($offset>($N-1)) {
echo "<a class='link' href=office_comments_full.php?active=".attr($active)."&offset=".attr($offset-$N).">[".xlt('Previous')."]</a>";
}
?>
</td><td align=right>
<?php
if ($result_count == $N) {
echo "<a class='link' href=office_comments_full.php?active=".attr($active)."&offset=".attr($offset+$N).">[".xlt('Next')."]</a>";
}
?>
</td></tr>
</table>
</div>

</body>
</html>
