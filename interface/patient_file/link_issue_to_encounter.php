<?php
/**
 * This links a specified or newly created GCAC issue to a specified
 * encounter. It is invoked from pos_checkout.php via a jquery getScript().
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");

$issue   = 0 + (empty($_REQUEST['issue']) ? 0 : $_REQUEST['issue']);
$thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
$thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);

if (!acl_check('patients', 'med')) {
    echo "alert(" . xlj('Not authorized') . ");\n";
    exit();
}

if (!($thisenc && $thispid)) {
    echo "alert(" . xlj('Internal error: pid or encounter is missing.') . ");\n";
    exit();
}

$msg = xl('Internal error!');

if ($issue) {
    $msg = xl('Issue') . " $issue " . xl('has been linked to visit') .
    " $thispid.$thisenc.";
} else {
    $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, user, groupname " .
    ") VALUES ( " .
    "NOW(), " .
    "?, " .
    "'ippf_gcac', " .
    "?, " .
    "1, " .
    "'', " .
    "?, " .
    "?, " .
    "? " .
    ")", array($thispid, xl('Auto-generated'), date('Y-m-d'), $_SESSION['authUser'], $_SESSION['authProvider']));

    if ($issue) {
        sqlStatement("INSERT INTO lists_ippf_gcac ( `id` ) VALUES ( ? )", array($issue));
        $msg = xl('An incomplete GCAC issue has been created and linked. Someone will need to complete it later.');
    }
}

if ($issue) {
    $query = "INSERT INTO issue_encounter ( " .
    "pid, list_id, encounter " .
    ") VALUES ( " .
    "?, ?, ?" .
    ")";
    sqlStatement($query, array($thispid, $issue, $thisenc));
}

echo "alert(" . js_escape($msg) . ");\n";
