<?php

/**
 * SSO Module Configuration Page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 4) . '/globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Access Denied');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'save' && CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // Handle form submission
    $providerId = $_POST['provider_id'] ?? '';
    $cryptoGen = new CryptoGen();

    // Get existing config to preserve encrypted secret if not changed
    $existingConfig = [];
    $existing = sqlQuery("SELECT config FROM sso_providers WHERE provider_type = ?", [$providerId]);
    if ($existing) {
        $existingConfig = json_decode($existing['config'], true) ?? [];
    }

    // Encrypt the client secret before storing, or keep existing
    $clientSecret = $_POST['client_secret'] ?? '';
    if (!empty($clientSecret)) {
        $clientSecret = $cryptoGen->encryptStandard($clientSecret);
    } else {
        // Keep existing encrypted secret if no new one provided
        $clientSecret = $existingConfig['client_secret'] ?? '';
    }

    $config = [
        'enabled' => isset($_POST['enabled']) ? 1 : 0,
        'client_id' => $_POST['client_id'] ?? '',
        'client_secret' => $clientSecret,
        'tenant_id' => $_POST['tenant_id'] ?? '',
        'discovery_url' => $_POST['discovery_url'] ?? '',
        'display_name' => $_POST['display_name'] ?? '',
        'icon_url' => $_POST['icon_url'] ?? '',
        'auto_provision' => isset($_POST['auto_provision']) ? 1 : 0,
    ];

    // Extract values that need to be in columns (not just JSON)
    $autoProvision = $config['auto_provision'] ?? 0;
    $defaultAcl = $_POST['default_acl'] ?? 'users';

    // Save to database
    if ($existing) {
        sqlStatement(
            "UPDATE sso_providers SET enabled = ?, auto_provision = ?, default_acl = ?, config = ?, updated_at = NOW() WHERE provider_type = ?",
            [$config['enabled'], $autoProvision, $defaultAcl, json_encode($config), $providerId]
        );
    } else {
        sqlStatement(
            "INSERT INTO sso_providers (provider_type, name, enabled, auto_provision, default_acl, config, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$providerId, ucfirst($providerId), $config['enabled'], $autoProvision, $defaultAcl, json_encode($config)]
        );
    }
}

// Load existing configurations
$providers = [
    'entra' => ['name' => 'Microsoft Entra ID', 'config' => [], 'default_acl' => 'users'],
    'google' => ['name' => 'Google Workspace', 'config' => [], 'default_acl' => 'users'],
    'generic_oidc' => ['name' => 'Generic OIDC', 'config' => [], 'default_acl' => 'users'],
];

$results = sqlStatement("SELECT * FROM sso_providers");
while ($row = sqlFetchArray($results)) {
    if (isset($providers[$row['provider_type']])) {
        $providers[$row['provider_type']]['config'] = json_decode($row['config'], true) ?? [];
        $providers[$row['provider_type']]['enabled'] = $row['enabled'];
        $providers[$row['provider_type']]['default_acl'] = $row['default_acl'] ?? 'users';
    }
}

// Load available ACL groups for dropdown
$aclGroups = [];
$aclResults = sqlStatement("SELECT id, value, name FROM gacl_aro_groups ORDER BY name");
while ($row = sqlFetchArray($aclResults)) {
    $aclGroups[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('SSO Configuration'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        .provider-card { margin-bottom: 20px; }
        .provider-card .card-header { cursor: pointer; }
        .config-section { padding: 15px; }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('SSO Authentication Configuration'); ?></h2>
        <p class="text-muted"><?php echo xlt('Configure Single Sign-On providers for OpenEMR authentication.'); ?></p>

        <div class="accordion" id="providerAccordion">
            <?php foreach ($providers as $id => $provider): ?>
            <div class="card provider-card">
                <div class="card-header" data-toggle="collapse" data-target="#collapse-<?php echo attr($id); ?>">
                    <h5 class="mb-0">
                        <?php echo text($provider['name']); ?>
                        <?php if (!empty($provider['enabled'])): ?>
                        <span class="badge badge-success ml-2"><?php echo xlt('Enabled'); ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div id="collapse-<?php echo attr($id); ?>" class="collapse" data-parent="#providerAccordion">
                    <div class="card-body">
                        <form method="POST" class="config-section">
                            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="provider_id" value="<?php echo attr($id); ?>">

                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" name="enabled" id="enabled-<?php echo attr($id); ?>"
                                    <?php echo !empty($provider['config']['enabled']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enabled-<?php echo attr($id); ?>">
                                    <?php echo xlt('Enable this provider'); ?>
                                </label>
                            </div>

                            <div class="form-group">
                                <label><?php echo xlt('Client ID'); ?></label>
                                <input type="text" class="form-control" name="client_id"
                                    value="<?php echo attr($provider['config']['client_id'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label><?php echo xlt('Client Secret'); ?></label>
                                <input type="password" class="form-control" name="client_secret"
                                    placeholder="<?php echo !empty($provider['config']['client_secret']) ? '••••••••••••' : ''; ?>">
                                <?php if (!empty($provider['config']['client_secret'])): ?>
                                <small class="form-text text-muted"><?php echo xlt('Leave blank to keep existing secret'); ?></small>
                                <?php endif; ?>
                            </div>

                            <?php if ($id === 'entra'): ?>
                            <div class="form-group">
                                <label><?php echo xlt('Tenant ID'); ?></label>
                                <input type="text" class="form-control" name="tenant_id"
                                    value="<?php echo attr($provider['config']['tenant_id'] ?? ''); ?>"
                                    placeholder="e.g., your-tenant-id or common">
                            </div>
                            <?php endif; ?>

                            <?php if ($id === 'generic_oidc'): ?>
                            <div class="form-group">
                                <label><?php echo xlt('Display Name'); ?></label>
                                <input type="text" class="form-control" name="display_name"
                                    value="<?php echo attr($provider['config']['display_name'] ?? ''); ?>"
                                    placeholder="e.g., Okta, Keycloak, Auth0">
                                <small class="form-text text-muted"><?php echo xlt('Name shown on the login button (defaults to "SSO" if empty)'); ?></small>
                            </div>
                            <div class="form-group">
                                <label><?php echo xlt('Icon URL'); ?></label>
                                <input type="url" class="form-control" name="icon_url"
                                    value="<?php echo attr($provider['config']['icon_url'] ?? ''); ?>"
                                    placeholder="https://example.com/icon.png">
                                <small class="form-text text-muted"><?php echo xlt('URL to a small icon image (optional, uses default lock icon if empty)'); ?></small>
                            </div>
                            <div class="form-group">
                                <label><?php echo xlt('Discovery URL'); ?></label>
                                <input type="url" class="form-control" name="discovery_url"
                                    value="<?php echo attr($provider['config']['discovery_url'] ?? ''); ?>"
                                    placeholder="https://example.com/.well-known/openid-configuration">
                            </div>
                            <?php endif; ?>

                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" name="auto_provision" id="provision-<?php echo attr($id); ?>"
                                    <?php echo !empty($provider['config']['auto_provision']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="provision-<?php echo attr($id); ?>">
                                    <?php echo xlt('Auto-provision new users'); ?>
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="default_acl-<?php echo attr($id); ?>"><?php echo xlt('Default ACL Group for New Users'); ?></label>
                                <select class="form-control" name="default_acl" id="default_acl-<?php echo attr($id); ?>">
                                    <?php foreach ($aclGroups as $aclGroup): ?>
                                    <option value="<?php echo attr($aclGroup['value']); ?>"
                                        <?php echo ($provider['default_acl'] === $aclGroup['value']) ? 'selected' : ''; ?>>
                                        <?php echo text($aclGroup['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted"><?php echo xlt('ACL group assigned to auto-provisioned users'); ?></small>
                            </div>

                            <button type="submit" class="btn btn-primary"><?php echo xlt('Save Configuration'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5><?php echo xlt('Callback URLs'); ?></h5>
            </div>
            <div class="card-body">
                <p><?php echo xlt('Configure these URLs in your identity provider:'); ?></p>
                <table class="table table-sm">
                    <tr>
                        <td><strong><?php echo xlt('Redirect URI'); ?></strong></td>
                        <td><code><?php echo text($GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-sso/public/callback.php'); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo xlt('Logout URI'); ?></strong></td>
                        <td><code><?php echo text($GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-sso/public/logout.php'); ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
