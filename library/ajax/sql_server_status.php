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
 * Not sure I can do csrf here!
 * */

$ignoreAuth = true;

require_once(__DIR__ . '/../../interface/globals.php');

$trans_query = <<< strQuery
Select * From INFORMATION_SCHEMA.PROCESSLIST
Where COMMAND <> 'Sleep'
And INFO NOT LIKE '%INFORMATION_SCHEMA.PROCESSLIST%'
And DB = ?;
strQuery;

if (isset($_GET['poll'])) {
    $cur_date = date("m/d H:i:s");
    $db_in_question = $GLOBALS ['dbase'];
    $stat_result = sqlStatementNoLog($trans_query, array($db_in_question));
    $q_msg = '';
    while ($stat_row = sqlFetchArray($stat_result)) {
        // remove select reporting.
        if (stripos($stat_row['INFO'], 'SELECT') !== false) {
            continue;
        }
        $q_msg .= "<li>";
        $q_msg .= $cur_date . "  " . $stat_row['INFO'];
        $q_msg .= "</li>";
    }

    // inform the world!.
    header('Cache-Control: no-cache');

    echo $q_msg;

    exit();
}
