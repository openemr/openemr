<?php

/**
 * GCIP Authentication Module Setup Template
 * 
 * <!-- AI-Generated Content Start -->
 * This template provides the administrative interface for configuring
 * GCIP authentication settings, including OAuth2 credentials, domain
 * restrictions, and security options.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;

// Initialize services - AI-Generated
$configService = new GcipConfigService();
$auditService = new GcipAuditService();

// Handle form submission - AI-Generated
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $currentUser = $_SESSION['authUser'] ?? 'admin';
    
    if ($action === 'save_config') {
        // Save configuration - AI-Generated
        $configService->setConfigValue('gcip_enabled', isset($_POST['gcip_enabled']));
        $configService->setConfigValue('gcip_project_id', $_POST['gcip_project_id'] ?? '');
        $configService->setConfigValue('gcip_client_id', $_POST['gcip_client_id'] ?? '');
        
        // Encrypt and save client secret if provided - AI-Generated
        if (!empty($_POST['gcip_client_secret'])) {
            $configService->setConfigValue('gcip_client_secret', $_POST['gcip_client_secret'], true);
        }
        
        $configService->setConfigValue('gcip_tenant_id', $_POST['gcip_tenant_id'] ?? '');
        $configService->setConfigValue('gcip_redirect_uri', $_POST['gcip_redirect_uri'] ?? '');
        $configService->setConfigValue('gcip_domain_restriction', $_POST['gcip_domain_restriction'] ?? '');
        $configService->setConfigValue('gcip_auto_user_creation', isset($_POST['gcip_auto_user_creation']));
        $configService->setConfigValue('gcip_default_role', $_POST['gcip_default_role'] ?? 'Clinician');
        $configService->setConfigValue('gcip_audit_logging', isset($_POST['gcip_audit_logging']));
        
        // Log configuration change - AI-Generated
        $auditService->logConfigurationChange($currentUser, 'GCIP Module Settings', 'updated');
        
        $configSaved = true;
    }
    
    if ($action === 'validate_config') {
        // Validate configuration - AI-Generated
        $validation = $configService->validateConfiguration();
        $validationResult = $validation;
    }
}

// Get current configuration - AI-Generated
$config = $configService->getAllConfig();
$validation = $configService->validateConfiguration();

// Generate default redirect URI - AI-Generated
$defaultRedirectUri = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-gcip-auth/public/callback.php';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('GCIP Authentication Setup'); ?></title>
    <!-- AI-Generated CSS imports -->
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/themes/style_color.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/bootstrap.min.css">
    <style>
        /* AI-Generated styles for GCIP setup interface */
        .gcip-setup-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .config-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 20px;
            margin-bottom: 20px;
        }
        .validation-error {
            color: #dc3545;
            font-size: 0.875em;
        }
        .validation-success {
            color: #28a745;
            font-size: 0.875em;
        }
        .form-help {
            font-size: 0.875em;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-enabled { background-color: #28a745; }
        .status-disabled { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; }
    </style>
</head>
<body>
    <div class="gcip-setup-container">
        <h2><?php echo xlt('GCIP Authentication Module Setup'); ?></h2>
        
        <?php if (isset($configSaved)): ?>
            <!-- AI-Generated success message -->
            <div class="alert alert-success">
                <?php echo xlt('Configuration saved successfully!'); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($validationResult)): ?>
            <!-- AI-Generated validation result -->
            <div class="alert <?php echo $validationResult['valid'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php if ($validationResult['valid']): ?>
                    <?php echo xlt('Configuration validation successful!'); ?>
                <?php else: ?>
                    <?php echo xlt('Configuration validation failed:'); ?>
                    <ul>
                        <?php foreach ($validationResult['errors'] as $error): ?>
                            <li><?php echo xht($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="action" value="save_config">
            
            <!-- Module Status Section - AI-Generated -->
            <div class="config-section">
                <h4>
                    <span class="status-indicator <?php echo $config['enabled'] ? 'status-enabled' : 'status-disabled'; ?>"></span>
                    <?php echo xlt('Module Status'); ?>
                </h4>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="gcip_enabled" name="gcip_enabled" 
                           <?php echo $config['enabled'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gcip_enabled">
                        <?php echo xlt('Enable GCIP Authentication'); ?>
                    </label>
                    <div class="form-help">
                        <?php echo xlt('Enable or disable GCIP authentication for this OpenEMR installation.'); ?>
                    </div>
                </div>
            </div>

            <!-- Primary GCIP Settings Section - AI-Generated -->
            <div class="config-section">
                <h4><?php echo xlt('Primary GCIP Settings'); ?></h4>
                
                <div class="form-group">
                    <label for="gcip_project_id"><?php echo xlt('GCIP Project ID'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="gcip_project_id" name="gcip_project_id" 
                           value="<?php echo xla($config['project_id'] ?? ''); ?>" required>
                    <div class="form-help">
                        <?php echo xlt('Your Google Cloud project ID where GCIP is configured.'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gcip_client_id"><?php echo xlt('OAuth2 Client ID'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="gcip_client_id" name="gcip_client_id" 
                           value="<?php echo xla($config['client_id'] ?? ''); ?>" required>
                    <div class="form-help">
                        <?php echo xlt('OAuth2 client ID from Google Cloud Console.'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gcip_client_secret"><?php echo xlt('OAuth2 Client Secret'); ?> <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="gcip_client_secret" name="gcip_client_secret" 
                           placeholder="<?php echo $config['client_secret_set'] ? xla('(Secret is set)') : xla('Enter client secret'); ?>">
                    <div class="form-help">
                        <?php echo xlt('OAuth2 client secret from Google Cloud Console. Leave blank to keep current secret.'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gcip_redirect_uri"><?php echo xlt('Redirect URI'); ?> <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="gcip_redirect_uri" name="gcip_redirect_uri" 
                           value="<?php echo xla($config['redirect_uri'] ?? $defaultRedirectUri); ?>" required>
                    <div class="form-help">
                        <?php echo xlt('OAuth2 redirect URI. This must match the URI configured in Google Cloud Console.'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gcip_tenant_id"><?php echo xlt('Tenant ID (Optional)'); ?></label>
                    <input type="text" class="form-control" id="gcip_tenant_id" name="gcip_tenant_id" 
                           value="<?php echo xla($config['tenant_id'] ?? ''); ?>">
                    <div class="form-help">
                        <?php echo xlt('Tenant ID for multi-tenant GCIP configurations. Leave blank for single-tenant setup.'); ?>
                    </div>
                </div>
            </div>

            <!-- Security Settings Section - AI-Generated -->
            <div class="config-section">
                <h4><?php echo xlt('Security Settings'); ?></h4>
                
                <div class="form-group">
                    <label for="gcip_domain_restriction"><?php echo xlt('Allowed Email Domains'); ?></label>
                    <input type="text" class="form-control" id="gcip_domain_restriction" name="gcip_domain_restriction" 
                           value="<?php echo xla($config['domain_restriction'] ?? ''); ?>" 
                           placeholder="example.com, company.org">
                    <div class="form-help">
                        <?php echo xlt('Comma-separated list of allowed email domains. Leave blank to allow any domain.'); ?>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="gcip_auto_user_creation" name="gcip_auto_user_creation" 
                           <?php echo $config['auto_user_creation'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gcip_auto_user_creation">
                        <?php echo xlt('Auto-create User Accounts'); ?>
                    </label>
                    <div class="form-help">
                        <?php echo xlt('Automatically create OpenEMR user accounts for new GCIP authenticated users.'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gcip_default_role"><?php echo xlt('Default Role for New Users'); ?></label>
                    <select class="form-control" id="gcip_default_role" name="gcip_default_role">
                        <option value="Clinician" <?php echo ($config['default_role'] ?? '') === 'Clinician' ? 'selected' : ''; ?>>
                            <?php echo xlt('Clinician'); ?>
                        </option>
                        <option value="Physician" <?php echo ($config['default_role'] ?? '') === 'Physician' ? 'selected' : ''; ?>>
                            <?php echo xlt('Physician'); ?>
                        </option>
                        <option value="Administrator" <?php echo ($config['default_role'] ?? '') === 'Administrator' ? 'selected' : ''; ?>>
                            <?php echo xlt('Administrator'); ?>
                        </option>
                    </select>
                    <div class="form-help">
                        <?php echo xlt('Default role assigned to auto-created user accounts.'); ?>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="gcip_audit_logging" name="gcip_audit_logging" 
                           <?php echo $config['audit_logging'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gcip_audit_logging">
                        <?php echo xlt('Enable Audit Logging'); ?>
                    </label>
                    <div class="form-help">
                        <?php echo xlt('Enable comprehensive audit logging for GCIP authentication events.'); ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons - AI-Generated -->
            <div class="form-group">
                <button type="submit" name="action" value="validate_config" class="btn btn-secondary">
                    <?php echo xlt('Validate Configuration'); ?>
                </button>
                <button type="submit" name="action" value="save_config" class="btn btn-primary">
                    <?php echo xlt('Save Configuration'); ?>
                </button>
            </div>
        </form>

        <!-- Configuration Status - AI-Generated -->
        <div class="config-section">
            <h4><?php echo xlt('Configuration Status'); ?></h4>
            
            <div class="validation-status">
                <?php if ($validation['valid']): ?>
                    <p class="validation-success">
                        <i class="fa fa-check-circle"></i>
                        <?php echo xlt('Configuration is valid and ready for use.'); ?>
                    </p>
                <?php else: ?>
                    <p class="validation-error">
                        <i class="fa fa-exclamation-triangle"></i>
                        <?php echo xlt('Configuration validation failed. Please check the errors above.'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- AI-Generated JavaScript for form validation -->
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.min.js"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Form validation for required fields - AI-Generated
            $('form').on('submit', function(e) {
                var isValid = true;
                $('input[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('<?php echo xjs('Please fill in all required fields.'); ?>');
                }
            });
            
            // Generate default redirect URI - AI-Generated
            if (!$('#gcip_redirect_uri').val()) {
                $('#gcip_redirect_uri').val('<?php echo xjs($defaultRedirectUri); ?>');
            }
        });
    </script>
</body>
</html>