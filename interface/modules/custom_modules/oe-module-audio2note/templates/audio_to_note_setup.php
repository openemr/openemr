<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// globals.php is expected to be loaded by moduleConfig.php, which should set up the environment.

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Call Header::setupHeader() early to ensure environment, including session and globals like site_id, is fully initialized.
// This is typically done before any significant logic or HTML output.
// Note: This might output headers early. If issues arise, this might need adjustment
// or a more targeted way to ensure $GLOBALS['site_id'] is populated.
if (class_exists('OpenEMR\Core\Header')) {
    Header::setupHeader();
}
// use OpenEMR\Common\Crypto\CryptoGen;
// Replaced by EncryptionService
use OpenEMR\Modules\OpenemrAudio2Note\Logic\EncryptionService;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\LicenseClient;
// Assuming this will be created
use OpenEMR\Modules\OpenemrAudio2Note\Setup;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        die(xlt('CSRF token validation failed. Please try again.'));
    }

    // Sanitize inputs
    // $backend_audio_process_base_url = filter_input(INPUT_POST, 'audio_note_backend_audio_process_base_url', FILTER_SANITIZE_URL); // Static now
    // $license_api_base_url = filter_input(INPUT_POST, 'audio_note_license_api_base_url', FILTER_SANITIZE_URL); // Static now
    $license_key = filter_input(INPUT_POST, 'audio_note_license_key', FILTER_UNSAFE_RAW);
    $license_consumer_key = filter_input(INPUT_POST, 'audio_note_license_consumer_key', FILTER_UNSAFE_RAW);
    $license_consumer_secret = filter_input(INPUT_POST, 'audio_note_license_consumer_secret', FILTER_UNSAFE_RAW);

    // Adjusted condition as two URLs are now static
    if (empty($license_key) || empty($license_consumer_key) || empty($license_consumer_secret)) {
        echo "<div class='alert alert-danger'>" . xlt('License Key, Consumer Key, and Consumer Secret are required.') . "</div>";
    // Removed HTTPS check for $backend_audio_process_base_url as it's static
    } else {
        try {
            // Initialize Encryption Service
            $encryptionService = new EncryptionService();

            // Encrypt sensitive data
            // $encrypted_backend_audio_process_base_url = $encryptionService->encrypt($backend_audio_process_base_url); // Static now
            // $encrypted_license_api_base_url = $encryptionService->encrypt($license_api_base_url); // Static now
            $encrypted_license_key = $encryptionService->encrypt($license_key);
            $encrypted_license_consumer_key = $encryptionService->encrypt($license_consumer_key);
            $encrypted_license_consumer_secret = $encryptionService->encrypt($license_consumer_secret);

            // Adjusted condition
            if ($encrypted_license_key === false || $encrypted_license_consumer_key === false || $encrypted_license_consumer_secret === false) {
                 throw new \Exception(xlt('Failed to encrypt sensitive data (license key, consumer key, or consumer secret). Check OpenEMR logs.'));
            }

            $instanceUuid = Setup::getStoredInstanceUuid();
            if (empty($instanceUuid)) {
                 throw new \Exception(xlt('Failed to retrieve OpenEMR instance UUID. Module might not be installed correctly.'));
            }

            // Compute Effective Instance Identifier
            $sqlconf_path = dirname(__FILE__, 6) . '/sqlconf.php';
            $sqlconf_content = file_exists($sqlconf_path) ? file_get_contents($sqlconf_path) : '';
            
            $site_id_for_hash = $GLOBALS['site_id'] ?? 'default'; // Fallback if somehow not set
            // error_log("Audio2Note Module Configuration Info: Using site_id for hash: " . $site_id_for_hash);
            $effective_instance_identifier = hash('sha256', $instanceUuid . $sqlconf_content . $site_id_for_hash);

            $config_row_id = 1; // Assuming a single configuration row
            $now = date('Y-m-d H:i:s');

            $resultForExistingCheck = sqlQuery("SELECT id FROM `audio2note_config` WHERE id = ?", [$config_row_id]);
            $configRowActuallyExists = ($resultForExistingCheck && ( (is_array($resultForExistingCheck) && !empty($resultForExistingCheck['id'])) || (is_object($resultForExistingCheck) && method_exists($resultForExistingCheck, 'RecordCount') && $resultForExistingCheck->RecordCount() > 0) ));

            if ($configRowActuallyExists) {
                sqlStatement(
                    "UPDATE `audio2note_config` SET
                    `openemr_internal_random_uuid` = ?, `effective_instance_identifier` = ?,
                    `encrypted_license_key` = ?, `encrypted_license_consumer_key` = ?, `encrypted_license_consumer_secret` = ?,
                    `updated_at` = ?
                    WHERE `id` = ?",
                    [
                        $instanceUuid, $effective_instance_identifier,
                        $encrypted_license_key, $encrypted_license_consumer_key, $encrypted_license_consumer_secret,
                        $now,
                        $config_row_id
                    ]
                );
            } else {
                // This path indicates an issue if Setup::install() didn't create the initial row.
                error_log("Audio2Note Module Configuration WARNING: audio2note_config row with ID {$config_row_id} not found. Attempting to insert.");
                sqlStatement(
                    "INSERT INTO `audio2note_config` (
                    `id`, `openemr_internal_random_uuid`, `effective_instance_identifier`,
                    `encrypted_license_key`, `encrypted_license_consumer_key`, `encrypted_license_consumer_secret`,
                    `created_at`, `updated_at`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $config_row_id, $instanceUuid, $effective_instance_identifier,
                        $encrypted_license_key, $encrypted_license_consumer_key, $encrypted_license_consumer_secret,
                        $now, $now
                    ]
                );
            }
            // Remove old global if it exists
            sqlStatement("DELETE FROM `globals` WHERE `gl_name` = 'audio_note_backend_audio_process_base_url'");
            // Also remove old global for license_api_base_url if it existed
            sqlStatement("DELETE FROM `globals` WHERE `gl_name` = 'audio_note_license_api_base_url'");


            // Call Licensing Service API for Activation
            // LicenseClient constructor now uses its own hardcoded URL and only needs consumer key/secret.
            $licenseClient = new LicenseClient(
                $license_consumer_key,
                $license_consumer_secret
            );
            $activationResult = $licenseClient->activateLicense($license_key, $effective_instance_identifier);

            // Handle Activation Result
            $license_status_val = 'inactive';
            $license_expires_at_val = null;
            $encrypted_dlm_token_val = null;
            $message = xlt('License activation failed.');
            $alertClass = 'alert-danger';

            if ($activationResult && isset($activationResult['success']) && $activationResult['success'] === true && isset($activationResult['data'])) {
                $license_status_val = 'active';
                $message = xlt('License activated successfully.');
                if (isset($activationResult['data']['license']['expires_at'])) {
                    $message .= " " . xlt('Expires:') . " " . $activationResult['data']['license']['expires_at'];
                    $license_expires_at_val = $activationResult['data']['license']['expires_at'];
                }
                if (isset($activationResult['data']['token'])) {
                    $encrypted_dlm_token_val = $encryptionService->encrypt($activationResult['data']['token']);
                }
                $alertClass = 'alert-success';
            } else {
                if ($activationResult && isset($activationResult['message'])) {
                    $message .= " " . htmlspecialchars($activationResult['message']);
                } elseif ($activationResult && isset($activationResult['code'])) {
                     $message .= " Error code: " . htmlspecialchars($activationResult['code']);
                     if(isset($activationResult['data']['status'])) {
                         $message .= " Status: " . htmlspecialchars($activationResult['data']['status']);
                     }
                } else {
                     $message .= " " . xlt('Unknown API response or connection error.');
                }
            }
            // Update DB with activation details
            sqlStatement(
                "UPDATE `audio2note_config` SET
                `license_status` = ?, `license_expires_at` = ?, `encrypted_dlm_activation_token` = ?, `updated_at` = ?
                WHERE `id` = ?",
                [$license_status_val, $license_expires_at_val, $encrypted_dlm_token_val, $now, $config_row_id]
            );

            echo "<div class='alert {$alertClass}'>" . $message . "</div>";

        } catch (\Exception $e) {
            echo "<div class='alert alert-danger'>" . xlt('Error processing configuration:') . " " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Audio2Note Module Configuration Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}

// Load current settings for display
$currentConfig = sqlQuery("SELECT * FROM `audio2note_config` WHERE id = 1 LIMIT 1");
// $decrypted_backend_audio_process_base_url = ''; // Removed, URL is static
// $decrypted_license_api_base_url = ''; // Removed, URL is static

if ($currentConfig) {
    // $encryptionServiceForDisplay = new EncryptionService(); // Instantiated only if needed
    // $decrypted_backend_audio_process_base_url = isset($currentConfig['encrypted_backend_audio_process_base_url']) ? ($encryptionServiceForDisplay->decrypt($currentConfig['encrypted_backend_audio_process_base_url']) ?: '') : ''; // Removed
    // $decrypted_license_api_base_url = isset($currentConfig['encrypted_license_api_base_url']) ? ($encryptionServiceForDisplay->decrypt($currentConfig['encrypted_license_api_base_url']) ?: '') : ''; // Removed
}

$license_status_display = $currentConfig['license_status'] ?? xlt('Not Configured');
$license_expires_at_display = $currentConfig['license_expires_at'] ?? xlt('N/A');
if ($license_expires_at_display && $license_expires_at_display !== xlt('N/A')) {
    $license_expires_at_display = oeFormatShortDate($license_expires_at_display) . " " . oeFormatTime($license_expires_at_display);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Audio to Note Configuration'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body { padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .license-info { margin-top: 15px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h3><?php echo xlt('Audio to Note Module Configuration'); ?></h3>
        <hr>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />


            <div class="form-group">
                <label for="audio_note_license_key"><?php echo xlt('License Key'); ?></label>
                <input type="password" class="form-control" id="audio_note_license_key" name="audio_note_license_key" value="" autocomplete="new-password" required>
                <small class="form-text text-muted"><?php echo xlt('Enter your purchased license key.'); ?></small>
            </div>

            <div class="form-group">
                <label for="audio_note_license_consumer_key"><?php echo xlt('Licensing Service API Consumer Key'); ?></label>
                <input type="password" class="form-control" id="audio_note_license_consumer_key" name="audio_note_license_consumer_key" value="" autocomplete="new-password" required>
                <small class="form-text text-muted"><?php echo xlt('Your Licensing Service API Consumer Key.'); ?></small>
            </div>

            <div class="form-group">
                <label for="audio_note_license_consumer_secret"><?php echo xlt('Licensing Service API Consumer Secret'); ?></label>
                <input type="password" class="form-control" id="audio_note_license_consumer_secret" name="audio_note_license_consumer_secret" value="" autocomplete="new-password" required>
                <small class="form-text text-muted"><?php echo xlt('Your Licensing Service API Consumer Secret.'); ?></small>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo xlt('Save and Activate License'); ?></button>
        </form>

        <div class="license-info">
            <h4><?php echo xlt('Current License Status'); ?></h4>
            <p><strong><?php echo xlt('Status:'); ?></strong> <span id="license-status-text"><?php echo htmlspecialchars($license_status_display); ?></span></p>
            <p><strong><?php echo xlt('Expires:'); ?></strong> <span id="license-expiry-text"><?php echo htmlspecialchars($license_expires_at_display); ?></span></p>
        </div>
    </div>
</body>
</html>