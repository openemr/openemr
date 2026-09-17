<?php

/**
 * Begin OpenID Connect staff login (authorization-code + PKCE).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Common\Auth\OidcRp\OidcLoginService;
use OpenEMR\Common\Auth\OidcRp\OidcRpException;
use OpenEMR\Common\Auth\OidcRp\OidcSessionCookie;
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
$siteId = $session->get('site_id');
$siteId = is_string($siteId) ? $siteId : '';

$url = null;
try {
    $service = OidcLoginService::fromContainer($globalsBag, $session);
    $url = $service->startUrl(
        $globalsBag->getString('site_addr_oath') ?: '',
        $globalsBag->getWebRoot(),
    );
} catch (OidcRpException) {
    $url = null;
}

if ($url === null) {
    $session->set('loginfailure', 1);
    $session->set('oidc_login_error', xl('Single sign-on is not available.'));
    header('Location: ' . $globalsBag->getWebRoot() . '/interface/login/login.php?site=' . urlencode($siteId) . '&local=1');
    exit;
}

header('Set-Cookie: ' . OidcSessionCookie::create($session, session_get_cookie_params(), true), false);
header('Location: ' . $url);
exit;
