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

// It's good practice to use a proper HTTP client library like Guzzle,
// but for simplicity in this example, we might outline with basic cURL or file_get_contents.
// However, for production, a robust HTTP client is recommended.

class LicenseClient
{
    private $apiBaseUrl;
    private $consumerKey;
    private $consumerSecret;
    private $encryptionService; // To decrypt stored consumer key/secret if needed

    public function __construct(string $consumerKey, string $consumerSecret)
    {
        // API Base URL is now static
        $this->apiBaseUrl = 'https://www.audio2note.org';
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * Sends an HTTP GET request using cURL.
     *
     * @param string $url The URL to request.
     * @param string $actionName A descriptive name for the action (e.g., "activate", "validate") for logging.
     * @return array|false The API response as an associative array, or false on failure/error.
     */
    private function _sendRequest(string $url, string $actionName): array|false
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->consumerKey . ":" . $this->consumerSecret);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNum = curl_errno($ch);
        $curlErrorMsg = curl_error($ch);
        curl_close($ch);

        if ($curlErrorNum) {
            error_log("LicenseClient: cURL Error on " . $actionName . " (" . $url . "): [" . $curlErrorNum . "] " . $curlErrorMsg);
            return false;
        }

        $decodedResponse = json_decode($responseBody, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("LicenseClient: JSON Decode Error on " . $actionName . " success (" . $httpCode . ") for URL (" . $url . "). Response: " . $responseBody);
                return false;
            }
            // Successful response, log might be too verbose for every call.
            // error_log("LicenseClient: " . ucfirst($actionName) . " successful (" . $httpCode . ") for URL (" . $url . "). Response: " . $responseBody);
            return $decodedResponse;
        } else {
            error_log("LicenseClient: HTTP Error on " . $actionName . " (" . $httpCode . ") for URL (" . $url . "). Response: " . $responseBody);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse)) {
                return $decodedResponse; // Return API's error structure if available
            }
            return false;
        }
    }

    /**
     * Activates a license key with the Licensing Service.
     *
     * @param string $licenseKey The license key to activate.
     * @param string $effectiveInstanceIdentifier The unique identifier for this OpenEMR instance.
     * @return array|false The API response as an associative array, or false on failure.
     */
    public function activateLicense(string $licenseKey, string $effectiveInstanceIdentifier): array|false
    {
        $endpoint = $this->apiBaseUrl . '/wp-json/dlm/v1/licenses/activate/' . rawurlencode($licenseKey);
        $queryParams = http_build_query([
            'label' => $effectiveInstanceIdentifier,
        ]);
        $url = $endpoint . '?' . $queryParams;

        // error_log("LicenseClient: Attempting to activate license. URL: " . $url); // Potentially verbose
        return $this->_sendRequest($url, 'activate');
    }

    /**
     * Validates an activation token with the Licensing Service.
     *
     * @param string $activationToken The activation token to validate.
     * @return array|false The API response as an associative array, or false on failure.
     */
    public function validateLicense(string $activationToken): array|false
    {
        $endpoint = $this->apiBaseUrl . '/wp-json/dlm/v1/licenses/validate/' . rawurlencode($activationToken);
        // error_log("LicenseClient: Attempting to validate license. URL: " . $endpoint); // Potentially verbose
        return $this->_sendRequest($endpoint, 'validate');
    }

    /**
     * Deactivates an activation token with the Licensing Service.
     *
     * @param string $activationToken The activation token to deactivate.
     * @return array|false The API response as an associative array, or false on failure.
     */
    public function deactivateLicense(string $activationToken): array|false
    {
        $endpoint = $this->apiBaseUrl . '/wp-json/dlm/v1/licenses/deactivate/' . rawurlencode($activationToken);
        // error_log("LicenseClient: Attempting to deactivate license. URL: " . $endpoint); // Potentially verbose
        return $this->_sendRequest($endpoint, 'deactivate');
    }
}