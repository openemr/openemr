<?php
$arr_injtime = array(
  '1' => 'Warm Up',
  '2' => 'Extra Time',
  '3' => 'Cool Down',
  '4' => 'Training Warm Up (deprecated)',
  '5' => 'Training Session (deprecated)',
  '6' => 'Training Cool Down (deprecated)',
  '7' => 'Training Rehab (deprecated)',
);

// This is the number of leading entries in $arr_activity that are for
// injuries due to contact with a player or object.
$arr_activity_contact_count = 12;

$arr_activity = array(
  'blocked'    => 'Blocked',                     // new
  'hitbyball'  => 'Hit by Ball',                 // new
  'goalpost'   => 'Collision with Goal Post',    // new
  'ground'     => 'Collision with Ground',       // new
  'kicked'     => 'Collision with Other Player',
  'collother'  => 'Collision with Other Object', // new
  'collision'  => 'Collision Non Specified',
  'tackled'    => 'Tackled from Front',
  'tackside'   => 'Tackled from Side',           // new
  'tackback'   => 'Tackled from Back',           // new
  'tackling'   => 'Tackling Other Player',
  'elbow'      => 'Use of Arm/Elbow',
  // 12 Contact entries, see $arr_activity_contact_count.
  'running'    => 'Running/Sprinting',
  'turning'    => 'Twisting/Turning',
  'shooting'   => 'Shooting',
  'passing'    => 'Passing/Crossing',
  'dribbling'  => 'Dribbling',
  'jumping'    => 'Jumping/Landing',
  'fall'       => 'Falling/Diving',
  'stretching' => 'Stretching',
  'sliding'    => 'Sliding',                     // new
  'throwing'   => 'Throwing',
  'heading'    => 'Heading',
  'landing'    => 'Landing (deprecated)',  // change to jumping
  'diving'     => 'Diving (deprecated)',   // change to fall
  'overuse'    => 'Overuse (deprecated)',  // s/b recorded in Classification
);

$arr_sanction = array(
  'nofoul'     => 'No Foul',
  'oppfoul'    => 'Opponent Foul',
  'ownfoul'    => 'Own Foul',
  'yellow'     => 'Yellow Card',
  'red'        => 'Red Card',
);

$arr_surface = array(
  '7' => 'Grass'                ,
  '3' => 'Outdoor Artificial'   ,
  '4' => 'Indoor Artificial'    ,
  '8' => 'Wooden'               ,
  '9' => 'Concrete'             ,
 '10' => 'Carpet'               ,
  '6' => 'Other'                ,
  '1' => 'Pitch (deprecated)'   ,
  '2' => 'Training (deprecated)',
  '5' => 'Gym (deprecated)'     ,
);

$arr_condition = array(
  '1' => 'Hard'        ,
  '2' => 'Firm'        ,
  '3' => 'Soft'        ,
  '4' => 'Slippery/Wet',
  '5' => 'Uneven'      ,
  '6' => 'Good'        ,
  '7' => 'Poor'        ,
);

$arr_weather = array(
  'sunny'    => 'Sunny'     ,
  'rainy'    => 'Rainy'     ,
  'windy'    => 'Windy'     ,
  'dry'      => 'Dry'       ,
  'sleet'    => 'Sleet/Snow',
  'overcast' => 'Overcast'  ,
);

$arr_position = array(
  '5' => 'Goal Keeper'                    ,
  '1' => 'Defender'                       ,
  '7' => 'Midfielder'                     ,
  '4' => 'Forward'                        ,
  '2' => 'Midfield Offensive (deprecated)',
  '3' => 'Midfield Defensive (deprecated)',
  '6' => 'Substitute (deprecated)'        ,
);

$arr_footwear = array(
  '4' => 'Blades'          ,
  '1' => 'Molded Studs'    ,
  '2' => 'Detachable Studs',
  '3' => 'Indoor Shoes'    ,
  '5' => 'Trainers'        ,
);

/*********************************************************************
$arr_side = array(
  '1' => 'Left'          ,
  '2' => 'Right'         ,
  '3' => 'Bilateral'     ,
  '4' => 'Not Applicable',
);
*********************************************************************/

$arr_removed = array(
  '1' => 'Immediately',
  '2' => 'Later'      ,
  '3' => 'Not at All' ,
);

$arr_match_type = array(
  '1' => 'Premiership (deprecated)',
  '2' => 'FA Cup (deprecated)',
  '3' => 'League Cup (deprecated)',
  '4' => 'Champions League Cup (deprecated)',
  '5' => 'Championship Match (deprecated)',
  '6' => 'League One Match (deprecated)',
  '7' => 'League Two Match (deprecated)',
  '8' => 'International Match (deprecated)',
  '9' => 'Friendly',
 '10' => 'League Match',
 '11' => 'UEFA Champtions League Match',
 '12' => 'UEFA Europa League Match',
 '13' => 'Carling Cup Match',
 '14' => 'Other Cup Match',
 '15' => 'National Match',
 '16' => 'Reserve Match',
 '17' => 'Youth Team Match',
);

$arr_training_type = array(
 '51' => 'Football',
 '52' => 'Other',
 '53' => 'Mixed i.e. Football + Other',
 '54' => 'Endurance',
 '55' => 'Speed Agility Quickness',
 '56' => 'Strength',
 '57' => 'Injury Prevention F-mark 11',
 '58' => 'Injury Prevention Squad Own',
 '59' => 'Recovery',
);

$firow = array();

function rbfiinput($name, $value, $desc, $colname) {
  global $firow;
  $ret  = "<input type='radio' name='$name' value='$value'";
  if ($firow[$colname] == $value) $ret .= " checked";
  $ret .= " />$desc";
  return $ret;
}

function rbficell($name, $value, $desc, $colname) {
 return "<td width='25%' nowrap>" . rbfiinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbfiinput($name, $colname) {
 global $firow;
 $ret  = "<input type='checkbox' name='$name' value='1'";
 if ($firow[$colname]) $ret .= " checked";
 $ret .= " />";
 return $ret;
}

function cbficell($name, $desc, $colname) {
 return "<td width='25%' nowrap>" . cbfiinput($name, $colname) . "$desc</td>\n";
}

function issue_football_injury_newtype() {
  echo "  var fiadisp = (aitypes[index] == 2) ? '' : 'none';\n";
  echo "  document.getElementById('football_injury').style.display = fiadisp;\n";
}

function issue_football_injury_save($issue) {
  global $arr_activity, $arr_sanction, $arr_weather;
  $query = "REPLACE INTO lists_football_injury ( " .
    "id, fiinjmin, fiinjtime, fimatchtype, ";
  foreach ($arr_activity as $key => $value)
    $query .= "fimech_$key, ";
  foreach ($arr_sanction as $key => $value)
    $query .= "fimech_$key, ";
  foreach ($arr_weather as $key => $value)
    $query .= "fiweather_$key, ";
  $query .= "fimech_othercon, fimech_othernon, fiweather_temperature, " .
    "fisurface, fiposition, fifootwear, firemoved, ficondition " .
    ") VALUES ( " .
    $issue                          . ", " .
    invalue('form_injmin')          . ", " .
    rbvalue('form_injtime')         . ", " .
    rbvalue('form_matchtype')       . ", ";
  foreach ($arr_activity as $key => $value)
    $query .= cbvalue("form_mech_$key") . ", ";
  foreach ($arr_sanction as $key => $value)
    $query .= cbvalue("form_mech_$key") . ", ";
  foreach ($arr_weather as $key => $value)
    $query .= cbvalue("form_weather_$key") . ", ";
  $query .=
    txvalue('form_mech_othercon')   . ", " .
    txvalue('form_mech_othernon')   . ", " .
    txvalue('form_weather_temperature') . ", " .
    rbvalue('form_surface')         . ", " .
    rbvalue('form_position')        . ", " .
    rbvalue('form_footwear')        . ", " .
    // rbvalue('form_side')            . ", " .
    rbvalue('form_removed')         . ", " .
    rbvalue('form_condition')       . " "  .
    ")";
  sqlStatement($query);
}

function issue_football_injury_form($issue) {
  global $firow, $arr_match_type, $arr_injtime, $arr_training_type;
  global $arr_activity, $arr_activity_contact_count, $arr_position;
  global $arr_surface, $arr_condition, $arr_weather, $arr_footwear;

  if ($issue) {
    $firow = sqlQuery ("SELECT * FROM lists_football_injury WHERE id = '$issue'");
  } else {
    $firow = array();
  }
?>

<table border='1' width='98%' id='football_injury' style='display:none;margin-top:6pt;'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Timing of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Match Play</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td nowrap>
      Min of Injury
      <input type='text' name='form_injmin' size='4'
       value='<? echo addslashes($firow['fiinjmin']) ?>' />
     </td>
<?php
$i = 1;
foreach ($arr_injtime as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['fiinjtime'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_injtime', $key, $value, 'fiinjtime');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
    </tr>
   </table>
  </td>
 </tr>

 <!-- Training Type and Match Type are all a single set of radio buttons. -->

 <tr>
  <td nowrap>Training Type</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_training_type as $key => $value) {
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_matchtype', $key, $value, 'fimatchtype');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Match Type</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_match_type as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['fimatchtype'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_matchtype', $key, $value, 'fimatchtype');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Injury Mechanism</b></td>
 </tr>

 <tr>
  <td nowrap>Contact</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
$index = 0;
foreach ($arr_activity as $key => $value) {
  if (++$index > $arr_activity_contact_count) break;
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && empty($firow["fimech_$key"])) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . cbficell("form_mech_$key", $value, "fimech_$key");
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
     <td nowrap>
      Other:
      <input type='text' name='form_mech_othercon' size='8'
       title='Describe other'
       value='<?php echo addslashes($firow['fimech_othercon']) ?>' />
     </td>
<?php
++$i;
if ($i % 4 == 0) echo "    </tr>\n";
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Non Contact</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
$index = 0;
foreach ($arr_activity as $key => $value) {
  if (++$index <= $arr_activity_contact_count) continue;
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && empty($firow["fimech_$key"])) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . cbficell("form_mech_$key", $value, "fimech_$key");
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
     <td nowrap>
      Other:
      <input type='text' name='form_mech_othernon' size='8'
       title='Describe other'
       value='<?php echo addslashes($firow['fimech_othernon']) ?>' />
     </td>
<?php
++$i;
if ($i % 4 == 0) echo "    </tr>\n";
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Additional Factors</b></td>
 </tr>

 <tr>
  <td nowrap>Playing Position</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_position as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['fiposition'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_position', $key, $value, 'fiposition');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Surface</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_surface as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['fisurface'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_surface', $key, $value, 'fisurface');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Condition of Grass/Pitch</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_condition as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['ficondition'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_condition', $key, $value, 'ficondition');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Weather Conditions</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_weather as $key => $value) {
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . cbficell("form_weather_$key", $value, "fiweather_$key");
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
     <td nowrap>
      Temperature:
      <input type='text' name='form_weather_temperature' size='3'
       title='Ambient temperature in degrees Celsius'
       value='<?php echo addslashes($firow['fiweather_temperature']) ?>' />
     </td>
<?php
++$i;
if ($i % 4 == 0) echo "    </tr>\n";
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Footwear</td>
  <td nowrap>
   <table width='100%'>
<?php
$i = 0;
foreach ($arr_footwear as $key => $value) {
  // Skip deprecated values except if currently selected.
  if (stristr($value, 'deprecated') && $firow['fifootwear'] != $key) continue;
  //
  if ($i % 4 == 0) echo "    <tr>\n";
  echo "     " . rbficell('form_footwear', $key, $value, 'fifootwear');
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
while ($i % 4 > 0) {
  echo "     <td width='25%'>&nbsp;</td>\n";
  ++$i;
  if ($i % 4 == 0) echo "    </tr>\n";
}
?>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Referee's Sanction</b></td>
 </tr>

 <tr>
  <td nowrap>Referee's Sanction</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo cbficell('form_mech_nofoul' , 'No Foul'      , 'fimech_nofoul' ) ?>
     <? echo cbficell('form_mech_oppfoul', 'Opponent Foul', 'fimech_oppfoul') ?>
     <? echo cbficell('form_mech_ownfoul', 'Own Foul'     , 'fimech_ownfoul') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
    <tr>
     <? echo cbficell('form_mech_yellow' , 'Yellow Card'  , 'fimech_yellow' ) ?>
     <? echo cbficell('form_mech_red'    , 'Red Card'     , 'fimech_red'    ) ?>
     <td width='25%'>&nbsp;</td>
     <td width='25%'>&nbsp;</td>
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
     <? echo rbficell('form_removed', '1', 'Immediately', 'firemoved') ?>
     <? echo rbficell('form_removed', '2', 'Later'      , 'firemoved') ?>
     <? echo rbficell('form_removed', '3', 'Not at All' , 'firemoved') ?>
     <td width='25%'>&nbsp;</td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<?php
}
?>
