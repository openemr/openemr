<?php

/**
 * GCIP module admin configuration page.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Access denied');
    exit;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
$webroot = OEGlobalsBag::getInstance()->getString('webroot');
$modulePath = $webroot . '/interface/modules/custom_modules/oe-module-gcip-auth';

$configService = new GcipConfigService();
$config = $configService->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('GCIP Authentication Configuration'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        .gcip-config-form { max-width: 600px; margin: 20px auto; }
        .gcip-config-form .form-group { margin-bottom: 15px; }
        .gcip-config-form label { font-weight: bold; }
        .gcip-config-form .help-text { font-size: 0.85em; color: #666; margin-top: 4px; }
        .gcip-status { margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo xlt('GCIP Authentication Configuration'); ?></h2>
        <hr>

        <div class="gcip-config-form">
            <form id="gcip-config-form">
                <input type="hidden" name="csrf_token" value="<?php echo attr($csrfToken); ?>">
                <input type="hidden" name="action" value="save">

                <div class="form-group">
                    <label for="gcip_firebase_project_id"><?php echo xlt('Firebase Project ID'); ?></label>
                    <input type="text" class="form-control" id="gcip_firebase_project_id"
                           name="gcip_firebase_project_id"
                           value="<?php echo attr($config['gcip_firebase_project_id'] ?? ''); ?>">
                    <div class="help-text"><?php echo xlt('Your Google Cloud/Firebase project identifier'); ?></div>
                </div>

                <div class="form-group">
                    <label for="gcip_firebase_api_key"><?php echo xlt('Firebase API Key'); ?></label>
                    <input type="text" class="form-control" id="gcip_firebase_api_key"
                           name="gcip_firebase_api_key"
                           value="<?php echo attr($config['gcip_firebase_api_key'] ?? ''); ?>">
                    <div class="help-text"><?php echo xlt('Client-side API key for Firebase JS SDK'); ?></div>
                </div>

                <div class="form-group">
                    <label for="gcip_firebase_auth_domain"><?php echo xlt('Firebase Auth Domain'); ?></label>
                    <input type="text" class="form-control" id="gcip_firebase_auth_domain"
                           name="gcip_firebase_auth_domain"
                           value="<?php echo attr($config['gcip_firebase_auth_domain'] ?? ''); ?>"
                           placeholder="your-project.firebaseapp.com">
                    <div class="help-text"><?php echo xlt('Firebase authentication domain'); ?></div>
                </div>

                <div class="form-group">
                    <label for="gcip_issuer"><?php echo xlt('OIDC Issuer URL'); ?></label>
                    <input type="text" class="form-control" id="gcip_issuer"
                           name="gcip_issuer"
                           value="<?php echo attr($config['gcip_issuer'] ?? ''); ?>"
                           placeholder="https://securetoken.google.com/your-project">
                    <div class="help-text"><?php echo xlt('Expected issuer (iss) claim in ID tokens'); ?></div>
                </div>

                <div class="form-group">
                    <label for="gcip_client_id"><?php echo xlt('Expected Audience (Client ID)'); ?></label>
                    <input type="text" class="form-control" id="gcip_client_id"
                           name="gcip_client_id"
                           value="<?php echo attr($config['gcip_client_id'] ?? ''); ?>"
                           placeholder="your-firebase-project-id">
                    <div class="help-text"><?php echo xlt('Expected audience (aud) claim — typically the Firebase project ID'); ?></div>
                </div>

                <div class="form-group">
                    <label for="gcip_allowed_tenant_ids"><?php echo xlt('Allowed Tenant IDs'); ?></label>
                    <input type="text" class="form-control" id="gcip_allowed_tenant_ids"
                           name="gcip_allowed_tenant_ids"
                           value="<?php echo attr($config['gcip_allowed_tenant_ids'] ?? ''); ?>"
                           placeholder="tenant-1, tenant-2">
                    <div class="help-text"><?php echo xlt('Comma-separated list of allowed GCIP tenant IDs (leave empty for no tenant filtering)'); ?></div>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo xlt('Save Configuration'); ?></button>

                <div class="gcip-status" id="gcip-save-status"></div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            const form = document.getElementById('gcip-config-form');
            const status = document.getElementById('gcip-save-status');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                status.textContent = <?php echo xlj('Saving...'); ?>;
                status.className = 'gcip-status text-info';

                const formData = new FormData(form);

                fetch(<?php echo json_encode($modulePath . '/public/admin_ajax.php'); ?>, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        status.textContent = <?php echo xlj('Configuration saved successfully.'); ?>;
                        status.className = 'gcip-status text-success';
                    } else {
                        status.textContent = data.error || <?php echo xlj('Save failed.'); ?>;
                        status.className = 'gcip-status text-danger';
                    }
                })
                .catch(function() {
                    status.textContent = <?php echo xlj('Save failed — network error.'); ?>;
                    status.className = 'gcip-status text-danger';
                });
            });
        })();
    </script>
</body>
</html>
