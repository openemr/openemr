<?php

/**
 * External IdP direct JWT login endpoint.
 *
 * Accepts a provider-scoped OIDC JWT from a trusted caller and starts the
 * normal local OpenEMR login continuation flow.
 *
 * Supported inputs:
 * - Authorization: Bearer <jwt>
 * - POST/GET id_token=<jwt>
 * - POST/GET jwt=<jwt>
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

$ignoreAuth = true;
$sessionAllowWrite = true;
require_once(__DIR__ . '/../../../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ExternalIdp\Service\OidcAuthenticationService;
use OpenEMR\Modules\ExternalIdp\Service\OidcStateService;

$providerIdForAudit = is_scalar($_REQUEST['provider_id'] ?? null) ? (string) $_REQUEST['provider_id'] : '';
$siteId = OidcStateService::normalizeSiteId($_REQUEST['site'] ?? 'default');

try {
    $redirectTarget = (new OidcAuthenticationService())->finishDirectTokenLogin($_REQUEST);
    header('Location: ' . $redirectTarget);
    exit;
} catch (\Throwable $exception) {
    EventAuditLogger::getInstance()->newEvent('external_login_failure', '', $providerIdForAudit, 0, 'direct jwt login failed');
    ServiceContainer::getLogger()->error('External IdP direct JWT login failed', ['exception' => $exception]);
    $errorMessage = rawurlencode(substr(trim($exception->getMessage()), 0, 512));
    header('Location: ' . OEGlobalsBag::getInstance()->get('login_screen') . '?error=1&site=' . rawurlencode($siteId) . '&external_idp_error=' . $errorMessage);
    exit;
}
