<?php

/**
 * Complete OpenID Connect staff login.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Common\Auth\OidcRp\OidcLoginService;
use OpenEMR\Common\Auth\OidcRp\OidcRpException;
use OpenEMR\Common\Auth\OidcRp\OidcRpSettings;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionTracker;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

require_once __DIR__ . '/../../vendor/autoload.php';

header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");

SessionUtil::setAppCookie(SessionUtil::CORE_SESSION_ID);

$ignoreAuth = true;
$sessionAllowWrite = true;
require_once(__DIR__ . "/../globals.php");

$globalsBag = OEGlobalsBag::getInstance();
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();
$siteId = $session->get('site_id');
$siteId = is_string($siteId) ? urlencode($siteId) : '';
$loginUrl = $globalsBag->getWebRoot() . '/interface/login/login.php?site=' . $siteId . '&local=1';

$providerError = $request->query->getString('error');
if ($providerError !== '') {
    $session->set('loginfailure', 1);
    $session->set('oidc_login_error', xl('Single sign-on was cancelled or rejected.'));
    EventAuditLogger::getInstance()->newEvent('login', '', '', 0, 'OIDC SSO provider returned error');
    header('Location: ' . $loginUrl);
    exit;
}

$completed = false;
try {
    $service = OidcLoginService::fromContainer($globalsBag, $session);
    $service->complete(
        $request->query->getString('code'),
        $request->query->getString('state'),
        $globalsBag->getString('site_addr_oath') ?: '',
        $globalsBag->getWebRoot(),
    );
    SessionTracker::setupSessionDatabaseTracker();
    $session->set(OidcRpSettings::SESSION_NEW_LOGIN, true);
    $completed = true;
} catch (OidcRpException) {
    $completed = false;
}

if (!$completed) {
    $session->set('loginfailure', 1);
    $session->set('oidc_login_error', xl('Single sign-on failed. Sign in locally or contact an administrator.'));
    EventAuditLogger::getInstance()->newEvent('login', '', '', 0, 'OIDC SSO failure');
    header('Location: ' . $loginUrl);
    exit;
}

header('Location: ' . $globalsBag->getWebRoot() . '/interface/main/main_screen.php?site=' . $siteId);
exit;
