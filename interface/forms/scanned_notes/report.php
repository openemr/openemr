<?php

/**
 * scanned_notes report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");

function scanned_notes_report($pid, $useless_encounter, $cols, $id)
{
    global $webserver_root, $web_root, $encounter;

 // In the case of a patient report, the passed encounter is vital.
    $thisenc = $useless_encounter ? $useless_encounter : $encounter;

    $count = 0;

    $data = sqlQuery("SELECT * " .
    "FROM form_scanned_notes WHERE " .
    "id = ? AND activity = '1'", array($id));

    if ($data) {
        if ($data['notes']) {
            echo "  <span class='bold'>Comments: </span><span class='text'>";
            echo nl2br(text($data['notes'])) . "</span><br />\n";
        }

        for ($i = -1; true; ++$i) {
             $suffix = ($i < 0) ? "" : "-$i";
             $imagepath = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($pid) . "/encounters/" . check_file_dir_name($thisenc) . "_" . check_file_dir_name($id) . check_file_dir_name($suffix) . ".jpg";
             $imageurl  = $web_root . "/sites/" . $_SESSION['site_id'] . "/documents/" . check_file_dir_name($pid) . "/encounters/" . check_file_dir_name($thisenc) . "_" . check_file_dir_name($id) . check_file_dir_name($suffix) . ".jpg";
            if (is_file($imagepath)) {
                echo "   <img src='$imageurl'";
                // Flag images with excessive width for possible stylesheet action.
                $asize = getimagesize($imagepath);
                if ($asize[0] > 750) {
                    echo " class='bigimage'";
                }

                echo " />\n";
                echo " <br />\n";
            } else {
                if ($i >= 0) {
                    break;
                }
            }
        }
    }
}
