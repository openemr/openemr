<?php

/**
 * sql_server_status.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*
 * I wrote this mainly to show server activity for transaction intensive upgrades
 * where the user can know we are still working though no activity from upgrade sequence.
 */

$ignoreAuth = true;
// to prevent config.php call keys table oops
$GLOBALS['ongoing_sql_upgrade'] = true;
$GLOBALS['connection_pooling_off'] = true; // force off database connection pooling
require_once(__DIR__ . '/../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

// this will ensure that the only script that can use this ajax call is the sql_upgrade.php script
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'sqlupgrade')) {
    CsrfUtils::csrfNotVerified();
}

$trans_query = <<< strQuery
Select * From INFORMATION_SCHEMA.PROCESSLIST
Where COMMAND <> 'Sleep'
And INFO NOT LIKE '%INFORMATION_SCHEMA.PROCESSLIST%'
And DB = ?;
strQuery;

if (isset($_POST['poll'])) {
    $cur_date = date("m/d H:i:s");
    $db_in_question = $GLOBALS ['dbase'];
    $stat_result = sqlStatementNoLog($trans_query, array($db_in_question));
    $q_msg = '';
    while ($stat_row = sqlFetchArray($stat_result)) {
        // Convert binary characters to a ? character
        $stat_row['INFO'] = mb_convert_encoding($stat_row['INFO'], 'UTF-8', 'UTF-8');
        // Several preg replaces to ensure no data is passed
        $stat_row['INFO'] = preg_replace(['!`.*?`!', '!\'.*?\'!', '!".*?"!', '![^A-Z]+!'], ['', '', '', ' * '], $stat_row['INFO']);
        $q_msg .= "<li class='text-primary'>";
        $q_msg .= text($cur_date) . "  " . text($_GET['poll']) . " " . text($stat_row['INFO']);
        $q_msg .= "</li>";
    }

    // inform the world!.
    header('Cache-Control: no-cache');

    echo $q_msg;

    exit();
}
