<?php

/**
 * login_counter_ip_tracker.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;

require_once(__DIR__ . "/../../interface/globals.php");

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'counter')) {
    CsrfUtils::csrfNotVerified(false);
}

if (empty($_POST['function'])) {
    exit;
}

if ($_POST['function'] == 'resetUsernameCounter') {
    if (!AclMain::aclCheckCore('admin', 'users')) {
        error_log("Failed ACL access to login_counter_ip_tracker.php script for resetUsernameCounter function");
        exit;
    }

    if (empty($_POST['username'])) {
        exit;
    }
    AuthUtils::resetLoginFailedCounter($_POST['username']);
    exit;
}


// all function below require admin super access
if (!AclMain::aclCheckCore('admin', 'super')) {
    error_log("Failed ACL access to login_counter_ip_tracker.php script for " . errorLogEscape($_POST['function']) . " function");
    exit;
}

if ($_POST['function'] == 'disableIp') {
    if (empty((int)$_POST['ipId'])) {
        exit;
    }
    AuthUtils::disableIp((int)$_POST['ipId']);
    exit;
}

if ($_POST['function'] == 'enableIp') {
    if (empty((int)$_POST['ipId'])) {
        exit;
    }
    AuthUtils::enableIp((int)$_POST['ipId']);
    exit;
}

if ($_POST['function'] == 'skipTiming') {
    if (empty((int)$_POST['ipId'])) {
        exit;
    }
    AuthUtils::skipTimingIp((int)$_POST['ipId']);
    exit;
}

if ($_POST['function'] == 'noSkipTiming') {
    if (empty((int)$_POST['ipId'])) {
        exit;
    }
    AuthUtils::noSkipTimingIp((int)$_POST['ipId']);
    exit;
}

if ($_POST['function'] == 'resetIpCounter') {
    if (empty((int)$_POST['ipId'])) {
        exit;
    }
    AuthUtils::resetIpCounter((int)$_POST['ipId']);
    exit;
}
