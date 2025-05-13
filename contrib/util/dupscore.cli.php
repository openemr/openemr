#!/usr/bin/php
<?php

/**
 * CLI script to compute patient duplication scores in patient_data.dupscore.
 * The score is a measure of the likelihood that the patient is a duplicate of
 * some patient created before it. Optional arguments specifying values are:
 *
 * --webdir   The full path to the OpenEMR web directory. Defaults to the directory
 *            two levels above that of this script.
 * --site     The site ID. Defaults to "default".
 * --maxmins  The maximum number of minutes to run. Defaults to 60. Use 0 for no limit.
 *
 * Arguments not having a value may be:
 *
 * -q         Suppresses messages on stdout.
 * -c         Clears existing scores to recompute all of them; except scores of -1
 *            are not cleared because they are manually assigned.
 *
 * Because we are comparing every patient with every other patient, this script can
 * run for a very long time with a large database. Thus we want to do it offline.
 * If --maxmins is exceeded the script will terminate but may be run again to resume
 * where it left off.
 *
 * A common usage is:
 * php /var/www/html/openemr/contrib/util/dupscore.cli.php --maxmins=240
 *
 * Here is a sample crontab entry to automatically run up to 2 hours nightly:
 * 3 1 * * * root php /var/www/html/openemr/contrib/util/dupscore.cli.php -q --maxmins=120
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// The number of scores to compute between tests for time expiration.
$querylimit = 1000;

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line!\n");
}

$args = getopt('cq', array('webdir:', 'site:', 'maxmins:'));

// print_r($args); // debugging

$args['webdir'] = $args['webdir'] ?? dirname(dirname(dirname(__FILE__)));
$args['site'] = $args['site'] ?? 'default';
$args['maxmins'] = floatval($args['maxmins'] ?? 60);

if (stripos(PHP_OS, 'WIN') === 0) {
    $args['webdir'] = str_replace("\\", "/", $args['webdir']);
}

// Bring in some libraries and settings shared with web scripts.
$_GET['site'] = $args['site'];
$ignoreAuth = 1;
require_once($args['webdir'] . "/interface/globals.php");

// Bring in the getDupScoreSQL() function.
require_once("$srcdir/dupscore.inc.php");

$endtime = time() + 365 * 24 * 60 * 60; // a year from now
if (!empty($args['maxmins'])) {
    $endtime = time() + $args['maxmins'] * 60;
}

if (isset($args['c'])) {
    // Note -1 means the patient is manually flagged as not a duplicate.
    sqlStatementNoLog("UPDATE patient_data SET dupscore = -9 WHERE dupscore != -1");
    if (!isset($args['q'])) {
        echo xl("All scores have been cleared.") . "\n";
    }
}

$count = 0;
$finished = false;

while (!$finished && time() < $endtime) {
    $scores = array();
    $query1 = "SELECT p1.pid, MAX(" . getDupScoreSQL() . ") AS dupscore" .
        " FROM patient_data AS p1, patient_data AS p2" .
        " WHERE p1.dupscore = -9 AND p2.pid < p1.pid" .
        " GROUP BY p1.pid ORDER BY p1.pid LIMIT " . escape_limit($querylimit);

    // echo "$query1\n"; // debugging

    $res1 = sqlStatementNoLog($query1);
    while ($row1 = sqlFetchArray($res1)) {
        $scores[$row1['pid']] = $row1['dupscore'];
    };
    foreach ($scores as $pid => $score) {
        sqlStatementNoLog(
            "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
            array($score, $pid)
        );
        ++$count;
    }

    if (!isset($args['q']) && count($scores) > 0) {
        echo "$count... ";
    }
    if (count($scores) < $querylimit) {
        $finished = true;
    }
}

if (!isset($args['q'])) {
    if (!$count) {
        echo xl("No patients without scores were found.");
    }
    if ($finished) {
        echo "\n" . xl("All done.") . "\n";
    } else {
        echo "\n" . xl("This run is incomplete due to time expiration.") . "\n";
    }
}

if (!$finished) {
    exit(1);
}
