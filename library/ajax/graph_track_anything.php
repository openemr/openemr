<?php

/**
 * Trending script for graphing objects in track anything module.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Joe Slam <joe@produnis.de>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2014 Joe Slam <joe@produnis.de>
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// get $_POSTed data
$titleGraph       = json_decode($_POST['track'], true);
$the_date_array   = json_decode($_POST['dates'], true);
$the_value_array  = json_decode($_POST['values'], true);
$the_item_names   = json_decode($_POST['items'], true);
$the_checked_cols = json_decode($_POST['thecheckboxes'], true);
// ++++++/end get POSTed data

// check if something was sent
// and quit if not
//-------------------------------
if ($the_checked_cols == null) {
    // nothing to plot
    echo "No item checked,\n";
    echo "nothing to plot."; // DEBUG ONLY! COMMENT ME OUT!
    exit;
}

// end check if NULL data

// build labels
$data_final = array();
$data_final = xl('Date');
foreach ($the_checked_cols as $col) {
    if (is_numeric($the_value_array[$col][0])) {
        $data_final .= "\t" . $the_item_names[$col];
    } else {
        // is NOT numeric, so skip column
    }
}

$data_final .= "\n";

// build data
for ($i = 0; $i < count($the_date_array); $i++) {
    $data_final .= $the_date_array[$i];
    foreach ($the_checked_cols as $col) {
        if (is_numeric($the_value_array[$col][0])) {
            // is numeric
            $data_final .= "\t" . $the_value_array[$col][$i];
        } else {
            // is NOT numeric, do nothing
        }
    }

    $data_final .= "\n";
}

// Build and send back the json
$graph_build = array();
$graph_build['data_final'] = $data_final;
$graph_build['title'] = $titleGraph;

// Note need to also use " when building the $data_final rather
// than ' , or else JSON_UNESCAPED_SLASHES doesn't work and \n and
// \t get escaped.
echo json_encode($graph_build, JSON_UNESCAPED_SLASHES);
