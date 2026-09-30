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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;

require_once(__DIR__ . "/../../interface/globals.php");

// The three admin-unblock handlers added for #14187 read their inputs via the
// typed request bag rather than direct $_POST so the phpstan baseline for this
// legacy file does not grow. Pre-existing branches (resetUsernameCounter,
// disableIp, enableIp, skipTiming, noSkipTiming, resetIpCounter) are left
// untouched to keep this PR's scope focused on the followup issue.
$request = CurrentRequest::get();
$requestFunction = $request->request->getString('function');

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], $session, 'counter')) {
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

if ($requestFunction === 'resetMfaFailCounter') {
    if (!AclMain::aclCheckCore('admin', 'users')) {
        // Send an explicit 403 (vs the silent 200-with-empty-body that the
        // pre-existing branches below use) so the caller's fetch().then()
        // can distinguish an ACL denial from a successful reset and hold
        // the DOM instead of falsely painting "cleared". Existing branches
        // are left on the legacy pattern to keep this PR's scope bounded.
        http_response_code(403);
        ServiceContainer::getLogger()->error('Failed ACL access to login_counter_ip_tracker.php script', ['function' => 'resetMfaFailCounter']);
        exit;
    }

    $username = $request->request->getString('username');
    if ($username === '') {
        exit;
    }
    AuthUtils::resetMfaUserFailCounter($username);
    exit;
}

// The two per-#14187 admin/super handlers are placed above the shared
// admin/super gate so they can each return an explicit 403 on ACL denial
// without changing the silent-200 behaviour of the pre-existing branches
// (disableIp/enableIp/skipTiming/noSkipTiming/resetIpCounter). See the
// resetMfaFailCounter branch above for the same rationale.
if ($requestFunction === 'resetIpMfaCounter') {
    if (!AclMain::aclCheckCore('admin', 'super')) {
        http_response_code(403);
        ServiceContainer::getLogger()->error('Failed ACL access to login_counter_ip_tracker.php script', ['function' => 'resetIpMfaCounter']);
        exit;
    }
    $ipId = $request->request->getInt('ipId');
    if ($ipId <= 0) {
        exit;
    }
    AuthUtils::resetMfaIpCounter($ipId);
    exit;
}

if ($requestFunction === 'resetPortalAccountCounter') {
    if (!AclMain::aclCheckCore('admin', 'super')) {
        http_response_code(403);
        ServiceContainer::getLogger()->error('Failed ACL access to login_counter_ip_tracker.php script', ['function' => 'resetPortalAccountCounter']);
        exit;
    }
    $portalLoginUsername = $request->request->getString('portalLoginUsername');
    if ($portalLoginUsername === '') {
        exit;
    }
    AuthUtils::resetPortalAccountFailedCounter($portalLoginUsername);
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
