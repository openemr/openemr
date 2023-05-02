<?php

/**
 * This file is used to add an item to the list_options table
 *
 * OUTPUT
 *   on error = NULL
 *   on success = JSON data, array of "value":"title" for new list of options
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jason Morrill <jason@italktech.net>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2009 Jason Morrill <jason@italktech.net>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    echo json_encode(array("error" => xl('Authentication Error') ));
    CsrfUtils::csrfNotVerified(false);
}

// check for required values
if ($_GET['listid'] == "" || trim($_GET['newitem']) == "" || trim($_GET['newitem_abbr']) == "") {
    exit;
}

// set the values for the new list item
$is_default = 0;
$list_id = $_GET['listid'];
$title = trim($_GET['newitem']);
$option_id = trim($_GET['newitem_abbr']);
$option_value = 0;

// make sure we're not adding a duplicate title or id
$exists_title = sqlQuery("SELECT * FROM list_options WHERE " .
    " list_id= ? " .
    " and title = ? AND activity = 1", array($list_id, $title));
if ($exists_title) {
    echo json_encode(array("error" => xl('Record already exist') ));
    exit;
}

$exists_id = sqlQuery("SELECT * FROM list_options WHERE " .
    " list_id= ?" .
    " AND option_id = ?" . "AND activity = 1", array($list_id, $option_id));
if ($exists_id) {
    echo json_encode(array("error" => xl('Record already exist') ));
    exit;
}

// determine the sequential order of the new item,
// it should be the maximum number for the specified list plus one
$seq = 0;
$row = sqlQuery("SELECT max(seq) as maxseq FROM list_options WHERE list_id = ? AND activity = 1", array($list_id));
$seq = $row['maxseq'] + 1;

// add the new list item
$rc = sqlInsert("INSERT INTO list_options ( " .
    "list_id, option_id, title, seq, is_default, option_value ) VALUES ( ?, ?, ?, ?, ?, ? )", array($list_id, $option_id, $title, $seq, $is_default, $option_value));

// return JSON data of list items on success
echo '{ "error":"", "options": [';
// send the 'Unassigned' empty variable
echo '{"id":"","title":' . xlj('Unassigned') . '}';
$comma = ",";
$lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq", array($list_id));
while ($lrow = sqlFetchArray($lres)) {
    echo $comma;
    echo '{"id":' . js_escape($lrow['option_id']) . ',';

    // translate title if translate-lists flag set and not english
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo '"title":' . xlj($lrow['title']) . '}';
    } else {
        echo '"title":' . js_escape($lrow['title']) . '}';
    }
}

echo "]}";
exit;
