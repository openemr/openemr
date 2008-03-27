<?php
 include_once("../../globals.php");
 include_once("$srcdir/forms.inc");
 include_once("$srcdir/billing.inc");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/acl.inc");

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
  echo "<p>(".xl('Encounters not authorized').")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script language="JavaScript">
 function toencounter(enc, datestr) {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.left_nav.setEncounter(datestr, enc, window.name);
  parent.left_nav.setRadio(window.name, 'enc');
  location.href  = '../encounter/encounter_top.php?set_encounter=' + enc;
<?php } else { ?>
  top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
  top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
 }
</script>
</head>

<body class="body_top">

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href='encounters.php'>
<?php } else { ?>
<a href="patient_history.php" target="Main">
<?php } ?>
<font class="title"><?php xl('Past Encounters','e'); ?></font>
<font class=back><?php echo $tback;?></font></a><br>

<table width='100%'>
<tr>
<td><span class='bold'><?php xl('Date','e'); ?></span></td>
<td><span class='bold'><?php xl('Issue','e'); ?></span></td>
<td><span class='bold'><?php xl('Reason','e'); ?></span></td>
<td><span class='bold'><?php echo ($GLOBALS['phone_country_code'] == '1') ? 'Billing' : 'Coding' ?></span></td>
<td><span class='bold'><?php xl('Provider','e'); ?></span></td>
<?php if (!$GLOBALS['athletic_team']) { ?>
<td><span class='bold'><?php xl('Insurance','e'); ?></span></td>
<?php } ?>
</tr>

<?
 if ($result = getEncounters($pid)) {
  foreach ($result as $iter ) {

    // $href = "javascript:window.toencounter(" . $iter['encounter'] . ")";

    $reason_string = "";
    $auth_sensitivity = true;
    // $linkbeg = "<a class='text' href='$href'>"; // TBD: include target=Main ?
    $linkend = "</a>";
    if ($result4 = sqlQuery("select * from form_encounter where encounter='" .
      $iter{"encounter"} . "' and pid='$pid'"))
    {
      $raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
      $encounter_date = date("D F jS", strtotime($result4{"date"}));

      // if ($auth_notes_a || ($auth_notes && $iter['user'] == $_SESSION['authUser']))
      $reason_string .= $result4{"reason"} . "<br>\n";
      // else
      //  $reason_string = "(No access)";

      $href = "javascript:window.toencounter(" . $iter['encounter'] . ",\"$raw_encounter_date\")";
      $linkbeg = "<a class='text' href='$href'>";

      if ($result4['sensitivity']) {
        $auth_sensitivity = acl_check('sensitivities', $result4['sensitivity']);
        if (!$auth_sensitivity) {
          $reason_string = "(No access)";
          $linkbeg = "<span class='text'>";
          $linkend = "</span>";
        }
      }
    }

    $erow = sqlQuery("SELECT user FROM forms WHERE encounter = '" .
      $iter['encounter'] . "' AND formdir = 'newpatient' LIMIT 1");

    print "<tr>\n";

    // show encounter date
    echo "<td valign='top'>$linkbeg" .
      $raw_encounter_date . "$linkend</td>\n";

    // show issues for this encounter
    echo "<td valign='top'>$linkbeg";
    if ($auth_med && $auth_sensitivity) {
     $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
      "FROM issue_encounter, lists WHERE " .
      "issue_encounter.pid = '$pid' AND " .
      "issue_encounter.encounter = '" . $iter['encounter'] . "' AND " .
      "lists.id = issue_encounter.list_id " .
      "ORDER BY lists.type, lists.begdate");
     for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
      if ($i > 0) echo "<br>";
      $tcode = $irow['type'];
      if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
      echo "$tcode: " . $irow['title'];
     }
    } else {
     echo "(".xl('No access').")";
    }
    echo "$linkend</td>\n";

    // show encounter reason
    echo "<td valign='top'>$linkbeg" .
      $reason_string . "$linkend</td>\n";

    //this is where we print out the text of the billing that occurred on this encounter
    $thisauth = $auth_coding_a;
    if (!$thisauth && $auth_coding) {
     if ($erow['user'] == $_SESSION['authUser'])
      $thisauth = $auth_coding;
    }
    $coded = "";
    if ($thisauth && $auth_sensitivity) {
     if ($subresult2 = getBillingByEncounter($pid, $iter{"encounter"})) {
      foreach ($subresult2 as $iter2) {
       $coded .= "<span title='" . addslashes($iter2{"code_text"}) . "'>";
       $coded .= $iter2{"code"} . "</span>, ";
      }
      $coded = substr($coded, 0, strlen($coded) - 2);
     }
    } else {
     $coded = "(No access)";
    }
    echo "<td valign='top'>$linkbeg" .
      $coded . "$linkend</td>\n";

    // show provider
    echo "<td valign='top'>$linkbeg" .
      $erow['user'] . "$linkend</td>\n";

    // Show insurance.
    if (!$GLOBALS['athletic_team']) {
      $insured = "$raw_encounter_date";
      if ($auth_demo) {
        $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
        if ($subresult5 && $subresult5{"provider_name"}) {
          $insured = "<span class='text'>".xl('Primary').": " . $subresult5{"provider_name"} . "</span><br>\n";
        }
        $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
        if ($subresult6 && $subresult6{"provider_name"}) {
          $insured .= "<span class='text'>".xl('Secondary').": ".$subresult6{"provider_name"}."</span><br>\n";
        }
        $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
        if ($subresult6 && $subresult7{"provider_name"}) {
          $insured .= "<span class='text'>".xl('Tertiary').": ".$subresult7{"provider_name"}."</span><br>\n";
        }
      } else {
        $insured = "(No access)";
      }
      echo "<td valign='top'>$linkbeg" .
        $insured . "</a></td>\n";
    }

    print "</tr>\n";

    // Now show a line for each encounter form, if the user is authorized to
    // see this encounter's notes.
    //
    if ($auth_sensitivity && ($auth_notes_a || ($auth_notes && $iter['user'] == $_SESSION['authUser']))) {
      $encarr = getFormByEncounter($pid, $iter['encounter'], "formdir, user, form_name, form_id");
      foreach ($encarr as $enc) {
        if ($enc['formdir'] == 'newpatient') continue;
        $title = "";
        $frow = sqlQuery("select * from form_" . $enc['formdir'] .
          " where id = " . $enc['form_id']);
        foreach ($frow as $fkey => $fvalue) {
          if (! preg_match('/[A-Za-z]/', $fvalue)) continue;
          if ($title) $title .= "; ";
          $title .= strtoupper($fkey) . ': ' . $fvalue;
        }
        $title = htmlspecialchars(strtr($title, "\t\n\r", "   "), ENT_QUOTES);

        echo "<tr>\n";
        echo " <td valign='top' colspan='2'></td>\n";
        echo " <td valign='top' colspan='2' title='$title'>$linkbeg" .
          "&nbsp;&nbsp;&nbsp;" .
          $enc['form_name'] . "$linkend</td>\n";
        echo " <td valign='top'>$linkbeg" .
          $enc['user'] . "$linkend</td>\n";
        echo "</tr>\n";
      } // end foreach $encarr
    } // end if

  }
}

?>

</table>

</body>
</html>
