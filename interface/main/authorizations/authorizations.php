<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/log.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/transactions.inc");
include_once("$srcdir/lists.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/options.inc.php");

// The number of authorizations to display in the quick view:
// MAR 20041008 the full authorizations screen sucks... no links to the patient charts
// increase to a high number to make the mini frame more useful.
$N = 50;

$atemp = sqlQuery("SELECT see_auth FROM users WHERE username = ?", array($_SESSION['authUser']) );
$see_auth = $atemp['see_auth'];

$imauthorized = $_SESSION['userauthorized'] || $see_auth > 2;

// This authorizes everything for the specified patient.
if (isset($_GET["mode"]) && $_GET["mode"] == "authorize" && $imauthorized) {
  $retVal = getProviderId($_SESSION['authUser']);	
  newEvent("authorize", $_SESSION["authUser"], $_SESSION["authProvider"], 1, $_GET["pid"]);
  sqlStatement("update billing set authorized=1 where pid=?", array($_GET["pid"]) );
  sqlStatement("update forms set authorized=1 where pid=?", array($_GET["pid"]) );
  sqlStatement("update pnotes set authorized=1 where pid=?", array($_GET["pid"]) );
  sqlStatement("update transactions set authorized=1 where pid=?", array($_GET["pid"]) );
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>
<style>
/* min & max buttons are hidden in the newer concurrent layout */
#min {
    float: right;
    padding: 3px;
    margin: 2px;
    cursor: pointer; cursor: hand;
    <?php if ($GLOBALS['concurrent_layout']) echo "display: none;"; ?>
}
#max {
    float: right;
    padding: 3px;
    margin: 2px;
    cursor: pointer; cursor: hand;
    <?php if ($GLOBALS['concurrent_layout']) echo "display: none;"; ?>
}
</style>
</head>
<body class="body_bottom">

<!-- 'buttons' to min/max the bottom frame -JRM -->
<div id="max" title="Restore this information">
<img src="<?php echo $GLOBALS['webroot']; ?>/images/max.gif">
</div>
<div id="min" title="Minimize this information">
<img src="<?php echo $GLOBALS['webroot']; ?>/images/min.gif">
</div>

<?php if ($imauthorized) { ?>
<span class='title'>
<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href='authorizations_full.php'>
<?php } else { ?>
<a href='authorizations_full.php' target='Main'>
<?php } ?>
<?php echo htmlspecialchars(xl('Authorizations'),ENT_NOQUOTES); ?> <span class='more'><?php echo htmlspecialchars($tmore,ENT_NOQUOTES); ?></span></a>
<?php 
	}
?>
</span>

<?php if (!$GLOBALS['concurrent_layout']) { ?>
<span class='more'> &nbsp;
<a href="#" id="findpatients" name='Find Patients'>(<?php echo htmlspecialchars(xl('Find Patient'),ENT_NOQUOTES); ?>)</a>
</span>
<?php } ?>

<?php
if ($imauthorized && $see_auth > 1) {

//  provider
//  billing
//  forms
//  pnotes
//  transactions

//fetch billing information:
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user " .
  "from billing LEFT JOIN users as u on billing.user = u.id where " .
  "billing.authorized = 0 and billing.activity = 1 and " .
  "groupname = ?", array($groupname) ))
{
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $result1[$iter] = $row;
  if ($result1) {
    foreach ($result1 as $iter) {
      $authorize{$iter{"pid"}}{"billing"} .= "<span class=text>" .
        htmlspecialchars($iter{"code_text"} . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
        "</span><br>\n";
    }
  }
}

//fetch transaction information:
if ($res = sqlStatement("select * from transactions where " .
  "authorized = 0 and groupname = ?", array($groupname) ))
{
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $result2[$iter] = $row;
  if ($result2) {
    foreach ($result2 as $iter) {
      $authorize{$iter{"pid"}}{"transaction"} .= "<span class=text>" .
        htmlspecialchars($iter{"title"} . ": " . (strterm($iter{"body"},25)) . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
	"</span><br>\n";
    }
  }
}

if (empty($GLOBALS['ignore_pnotes_authorization'])) {
  //fetch pnotes information:
  if ($res = sqlStatement("select * from pnotes where authorized = 0 and " .
    "groupname = ?", array($groupname) ))
  {
    for ($iter = 0;$row = sqlFetchArray($res);$iter++)
      $result3[$iter] = $row;
    if ($result3) {
      foreach ($result3 as $iter) {
        $authorize{$iter{"pid"}}{"pnotes"} .= "<span class=text>" .
          htmlspecialchars((strterm($iter{"body"},25)) . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
	  "</span><br>\n";
      }
    }
  }
}

//fetch forms information:
if ($res = sqlStatement("select * from forms where authorized = 0 and " .
  "groupname = ?", array($groupname) ))
{
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $result4[$iter] = $row;
  if ($result4) {
    foreach ($result4 as $iter) {
      $authorize{$iter{"pid"}}{"forms"} .= "<span class=text>" .
        htmlspecialchars($iter{"form_name"} . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
        "</span><br>\n";
    }
  }
}
?>

<table border='0' cellpadding='0' cellspacing='2' width='100%'>
<tr>
<td valign='top'>

<?php
if ($authorize) {
  $count = 0;

  while (list($ppid,$patient) = each($authorize)) {
    $name = getPatientData($ppid);

    // If I want to see mine only and this patient is not mine, skip it.
    if ($see_auth == 2 && $_SESSION['authUserID'] != $name['id'])
      continue;

    if ($count >= $N) {
      print "<tr><td colspan='5' align='center'><a" .
        ($GLOBALS['concurrent_layout'] ? "" : " target='Main'") .
        " href='authorizations_full.php?active=1' class='alert'>" .
        htmlspecialchars(xl('Some authorizations were not displayed. Click here to view all'),ENT_NOQUOTES) .
        "</a></td></tr>\n";
      break;
    }

    echo "<tr><td valign='top'>";
    if ($GLOBALS['concurrent_layout']) {
      // Clicking the patient name will load both frames for that patient,
      // as demographics.php takes care of loading the bottom frame.

        echo "<a href='$rootdir/patient_file/summary/demographics.php?set_pid=" .
	  htmlspecialchars($ppid,ENT_QUOTES) . "' target='RTop'>";

    } else {
      echo "<a href='$rootdir/patient_file/patient_file.php?set_pid=" .
	htmlspecialchars($ppid,ENT_QUOTES) . "' target='_top'>";
    }
    echo "<span class='bold'>" . htmlspecialchars($name{"fname"},ENT_NOQUOTES) . " " .
      htmlspecialchars($name{"lname"},ENT_NOQUOTES) . "</span></a><br>" .
      "<a class=link_submit href='authorizations.php?mode=authorize" .
      "&pid=" . htmlspecialchars($ppid,ENT_QUOTES) . "'>" .
      htmlspecialchars(xl('Authorize'),ENT_NOQUOTES) . "</a></td>\n";

    /****
    //Michael A Rowley MD 20041012.
    // added below 4 lines to add provider to authorizations for ez reference.
    $providerID = sqlFetchArray(sqlStatement(
      "select providerID from patient_data where pid=?", array($ppid) ));
    $userID=$providerID{"providerID"};
    $providerName = sqlFetchArray(sqlStatement(
      "select lname from users where id=?", array($userID) ));
    ****/
    // Don't use sqlQuery because there might be no match.
    $providerName = sqlFetchArray(sqlStatement(
      "select lname from users where id = ?", array($name['providerID']) ));
      
    echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Provider'),ENT_NOQUOTES).":</span><span class=text><br>" .
      htmlspecialchars($providerName{"lname"},ENT_NOQUOTES) . "</td>\n";
    echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Billing'),ENT_NOQUOTES).":</span><span class=text><br>" .
      $patient{"billing"} . "</td>\n";
    echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Transactions'),ENT_NOQUOTES).":</span><span class=text><br>" .
      $patient{"transaction"} . "</td>\n";
    echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Patient Notes'),ENT_NOQUOTES).":</span><span class=text><br>" .
      $patient{"pnotes"} . "</td>\n";
    echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Encounter Forms'),ENT_NOQUOTES).":</span><span class=text><br>" .
      $patient{"forms"} . "</td>\n";
    echo "</tr>\n";

    $count++;
  }
}
?>

</td>

</tr>
</table>

<?php } ?>

</body>
<script language='JavaScript'>

/* added to adjust the height of this frame by the min/max buttons */
var origRows = null;
$(document).ready(function(){
    $("#findpatients").click(function() { RestoreFrame(this); document.location.href='../calendar/find_patient.php?no_nav=1&mode=reset'; return true; });
    
    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".noterow").click(function() { EditNote(this); });

    <?php if ($GLOBALS['concurrent_layout'] == 0) : ?>
    $("#min").click(function() { MinimizeFrame(this); });
    $("#max").click(function() { RestoreFrame(this); });
    var frmset = parent.document.getElementById('Main');
    origRows = frmset.rows;  // save the original frameset sizes
    <?php endif; ?>
});

<?php if ($GLOBALS['concurrent_layout'] == 0) : ?>
var MinimizeFrame = function(eventObject) {
    var frmset = parent.document.getElementById('Main');
    origRows = frmset.rows;  // save the original frameset sizes
    frmset.rows = "*, 10%";
}
var RestoreFrame = function(eventObject) {
    // restore the original frameset size
    var frmset = parent.document.getElementById('Main');
    if (origRows != null) { frmset.rows = origRows; }
}
<?php endif; ?>

var EditNote = function(note) {
    var parts = note.id.split("~");
<?php if (true): ?>
    top.restoreSession();
    <?php if ($GLOBALS['concurrent_layout']): ?>
    location.href = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/pnotes_full.php?noteid=" + parts[1] + "&set_pid=" + parts[0] + "&active=1";
    <?php else: ?>
    top.location.href = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/patient_file.php?noteid=" + parts[1] + "&set_pid=" + parts[0];
    <?php endif; ?>
<?php else: ?>
    // no-op
    alert("<?php echo htmlspecialchars(xl('You do not have access to view/edit this note'),ENT_QUOTES); ?>");
<?php endif; ?>
}

</script>

</html>
