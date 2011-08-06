<?php
//////////////////////////////////////////////////////////////////////
// ------------------ DO NOT MODIFY VIEW.PHP !!! ---------------------
// View.php is an exact duplicate of new.php.  If you wish to make
// any changes, then change new.php and either (recommended) make
// view.php a symbolic link to new.php, or copy new.php to view.php.
//
// And if you check in a change to either module, be sure to check
// in the other (identical) module also.
//
// This nonsense will go away if we ever move to subversion.
//////////////////////////////////////////////////////////////////////

// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname) {
 $tmp = $_POST[$rbname];
 if (! $tmp) $tmp = '0';
 return "'$tmp'";
}

function cbvalue($cbname) {
 return $_POST[$cbname] ? '1' : '0';
}

function invalue($inname) {
 return (int) trim($_POST[$inname]);
}

function txvalue($txname) {
 return "'" . trim($_POST[$txname]) . "'";
}

function rbinput($name, $value, $desc, $colname) {
 global $row;
 $ret  = "<input type='radio' name='$name' value='$value'";
 if ($row[$colname] == $value) $ret .= " checked";
 $ret .= " />$desc";
 return $ret;
}

function rbcell($name, $value, $desc, $colname) {
 return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname) {
 global $row;
 $ret  = "<input type='checkbox' name='$name' value='1'";
 if ($row[$colname]) $ret .= " checked";
 $ret .= " />";
 return $ret;
}

function cbcell($name, $desc, $colname) {
 return "<td width='25%' nowrap>" . cbinput($name, $colname) . "$desc</td>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
 $fiinjmin = (int) $_POST['form_injmin'];

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_football_injury_audit SET "                .
   "fiinjmin = "          . invalue('form_injmin')          . ", " .
   "fiinjtime = "         . rbvalue('form_injtime')         . ", " .
   "fimatchtype = "       . rbvalue('form_matchtype')       . ", " .
   "fimech_tackling = "   . cbvalue('form_mech_tackling')   . ", " .
   "fimech_tackled = "    . cbvalue('form_mech_tackled')    . ", " .
   "fimech_collision = "  . cbvalue('form_mech_collision')  . ", " .
   "fimech_kicked = "     . cbvalue('form_mech_kicked')     . ", " .
   "fimech_elbow = "      . cbvalue('form_mech_elbow')      . ", " .
   "fimech_nofoul = "     . cbvalue('form_mech_nofoul')     . ", " .
   "fimech_oppfoul = "    . cbvalue('form_mech_oppfoul')    . ", " .
   "fimech_ownfoul = "    . cbvalue('form_mech_ownfoul')    . ", " .
   "fimech_yellow = "     . cbvalue('form_mech_yellow')     . ", " .
   "fimech_red = "        . cbvalue('form_mech_red')        . ", " .
   "fimech_passing = "    . cbvalue('form_mech_passing')    . ", " .
   "fimech_shooting = "   . cbvalue('form_mech_shooting')   . ", " .
   "fimech_running = "    . cbvalue('form_mech_running')    . ", " .
   "fimech_dribbling = "  . cbvalue('form_mech_dribbling')  . ", " .
   "fimech_heading = "    . cbvalue('form_mech_heading')    . ", " .
   "fimech_jumping = "    . cbvalue('form_mech_jumping')    . ", " .
   "fimech_landing = "    . cbvalue('form_mech_landing')    . ", " .
   "fimech_fall = "       . cbvalue('form_mech_fall')       . ", " .
   "fimech_stretching = " . cbvalue('form_mech_stretching') . ", " .
   "fimech_turning = "    . cbvalue('form_mech_turning')    . ", " .
   "fimech_throwing = "   . cbvalue('form_mech_throwing')   . ", " .
   "fimech_diving = "     . cbvalue('form_mech_diving')     . ", " .
   "fimech_overuse = "    . cbvalue('form_mech_overuse')    . ", " .
   "fimech_othercon = "   . txvalue('form_mech_othercon')   . ", " .
   "fimech_othernon = "   . txvalue('form_mech_othernon')   . ", " .
   "fisurface = "         . rbvalue('form_surface')         . ", " .
   "fiposition = "        . rbvalue('form_position')        . ", " .
   "fifootwear = "        . rbvalue('form_footwear')        . ", " .
   "fiside = "            . rbvalue('form_side')            . ", " .
   "firemoved = "         . rbvalue('form_removed')         . " "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_football_injury_audit ( " .
   "fiinjmin, fiinjtime, fimatchtype, " .
   "fimech_tackling, fimech_tackled, fimech_collision, " .
   "fimech_kicked, fimech_elbow, fimech_nofoul, fimech_oppfoul, " .
   "fimech_ownfoul, fimech_yellow, fimech_red, fimech_passing, " .
   "fimech_shooting, fimech_running, fimech_dribbling, fimech_heading, " .
   "fimech_jumping, fimech_landing, fimech_fall, fimech_stretching, " .
   "fimech_turning, fimech_throwing, fimech_diving, fimech_overuse, " .
   "fimech_othercon, fimech_othernon, fisurface, fiposition, fifootwear, " .
   "fiside, firemoved " .
   ") VALUES ( " .
   invalue('form_injmin')          . ", " .
   rbvalue('form_injtime')         . ", " .
   rbvalue('form_matchtype')       . ", " .
   cbvalue('form_mech_tackling')   . ", " .
   cbvalue('form_mech_tackled')    . ", " .
   cbvalue('form_mech_collision')  . ", " .
   cbvalue('form_mech_kicked')     . ", " .
   cbvalue('form_mech_elbow')      . ", " .
   cbvalue('form_mech_nofoul')     . ", " .
   cbvalue('form_mech_oppfoul')    . ", " .
   cbvalue('form_mech_ownfoul')    . ", " .
   cbvalue('form_mech_yellow')     . ", " .
   cbvalue('form_mech_red')        . ", " .
   cbvalue('form_mech_passing')    . ", " .
   cbvalue('form_mech_shooting')   . ", " .
   cbvalue('form_mech_running')    . ", " .
   cbvalue('form_mech_dribbling')  . ", " .
   cbvalue('form_mech_heading')    . ", " .
   cbvalue('form_mech_jumping')    . ", " .
   cbvalue('form_mech_landing')    . ", " .
   cbvalue('form_mech_fall')       . ", " .
   cbvalue('form_mech_stretching') . ", " .
   cbvalue('form_mech_turning')    . ", " .
   cbvalue('form_mech_throwing')   . ", " .
   cbvalue('form_mech_diving')     . ", " .
   cbvalue('form_mech_overuse')    . ", " .
   txvalue('form_mech_othercon')   . ", " .
   txvalue('form_mech_othernon')   . ", " .
   rbvalue('form_surface')         . ", " .
   rbvalue('form_position')        . ", " .
   rbvalue('form_footwear')        . ", " .
   rbvalue('form_side')            . ", " .
   rbvalue('form_removed')         . " "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Football Injury Audit", $newid, "football_injury_audit", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_football_injury_audit WHERE " .
  "id = '$formid' AND activity = '1'") ;
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

// Pop up the coding window.
function docoding() {
 var width = screen.width - 50;
 if (!isNaN(top.screenX)) {
  width -= top.screenX;
 } else if (!isNaN(top.screenLeft)) {
  width -= top.screenLeft;
 }
 if (width > 1000) width = 1000;
 dlgopen('../../patient_file/encounter/coding_popup.php', '_blank', width, 550);
}

</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/football_injury_audit/new.php?id=<?php echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p class='title' style='margin-top:8px;margin-bottom:8px'>Football Injury Statistics</p>

<table border='1' width='98%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Time of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Match Play</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td nowrap>
      Min of Injury
      <input type='text' name='form_injmin' size='4'
       value='<?php echo addslashes($row['fiinjmin']) ?>' />
     </td>
     <?php echo rbcell('form_injtime', '1', 'Warm Up'   , 'fiinjtime') ?>
     <?php echo rbcell('form_injtime', '2', 'Extra Time', 'fiinjtime') ?>
     <?php echo rbcell('form_injtime', '3', 'Cool Down' , 'fiinjtime') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Training</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_injtime', '4', 'Warm Up'       , 'fiinjtime') ?>
     <?php echo rbcell('form_injtime', '5', 'During Session', 'fiinjtime') ?>
     <?php echo rbcell('form_injtime', '6', 'Cool Down'     , 'fiinjtime') ?>
     <?php echo rbcell('form_injtime', '7', 'Rehabilitation', 'fiinjtime') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Match Type</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_matchtype', '1', 'Premiership'         , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '2', 'FA Cup'              , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '3', 'League Cup'          , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '4', 'Champions League Cup', 'fimatchtype') ?>
    </tr>
    <tr>
     <?php echo rbcell('form_matchtype', '5', 'Championship Match'  , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '6', 'League One Match'    , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '7', 'League Two Match'    , 'fimatchtype') ?>
     <?php echo rbcell('form_matchtype', '8', 'International Match' , 'fimatchtype') ?>
    </tr>
    <tr>
     <?php echo rbcell('form_matchtype', '9', 'Friendly'            , 'fimatchtype') ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Mechanism of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Contact</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo cbcell('form_mech_tackling' , 'Tackling' , 'fimech_tackling' ) ?>
     <?php echo cbcell('form_mech_tackled'  , 'Tackled'  , 'fimech_tackled'  ) ?>
     <?php echo cbcell('form_mech_collision', 'Collision', 'fimech_collision') ?>
     <?php echo cbcell('form_mech_kicked'   , 'Kicked'   , 'fimech_kicked'   ) ?>
    </tr>
    <tr>
     <?php echo cbcell('form_mech_elbow' , 'Use of Elbow' , 'fimech_elbow' ) ?>
     <td colspan='3' nowrap>
      Other:
      <input type='text' name='form_mech_othercon' size='10'
       title='Describe other'
       value='<?php echo addslashes($row['fimech_othercon']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Referee's Sanction</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo cbcell('form_mech_nofoul' , 'No Foul'      , 'fimech_nofoul' ) ?>
     <?php echo cbcell('form_mech_oppfoul', 'Opponent Foul', 'fimech_oppfoul') ?>
     <?php echo cbcell('form_mech_ownfoul', 'Own Foul'     , 'fimech_ownfoul') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
    <tr>
     <?php echo cbcell('form_mech_yellow' , 'Yellow Card'  , 'fimech_yellow' ) ?>
     <?php echo cbcell('form_mech_red'    , 'Red Card'     , 'fimech_red'    ) ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Non Contact</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo cbcell('form_mech_passing'  , 'Passing'  , 'fimech_passing'  ) ?>
     <?php echo cbcell('form_mech_shooting' , 'Shooting' , 'fimech_shooting' ) ?>
     <?php echo cbcell('form_mech_running'  , 'Running'  , 'fimech_running'  ) ?>
     <?php echo cbcell('form_mech_dribbling', 'Dribbling', 'fimech_dribbling') ?>
    </tr>
    <tr>
     <?php echo cbcell('form_mech_heading'  , 'Heading'  , 'fimech_heading'  ) ?>
     <?php echo cbcell('form_mech_jumping'  , 'Jumping'  , 'fimech_jumping'  ) ?>
     <?php echo cbcell('form_mech_landing'  , 'Landing'  , 'fimech_landing'  ) ?>
     <?php echo cbcell('form_mech_fall'     , 'Fall'     , 'fimech_fall'     ) ?>
    </tr>
    <tr>
     <?php echo cbcell('form_mech_stretching', 'Stretching'      , 'fimech_stretching') ?>
     <?php echo cbcell('form_mech_turning'   , 'Twisting/Turning', 'fimech_turning'   ) ?>
     <?php echo cbcell('form_mech_throwing'  , 'Throwing'        , 'fimech_throwing'  ) ?>
     <?php echo cbcell('form_mech_diving'    , 'Diving'          , 'fimech_diving'    ) ?>
    </tr>
    <tr>
     <?php echo cbcell('form_mech_overuse', 'Overuse', 'fimech_overuse' ) ?>
     <td colspan='3' nowrap>
      Other:
      <input type='text' name='form_mech_othernon' size='10'
       title='Describe other'
       value='<?php echo addslashes($row['fimech_othernon']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Conditions</b></td>
 </tr>

 <tr>
  <td nowrap>Surface</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_surface', '1', 'Pitch'      , 'fisurface') ?>
     <?php echo rbcell('form_surface', '2', 'Training'   , 'fisurface') ?>
     <?php echo rbcell('form_surface', '3', 'Artificial' , 'fisurface') ?>
     <?php echo rbcell('form_surface', '4', 'Indoor'     , 'fisurface') ?>
    </tr>
    <tr>
     <?php echo rbcell('form_surface', '5', 'Gym'        , 'fisurface') ?>
     <?php echo rbcell('form_surface', '6', 'Other'      , 'fisurface') ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Position</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_position', '1', 'Defender'          , 'fiposition') ?>
     <?php echo rbcell('form_position', '2', 'Midfield Offensive', 'fiposition') ?>
     <?php echo rbcell('form_position', '3', 'Midfield Defensive', 'fiposition') ?>
     <?php echo rbcell('form_position', '4', 'Forward'           , 'fiposition') ?>
    </tr>
    <tr>
     <?php echo rbcell('form_position', '5', 'Goal Keeper'       , 'fiposition') ?>
     <?php echo rbcell('form_position', '6', 'Substitute'        , 'fiposition') ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Footwear</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_footwear', '1', 'Molded Stud'    , 'fifootwear') ?>
     <?php echo rbcell('form_footwear', '2', 'Detachable Stud', 'fifootwear') ?>
     <?php echo rbcell('form_footwear', '3', 'Indoor Shoes'   , 'fifootwear') ?>
     <?php echo rbcell('form_footwear', '4', 'Blades'         , 'fifootwear') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Side of Injury</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_side', '1', 'Left'          , 'fiside') ?>
     <?php echo rbcell('form_side', '2', 'Right'         , 'fiside') ?>
     <?php echo rbcell('form_side', '3', 'Bilateral'     , 'fiside') ?>
     <?php echo rbcell('form_side', '4', 'Not Applicable', 'fiside') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Post Injury Sequelae</b></td>
 </tr>

 <tr>
  <td nowrap>Removed from<br>Play/Training<br>after Injury</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <?php echo rbcell('form_removed', '1', 'Immediately', 'firemoved') ?>
     <?php echo rbcell('form_removed', '2', 'Later'      , 'firemoved') ?>
     <?php echo rbcell('form_removed', '3', 'Not at All' , 'firemoved') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
&nbsp;
<input type='button' value='Add Injury Diagnosis...' onclick='docoding();'
 title='Add or change coding for this encounter'
 style='background-color:#ffff00;' />
</p>

</center>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
