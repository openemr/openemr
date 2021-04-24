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

require_once("../../globals.php");
require_once("translation_utilities.php");

$errmsg = '';

if (!$errmsg && !isset($_REQUEST['translations'])) {
    $errmsg = xlt("No translations!");
}

if (!$errmsg && !isset($_REQUEST['lang_id'])) {
    $errmsg = xlt("No Language ID specified!");
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
            false,
            $preview
        );
        if (strpos($result, xl('Definition Exists') . ':') !== 0) {
            if (strpos($result, xl('Empty Definition')) !== 0) {
                if ($result) {
                    array_push($changed, $result);
                    if (strpos($result, xl('Update From') . ':') === 0) {
                        array_push($updated, $result);
                    } else if (strpos($result, xl('Create') . ':') === 0) {
                        array_push($created, $result);
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
