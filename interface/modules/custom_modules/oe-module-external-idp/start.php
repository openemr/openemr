<?php

/**
 * External IdP authorization start endpoint.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

$sessionAllowWrite = true;
require_once(__DIR__ . '/../../../globals.php');

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ExternalIdp\Service\OidcAuthenticationService;
use OpenEMR\Modules\ExternalIdp\Service\OidcStateService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$siteId = OidcStateService::normalizeSiteId($_GET['site'] ?? $session->get('site_id') ?? 'default');
$providerIdForAudit = is_scalar($_GET['provider_id'] ?? null) ? (string) $_GET['provider_id'] : '';

try {
    $authorizationUrl = (new OidcAuthenticationService())->start($_GET);
    header('Location: ' . $authorizationUrl);
    exit;
} catch (\Throwable $exception) {
    EventAuditLogger::getInstance()->newEvent('external_login_failure', '', $providerIdForAudit, 0, 'authorization start failed');
    ServiceContainer::getLogger()->error('External IdP start failed', ['exception' => $exception]);
    header('Location: ' . OEGlobalsBag::getInstance()->get('login_screen') . '?error=1&site=' . rawurlencode($siteId));
    exit;
}
