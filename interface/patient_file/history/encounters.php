<?
 include_once("../../globals.php");
 include_once("$srcdir/forms.inc");
 include_once("$srcdir/billing.inc");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/acl.inc");

 //maximum number of encounter entries to display on this page:
 $N = 12;

 // Get relevant ACL info.
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_coding_a = acl_check('encounters', 'coding_a');
 $auth_coding   = acl_check('encounters', 'coding');
 $auth_relaxed  = acl_check('encounters', 'relaxed');
 $auth_med      = acl_check('patients'  , 'med');
 $auth_demo     = acl_check('patients'  , 'demo');

 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  $auth_notes_a = $auth_notes = $auth_coding_a = $auth_coding = $auth_med = $auth_demo = $auth_relaxed = 0;

 if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
  echo "<body>\n<html>\n";
  echo "<p>(Encounters not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script language="JavaScript">
 function toencounter(enc) {
  top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
  top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
 }
</script>
</head>

<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<a href="encounters_full.php" target="Main"><font class="title">Past Encounters</font><font class=more><?echo $tmore;?></font></a><br>

<table width="100%">
<tr>
<td><span class=bold>Date</span></td>
<td><span class=bold>Reason</span></td>
<td><span class=bold>Issue</span></td>
<td><span class=bold><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Billing' : 'Coding' ?></span></td>
<td><span class=bold>Insurance</span></td>
</tr>

<?
$count = 0;
if ($result = getEncounters($pid)) {
  foreach ($result as $iter ) {
    $count++;
    if ($count > $N) {
      //we have more encounters to print, but we've reached our display maximum
      print "<tr><td colspan='4' align='center'><a target='Main' href='encounters_full.php' class='alert'>Some encounters were not displayed. Click here to view all.</a></td></tr>\n";
      break;
    }

    $reason_string = "";
    if ($result4 = sqlQuery("select * from form_encounter where encounter='" .
      $iter{"encounter"} . "' and pid='$pid'"))
    {
      $raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
      $encounter_date = date("D F jS", strtotime($result4{"date"}));

      // if ($auth_notes_a || ($auth_notes && $iter['user'] == $_SESSION['authUser']))
      $reason_string .= $result4{"reason"} . "<br>\n";
      // else
      //   $reason_string = "(No access)";
    }

    echo "<tr>\n";

    echo "<td valign='top'><a class='text' " .
      "href='javascript:window.toencounter(" . $iter{"encounter"} . ")'>" .
      $raw_encounter_date . "</a></td>\n";

    echo "<td valign='top'><a class='text' " .
      "href='javascript:window.toencounter(" . $iter{"encounter"} . ")'>" .
      $reason_string . "</a></td>\n";

    // show issues for this encounter
    echo "<td valign='top'><a class='text' " .
      "href='javascript:window.toencounter(" . $iter{"encounter"} . ")'>";

    if ($auth_med) {
     $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
      "FROM issue_encounter, lists WHERE " .
      "issue_encounter.pid = '$pid' AND " .
      "issue_encounter.encounter = '" . $iter['encounter'] . "' AND " .
      "lists.id = issue_encounter.list_id " .
      "ORDER BY lists.type, lists.begdate");
     for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
      if ($i > 0) echo "<br>";
      $tcode = $irow['type'];

      /****
      if ($tcode == 'medical_problem' || $tcode == 'problem') $tcode = 'P';
      else if ($tcode == 'allergy')    $tcode = 'A';
      else if ($tcode == 'medication') $tcode = 'M';
      else if ($tcode == 'surgery')    $tcode = 'S';
      ****/
      if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];

      echo "$tcode: " . $irow['title'];
     }
    } else {
     echo "(No access)";
    }

    echo "</a></td>\n";

    //this is where we print out the text of the billing that occurred on this encounter
    $thisauth = $auth_coding_a;
    if (!$thisauth && $auth_coding) {
     $erow = sqlQuery("SELECT user FROM forms WHERE encounter = '" .
      $iter['encounter'] . "' AND formdir = 'newpatient' LIMIT 1");
     if ($erow['user'] == $_SESSION['authUser'])
      $thisauth = $auth_coding;
    }
    $coded = "";
    if ($thisauth) {
     if ($subresult2 = getBillingByEncounter($pid,$iter{"encounter"})) {
      foreach ($subresult2 as $iter2) {
       $coded .= "<span title='" . addslashes($iter2{"code_text"}) . "'>";
       $coded .= $iter2{"code"} . "</span>, ";
      }
      $coded = substr($coded, 0, strlen($coded) - 2);
     }
    } else {
     $coded = "(No access)";
    }

    echo "<td valign='top'><a class='text' " .
      "href='javascript:window.toencounter(" . $iter{"encounter"} . ")'>" .
      $coded . "</a></td>\n";

    // Show insurance.
    $insured = "$raw_encounter_date";
    if ($auth_demo) {
      $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
      if ($subresult5 && $subresult5{"provider_name"}) {
        $insured = "<span class='text'>Primary: " . $subresult5{"provider_name"} . "</span><br>\n";
      }
      $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
      if ($subresult6 && $subresult6{"provider_name"}) {
        $insured .= "<span class='text'>Secondary: ".$subresult6{"provider_name"}."</span><br>\n";
      }
      $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
      if ($subresult6 && $subresult7{"provider_name"}) {
        $insured .= "<span class='text'>Tertiary: ".$subresult7{"provider_name"}."</span><br>\n";
      }
    } else {
      $insured = "(No access)";
    }

    echo "<td valign='top'><a class='text' " .
      "href='javascript:window.toencounter(" . $iter{"encounter"} . ")'>" .
      $insured . "</a></td>\n";

    echo "</tr>\n";
  }
}
?>

</table>

</body>
</html>
