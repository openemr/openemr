<?php

/**
 * cronjob mapping to allow seamless use of scripts in background.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MD Support <mdsupport@users.sf.net>
 * @copyright Copyright (c) 2017 MD Support <mdsupport@users.sf.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Check if running as a cronjob
if (php_sapi_name() !== 'cli') {
    return;
}

/**
 * Cronjobs are expected to specify arguments as -
 * php -f full_script_name arg1=value1 arg2=value2
 *
 * Code below translates $argv to $_REQUEST
 */
foreach ($argv as $argk => $argval) {
    if ($argk == 0) {
        continue;
    }
    $pair = explode("=", $argval);
    $_REQUEST[trim($pair[0])] = (isset($pair[1]) ? trim($pair[1]) : null);
}

// Every job must have at least one argument specifing site
if (!isset($_REQUEST['site'])) {
    exit("site=?");
}

// Simulate $_GET and $_POST for use by scripts
$_GET = $_REQUEST;
$_POST = $_REQUEST;

// Ignore auth checks
$ignoreAuth = true;

// Since from command line, set $sessionAllowWrite since need to set site_id session and no benefit to set to false
$sessionAllowWrite = true;
