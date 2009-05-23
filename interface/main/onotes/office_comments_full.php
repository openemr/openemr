<?php
include_once("../../globals.php");
include_once("$srcdir/onotes.inc");

//the number of records to display per screen
$N = 10;

if (!isset($offset)) { $offset=0; }

if (!isset($active)) { $active="all"; }

//this code handles changing the state of activity tags when the user updates them through the interface
if (isset($mode)) {
    if ($mode == "update") {
        foreach ($_POST as $var => $val) {
            if ($val == "true" || $val == "false") {
                $id = str_replace("act","",$var);
                if ($val == "true") {reappearOnote ($id);}elseif($val=="false"){disappearOnote($id);};
                //print "$id: $val > $act_state<br>\n";
            }
        }
    } elseif ($mode == "new") {
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

<span class="title"><?php xl('Office Notes','e'); ?></span>
<span class="back"><?php echo $tback; ?></span></a>

<br>
<input type="hidden" name="mode" value="new">
<input type="hidden" name="offset" value="<?php echo $offset; ?>">
<input type="hidden" name="active" value="<?php echo $active; ?>">

<textarea name="note" rows="6" cols="40" wrap="virtual"></textarea>
<br>
<a href="javascript:document.new_note.submit();" class="link_submit">[<?php xl ('Add New Note','e'); ?>]</a>
</form>

<br/>

<form method="post" name="update_activity" action="office_comments_full.php">

<?php //change the view on the current mode, whether all, active, or inactive
$all_class="link"; $active_class="link"; $inactive_class="link";
if ($active=="all") { $all_class="link_selected"; }
elseif ($active==1) { $active_class="link_selected"; }
elseif ($active==0) { $inactive_class="link_selected"; }
?>

<span class="text"><?php xl('View:','e'); ?> </span> 
<a href="office_comments_full.php?offset=0&active=all" class="<?php echo $all_class;?>">[<?php xl('All','e'); ?>]</a>
<a href="office_comments_full.php?offset=0&active=1" class="<?php echo $active_class;?>">[<?php xl ('Only Active','e'); ?>]</a>
<a href="office_comments_full.php?offset=0&active=0" class="<?php echo $inactive_class;?>">[<?php xl('Only Inactive','e'); ?>]</a>

<input type="hidden" name="mode" value="update">
<input type="hidden" name="offset" value="<?php echo $offset;?>">
<input type="hidden" name="active" value="<?php echo $active;?>">
<br/>
<a href="javascript:document.update_activity.submit();" class="link_submit">[<?php xl('Change Activity','e'); ?>]</a>

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

    print "<tr><td><input type=hidden value='' name='act".$iter{"id"}."' id='act".$iter{"id"}."'>";
    print "<input name='box".$iter{"id"}."' id='box".$iter{"id"}."' onClick='javascript:document.update_activity.act".$iter{"id"}.".value=this.checked' type=checkbox $checked></td>";
    print "<td><label for='box".$iter{"id"}."' class='bold'>".$date_string . "</label>";
    print " <label for='box".$iter{"id"}."' class='bold'>(". $iter{"user"}.")</label></td>";
    print "<td><label for='box".$iter{"id"}."' class='text'>" . stripslashes($iter{"body"}) . "&nbsp;</label></td></tr>\n";
    
    
    $notes_count++;
}
}else{
//no results
print "<tr><td></td><td></td><td></td></tr>\n";
}

?>
</table>

<a href="javascript:document.update_activity.submit();" class="link_submit">[<?php xl ('Change Activity','e'); ?>]</a>
</form>

<hr>
<table width="400" border="0" cellpadding="0" cellspacing="0">
<tr><td>
<?php
if ($offset>($N-1)) {
echo "<a class='link' href=office_comments_full.php?active=".$active."&offset=".($offset-$N).">[".xl('Previous','e')."]</a>";
}
?>
</td><td align=right>
<?php
if ($result_count == $N) {
echo "<a class='link' href=office_comments_full.php?active=".$active."&offset=".($offset+$N).">[".xl('Next')."]</a>";
}
?>
</td></tr>
</table>
</div>

</body>
</html>
