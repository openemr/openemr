<?php

/*
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This script is ajax-loaded into a div by validate_csv.php, so much of
// the normal framework does not apply.
header('Content-Type: application/json');

require_once("../../globals.php");
require_once("translation_utilities.php");

$errmsg = '';

if (!$errmsg && !isset($_REQUEST['translations'])) {
    $errmsg = xlt("No translations!");
}

if (!$errmsg && !isset($_REQUEST['lang_id'])) {
    $errmsg = xlt("No Language ID specified");
}

if (!isset($_REQUEST['preview'])) {
    $preview = true;
} else {
    $preview = $_REQUEST['preview'];
    if ($preview === "false") {
        $preview = false;
    }
}

$unchanged = 0;
$empty = 0;
$changed = array();
$created = array();
$updated = array();

if (!$errmsg) {
    $lang_id = $_REQUEST['lang_id'];
    $translations = json_decode($_REQUEST['translations']);
    foreach ($translations as $translation) {
        $result = verify_translation(
            str_replace("\r\n", "\n", $translation[0]),
            str_replace("\r\n", "\n", $translation[1]),
            $lang_id,
            true,
            "",
            $preview
        );
        if (strpos($result, '[2]') !== 0) { // Definition Exists
            if (strpos($result, '[1]') !== 0) { // Empty Definition
                if ($result) {
                    array_push($changed, $result);
                    if (strpos($result, '[3]') === 0) {
                        array_push($updated, substr($result, 3));
                    } else if (strpos($result, '[5]') === 0) {
                        array_push($created, substr($result, 3));
                    }
                }
            } else {
                $empty++;
            }
        } else {
            $unchanged++;
        }
    }
}

if ($errmsg) {
    $created[] = xl('ERROR') . ': ' . $errmsg;
}

$retval = array();
$retval['changed'] = $changed;
$retval['unchanged'] = $unchanged;
$retval['empty'] = $empty;
$retval['updated'] = $updated;
$retval['created'] = $created;
$changes_html = "";
foreach ($changed as $change) {
    $changes_html .= $change . "<br>";
}
$retval['html_changes'] = $changes_html;
echo json_encode($retval);
