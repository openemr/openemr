<?php

/**
 * This script add and delete Issues and Encounters relationships.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2015-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$patdata = getPatientData($pid, "fname,lname,squad");

$thisauth = ((AclMain::aclCheckCore('encounters', 'notes', '', 'write') ||
            AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) &&
            AclMain::aclCheckCore('patients', 'med', '', 'write'));

if ($patdata['squad'] && ! AclMain::aclCheckCore('squads', $patdata['squad'])) {
     $thisauth = 0;
}

if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

$alertmsg = ""; // anything here pops up in an alert box
$endjs = "";    // holds javascript to write at the end

// If the Save button was clicked...
if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $form_pid = $_POST['form_pid'];
    $form_pelist = $_POST['form_pelist'];
    // $pattern = '|/(\d+),(\d+),([YN])|';
    $pattern = '|/(\d+),(\d+)|';

    preg_match_all($pattern, $form_pelist, $matches);
    $numsets = count($matches[1]);

    $query = "DELETE FROM issue_encounter WHERE pid = ?";
    sqlQuery($query, array($form_pid));
    for ($i = 0; $i < $numsets; ++$i) {
        $list_id   = $matches[1][$i];
        $encounter = $matches[2][$i];
        $query = "INSERT INTO issue_encounter ( " .
            "pid, list_id, encounter" .
            ") VALUES ( " .
            " ?, ?, ?" .
            ")";
        sqlQuery($query, array($form_pid, $list_id, $encounter));
    }

    echo "<html><body>"
    . "<script type=\"text/javascript\" src=\"" . $webroot . "/interface/main/tabs/js/include_opener.js\"></script>"
    . "<script>\n";
    if ($alertmsg) {
        echo " alert(" . js_escape($alertmsg) . ");\n";
    }

    echo " var myboss = opener ? opener : parent;\n";
    echo " myboss.location.reload();\n";
    echo " dlgclose();\n";
    echo "</script></body></html>\n";
    exit();
}

// get problems
$pres = sqlStatement("SELECT * FROM lists WHERE pid = ? " .
"ORDER BY type, date", array($pid));

// get encounters
$eres = sqlStatement("SELECT * FROM form_encounter WHERE pid = ? " .
"ORDER BY date DESC", array($pid));

// get problem/encounter relations
$peres = sqlStatement("SELECT * FROM issue_encounter WHERE pid = ?", array($pid));
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener', 'topdialog', 'dialog']); ?>

<title><?php echo xlt('Issues and Encounters'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; background-color:#eeeeee; }
</style>

<script>

// These are the possible colors for table rows.
var trcolors = new Object();
// Colors for:            Foreground Background
trcolors['U'] = new Array('var(--black)', 'var(--gray200)'); // unselected
trcolors['K'] = new Array('var(--black)', 'var(--yellow)'); // selected key
trcolors['V'] = new Array('var(--black)', 'var(--indigo)'); // selected value

var pselected = new Object();
var eselected = new Object();
var keyid = null; // id of currently hilited key, if any

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
 top.restoreSession();
 location.reload();
}

// New Issue button is clicked.
function newIssue() {
 var f = document.forms[0];
 var tmp = (keyid && f.form_key[1].checked) ? ('?enclink=' + encodeURIComponent(keyid)) : '';
 dlgopen('summary/add_edit_issue.php' + tmp, '_blank', 600, 625);
}

// Determine if a given problem/encounter pair is currently linked.
// If yes, return the "resolved" character (Y or N), else an empty string.
function isPair(problem, encounter) {
 var pelist = document.forms[0].form_pelist;
 // var frag = '/' + problem + ',' + encounter + ',';
 var frag = '/' + problem + ',' + encounter + '/';
 var i = pelist.value.indexOf(frag);
 if (i < 0) return '';
 // return pelist.value.charAt(i + frag.length);
 return 'V';
}

// Unlink a problem/encounter pair.
function removePair(problem, encounter) {
 var pelist = document.forms[0].form_pelist;
 // var frag = '/' + problem + ',' + encounter + ',';
 var frag = '/' + problem + ',' + encounter + '/';
 var i = pelist.value.indexOf(frag);
 if (i >= 0) {
  // pelist.value = pelist.value.substring(0, i) + pelist.value.substring(i + frag.length + 1);
  pelist.value = pelist.value.substring(0, i) + pelist.value.substring(i + frag.length - 1);
  document.forms[0].form_save.disabled = false;
 }
}

// Link a new or modified problem/encounter pair.
// function addPair(problem, encounter, resolved) {
function addPair(problem, encounter) {
 removePair(problem, encounter);
 var pelist = document.forms[0].form_pelist;
 // pelist.value += '' + problem + ',' + encounter + ',' + resolved + '/';
 pelist.value += '' + problem + ',' + encounter + '/';
 document.forms[0].form_save.disabled = false;
}

// Clear displayed highlights.
function doclearall(pfx) {
 var thisarr = (pfx == 'p') ? pselected : eselected;
 for (var id in thisarr) {
  var thistr = document.getElementById(pfx + '_' + id);
  if (thisarr[id]) {
   thisarr[id] = '';
   thistr.style.color = trcolors['U'][0];
   thistr.style.backgroundColor = trcolors['U'][1];
  }
 }
}

function clearall() {
 doclearall('p');
 doclearall('e');
 keyid = null;
}

// Process clicks on table rows.
function doclick(pfx, id) {
 var thisstyle = document.getElementById(pfx + '_' + id).style;
 var thisarr = (pfx == 'p') ? pselected : eselected;
 var piskey = document.forms[0].form_key[0].checked;
 var thisiskey = (pfx == 'p') ? piskey : !piskey;
 var wasset = thisarr[id];
 if (thisiskey) { // they clicked in the key table
  clearall();
  if (!wasset) { // this item is not already hilited
   keyid = id;
   thisarr[id] = 'K';
   thisstyle.color = trcolors['K'][0];
   thisstyle.backgroundColor = trcolors['K'][1];
   // Now hilite the related value table entries:
   if (pfx == 'p') { // key is problems, values are encounters
    for (key in eselected) {
     var resolved = isPair(id, key);
     if (resolved.length > 0) {
      eselected[key] = resolved;
      var valstyle = document.getElementById('e_' + key).style;
      valstyle.color = trcolors[resolved][0];
      valstyle.backgroundColor = trcolors[resolved][1];
     }
    }
   } else { // key is encounters, values are problems
    for (key in pselected) {
     var resolved = isPair(key, id);
     if (resolved.length > 0) {
      pselected[key] = resolved;
      var valstyle = document.getElementById('p_' + key).style;
      valstyle.color = trcolors[resolved][0];
      valstyle.backgroundColor = trcolors[resolved][1];
     }
    }
   }
  }
 } else { // they clicked in the value table
  if (keyid) {
   var resolved = thisarr[id];
   // if (resolved == 'Y') { // it was hilited and resolved, change to unresolved
   //  thisarr[id] = 'N';
   //  thisstyle.color = trcolors['N'][0];
   //  thisstyle.backgroundColor = trcolors['N'][1];
   //  if (pfx == 'p') addPair(id, keyid, 'N'); else addPair(keyid, id, 'N');
   // } else if (resolved == 'N') { // it was hilited and unresolved, remove it
   if (resolved != '') { // hilited, so remove it
    thisarr[id] = '';
    thisstyle.color = trcolors['U'][0];
    thisstyle.backgroundColor = trcolors['U'][1];
    if (pfx == 'p') removePair(id, keyid); else removePair(keyid, id);
   // } else { // not hilited, change to hilited and resolved
   //  thisarr[id] = 'Y';
   //  thisstyle.color = trcolors['Y'][0];
   //  thisstyle.backgroundColor = trcolors['Y'][1];
   //  if (pfx == 'p') addPair(id, keyid, 'Y'); else addPair(keyid, id, 'Y');
   } else { // not hilited, change to hilited
    thisarr[id] = 'V';
    thisstyle.color = trcolors['V'][0];
    thisstyle.backgroundColor = trcolors['V'][1];
    if (pfx == 'p') addPair(id, keyid); else addPair(keyid, id);
   }
  } else {
   alert(<?php echo xlj('You must first select an item in the section whose radio button is checked.') ;?>);
  }
 }
}

</script>

</head>
<body>
    <div class="container">
        <form method='post' action='problem_encounter.php' onsubmit='return top.restoreSession()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <?php
            echo "<input type='hidden' name='form_pid' value='" . attr($pid) . "' />\n";
            // pelist looks like /problem,encounter/problem,encounter/[...].
            echo "<input type='hidden' name='form_pelist' value='/";
            while ($row = sqlFetchArray($peres)) {
                // echo $row['list_id'] . "," . $row['encounter'] . "," .
                //  ($row['resolved'] ? "Y" : "N") . "/";
                echo attr($row['list_id']) . "," . attr($row['encounter']) . "/";
            }
            echo "' />\n";
            ?>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <td colspan='2' class="text-center font-weight-bold">
                            <?php echo xlt('Issues and Encounters for'); ?> <?php echo text($patdata['fname']) . " " . text($patdata['lname']) . " (" . text($pid) . ")</b>\n"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center align-top p-0 pl-3">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr class='head'>
                                        <td colspan='3' class="text-center">
                                            <input type='radio' name='form_key' value='p' onclick='clearall()' checked />
                                            <span class="font-weight-bold"><?php echo xlt('Issues Section'); ?></span>
                                        </td>
                                    </tr>
                                    <tr class='head'>
                                        <td><?php echo xlt('Type'); ?></td>
                                        <td><?php echo xlt('Title'); ?></td>
                                        <td><?php echo xlt('Description'); ?></td>
                                    </tr>
                                    <?php
                                    while ($row = sqlFetchArray($pres)) {
                                        $rowid = $row['id'];
                                        echo "    <tr class='detail' id='p_" . attr($rowid) . "' onclick='doclick(\"p\", " . attr_js($rowid) . ")'>\n";
                                        echo "     <td class='align-top'>" . text($ISSUE_TYPES[($row['type'])][1]) . "</td>\n";
                                        echo "     <td class='align-top'>" . text($row['title']) . "</td>\n";
                                        echo "     <td class='align-top'>" . text($row['comments']) . "</td>\n";
                                        echo "    </tr>\n";
                                        $endjs .= "pselected[" . js_escape($rowid) . "] = '';\n";
                                    }
                                    ?>
                                </table>
                            </div>
                        </td>
                        <td class="text-center align-top p-0 pr-3">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr class='head'>
                                        <td colspan='2' class="text-center">
                                            <input type='radio' name='form_key' value='e' onclick='clearall()' />
                                            <span class="font-weight-bold"><?php echo xlt('Encounters Section'); ?></span>
                                        </td>
                                    </tr>
                                    <tr class='head'>
                                        <td><?php echo xlt('Date'); ?></td>
                                        <td><?php echo xlt('Presenting Complaint'); ?></td>
                                    </tr>
                                    <?php
                                    while ($row = sqlFetchArray($eres)) {
                                        $rowid = $row['encounter'];
                                        echo "    <tr class='detail' id='e_" . attr($rowid) . "' onclick='doclick(\"e\", " . attr_js($rowid) . ")'>\n";
                                        echo "     <td class='align-top'>" . text(substr($row['date'], 0, 10)) . "</td>\n";
                                        echo "     <td class='align-top'>" . text($row['reason']) . "</td>\n";
                                        echo "    </tr>\n";
                                        $endjs .= "eselected[" . js_escape($rowid) . "] = '';\n";
                                    }
                                    ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2' class="text-center">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary btn-sm btn-save" name='form_save' value='<?php echo xla('Save'); ?>' disabled>
                                    <?php echo xlt('Save'); ?>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm btn-add" value='<?php echo xla('Add Issue'); ?>' onclick='newIssue()'>
                                    <?php echo xlt('Add Issue'); ?>
                                </button>
                                <button type="button" class='btn btn-secondary btn-sm btn-cancel' onclick='dlgclose()'><?php echo xla('Cancel'); ?></button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <p>
            <span class="font-weight-bold"><?php echo xlt('Instructions:'); ?></span> <?php echo xlt('Choose a section and click an item within it; then in the other section you will see the related items highlighted, and you can click in that section to add and delete relationships.'); ?>
        </p>
    </div>
<script>
<?php
 echo $endjs;
if (!empty($_REQUEST['issue'])) {
    echo "doclick('p', " . js_escape($_REQUEST['issue']) . ");\n";
}

if ($alertmsg) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}
?>
</script>
</body>
</html>
