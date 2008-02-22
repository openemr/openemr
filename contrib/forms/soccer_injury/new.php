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

// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
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
 $tmp = strtotime($_POST['time'] . $_POST['timeampm']);
 if ($tmp < 0) die("Time is not valid!");
 $siinjtime = date("H:i:s", $tmp);

 $simech_other = '';
 if ($_POST['activity'] == '7') {
  $simech_other = $_POST['activity_other'];
 }
 else if ($_POST['activity'] == '23') {
  $simech_other = $_POST['activity_nc_other'];
 }

 $sitreat_other = '';
 if ($_POST['treat_10']) {
  $sitreat_other = $_POST['treat_other'];
 }

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_soccer_injury SET "      .
   "siinjtime = '$siinjtime', "                  .
   "sigametime = "  . rbvalue('gameplay') . ", " .
   "simechanism = " . rbvalue('activity') . ", " .
   "simech_other = '$simech_other', "            .
   "sisurface = "   . rbvalue('surface')  . ", " .
   "siposition = "  . rbvalue('position') . ", " .
   "sifootwear = "  . rbvalue('footwear') . ", " .
   "siequip_1 = "   . cbvalue('equip_1')  . ", " .
   "siequip_2 = "   . cbvalue('equip_2')  . ", " .
   "siequip_3 = "   . cbvalue('equip_3')  . ", " .
   "siequip_4 = "   . cbvalue('equip_4')  . ", " .
   "siequip_5 = "   . cbvalue('equip_5')  . ", " .
   "siequip_6 = "   . cbvalue('equip_6')  . ", " .
   "siside = "      . rbvalue('side')     . ", " .
   "siremoved = "   . rbvalue('removed')  . ", " .
   "sitreat_1 = "   . cbvalue('treat_1')  . ", " .
   "sitreat_2 = "   . cbvalue('treat_2')  . ", " .
   "sitreat_3 = "   . cbvalue('treat_3')  . ", " .
   "sitreat_4 = "   . cbvalue('treat_4')  . ", " .
   "sitreat_5 = "   . cbvalue('treat_5')  . ", " .
   "sitreat_6 = "   . cbvalue('treat_6')  . ", " .
   "sitreat_7 = "   . cbvalue('treat_7')  . ", " .
   "sitreat_8 = "   . cbvalue('treat_8')  . ", " .
   "sitreat_9 = "   . cbvalue('treat_9')  . ", " .
   "sitreat_10 = "  . cbvalue('treat_10') . ", " .
   "sitreat_other = '$sitreat_other', "          .
   "sinoreturn = "  . cbvalue('noreturn') . " "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_soccer_injury ( " .
   "siinjtime, sigametime, simechanism, simech_other, sisurface, " .
   "siposition, sifootwear, " .
   "siequip_1, siequip_2, siequip_3, siequip_4, siequip_5, siequip_6, " .
   "siside, siremoved, " .
   "sitreat_1, sitreat_2, sitreat_3, sitreat_4, sitreat_5, " .
   "sitreat_6, sitreat_7, sitreat_8, sitreat_9, sitreat_10, " .
   "sitreat_other, sinoreturn " .
   ") VALUES ( " .
   "'$siinjtime', " .
   rbvalue('gameplay') . ", " .
   rbvalue('activity') . ", " .
   "'$simech_other', "        .
   rbvalue('surface')  . ", " .
   rbvalue('position') . ", " .
   rbvalue('footwear') . ", " .
   cbvalue('equip_1')  . ", " .
   cbvalue('equip_2')  . ", " .
   cbvalue('equip_3')  . ", " .
   cbvalue('equip_4')  . ", " .
   cbvalue('equip_5')  . ", " .
   cbvalue('equip_6')  . ", " .
   rbvalue('side')     . ", " .
   rbvalue('removed')  . ", " .
   cbvalue('treat_1')  . ", " .
   cbvalue('treat_2')  . ", " .
   cbvalue('treat_3')  . ", " .
   cbvalue('treat_4')  . ", " .
   cbvalue('treat_5')  . ", " .
   cbvalue('treat_6')  . ", " .
   cbvalue('treat_7')  . ", " .
   cbvalue('treat_8')  . ", " .
   cbvalue('treat_9')  . ", " .
   cbvalue('treat_10') . ", " .
   "'$sitreat_other', "       .
   cbvalue('noreturn') . " "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Football Injury", $newid, "soccer_injury", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

$siinjtime = '';
$siampm = '';
if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_soccer_injury WHERE " .
  "id = '$formid' AND activity = '1'") ;
 $siinjtime = substr($row['siinjtime'], 0, 5);
 $siampm = 'am';
 $siinjhour = substr($siinjtime, 0, 2);
 if ($siinjhour > 12) {
  $siampm = 'pm';
  $siinjtime = substr($siinjhour + 500 - 12, 1, 2) . substr($siinjtime, 2);
 }
}
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script language="JavaScript">

</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/soccer_injury/new.php?id=<? echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p class='title' style='margin-top:8px;margin-bottom:8px'>Football Injury Statistics</p>

<table border='1'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Time of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Time</td>
  <td nowrap>
   <input type='text' name='time' size='5' title='Hour or hh:mm' value='<? echo $siinjtime ?>' />&nbsp;
   <input type='radio' name='timeampm' value='am'<? if ($siampm == 'am') echo ' checked' ?> />am&nbsp;
   <input type='radio' name='timeampm' value='pm'<? if ($siampm == 'pm') echo ' checked' ?> />pm&nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Game Play</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbcell('gameplay', '1', '1st Quarter', 'sigametime') ?>
     <? echo rbcell('gameplay', '2', '2nd Quarter', 'sigametime') ?>
     <? echo rbcell('gameplay', '3', '3rd Quarter', 'sigametime') ?>
     <? echo rbcell('gameplay', '4', '4th Quarter', 'sigametime') ?>
    </tr>
    <tr>
     <? echo rbcell('gameplay', '5', 'Warm Up'   , 'sigametime') ?>
     <? echo rbcell('gameplay', '6', 'Extra Time', 'sigametime') ?>
     <? echo rbcell('gameplay', '7', 'Cool Down' , 'sigametime') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Training</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbcell('gameplay', '11', 'Warm Up'       , 'sigametime') ?>
     <? echo rbcell('gameplay', '12', 'During Session', 'sigametime') ?>
     <? echo rbcell('gameplay', '13', 'Cool Down'     , 'sigametime') ?>
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
     <? echo rbcell('activity', '1', 'Tackling'    , 'simechanism') ?>
     <? echo rbcell('activity', '2', 'Tackled'     , 'simechanism') ?>
     <? echo rbcell('activity', '3', 'Collision'   , 'simechanism') ?>
     <? echo rbcell('activity', '4', 'Kicked'      , 'simechanism') ?>
    </tr>
    <tr>
     <? echo rbcell('activity', '5', 'Use of Elbow', 'simechanism') ?>
     <? echo rbcell('activity', '6', 'Hit by Ball' , 'simechanism') ?>
     <td colspan='2' nowrap>
      <? echo rbinput('activity', '7', 'Other:', 'simechanism') ?>
      <input type='text' name='activity_other' size='10'
       title='Describe other'
       value='<? echo addslashes($row['simech_other']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Non Contact</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbcell('activity', '11', 'Passing'    , 'simechanism') ?>
     <? echo rbcell('activity', '12', 'Shooting'   , 'simechanism') ?>
     <? echo rbcell('activity', '13', 'Running'    , 'simechanism') ?>
     <? echo rbcell('activity', '14', 'Dribbling'  , 'simechanism') ?>
    </tr>
    <tr>
     <? echo rbcell('activity', '15', 'Heading'    , 'simechanism') ?>
     <? echo rbcell('activity', '16', 'Jumping'    , 'simechanism') ?>
     <? echo rbcell('activity', '17', 'Landing'    , 'simechanism') ?>
     <? echo rbcell('activity', '18', 'Fall'       , 'simechanism') ?>
    </tr>
    <tr>
     <? echo rbcell('activity', '19', 'Stretching'   , 'simechanism') ?>
     <? echo rbcell('activity', '20', 'Twist/Turning', 'simechanism') ?>
     <? echo rbcell('activity', '21', 'Throwing'     , 'simechanism') ?>
     <? echo rbcell('activity', '22', 'Diving'       , 'simechanism') ?>
    </tr>
    <tr>
     <td colspan='4' nowrap>
      <? echo rbinput('activity', '23', 'Other:', 'simechanism') ?>
      <input type='text' name='activity_nc_other' size='10'
       title='Describe other'
       value='<? echo addslashes($row['simech_other']) ?>' />
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
     <? echo rbcell('surface', '1', 'Pitch'      , 'sisurface') ?>
     <? echo rbcell('surface', '2', 'Training'   , 'sisurface') ?>
     <? echo rbcell('surface', '3', 'Artificial' , 'sisurface') ?>
     <? echo rbcell('surface', '4', 'All Weather', 'sisurface') ?>
    </tr>
    <tr>
     <? echo rbcell('surface', '5', 'Indoor'     , 'sisurface') ?>
     <? echo rbcell('surface', '6', 'Gym'        , 'sisurface') ?>
     <? echo rbcell('surface', '7', 'Other'      , 'sisurface') ?>
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
     <? echo rbcell('position', '1', 'Defender'        , 'siposition') ?>
     <? echo rbcell('position', '2', 'Midfield Offense', 'siposition') ?>
     <? echo rbcell('position', '3', 'Midfield Defense', 'siposition') ?>
     <? echo rbcell('position', '4', 'Wing Back'       , 'siposition') ?>
    </tr>
    <tr>
     <? echo rbcell('position', '5', 'Forward'         , 'siposition') ?>
     <? echo rbcell('position', '6', 'Striker'         , 'siposition') ?>
     <? echo rbcell('position', '7', 'Goal Keeper'     , 'siposition') ?>
     <? echo rbcell('position', '8', 'Starting Lineup' , 'siposition') ?>
    </tr>
    <tr>
     <? echo rbcell('position', '9', 'Substitute'      , 'siposition') ?>
     <td width='25%'>&nbsp;</td>
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
     <? echo rbcell('footwear', '1', 'Molded Cleat'     , 'sifootwear') ?>
     <? echo rbcell('footwear', '2', 'Detachable Cleats', 'sifootwear') ?>
     <? echo rbcell('footwear', '3', 'Indoor Shoes'     , 'sifootwear') ?>
     <? echo rbcell('footwear', '4', 'Turf Shoes'       , 'sifootwear') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Other Equipment</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo cbcell('equip_1', 'Shin Pads'      , 'siequip_1') ?>
     <? echo cbcell('equip_2', 'Gloves'         , 'siequip_2') ?>
     <? echo cbcell('equip_3', 'Ankle Strapping', 'siequip_3') ?>
     <? echo cbcell('equip_4', 'Knee Strapping' , 'siequip_4') ?>
    </tr>
    <tr>
     <? echo cbcell('equip_5', 'Bracing'        , 'siequip_5') ?>
     <? echo cbcell('equip_6', 'Synthetic Cast' , 'siequip_6') ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Side of Injury</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbcell('side', '1', 'Left'          , 'siside') ?>
     <? echo rbcell('side', '2', 'Right'         , 'siside') ?>
     <? echo rbcell('side', '3', 'Bilateral'     , 'siside') ?>
     <? echo rbcell('side', '4', 'Not Applicable', 'siside') ?>
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
     <? echo rbcell('removed', '1', 'Immediately', 'siremoved') ?>
     <? echo rbcell('removed', '2', 'Later'      , 'siremoved') ?>
     <? echo rbcell('removed', '3', 'Not at All' , 'siremoved') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Medical Treatment<br>Sought from</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo cbcell('treat_1', 'Hospital A&amp;E Dept', 'sitreat_1') ?>
     <? echo cbcell('treat_2', 'General Practitioner' , 'sitreat_2') ?>
     <? echo cbcell('treat_3', 'Physiotherapist'      , 'sitreat_3') ?>
     <? echo cbcell('treat_4', 'Nurse'                , 'sitreat_4') ?>
    </tr>
    <tr>
     <? echo cbcell('treat_5', 'Hospital Specialist'  , 'sitreat_5') ?>
     <? echo cbcell('treat_6', 'Osteopath'            , 'sitreat_6') ?>
     <? echo cbcell('treat_7', 'Chiropractor'         , 'sitreat_7') ?>
     <? echo cbcell('treat_8', 'Sports Massage Th'    , 'sitreat_8') ?>
    </tr>
    <tr>
     <? echo cbcell('treat_9', 'Sports Physician'     , 'sitreat_9') ?>
     <td colspan='3' nowrap>
      <? echo cbinput('treat_10', 'sitreat_10') ?>Other:
      <input type='text' name='treat_other' size='10'
       title='Describe other'
       value='<? echo addslashes($row['sitreat_other']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td colspan='2' nowrap>
   If player is unlikely to return to play please check here:
   <? echo cbinput('noreturn', 'sinoreturn') ?>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
</p>

</center>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
