<?php
$arr_injtime = array(
  '1' => 'Warm Up',
  '2' => 'Extra Time',
  '3' => 'Cool Down',
  '4' => 'Training Warm Up',
  '5' => 'Training Session',
  '6' => 'Training Cool Down',
  '7' => 'Training Rehab',
);

$arr_activity = array(
  'tackling'   => 'Tackling',
  'tackled'    => 'Tackled',
  'collision'  => 'Collision',
  'kicked'     => 'Kicked',
  'elbow'      => 'Use of Elbow',
  'passing'    => 'Passing',
  'shooting'   => 'Shooting',
  'running'    => 'Running',
  'dribbling'  => 'Dribbling',
  'heading'    => 'Heading',
  'jumping'    => 'Jumping',
  'landing'    => 'Landing',
  'fall'       => 'Fall',
  'stretching' => 'Stretching',
  'turning'    => 'Twist/Turning',
  'throwing'   => 'Throwing',
  'diving'     => 'Diving',
  'overuse'    => 'Overuse',
);

$arr_sanction = array(
  'nofoul'     => 'No Foul',
  'oppfoul'    => 'Opponent Foul',
  'ownfoul'    => 'Own Foul',
  'yellow'     => 'Yellow Card',
  'red'        => 'Red Card',
);

$arr_surface = array(
  '1' => 'Pitch'      ,
  '2' => 'Training'   ,
  '3' => 'Artificial' ,
  '4' => 'Indoor'     ,
  '5' => 'Gym'        ,
  '6' => 'Other'      ,
);

$arr_position = array(
  '1' => 'Defender'          ,
  '2' => 'Midfield Offensive',
  '3' => 'Midfield Defensive',
  '4' => 'Forward'           ,
  '5' => 'Goal Keeper'       ,
  '6' => 'Substitute'        ,
);

$arr_footwear = array(
  '1' => 'Molded Stud'      ,
  '2' => 'Detachable Stud'  ,
  '3' => 'Indoor Shoes'     ,
  '4' => 'Blades'           ,
);

$arr_side = array(
  '1' => 'Left'          ,
  '2' => 'Right'         ,
  '3' => 'Bilateral'     ,
  '4' => 'Not Applicable',
);

$arr_removed = array(
  '1' => 'Immediately',
  '2' => 'Later'      ,
  '3' => 'Not at All' ,
);

$arr_match_type = array(
  '1' => 'Premiership',
  '2' => 'FA Cup',
  '3' => 'League Cup',
  '4' => 'Champions League Cup',
  '5' => 'Championship Match',
  '6' => 'League One Match',
  '7' => 'League Two Match',
  '8' => 'International Match',
  '9' => 'Friendly'
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
  $query = "REPLACE INTO lists_football_injury ( " .
    "id, fiinjmin, fiinjtime, fimatchtype, " .
    "fimech_tackling, fimech_tackled, fimech_collision, " .
    "fimech_kicked, fimech_elbow, fimech_nofoul, fimech_oppfoul, " .
    "fimech_ownfoul, fimech_yellow, fimech_red, fimech_passing, " .
    "fimech_shooting, fimech_running, fimech_dribbling, fimech_heading, " .
    "fimech_jumping, fimech_landing, fimech_fall, fimech_stretching, " .
    "fimech_turning, fimech_throwing, fimech_diving, fimech_overuse, " .
    "fimech_othercon, fimech_othernon, fisurface, fiposition, fifootwear, " .
    "fiside, firemoved " .
    ") VALUES ( " .
    $issue                          . ", " .
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
  sqlStatement($query);
}

function issue_football_injury_form($issue) {
  global $firow;
  if ($issue) {
    $firow = sqlQuery ("SELECT * FROM lists_football_injury WHERE id = '$issue'");
  } else {
    $firow = array();
  }
?>

<table border='1' width='98%' id='football_injury' style='display:none;margin-top:6pt;'>

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
       value='<? echo addslashes($firow['fiinjmin']) ?>' />
     </td>
     <? echo rbficell('form_injtime', '1', 'Warm Up'   , 'fiinjtime') ?>
     <? echo rbficell('form_injtime', '2', 'Extra Time', 'fiinjtime') ?>
     <? echo rbficell('form_injtime', '3', 'Cool Down' , 'fiinjtime') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Training</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbficell('form_injtime', '4', 'Warm Up'       , 'fiinjtime') ?>
     <? echo rbficell('form_injtime', '5', 'During Session', 'fiinjtime') ?>
     <? echo rbficell('form_injtime', '6', 'Cool Down'     , 'fiinjtime') ?>
     <? echo rbficell('form_injtime', '7', 'Rehabilitation', 'fiinjtime') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Match Type</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbficell('form_matchtype', '1', 'Premiership'         , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '2', 'FA Cup'              , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '3', 'League Cup'          , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '4', 'Champions League Cup', 'fimatchtype') ?>
    </tr>
    <tr>
     <? echo rbficell('form_matchtype', '5', 'Championship Match'  , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '6', 'League One Match'    , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '7', 'League Two Match'    , 'fimatchtype') ?>
     <? echo rbficell('form_matchtype', '8', 'International Match' , 'fimatchtype') ?>
    </tr>
    <tr>
     <? echo rbficell('form_matchtype', '9', 'Friendly'            , 'fimatchtype') ?>
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
     <? echo cbficell('form_mech_tackling' , 'Tackling' , 'fimech_tackling' ) ?>
     <? echo cbficell('form_mech_tackled'  , 'Tackled'  , 'fimech_tackled'  ) ?>
     <? echo cbficell('form_mech_collision', 'Collision', 'fimech_collision') ?>
     <? echo cbficell('form_mech_kicked'   , 'Kicked'   , 'fimech_kicked'   ) ?>
    </tr>
    <tr>
     <? echo cbficell('form_mech_elbow' , 'Use of Elbow' , 'fimech_elbow' ) ?>
     <td colspan='3' nowrap>
      Other:
      <input type='text' name='form_mech_othercon' size='10'
       title='Describe other'
       value='<? echo addslashes($firow['fimech_othercon']) ?>' />
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

 <tr>
  <td nowrap>Non Contact</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo cbficell('form_mech_passing'  , 'Passing'  , 'fimech_passing'  ) ?>
     <? echo cbficell('form_mech_shooting' , 'Shooting' , 'fimech_shooting' ) ?>
     <? echo cbficell('form_mech_running'  , 'Running'  , 'fimech_running'  ) ?>
     <? echo cbficell('form_mech_dribbling', 'Dribbling', 'fimech_dribbling') ?>
    </tr>
    <tr>
     <? echo cbficell('form_mech_heading'  , 'Heading'  , 'fimech_heading'  ) ?>
     <? echo cbficell('form_mech_jumping'  , 'Jumping'  , 'fimech_jumping'  ) ?>
     <? echo cbficell('form_mech_landing'  , 'Landing'  , 'fimech_landing'  ) ?>
     <? echo cbficell('form_mech_fall'     , 'Fall'     , 'fimech_fall'     ) ?>
    </tr>
    <tr>
     <? echo cbficell('form_mech_stretching', 'Stretching'      , 'fimech_stretching') ?>
     <? echo cbficell('form_mech_turning'   , 'Twisting/Turning', 'fimech_turning'   ) ?>
     <? echo cbficell('form_mech_throwing'  , 'Throwing'        , 'fimech_throwing'  ) ?>
     <? echo cbficell('form_mech_diving'    , 'Diving'          , 'fimech_diving'    ) ?>
    </tr>
    <tr>
     <? echo cbficell('form_mech_overuse', 'Overuse', 'fimech_overuse' ) ?>
     <td colspan='3' nowrap>
      Other:
      <input type='text' name='form_mech_othernon' size='10'
       title='Describe other'
       value='<? echo addslashes($firow['fimech_othernon']) ?>' />
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
     <? echo rbficell('form_surface', '1', 'Pitch'      , 'fisurface') ?>
     <? echo rbficell('form_surface', '2', 'Training'   , 'fisurface') ?>
     <? echo rbficell('form_surface', '3', 'Artificial' , 'fisurface') ?>
     <? echo rbficell('form_surface', '4', 'Indoor'     , 'fisurface') ?>
    </tr>
    <tr>
     <? echo rbficell('form_surface', '5', 'Gym'        , 'fisurface') ?>
     <? echo rbficell('form_surface', '6', 'Other'      , 'fisurface') ?>
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
     <? echo rbficell('form_position', '1', 'Defender'          , 'fiposition') ?>
     <? echo rbficell('form_position', '2', 'Midfield Offensive', 'fiposition') ?>
     <? echo rbficell('form_position', '3', 'Midfield Defensive', 'fiposition') ?>
     <? echo rbficell('form_position', '4', 'Forward'           , 'fiposition') ?>
    </tr>
    <tr>
     <? echo rbficell('form_position', '5', 'Goal Keeper'       , 'fiposition') ?>
     <? echo rbficell('form_position', '6', 'Substitute'        , 'fiposition') ?>
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
     <? echo rbficell('form_footwear', '1', 'Molded Stud'    , 'fifootwear') ?>
     <? echo rbficell('form_footwear', '2', 'Detachable Stud', 'fifootwear') ?>
     <? echo rbficell('form_footwear', '3', 'Indoor Shoes'   , 'fifootwear') ?>
     <? echo rbficell('form_footwear', '4', 'Blades'         , 'fifootwear') ?>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Side of Injury</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <? echo rbficell('form_side', '1', 'Left'          , 'fiside') ?>
     <? echo rbficell('form_side', '2', 'Right'         , 'fiside') ?>
     <? echo rbficell('form_side', '3', 'Bilateral'     , 'fiside') ?>
     <? echo rbficell('form_side', '4', 'Not Applicable', 'fiside') ?>
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
