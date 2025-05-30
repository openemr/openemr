<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\OpenemrAudio2Note\Logic;

use OpenEMR\Common\Logging\SystemLogger;
use Psr\Log\LoggerInterface;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\EncryptionService; // Corrected
use OpenEMR\Modules\OpenemrAudio2Note\Logic\LicenseClient;

class LicenseStatusChecker
{
    private const CACHE_DURATION_SECONDS = 24 * 60 * 60; // 24 hours
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
    }

    public function isLicenseActive(): bool
    {
        // Ensure $GLOBALS['site_id'] is set for this context, as encryption/decryption might depend on it.
        if (empty($GLOBALS['site_id'])) {
            if (isset($_SESSION['site_id']) && !empty($_SESSION['site_id'])) {
                $GLOBALS['site_id'] = $_SESSION['site_id'];
            } else {
                // Fallback to 'default' if session also doesn't have it.
                $GLOBALS['site_id'] = 'default';
                $this->logger->warning("Audio2Note: LicenseStatusChecker: \$GLOBALS['site_id'] and \$_SESSION['site_id'] were empty, defaulted \$GLOBALS['site_id'] to 'default'");
            }
        }
        $this->logger->info("Audio2Note: LicenseStatusChecker: Using site_id: " . ($GLOBALS['site_id'] ?? 'NOT SET'));

        // 1. Retrieve current license config from DB
        $config = sqlQuery("SELECT * FROM `audio2note_config` LIMIT 1");
        $this->logger->info("Audio2Note: LicenseStatusChecker: Raw config from DB: " . json_encode($config));


        if (!$config || empty($config['license_status']) || $config['license_status'] === 'not_configured') {
            $this->logger->warning("Audio2Note: LicenseStatusChecker: No config or license_status not configured or empty. Status: " . ($config['license_status'] ?? 'N/A'));
            return false; // No configuration or not configured
        }

        // 2. Check cached status and expiry
        $currentStatus = $config['license_status'];
        $expiresAt = $config['license_expires_at'];
        $lastValidationTimestamp = $config['last_validation_timestamp'];

        $now = new \DateTime();
        $isCacheValid = false;

        if ($lastValidationTimestamp) {
            $lastValidationDateTime = new \DateTime($lastValidationTimestamp);
            $interval = $now->getTimestamp() - $lastValidationDateTime->getTimestamp();
            if ($interval < self::CACHE_DURATION_SECONDS) {
                $isCacheValid = true;
            }
        }

        // If cached status is active and cache is valid, return true
        if ($currentStatus === 'active' && $isCacheValid) {
            // Also check expiry locally if available and not null
            if ($expiresAt) {
                $expiryDateTime = new \DateTime($expiresAt);
                if ($now < $expiryDateTime) {
                    return true; // Active, valid cache, not expired locally
                } else {
                    // License expired locally, force re-validation
                    $this->logger->info("Audio2Note: Local license expiry detected. Forcing re-validation.");
                }
            } else {
                 return true; // Active, valid cache, no expiry date set (e.g., perpetual license)
            }
        }

        // 3. If cache is stale or status is not active, re-validate with Licensing Service API
        $this->logger->info("Audio2Note: License cache stale or status not active. Re-validating with Licensing Service API.");

        // Decrypt sensitive credentials
        // require_once __DIR__ . '/LicenseEncryptionService.php'; // Rely on autoloader
        $encryptionService = new EncryptionService(); // Corrected

        $this->logger->info("Audio2Note: LicenseStatusChecker: Attempting to decrypt credentials.");
        $this->logger->info("Audio2Note: LicenseStatusChecker: Encrypted License Key from DB: " . ($config['encrypted_license_key'] ?? 'MISSING'));
        $this->logger->info("Audio2Note: LicenseStatusChecker: Encrypted Consumer Key from DB: " . ($config['encrypted_license_consumer_key'] ?? 'MISSING'));
        $this->logger->info("Audio2Note: LicenseStatusChecker: Encrypted Consumer Secret from DB: " . ($config['encrypted_license_consumer_secret'] ?? 'MISSING'));
        $this->logger->info("Audio2Note: LicenseStatusChecker: Effective Instance ID from DB: " . ($config['effective_instance_identifier'] ?? 'MISSING'));
        $this->logger->info("Audio2Note: LicenseStatusChecker: Encrypted Backend URL from DB: " . ($config['encrypted_backend_audio_process_base_url'] ?? 'MISSING'));


        $licenseKey = $encryptionService->decrypt($config['encrypted_license_key'] ?? '');
        $this->logger->info("Audio2Note: LicenseStatusChecker: Decrypted License Key: " . ($licenseKey ? 'OK_HIDDEN' : 'FAIL_EMPTY'));

        $licenseConsumerKey = $encryptionService->decrypt($config['encrypted_license_consumer_key'] ?? '');
        $this->logger->info("Audio2Note: LicenseStatusChecker: Decrypted Consumer Key: " . ($licenseConsumerKey ? 'OK_HIDDEN' : 'FAIL_EMPTY'));

        $licenseConsumerSecret = $encryptionService->decrypt($config['encrypted_license_consumer_secret'] ?? '');
        $this->logger->info("Audio2Note: LicenseStatusChecker: Decrypted Consumer Secret: " . ($licenseConsumerSecret ? 'OK_HIDDEN' : 'FAIL_EMPTY'));

        $effectiveInstanceIdentifier = $config['effective_instance_identifier'] ?? ''; // Not encrypted

        $backendAudioProcessBaseUrl = $encryptionService->decrypt($config['encrypted_backend_audio_process_base_url'] ?? ''); // Load from config and decrypt
        $this->logger->info("Audio2Note: LicenseStatusChecker: Decrypted Backend URL: " . ($backendAudioProcessBaseUrl ? 'OK_HIDDEN' : 'FAIL_EMPTY'));


        if (empty($licenseKey) || empty($licenseConsumerKey) || empty($licenseConsumerSecret) || empty($effectiveInstanceIdentifier) || empty($backendAudioProcessBaseUrl)) {
            $this->logger->error("Audio2Note: LicenseStatusChecker: Missing one or more required license credentials after decryption/retrieval for validation.");
            $this->logger->error("Audio2Note: LicenseStatusChecker: Details - LicenseKeyEmpty: " . (empty($licenseKey) ? 'YES' : 'NO') .
                                 ", ConsumerKeyEmpty: " . (empty($licenseConsumerKey) ? 'YES' : 'NO') .
                                 ", ConsumerSecretEmpty: " . (empty($licenseConsumerSecret) ? 'YES' : 'NO') .
                                 ", EffectiveIdEmpty: " . (empty($effectiveInstanceIdentifier) ? 'YES' : 'NO') .
                                 ", BackendUrlEmpty: " . (empty($backendAudioProcessBaseUrl) ? 'YES' : 'NO'));
            $this->updateLicenseStatus('invalid', null, null); // Mark as invalid due to missing credentials
            return false;
        }

        $this->logger->info("Audio2Note: LicenseStatusChecker: All credentials present for API validation call.");

        try {
            require_once __DIR__ . '/LicenseClient.php';
            $licenseClient = new LicenseClient($backendAudioProcessBaseUrl, $licenseConsumerKey, $licenseConsumerSecret);
            $validationResult = $licenseClient->validateLicense($licenseKey, $effectiveInstanceIdentifier);

            // 4. Process validation result and update DB
            $newStatus = 'inactive'; // Default to inactive on validation failure
            $newExpiresAt = null;
            $message = xlt('License validation failed.');

            if ($validationResult && isset($validationResult['valid']) && $validationResult['valid'] === true) {
                $newStatus = 'active';
                $message = xlt('License validated successfully.');
                if (isset($validationResult['license_data']['expires_at'])) {
                    $newExpiresAt = $validationResult['license_data']['expires_at'];
                }
            } else {
                 if ($validationResult && isset($validationResult['message'])) {
                    $message .= " " . htmlspecialchars($validationResult['message']);
                } elseif ($validationResult && isset($validationResult['error'])) {
                     $message .= " Error: " . htmlspecialchars($validationResult['error']);
                } else {
                     $message .= " " . xlt('Unknown API response or connection error during validation.');
                }
            }

            $this->updateLicenseStatus($newStatus, $newExpiresAt, $config['encrypted_dlm_activation_token'] ?? null); // Keep existing activation token

            if ($newStatus === 'active') {
                $this->logger->info("Audio2Note: License validation successful. Status: active.");
                return true;
            } else {
                $this->logger->warning("Audio2Note: License validation failed. Status: inactive. Message: " . $message);
                return false;
            }

        } catch (\Exception $e) {
            $this->logger->error("Audio2Note: Exception during license validation: " . $e->getMessage());
            $this->updateLicenseStatus('invalid', null, null); // Mark as invalid on exception
            return false;
        }
    }

    private function updateLicenseStatus(string $status, ?string $expiresAt, ?string $encryptedActivationToken): void
    {
        $now = date('Y-m-d H:i:s');
        sqlStatement(
            "UPDATE `audio2note_config` SET
            `license_status` = ?,
            `license_expires_at` = ?,
            `last_validation_timestamp` = ?,
            `encrypted_dlm_activation_token` = ?,
            `updated_at` = ?
            WHERE `id` = (SELECT id FROM (SELECT id FROM `audio2note_config` LIMIT 1) as temp)", // Use subquery to avoid "You can't specify target table 'audio2note_config' for update in FROM clause"
            [
                $status,
                $expiresAt,
                $now,
                $encryptedActivationToken,
                $now
            ]
        );
    }
}