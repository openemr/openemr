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
$GLOBALS['connection_pooling_off'] = true; // force off database connection pooling
require_once(__DIR__ . '/../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
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
        $stat_row['INFO'] = preg_replace('![a-z]+!', '*', $stat_row['INFO']);
        $q_msg .= "<li class='text-primary'>";
        $q_msg .= $cur_date . "  " . $_GET['poll'] . " " . $stat_row['INFO'];
        $q_msg .= "</li>";
    }

    // inform the world!.
    header('Cache-Control: no-cache');

    echo $q_msg;

    exit();
}
