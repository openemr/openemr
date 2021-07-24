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

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Core\Header;

$row = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname)
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        $tmp = '0';
    }

    return $tmp;
}

function cbvalue($cbname)
{
    return $_POST[$cbname] ? '1' : '0';
}

function rbinput($name, $value, $desc, $colname)
{
    global $row;
    $ret  = "<input type='radio' name='" . attr($name) . "' value='" . attr($value) . "'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />" . text($desc);
    return $ret;
}

function rbcell($name, $value, $desc, $colname)
{
    return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname)
{
    global $row;
    $ret  = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }

    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname)
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . "$desc</td>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
    $tmp = strtotime($_POST['time'] . $_POST['timeampm']);
    if ($tmp < 0) {
        die("Time is not valid!");
    }

    $siinjtime = date("H:i:s", $tmp);

    $simech_other = '';
    if ($_POST['activity'] == '7') {
        $simech_other = $_POST['activity_other'];
    } elseif ($_POST['activity'] == '23') {
        $simech_other = $_POST['activity_nc_other'];
    }

    $sitreat_other = '';
    if ($_POST['treat_10']) {
        $sitreat_other = $_POST['treat_other'];
    }

 // If updating an existing form...
 //
    if ($formid) {
        $query = "UPDATE form_soccer_injury SET
         siinjtime = ?,
         sigametime = ?,
         simechanism  ?,
         simech_other= ?,
         sisurface =  ?,
         siposition = ?,
         sifootwear = ?,
         siequip_1 =  ?,
         siequip_2 =  ?,
         siequip_3 =  ?,
         siequip_4 =  ?,
         siequip_5 =  ?,
         siequip_6 =  ?,
         siside =    ?,
         siremoved =  ?,
         sitreat_1 =  ?,
         sitreat_2 =  ?,
         sitreat_3 =  ?,
         sitreat_4 =  ?,
         sitreat_5 =  ?,
         sitreat_6 =  ?,
         sitreat_7 =  ?,
         sitreat_8 =  ?,
         sitreat_9 =  ?,
         sitreat_10 = ?,
         sitreat_other = ?,
         sinoreturn = ?,
         WHERE id = ?";
        sqlStatement($query, array( $siinjtime, rbvalue('gameplay'), rbvalue('activity'), $simech_other, rbvalue('surface'), rbvalue('position'), rbvalue('footwear'), cbvalue('equip_1'),
        cbvalue('equip_2'), cbvalue('equip_3'), cbvalue('equip_4'), cbvalue('equip_5'), cbvalue('equip_6'), rbvalue('side'), rbvalue('removed'), cbvalue('treat_1'), cbvalue('treat_2'),
        cbvalue('treat_3'), cbvalue('treat_4'), cbvalue('treat_5'), cbvalue('treat_6'), cbvalue('treat_7'), cbvalue('treat_8'), cbvalue('treat_9'), cbvalue('treat_10'), $sistreat_other, cbvalue('noreturn'),
        $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_soccer_injury ( " .
         "siinjtime, sigametime, simechanism, simech_other, sisurface, " .
         "siposition, sifootwear, " .
         "siequip_1, siequip_2, siequip_3, siequip_4, siequip_5, siequip_6, " .
         "siside, siremoved, " .
         "sitreat_1, sitreat_2, sitreat_3, sitreat_4, sitreat_5, " .
         "sitreat_6, sitreat_7, sitreat_8, sitreat_9, sitreat_10, " .
         "sitreat_other, sinoreturn " .
         ") VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?,
         ?,?,?,?,?,?,?,?,?,?,?,?,
         ?,?,?)";

        $newid = sqlInsert($query, array($siinjtime, rbvalue('gameplay'), rbvalue('activity'), $simech_other, rbvalue('surface'), rbvalue('position'), rbvalue('footwear'), cbvalue('equip_1'),
        cbvalue('equip_2'), cbvalue('equip_3'), cbvalue('equip_4'), cbvalue('equip_5'), cbvalue('equip_6'), rbvalue('side'), rbvalue('removed'), cbvalue('treat_1'), cbvalue('treat_2'),
        cbvalue('treat_3'), cbvalue('treat_4'), cbvalue('treat_5'), cbvalue('treat_6'), cbvalue('treat_7'), cbvalue('treat_8'), cbvalue('treat_9'), cbvalue('treat_10'), $sistreat_other, cbvalue('noreturn')));

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
    $row = sqlQuery("SELECT * FROM form_soccer_injury WHERE " .
    "id = ? AND activity = '1'", array($formid));
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
<?php Header::setupHeader(); ?>
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script>

</script>
</head>

<body class="body_top">
<form method="post" action="<?php echo $rootdir ?>/forms/soccer_injury/new.php?id=<?php echo attr_url($formid); ?>" onsubmit="return top.restoreSession()">

<center>

<p class='title' style='margin-top:8px;margin-bottom:8px'>Football Injury Statistics</p>

<table border='1'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Time of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Time</td>
  <td nowrap>
   <input type='text' name='time' size='5' title='Hour or hh:mm' value='<?php echo attr($siinjtime); ?>' />&nbsp;
   <input type='radio' name='timeampm' value='am'<?php if ($siampm == 'am') {
        echo ' checked';
                                                 } ?> />am&nbsp;
   <input type='radio' name='timeampm' value='pm'<?php if ($siampm == 'pm') {
        echo ' checked';
                                                 } ?> />pm&nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Game Play</td>
  <td nowrap>
   <table width='100%'>
    <tr>
        <?php echo rbcell('gameplay', '1', '1st Quarter', 'sigametime') ?>
        <?php echo rbcell('gameplay', '2', '2nd Quarter', 'sigametime') ?>
        <?php echo rbcell('gameplay', '3', '3rd Quarter', 'sigametime') ?>
        <?php echo rbcell('gameplay', '4', '4th Quarter', 'sigametime') ?>
    </tr>
    <tr>
        <?php echo rbcell('gameplay', '5', 'Warm Up', 'sigametime') ?>
        <?php echo rbcell('gameplay', '6', 'Extra Time', 'sigametime') ?>
        <?php echo rbcell('gameplay', '7', 'Cool Down', 'sigametime') ?>
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
        <?php echo rbcell('gameplay', '11', 'Warm Up', 'sigametime') ?>
        <?php echo rbcell('gameplay', '12', 'During Session', 'sigametime') ?>
        <?php echo rbcell('gameplay', '13', 'Cool Down', 'sigametime') ?>
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
        <?php echo rbcell('activity', '1', 'Tackling', 'simechanism') ?>
        <?php echo rbcell('activity', '2', 'Tackled', 'simechanism') ?>
        <?php echo rbcell('activity', '3', 'Collision', 'simechanism') ?>
        <?php echo rbcell('activity', '4', 'Kicked', 'simechanism') ?>
    </tr>
    <tr>
        <?php echo rbcell('activity', '5', 'Use of Elbow', 'simechanism') ?>
        <?php echo rbcell('activity', '6', 'Hit by Ball', 'simechanism') ?>
     <td colspan='2' nowrap>
        <?php echo rbinput('activity', '7', 'Other:', 'simechanism') ?>
      <input type='text' name='activity_other' size='10'
             title='Describe other'
             value='<?php echo attr($row['simech_other']); ?>' />
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
        <?php echo rbcell('activity', '11', 'Passing', 'simechanism') ?>
        <?php echo rbcell('activity', '12', 'Shooting', 'simechanism') ?>
        <?php echo rbcell('activity', '13', 'Running', 'simechanism') ?>
        <?php echo rbcell('activity', '14', 'Dribbling', 'simechanism') ?>
    </tr>
    <tr>
        <?php echo rbcell('activity', '15', 'Heading', 'simechanism') ?>
        <?php echo rbcell('activity', '16', 'Jumping', 'simechanism') ?>
        <?php echo rbcell('activity', '17', 'Landing', 'simechanism') ?>
        <?php echo rbcell('activity', '18', 'Fall', 'simechanism') ?>
    </tr>
    <tr>
        <?php echo rbcell('activity', '19', 'Stretching', 'simechanism') ?>
        <?php echo rbcell('activity', '20', 'Twist/Turning', 'simechanism') ?>
        <?php echo rbcell('activity', '21', 'Throwing', 'simechanism') ?>
        <?php echo rbcell('activity', '22', 'Diving', 'simechanism') ?>
    </tr>
    <tr>
     <td colspan='4' nowrap>
        <?php echo rbinput('activity', '23', 'Other:', 'simechanism') ?>
      <input type='text' name='activity_nc_other' size='10'
       title='Describe other'
       value='<?php echo attr($row['simech_other']) ?>' />
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
        <?php echo rbcell('surface', '1', 'Pitch', 'sisurface') ?>
        <?php echo rbcell('surface', '2', 'Training', 'sisurface') ?>
        <?php echo rbcell('surface', '3', 'Artificial', 'sisurface') ?>
        <?php echo rbcell('surface', '4', 'All Weather', 'sisurface') ?>
    </tr>
    <tr>
        <?php echo rbcell('surface', '5', 'Indoor', 'sisurface') ?>
        <?php echo rbcell('surface', '6', 'Gym', 'sisurface') ?>
        <?php echo rbcell('surface', '7', 'Other', 'sisurface') ?>
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
        <?php echo rbcell('position', '1', 'Defender', 'siposition') ?>
        <?php echo rbcell('position', '2', 'Midfield Offense', 'siposition') ?>
        <?php echo rbcell('position', '3', 'Midfield Defense', 'siposition') ?>
        <?php echo rbcell('position', '4', 'Wing Back', 'siposition') ?>
    </tr>
    <tr>
        <?php echo rbcell('position', '5', 'Forward', 'siposition') ?>
        <?php echo rbcell('position', '6', 'Striker', 'siposition') ?>
        <?php echo rbcell('position', '7', 'Goal Keeper', 'siposition') ?>
        <?php echo rbcell('position', '8', 'Starting Lineup', 'siposition') ?>
    </tr>
    <tr>
        <?php echo rbcell('position', '9', 'Substitute', 'siposition') ?>
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
        <?php echo rbcell('footwear', '1', 'Molded Cleat', 'sifootwear') ?>
        <?php echo rbcell('footwear', '2', 'Detachable Cleats', 'sifootwear') ?>
        <?php echo rbcell('footwear', '3', 'Indoor Shoes', 'sifootwear') ?>
        <?php echo rbcell('footwear', '4', 'Turf Shoes', 'sifootwear') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Other Equipment</td>
  <td nowrap>
   <table width='100%'>
    <tr>
        <?php echo cbcell('equip_1', 'Shin Pads', 'siequip_1') ?>
        <?php echo cbcell('equip_2', 'Gloves', 'siequip_2') ?>
        <?php echo cbcell('equip_3', 'Ankle Strapping', 'siequip_3') ?>
        <?php echo cbcell('equip_4', 'Knee Strapping', 'siequip_4') ?>
    </tr>
    <tr>
        <?php echo cbcell('equip_5', 'Bracing', 'siequip_5') ?>
        <?php echo cbcell('equip_6', 'Synthetic Cast', 'siequip_6') ?>
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
        <?php echo rbcell('side', '1', 'Left', 'siside') ?>
        <?php echo rbcell('side', '2', 'Right', 'siside') ?>
        <?php echo rbcell('side', '3', 'Bilateral', 'siside') ?>
        <?php echo rbcell('side', '4', 'Not Applicable', 'siside') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Post Injury Sequelae</b></td>
 </tr>

 <tr>
  <td nowrap>Removed from<br />Play/Training<br />after Injury</td>
  <td nowrap>
   <table width='100%'>
    <tr>
        <?php echo rbcell('removed', '1', 'Immediately', 'siremoved') ?>
        <?php echo rbcell('removed', '2', 'Later', 'siremoved') ?>
        <?php echo rbcell('removed', '3', 'Not at All', 'siremoved') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Medical Treatment<br />Sought from</td>
  <td nowrap>
   <table width='100%'>
    <tr>
        <?php echo cbcell('treat_1', 'Hospital A&amp;E Dept', 'sitreat_1') ?>
        <?php echo cbcell('treat_2', 'General Practitioner', 'sitreat_2') ?>
        <?php echo cbcell('treat_3', 'Physiotherapist', 'sitreat_3') ?>
        <?php echo cbcell('treat_4', 'Nurse', 'sitreat_4') ?>
    </tr>
    <tr>
        <?php echo cbcell('treat_5', 'Hospital Specialist', 'sitreat_5') ?>
        <?php echo cbcell('treat_6', 'Osteopath', 'sitreat_6') ?>
        <?php echo cbcell('treat_7', 'Chiropractor', 'sitreat_7') ?>
        <?php echo cbcell('treat_8', 'Sports Massage Th', 'sitreat_8') ?>
    </tr>
    <tr>
        <?php echo cbcell('treat_9', 'Sports Physician', 'sitreat_9') ?>
     <td colspan='3' nowrap>
        <?php echo cbinput('treat_10', 'sitreat_10') ?>Other:
      <input type='text' name='treat_other' size='10'
       title='Describe other'
       value='<?php echo attr($row['sitreat_other']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td colspan='2' nowrap>
   If player is unlikely to return to play please check here:
    <?php echo cbinput('noreturn', 'sinoreturn') ?>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
