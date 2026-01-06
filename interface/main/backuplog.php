<?php

/**
 *  backuplog.php
 *
 *  Here /interface/globals.php is not referred, because it includes sqlconf.php
 *  Pass these variables $webserver_root & $_GLOBALS[backup_log_dir] as parameters for CRON.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure running from command line only
if (php_sapi_name() !== 'cli') {
    exit;
}

if (!isset($argv[1])) {
    throw new RuntimeException("At least one argument is required");
}

require_once("$argv[1]/library/sqlconf.php");
$backuptime = date("Ymd_His");
$BACKUP_EVENTLOG_DIR = $argv[2] . "/emr_eventlog_backup";
if (!file_exists($BACKUP_EVENTLOG_DIR)) {
    mkdir($BACKUP_EVENTLOG_DIR);
    chmod($BACKUP_EVENTLOG_DIR, 0777);
}

$BACKUP_EVENTLOG_DIR = $BACKUP_EVENTLOG_DIR . '/eventlog_' . $backuptime . '.sql';
$cmd = escapeshellcmd($argv[1] . '/interface/main/backuplog.sh') . ' ' . escapeshellarg((string) $sqlconf["login"]) . ' ' . escapeshellarg((string) $sqlconf["pass"]) . ' ' . escapeshellarg((string) $sqlconf["dbase"]) . ' ' . escapeshellarg($BACKUP_EVENTLOG_DIR) . ' ' . escapeshellarg((string) $sqlconf["host"]) . ' ' . escapeshellarg((string) $sqlconf["port"]);
system($cmd);
