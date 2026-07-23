<?php

/**
 * External IdP module configuration page.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (empty($_GET['site']) && empty($_REQUEST['site'])) {
    $_GET['site'] = 'default';
    $_REQUEST['site'] = 'default';
}

$sessionAllowWrite = true;
require_once(__DIR__ . '/../../../globals.php');

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ExternalIdp\Repository\IdentityRepository;
use OpenEMR\Modules\ExternalIdp\Repository\ProviderRepository;
use OpenEMR\Modules\ExternalIdp\Service\DiscoveryService;

require_once __DIR__ . '/src/Service/DiscoveryService.php';

$module_config = 1;
$renderPartial = defined('OE_EXTERNAL_IDP_RENDER_PARTIAL') && OE_EXTERNAL_IDP_RENDER_PARTIAL;

/**
 * Ensure the module tables exist before the page touches repository queries.
 */
function externalIdpEnsureSchema(): void
{
    $providerTable = sqlQuery("SHOW TABLES LIKE 'module_external_idp_provider'");
    $identityTable = sqlQuery("SHOW TABLES LIKE 'module_external_idp_identity'");
    if (empty($providerTable) || empty($identityTable)) {
        $sql = file_get_contents(__DIR__ . '/table.sql');
        if ($sql === false) {
            throw new \RuntimeException('Unable to read the module schema definition.');
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($sql));
        foreach ($statements as $statement) {
            $statement = trim((string) $statement);
            if ($statement === '') {
                continue;
            }
            sqlStatement($statement);
        }
    }

    $providerColumns = [
        'bearer_audiences' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `bearer_audiences` text DEFAULT NULL AFTER `client_id`",
        'provisioning_mode' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `provisioning_mode` varchar(32) NOT NULL DEFAULT 'manual' AFTER `scopes`",
        'match_claim' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `match_claim` varchar(64) NOT NULL DEFAULT 'preferred_username' AFTER `provisioning_mode`",
        'username_claim' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `username_claim` varchar(64) NOT NULL DEFAULT 'preferred_username' AFTER `match_claim`",
        'email_claim' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `email_claim` varchar(64) NOT NULL DEFAULT 'email' AFTER `username_claim`",
        'first_name_claim' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `first_name_claim` varchar(64) NOT NULL DEFAULT 'given_name' AFTER `email_claim`",
        'last_name_claim' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `last_name_claim` varchar(64) NOT NULL DEFAULT 'family_name' AFTER `first_name_claim`",
        'default_group_name' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `default_group_name` varchar(255) NOT NULL DEFAULT '' AFTER `last_name_claim`",
        'default_acl_group' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `default_acl_group` varchar(255) NOT NULL DEFAULT '' AFTER `default_group_name`",
        'username_prefix' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `username_prefix` varchar(32) NOT NULL DEFAULT 'oidc_' AFTER `default_acl_group`",
        'default_facility_id' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `default_facility_id` bigint(20) NOT NULL DEFAULT 0 AFTER `username_prefix`",
        'default_authorized' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `default_authorized` tinyint(1) NOT NULL DEFAULT 0 AFTER `default_facility_id`",
        'default_active' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `default_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `default_authorized`",
        'sync_claims_on_login' => "ALTER TABLE `module_external_idp_provider` ADD COLUMN `sync_claims_on_login` tinyint(1) NOT NULL DEFAULT 1 AFTER `default_active`",
    ];

    foreach ($providerColumns as $columnName => $alterSql) {
        $column = sqlQuery("SHOW COLUMNS FROM `module_external_idp_provider` LIKE '" . add_escape_custom($columnName) . "'");
        if (empty($column)) {
            sqlStatement($alterSql);
        }
    }
}

$classLoader = $classLoader ?? new ModulesClassLoader(OEGlobalsBag::getInstance()->getProjectDir());
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\ExternalIdp\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$siteId = (string) ($session->get('site_id') ?: 'default');
$providerRepository = new ProviderRepository();
$identityRepository = new IdentityRepository();
$callbackHost = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
if (str_contains($callbackHost, ',')) {
    $callbackHost = trim(explode(',', $callbackHost)[0]);
}
$callbackScheme = trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
if ($callbackScheme !== '' && str_contains($callbackScheme, ',')) {
    $callbackScheme = trim(explode(',', $callbackScheme)[0]);
}
if ($callbackScheme === '') {
    $callbackScheme = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
}
$callbackUrl = $callbackScheme . '://' . ($callbackHost !== '' ? $callbackHost : 'localhost') . OEGlobalsBag::getInstance()->getWebRoot() . '/interface/modules/custom_modules/oe-module-external-idp/callback.php';
$csrfToken = CsrfUtils::collectCsrfToken($session, 'external-idp-config');
$isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

$message = '';
$messageType = 'success';
$bindingSearchQuery = '';
$bindingSubject = '';
$bindingUserId = '';
$bindingSearchResults = [];
$bootstrapError = '';
$postedProviderDraft = null;

try {
    externalIdpEnsureSchema();
    $provider = $providerRepository->getForSite($siteId);
    $provider = is_array($provider) ? $provider : [];
    $bindings = $identityRepository->listForSite($siteId);
} catch (\Throwable $exception) {
    $provider = [];
    $bindings = [];
    $bootstrapError = $exception->getMessage();
    $messageType = 'danger';
    $message = xlt('Configuration page failed to initialize: ') . $bootstrapError;
}

$provisioningModes = [
    'manual' => xlt('Manual binding only'),
    'auto_bind' => xlt('Auto-bind existing local user'),
    'auto_provision' => xlt('Auto-provision shadow user'),
    'auto_bind_or_provision' => xlt('Auto-bind or auto-provision'),
];
$defaultFacilityRows = sqlStatement('SELECT `id`, `name` FROM `facility` ORDER BY `name`');
$facilityOptions = [];
while ($row = sqlFetchArray($defaultFacilityRows)) {
    $facilityOptions[] = $row;
}
$groupRows = sqlStatement('SELECT DISTINCT `name` FROM `groups` WHERE `name` IS NOT NULL AND `name` != "" ORDER BY `name`');
$groupOptions = [];
while ($row = sqlFetchArray($groupRows)) {
    if (!empty($row['name'])) {
        $groupOptions[] = (string) $row['name'];
    }
}
$aclGroupOptions = AclExtended::aclGetGroupTitleList();

if ($bootstrapError === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    CsrfUtils::checkCsrfInput(INPUT_POST, session: $session, subject: 'external-idp-config', dieOnFail: true);

    $action = strtolower(trim((string) ($_POST['action'] ?? 'save')));
    $bindingSearchQuery = trim((string) ($_POST['binding_search'] ?? ''));
    $bindingSubject = trim((string) ($_POST['binding_subject'] ?? ''));
    $bindingUserId = trim((string) ($_POST['binding_user_id'] ?? ''));

    try {
        if ($action === 'test') {
            $issuerUrl = trim((string) ($_POST['issuer_url'] ?? ''));
            if ($issuerUrl === '') {
                throw new \InvalidArgumentException('Issuer URL is required for discovery testing.');
            }

            (new DiscoveryService())->discover($issuerUrl);
            $message = xlt('OIDC discovery succeeded. The configuration is reachable.');
            $messageType = 'success';
            if ($isAjaxRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'messageType' => $messageType,
                    'message' => $message,
                ]);
                exit;
            }
        } elseif ($action === 'search_users') {
            $bindingSearchResults = $providerRepository->searchUsers($bindingSearchQuery);
            if ($bindingSearchResults === []) {
                $messageType = 'info';
                $message = xlt('No active users matched the search term.');
            } else {
                $messageType = 'info';
                $message = xlt('Select a user from the search results and submit the binding form.');
            }
        } elseif ($action === 'bind') {
            if (empty($provider['id'])) {
                throw new \RuntimeException('Save the provider configuration before creating bindings.');
            }
            if ($bindingSubject === '') {
                throw new \InvalidArgumentException('External subject is required.');
            }
            if ($bindingUserId === '' || !ctype_digit($bindingUserId) || (int) $bindingUserId < 1) {
                throw new \InvalidArgumentException('Select a local user to bind.');
            }

            $bindingSearchResults = $bindingSearchQuery !== '' ? $providerRepository->searchUsers($bindingSearchQuery) : [];
            $identityRepository->saveBinding((int) $provider['id'], $bindingSubject, (int) $bindingUserId);
            $message = xlt('Identity binding saved.');
            $bindingSubject = '';
            $bindingUserId = '';
        } elseif ($action === 'unbind') {
            $bindingId = (int) ($_POST['binding_id'] ?? 0);
            $identityRepository->deleteBinding($bindingId);
            $message = xlt('Identity binding removed.');
        } else {
            $displayName = trim((string) ($_POST['display_name'] ?? ''));
            $issuerUrl = trim((string) ($_POST['issuer_url'] ?? ''));
            $clientId = trim((string) ($_POST['client_id'] ?? ''));
            $bearerAudiences = trim((string) ($_POST['bearer_audiences'] ?? ''));
            $clientSecret = (string) ($_POST['client_secret'] ?? '');
            $scopes = trim((string) ($_POST['scopes'] ?? 'openid profile email'));
            $enabled = !empty($_POST['enabled']);
            $provisioningMode = trim((string) ($_POST['provisioning_mode'] ?? ProviderRepository::DEFAULT_PROVISIONING_MODE));
            $matchClaim = trim((string) ($_POST['match_claim'] ?? 'preferred_username'));
            $usernameClaim = trim((string) ($_POST['username_claim'] ?? 'preferred_username'));
            $emailClaim = trim((string) ($_POST['email_claim'] ?? 'email'));
            $firstNameClaim = trim((string) ($_POST['first_name_claim'] ?? 'given_name'));
            $lastNameClaim = trim((string) ($_POST['last_name_claim'] ?? 'family_name'));
            $defaultGroupName = trim((string) ($_POST['default_group_name'] ?? ''));
            $defaultAclGroup = trim((string) ($_POST['default_acl_group'] ?? ''));
            $usernamePrefix = trim((string) ($_POST['username_prefix'] ?? 'oidc_'));
            $defaultFacilityId = (int) ($_POST['default_facility_id'] ?? 0);
            $defaultAuthorized = !empty($_POST['default_authorized']);
            $defaultActive = !array_key_exists('default_active', $_POST) || !empty($_POST['default_active']);
            $syncClaimsOnLogin = !array_key_exists('sync_claims_on_login', $_POST) || !empty($_POST['sync_claims_on_login']);
            $postedProviderDraft = [
                'display_name' => $displayName,
                'issuer_url' => $issuerUrl,
                'client_id' => $clientId,
                'bearer_audiences' => $bearerAudiences,
                'scopes' => $scopes,
                'enabled' => $enabled ? 1 : 0,
                'provisioning_mode' => $provisioningMode,
                'match_claim' => $matchClaim,
                'username_claim' => $usernameClaim,
                'email_claim' => $emailClaim,
                'first_name_claim' => $firstNameClaim,
                'last_name_claim' => $lastNameClaim,
                'default_group_name' => $defaultGroupName,
                'default_acl_group' => $defaultAclGroup,
                'username_prefix' => $usernamePrefix,
                'default_facility_id' => $defaultFacilityId,
                'default_authorized' => $defaultAuthorized ? 1 : 0,
                'default_active' => $defaultActive ? 1 : 0,
                'sync_claims_on_login' => $syncClaimsOnLogin ? 1 : 0,
            ];

            if ($displayName === '' || $clientId === '' || $scopes === '') {
                throw new \InvalidArgumentException('Display name, client ID, and scopes are required.');
            }
            if (!in_array($provisioningMode, ProviderRepository::PROVISIONING_MODES, true)) {
                throw new \InvalidArgumentException('Provisioning mode is invalid.');
            }
            if (($provisioningMode === 'auto_provision' || $provisioningMode === 'auto_bind_or_provision') && ($defaultGroupName === '' || $defaultAclGroup === '')) {
                throw new \InvalidArgumentException('Shadow-user provisioning requires a local group name and an ACL group.');
            }

            $scopeList = preg_split('/\s+/', $scopes, -1, PREG_SPLIT_NO_EMPTY);
            if (!in_array('openid', $scopeList, true)) {
                throw new \InvalidArgumentException('Scopes must include openid.');
            }

            if ($action === 'save') {
                $metadata = (new DiscoveryService())->discover($issuerUrl);
                $providerRepository->save($siteId, $displayName, rtrim($issuerUrl, '/'), $clientId, $clientSecret, $scopes, $enabled, $metadata, [
                    'bearer_audiences' => $bearerAudiences,
                    'provisioning_mode' => $provisioningMode,
                    'match_claim' => $matchClaim,
                    'username_claim' => $usernameClaim,
                    'email_claim' => $emailClaim,
                    'first_name_claim' => $firstNameClaim,
                    'last_name_claim' => $lastNameClaim,
                    'default_group_name' => $defaultGroupName,
                    'default_acl_group' => $defaultAclGroup,
                    'username_prefix' => $usernamePrefix,
                    'default_facility_id' => $defaultFacilityId,
                    'default_authorized' => $defaultAuthorized,
                    'default_active' => $defaultActive,
                    'sync_claims_on_login' => $syncClaimsOnLogin,
                ]);
                $message = $enabled ? xlt('OIDC discovery succeeded and the provider was enabled.') : xlt('OIDC discovery succeeded and the provider configuration was saved disabled.');
                $messageType = 'success';
                if ($isAjaxRequest) {
                    $provider = $providerRepository->getForSite($siteId);
                    $provider = is_array($provider) ? $provider : [];
                    $enabled = !empty($provider['enabled']);
                    $discoveryFetchedAt = (string) ($provider['discovery_fetched_at'] ?? '');
                    $lastFailureAt = (string) ($provider['last_failure_at'] ?? '');
                    $lastFailureMessage = (string) ($provider['last_failure_message'] ?? '');
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => true,
                        'messageType' => $messageType,
                        'message' => $message,
                        'provider' => [
                            'enabled' => $enabled,
                            'discovery_fetched_at' => $discoveryFetchedAt,
                            'last_failure_at' => $lastFailureAt,
                            'last_failure_message' => $lastFailureMessage,
                        ],
                    ]);
                    exit;
                }
            } else {
                throw new \InvalidArgumentException('Unsupported action.');
            }
        }
    } catch (\Throwable $exception) {
        $messageType = 'danger';
        $message = xlt('Request failed: ') . $exception->getMessage();
        if ($isAjaxRequest && in_array($action, ['test', 'save'], true)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'messageType' => $messageType,
                'message' => $message,
            ]);
            exit;
        }
    }
}

if ($bootstrapError === '') {
    $provider = $providerRepository->getForSite($siteId);
    $provider = is_array($provider) ? $provider : [];
    if ($postedProviderDraft !== null && $messageType === 'danger') {
        $provider = array_merge($provider, $postedProviderDraft);
    }
    $bindings = $identityRepository->listForSite($siteId);
}

$lastSuccessUser = null;
$lastSuccessUserUsername = '';
$lastSuccessUserFullName = '';
if (!empty($provider['last_success_user_id'])) {
    $lastSuccessUser = sqlQuery('SELECT `id`, `username`, `fname`, `lname` FROM `users` WHERE `id` = ?', [(int) $provider['last_success_user_id']]);
    if (is_array($lastSuccessUser)) {
        $lastSuccessUserUsername = trim((string) ($lastSuccessUser['username'] ?? ''));
        $lastSuccessUserFullName = trim((string) (($lastSuccessUser['fname'] ?? '') . ' ' . ($lastSuccessUser['lname'] ?? '')));
    }
}

if ($bindingSearchQuery !== '' && $bindingSearchResults === [] && ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($action ?? '') !== 'search_users')) {
    $bindingSearchResults = $providerRepository->searchUsers($bindingSearchQuery);
}
if (!$renderPartial) {
?>
<!doctype html>
<html lang="en">
<head>
    <title><?php echo xlt('External Identity Provider'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body>
<?php } ?>
<main class="container my-4">
    <h1 class="title"><?php echo xlt('External Identity Provider'); ?></h1>
    <p class="text-muted"><?php echo xlt('Configure discovery, enable sign-in, bind OpenEMR users, and review recent login status.'); ?></p>

    <div id="external-idp-message" <?php echo $message !== '' ? '' : 'style="display:none"'; ?> class="alert alert-<?php echo attr($messageType); ?>" role="alert"><?php echo $message !== '' ? text($message) : ''; ?></div>

    <div class="card mb-3">
        <div class="card-header"><?php echo xlt('Keycloak setup helper'); ?></div>
        <div class="card-body">
            <p class="mb-2"><?php echo xlt('Use this section to map Keycloak values into the OpenEMR OIDC fields below.'); ?></p>
            <ul class="small text-muted mb-3">
                <li><?php echo xlt('Realm ID becomes part of the issuer URL.'); ?></li>
                <li><?php echo xlt('Client ID is the OpenEMR OIDC client identifier.'); ?></li>
                <li><?php echo xlt('Client secret is the confidential client secret from Keycloak.'); ?></li>
            </ul>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="keycloak_base_url"><?php echo xlt('Keycloak base URL'); ?></label>
                    <input class="form-control" id="keycloak_base_url" type="url" placeholder="https://keycloak.example.com">
                </div>
                <div class="form-group col-md-6">
                    <label for="keycloak_realm_id"><?php echo xlt('Realm ID'); ?></label>
                    <input class="form-control" id="keycloak_realm_id" type="text" placeholder="clinic">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="keycloak_client_id"><?php echo xlt('OpenEMR client ID'); ?></label>
                    <input class="form-control" id="keycloak_client_id" type="text" placeholder="openemr">
                </div>
                <div class="form-group col-md-6">
                    <label for="keycloak_client_secret"><?php echo xlt('OpenEMR client secret'); ?></label>
                    <input class="form-control" id="keycloak_client_secret" type="password" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label for="keycloak_issuer_preview"><?php echo xlt('Computed issuer URL'); ?></label>
                <input class="form-control" id="keycloak_issuer_preview" type="text" readonly>
                <small class="form-text text-muted"><?php echo xlt('The issuer URL is what OpenEMR uses for discovery. For Keycloak it normally follows the pattern /realms/{realm-id}.'); ?></small>
            </div>

            <div class="btn-toolbar gap-2" role="toolbar">
                <button class="btn btn-outline-primary" type="button" id="keycloak_apply"><?php echo xlt('Apply to OIDC fields'); ?></button>
                <button class="btn btn-outline-secondary" type="button" id="keycloak_copy_issuer"><?php echo xlt('Copy issuer URL'); ?></button>
                <button class="btn btn-outline-info" type="button" id="keycloak_example"><?php echo xlt('Load example values'); ?></button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><?php echo xlt('Provider configuration'); ?></div>
        <div class="card-body">
            <form method="post" autocomplete="off" id="provider-form">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
                <input type="hidden" name="action" id="provider_action" value="save">

                <div class="form-group">
                    <label for="display_name"><?php echo xlt('Provider display name'); ?></label>
                    <input class="form-control" id="display_name" name="display_name" maxlength="128" required value="<?php echo attr($provider['display_name'] ?? 'External Identity Provider'); ?>">
                </div>

                <div class="form-group">
                    <label for="issuer_url"><?php echo xlt('Issuer URL'); ?></label>
                    <input class="form-control" id="issuer_url" name="issuer_url" type="url" maxlength="2048" placeholder="https://idp.example.com/realms/clinic" required value="<?php echo attr($provider['issuer_url'] ?? ''); ?>">
                    <small class="form-text text-muted"><?php echo xlt('Must be the exact issuer URL; discovery is requested from its standard well-known endpoint. HTTP is allowed for testing.'); ?></small>
                </div>

                <div class="form-group">
                    <label for="callback_url"><?php echo xlt('Callback URL'); ?></label>
                    <input class="form-control" id="callback_url" type="text" readonly value="<?php echo attr($callbackUrl); ?>">
                    <small class="form-text text-muted"><?php echo xlt('Configure this exact redirect URI in the external provider.'); ?></small>
                </div>

                <div class="form-group">
                    <label for="client_id"><?php echo xlt('Client ID'); ?></label>
                    <input class="form-control" id="client_id" name="client_id" maxlength="512" required value="<?php echo attr($provider['client_id'] ?? ''); ?>">
                    <small class="form-text text-muted"><?php echo xlt('Used for the browser OIDC login flow and as the default accepted audience for direct bearer-token API validation.'); ?></small>
                </div>

                <div class="form-group">
                    <label for="bearer_audiences"><?php echo xlt('Accepted bearer audiences'); ?></label>
                    <textarea class="form-control" id="bearer_audiences" name="bearer_audiences" rows="3" placeholder="ai_gateway_client&#10;other-trusted-client"><?php echo attr((string) ($provider['bearer_audiences'] ?? '')); ?></textarea>
                    <small class="form-text text-muted"><?php echo xlt('Optional. Add one audience or client ID per line, or separate with spaces or commas. API bearer tokens from this issuer are accepted when aud or azp matches the configured Client ID or one of these values.'); ?></small>
                </div>

                <div class="form-group">
                    <label for="client_secret"><?php echo xlt('Client secret'); ?></label>
                    <input class="form-control" id="client_secret" name="client_secret" type="password" autocomplete="new-password">
                    <small class="form-text text-muted"><?php echo xlt('Leave blank to retain the stored secret. A supplied secret is encrypted before it is stored.'); ?></small>
                </div>

                <div class="form-group">
                    <label for="scopes"><?php echo xlt('Scopes'); ?></label>
                    <input class="form-control" id="scopes" name="scopes" maxlength="512" required value="<?php echo attr($provider['scopes'] ?? 'openid profile email'); ?>">
                </div>

                <h2 class="h5 mt-4"><?php echo xlt('Shadow-user provisioning'); ?></h2>

                <div class="form-group">
                    <label for="provisioning_mode"><?php echo xlt('Provisioning mode'); ?></label>
                    <select class="form-control" id="provisioning_mode" name="provisioning_mode">
                        <?php foreach ($provisioningModes as $modeValue => $modeLabel) { ?>
                            <option value="<?php echo attr($modeValue); ?>" <?php echo (($provider['provisioning_mode'] ?? ProviderRepository::DEFAULT_PROVISIONING_MODE) === $modeValue) ? 'selected' : ''; ?>>
                                <?php echo text($modeLabel); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <small class="form-text text-muted"><?php echo xlt('Manual is the default. Automatic modes use exact claim matching and can optionally create local shadow users.'); ?></small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="match_claim"><?php echo xlt('Match claim'); ?></label>
                        <input class="form-control" id="match_claim" name="match_claim" maxlength="64" value="<?php echo attr($provider['match_claim'] ?? 'preferred_username'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="username_claim"><?php echo xlt('Username claim'); ?></label>
                        <input class="form-control" id="username_claim" name="username_claim" maxlength="64" value="<?php echo attr($provider['username_claim'] ?? 'preferred_username'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email_claim"><?php echo xlt('Email claim'); ?></label>
                        <input class="form-control" id="email_claim" name="email_claim" maxlength="64" value="<?php echo attr($provider['email_claim'] ?? 'email'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="first_name_claim"><?php echo xlt('First name claim'); ?></label>
                        <input class="form-control" id="first_name_claim" name="first_name_claim" maxlength="64" value="<?php echo attr($provider['first_name_claim'] ?? 'given_name'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="last_name_claim"><?php echo xlt('Last name claim'); ?></label>
                        <input class="form-control" id="last_name_claim" name="last_name_claim" maxlength="64" value="<?php echo attr($provider['last_name_claim'] ?? 'family_name'); ?>">
                    </div>
                </div>

                <div class="form-row provisioning-fields">
                    <div class="form-group col-md-6">
                        <label for="default_group_name"><?php echo xlt('Local group name'); ?></label>
                        <input class="form-control" list="default_group_name_options" id="default_group_name" name="default_group_name" maxlength="255" value="<?php echo attr($provider['default_group_name'] ?? ''); ?>">
                        <datalist id="default_group_name_options">
                            <?php foreach ($groupOptions as $groupNameOption) { ?>
                                <option value="<?php echo attr($groupNameOption); ?>"></option>
                            <?php } ?>
                        </datalist>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="default_acl_group"><?php echo xlt('ACL group'); ?></label>
                        <select class="form-control" id="default_acl_group" name="default_acl_group">
                            <option value=""><?php echo xlt('Select ACL group'); ?></option>
                            <?php foreach ($aclGroupOptions as $aclGroupOption) { ?>
                                <option value="<?php echo attr($aclGroupOption); ?>" <?php echo (($provider['default_acl_group'] ?? '') === $aclGroupOption) ? 'selected' : ''; ?>>
                                    <?php echo text($aclGroupOption); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-row provisioning-fields">
                    <div class="form-group col-md-4">
                        <label for="username_prefix"><?php echo xlt('Username prefix'); ?></label>
                        <input class="form-control" id="username_prefix" name="username_prefix" maxlength="32" value="<?php echo attr($provider['username_prefix'] ?? 'oidc_'); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="default_facility_id"><?php echo xlt('Default facility'); ?></label>
                        <select class="form-control" id="default_facility_id" name="default_facility_id">
                            <option value="0"><?php echo xlt('None'); ?></option>
                            <?php foreach ($facilityOptions as $facilityOption) { ?>
                                <option value="<?php echo attr((string) ($facilityOption['id'] ?? 0)); ?>" <?php echo ((int) ($provider['default_facility_id'] ?? 0) === (int) ($facilityOption['id'] ?? 0)) ? 'selected' : ''; ?>>
                                    <?php echo text((string) ($facilityOption['name'] ?? '')); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="default_authorized"><?php echo xlt('Default authorized flag'); ?></label>
                        <select class="form-control" id="default_authorized" name="default_authorized">
                            <option value="0" <?php echo empty($provider['default_authorized']) ? 'selected' : ''; ?>><?php echo xlt('No'); ?></option>
                            <option value="1" <?php echo !empty($provider['default_authorized']) ? 'selected' : ''; ?>><?php echo xlt('Yes'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row provisioning-fields">
                    <div class="form-group col-md-6 form-check ml-1">
                        <input class="form-check-input" type="checkbox" id="default_active" name="default_active" value="1" <?php echo !array_key_exists('default_active', $provider) || !empty($provider['default_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="default_active"><?php echo xlt('Provisioned users are active by default'); ?></label>
                    </div>
                    <div class="form-group col-md-6 form-check ml-1">
                        <input class="form-check-input" type="checkbox" id="sync_claims_on_login" name="sync_claims_on_login" value="1" <?php echo !array_key_exists('sync_claims_on_login', $provider) || !empty($provider['sync_claims_on_login']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="sync_claims_on_login"><?php echo xlt('Sync name/email claims on each login'); ?></label>
                    </div>
                </div>

                <div class="form-group form-check">
                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" <?php echo !empty($provider['enabled']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="enabled"><?php echo xlt('Enable sign-in with this provider'); ?></label>
                </div>

                <div class="btn-toolbar gap-2" role="toolbar">
                    <button class="btn btn-outline-secondary" type="submit" data-action="test"><?php echo xlt('Test discovery'); ?></button>
                    <button class="btn btn-primary" type="submit" data-action="save"><?php echo xlt('Validate discovery and save'); ?></button>
                </div>
            </form>

            <hr>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h2 class="h5"><?php echo xlt('Provider status'); ?></h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-5"><?php echo xlt('Enabled'); ?></dt>
                        <dd class="col-sm-7" id="provider-status-enabled"><?php echo !empty($provider['enabled']) ? xlt('Yes') : xlt('No'); ?></dd>

                        <dt class="col-sm-5"><?php echo xlt('Discovery fetched'); ?></dt>
                        <dd class="col-sm-7" id="provider-status-discovery"><?php echo !empty($provider['discovery_fetched_at']) ? text($provider['discovery_fetched_at']) : xlt('Not yet tested'); ?></dd>

                        <dt class="col-sm-5"><?php echo xlt('Last login start'); ?></dt>
                        <dd class="col-sm-7" id="provider-status-started"><?php echo !empty($provider['last_started_at']) ? text($provider['last_started_at']) : xlt('None'); ?></dd>

                        <dt class="col-sm-5"><?php echo xlt('Last success'); ?></dt>
                        <dd class="col-sm-7" id="provider-status-success">
                            <?php if (!empty($provider['last_success_at'])) {
                                echo text($provider['last_success_at']);
                                if (!empty($provider['last_success_user_id'])) {
                                    echo ' — ' . xlt('user') . ' #' . text((string) $provider['last_success_user_id']);
                                    if ($lastSuccessUserUsername !== '') {
                                        echo ' (' . text($lastSuccessUserUsername);
                                        if ($lastSuccessUserFullName !== '') {
                                            echo ' — ' . text($lastSuccessUserFullName);
                                        }
                                        echo ')';
                                    }
                                }
                            } else {
                                echo xlt('None');
                            } ?>
                        </dd>

                        <dt class="col-sm-5"><?php echo xlt('Last failure'); ?></dt>
                        <dd class="col-sm-7" id="provider-status-failure">
                            <?php if (!empty($provider['last_failure_at'])) {
                                echo text($provider['last_failure_at']);
                                if (!empty($provider['last_failure_message'])) {
                                    echo '<div class="text-break small">' . text((string) $provider['last_failure_message']) . '</div>';
                                }
                            } else {
                                echo xlt('None');
                            } ?>
                        </dd>
                    </dl>
                </div>

                <div class="col-md-6 mb-3">
                    <h2 class="h5"><?php echo xlt('Identity binding workflow'); ?></h2>
                    <?php if (empty($provider['id'])) { ?>
                        <div class="alert alert-warning mb-0"><?php echo xlt('Save the provider configuration before creating bindings.'); ?></div>
                    <?php } else { ?>
                        <form method="post" autocomplete="off" class="mb-3">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
                            <input type="hidden" name="action" value="search_users">
                            <div class="form-group">
                                <label for="binding_subject"><?php echo xlt('External subject'); ?></label>
                                <input class="form-control" id="binding_subject" name="binding_subject" maxlength="512" value="<?php echo attr($bindingSubject); ?>">
                                <small class="form-text text-muted"><?php echo xlt('Use the immutable OIDC subject value from the identity provider, not email address.'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="binding_search"><?php echo xlt('Search local users'); ?></label>
                                <input class="form-control" id="binding_search" name="binding_search" maxlength="128" value="<?php echo attr($bindingSearchQuery); ?>">
                                <small class="form-text text-muted"><?php echo xlt('Search by username or name. Only active users are listed.'); ?></small>
                            </div>
                            <button class="btn btn-outline-primary" type="submit"><?php echo xlt('Search'); ?></button>
                        </form>

                        <form method="post" autocomplete="off" class="mb-4">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
                            <input type="hidden" name="action" value="bind">
                            <input type="hidden" name="binding_search" value="<?php echo attr($bindingSearchQuery); ?>">
                            <div class="form-group">
                                <label for="binding_subject_bind"><?php echo xlt('External subject'); ?></label>
                                <input class="form-control" id="binding_subject_bind" name="binding_subject" maxlength="512" required value="<?php echo attr($bindingSubject); ?>">
                            </div>
                            <div class="form-group">
                                <label for="binding_user_id"><?php echo xlt('Local user'); ?></label>
                                <select class="form-control" id="binding_user_id" name="binding_user_id" required <?php echo $bindingSearchResults === [] ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo xlt('Select a user'); ?></option>
                                    <?php foreach ($bindingSearchResults as $user) {
                                        $userId = (int) ($user['id'] ?? 0);
                                        $label = trim((string) ($user['username'] ?? ''));
                                        $name = trim((string) (($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')));
                                        if ($name !== '') {
                                            $label .= $label !== '' ? ' — ' . $name : $name;
                                        }
                                        ?>
                                        <option value="<?php echo attr((string) $userId); ?>" <?php echo $bindingUserId !== '' && (int) $bindingUserId === $userId ? 'selected' : ''; ?>>
                                            <?php echo text($label); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit" <?php echo $bindingSearchResults === [] ? 'disabled' : ''; ?>><?php echo xlt('Bind selected user'); ?></button>
                        </form>

                        <?php if ($bindingSearchQuery !== '') { ?>
                            <h3 class="h6"><?php echo xlt('Search results'); ?></h3>
                            <?php if ($bindingSearchResults === []) { ?>
                                <p class="text-muted"><?php echo xlt('No active users matched the search term.'); ?></p>
                            <?php } else { ?>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo xlt('User'); ?></th>
                                                <th><?php echo xlt('Active'); ?></th>
                                                <th><?php echo xlt('Authorized'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($bindingSearchResults as $user) {
                                            $fullName = trim((string) (($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')));
                                            $username = trim((string) ($user['username'] ?? ''));
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php echo text($username !== '' ? $username : (string) ($user['id'] ?? '')); ?>
                                                    <?php if ($fullName !== '') { ?>
                                                        <div class="text-muted small"><?php echo text($fullName); ?></div>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo !empty($user['active']) ? xlt('Yes') : xlt('No'); ?></td>
                                                <td><?php echo !empty($user['authorized']) ? xlt('Yes') : xlt('No'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><?php echo xlt('Current bindings'); ?></div>
        <div class="card-body">
            <?php if ($bindings === []) { ?>
                <p class="text-muted mb-0"><?php echo xlt('No identity bindings have been created for this site.'); ?></p>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th><?php echo xlt('Provider'); ?></th>
                                <th><?php echo xlt('External subject'); ?></th>
                                <th><?php echo xlt('Local user'); ?></th>
                                <th><?php echo xlt('Created'); ?></th>
                                <th><?php echo xlt('Updated'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bindings as $binding) {
                            $fullName = trim((string) (($binding['fname'] ?? '') . ' ' . ($binding['lname'] ?? '')));
                            $username = trim((string) ($binding['username'] ?? ''));
                            ?>
                            <tr>
                                <td>
                                    <?php echo text((string) ($binding['display_name'] ?? '')); ?>
                                    <?php if (!empty($binding['issuer_url'])) { ?>
                                        <div class="text-muted small"><?php echo text((string) $binding['issuer_url']); ?></div>
                                    <?php } ?>
                                </td>
                                <td><code><?php echo text((string) ($binding['subject'] ?? '')); ?></code></td>
                                <td>
                                    <?php echo text($username !== '' ? $username : (string) ($binding['user_id'] ?? '')); ?>
                                    <?php if ($fullName !== '') { ?>
                                        <div class="text-muted small"><?php echo text($fullName); ?></div>
                                    <?php } ?>
                                    <?php if (empty($binding['active'])) { ?>
                                        <div class="badge badge-warning"><?php echo xlt('Inactive'); ?></div>
                                    <?php } ?>
                                </td>
                                <td><?php echo text((string) ($binding['created_at'] ?? '')); ?></td>
                                <td><?php echo text((string) ($binding['updated_at'] ?? '')); ?></td>
                                <td class="text-right">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
                                        <input type="hidden" name="action" value="unbind">
                                        <input type="hidden" name="binding_id" value="<?php echo attr((string) ($binding['id'] ?? 0)); ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><?php echo xlt('Revoke'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</main>
<script>
(function () {
    const form = document.getElementById('provider-form');
    const messageBox = document.getElementById('external-idp-message');
    const keycloakBaseUrl = document.getElementById('keycloak_base_url');
    const keycloakRealmId = document.getElementById('keycloak_realm_id');
    const keycloakClientId = document.getElementById('keycloak_client_id');
    const keycloakClientSecret = document.getElementById('keycloak_client_secret');
    const keycloakIssuerPreview = document.getElementById('keycloak_issuer_preview');
    const keycloakApply = document.getElementById('keycloak_apply');
    const keycloakCopyIssuer = document.getElementById('keycloak_copy_issuer');
    const keycloakExample = document.getElementById('keycloak_example');
    const issuerField = document.getElementById('issuer_url');
    const displayNameField = document.getElementById('display_name');
    const clientIdField = document.getElementById('client_id');
    const clientSecretField = document.getElementById('client_secret');
    const provisioningModeField = document.getElementById('provisioning_mode');
    const provisioningFields = document.querySelectorAll('.provisioning-fields');

    function setMessage(type, message) {
        if (!messageBox) {
            return;
        }
        messageBox.className = 'alert alert-' + type;
        messageBox.textContent = message;
        messageBox.style.display = '';
    }

    const yesText = <?php echo json_encode(xl('Yes')); ?>;
    const noText = <?php echo json_encode(xl('No')); ?>;
    const notYetText = <?php echo json_encode(xl('Not yet tested')); ?>;
    const noneText = <?php echo json_encode(xl('None')); ?>;

    function computeKeycloakIssuer() {
        const baseUrl = (keycloakBaseUrl?.value || '').trim().replace(/\/+$/, '');
        const realmId = (keycloakRealmId?.value || '').trim();
        if (!baseUrl || !realmId) {
            return '';
        }
        return baseUrl + '/realms/' + encodeURIComponent(realmId);
    }

    function syncKeycloakPreview() {
        if (!keycloakIssuerPreview) {
            return;
        }
        keycloakIssuerPreview.value = computeKeycloakIssuer();
    }

    function applyKeycloakValues() {
        const issuer = computeKeycloakIssuer();
        if (issuerField && issuer) {
            issuerField.value = issuer;
        }
        if (clientIdField && keycloakClientId && keycloakClientId.value.trim()) {
            clientIdField.value = keycloakClientId.value.trim();
        }
        if (clientSecretField && keycloakClientSecret && keycloakClientSecret.value) {
            clientSecretField.value = keycloakClientSecret.value;
        }
        if (displayNameField && (!displayNameField.value || displayNameField.value === <?php echo json_encode(xl('External Identity Provider')); ?>)) {
            displayNameField.value = 'Keycloak SSO';
        }
        syncKeycloakPreview();
    }

    function loadExampleKeycloakValues() {
        if (keycloakBaseUrl) {
            keycloakBaseUrl.value = 'https://keycloak.example.com';
        }
        if (keycloakRealmId) {
            keycloakRealmId.value = 'clinic';
        }
        if (keycloakClientId) {
            keycloakClientId.value = 'openemr';
        }
        if (keycloakClientSecret) {
            keycloakClientSecret.value = 'change-me';
        }
        if (displayNameField) {
            displayNameField.value = 'Keycloak SSO';
        }
        syncKeycloakPreview();
        applyKeycloakValues();
    }

    [keycloakBaseUrl, keycloakRealmId].forEach(function (field) {
        if (field) {
            field.addEventListener('input', syncKeycloakPreview);
            field.addEventListener('change', syncKeycloakPreview);
        }
    });

    if (keycloakApply) {
        keycloakApply.addEventListener('click', applyKeycloakValues);
    }

    if (keycloakExample) {
        keycloakExample.addEventListener('click', loadExampleKeycloakValues);
    }

    if (keycloakCopyIssuer) {
        keycloakCopyIssuer.addEventListener('click', async function () {
            const issuer = computeKeycloakIssuer();
            if (!issuer) {
                setMessage('warning', '<?php echo xlj('Enter a Keycloak base URL and realm ID first.'); ?>');
                return;
            }
            try {
                await navigator.clipboard.writeText(issuer);
                setMessage('success', '<?php echo xlj('Issuer URL copied to clipboard.'); ?>');
            } catch (error) {
                setMessage('warning', issuer);
            }
        });
    }

    syncKeycloakPreview();

    function syncProvisioningVisibility() {
        const mode = provisioningModeField?.value || 'manual';
        const visible = mode === 'auto_provision' || mode === 'auto_bind_or_provision';
        provisioningFields.forEach(function (node) {
            node.style.display = visible ? '' : 'none';
        });
    }

    if (provisioningModeField) {
        provisioningModeField.addEventListener('change', syncProvisioningVisibility);
    }

    syncProvisioningVisibility();

    function updateStatus(provider) {
        if (!provider) {
            return;
        }
        const enabledNode = document.getElementById('provider-status-enabled');
        const discoveryNode = document.getElementById('provider-status-discovery');
        const startedNode = document.getElementById('provider-status-started');
        const successNode = document.getElementById('provider-status-success');
        const failureNode = document.getElementById('provider-status-failure');

        if (enabledNode) {
            enabledNode.textContent = provider.enabled ? yesText : noText;
        }
        if (discoveryNode) {
            discoveryNode.textContent = provider.discovery_fetched_at || notYetText;
        }
        if (startedNode) {
            startedNode.textContent = provider.last_started_at || noneText;
        }
        if (successNode) {
            successNode.textContent = provider.last_success_at || noneText;
        }
        if (failureNode) {
            failureNode.innerHTML = '';
            if (provider.last_failure_at) {
                failureNode.appendChild(document.createTextNode(provider.last_failure_at));
                if (provider.last_failure_message) {
                    const details = document.createElement('div');
                    details.className = 'text-break small';
                    details.textContent = provider.last_failure_message;
                    failureNode.appendChild(details);
                }
            } else {
                failureNode.textContent = noneText;
            }
        }
    }

    if (!form) {
        return;
    }

    form.addEventListener('submit', async function (event) {
        const submitter = event.submitter;
        const action = submitter?.dataset?.action || form.querySelector('#provider_action')?.value || 'save';
        if (action !== 'test' && action !== 'save') {
            return;
        }

        event.preventDefault();
        form.querySelector('#provider_action').value = action;

        if (submitter) {
            submitter.disabled = true;
        }

        try {
            const formData = new FormData(form);
            formData.set('action', action);

            const response = await fetch(window.location.href, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload) {
                throw new Error('<?php echo xlj('Request failed.'); ?>');
            }
            if (!payload.success) {
                throw new Error(payload.message || '<?php echo xlj('Request failed.'); ?>');
            }

            setMessage(payload.messageType || 'success', payload.message || '<?php echo xlj('Success'); ?>');
            if (action === 'save') {
                updateStatus(payload.provider || null);
            }
        } catch (error) {
            setMessage('danger', error?.message || '<?php echo xlj('Request failed.'); ?>');
        } finally {
            if (submitter) {
                submitter.disabled = false;
            }
        }
    });
})();
</script>
<?php if (!$renderPartial) { ?>
</body>
</html>
<?php } ?>
