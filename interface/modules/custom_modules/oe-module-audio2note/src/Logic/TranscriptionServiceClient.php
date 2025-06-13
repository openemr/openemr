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

// Use Guzzle or another HTTP client library. Ensure it's available in OpenEMR or add via Composer.
// Example assumes GuzzleHttp\Client is available.
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils; // For file uploads
// Use the module's own EncryptionService, assuming PSR-4 autoloading is configured
// The EncryptionService class is located in src/Logic/EncryptionService.php
use OpenEMR\Modules\OpenemrAudio2Note\Logic\EncryptionService;

class TranscriptionServiceClient
{
    private $audioProcessingServiceBaseUrl;
    private $licenseKey; // This might be a specific API key for the transcription service itself, or the module license key if it gates the audio processing service call
    private $httpClient;
    private $encryptionService;

    public function __construct()
    {
        global $GLOBALS; // Ensure GLOBALS is accessible

        // Initialize EncryptionService. It should handle its own dependencies (like EncryptionKeyManager).
        // This assumes autoloader can find EncryptionService based on the 'use' statement.
        $this->encryptionService = new EncryptionService();

        $siteId = $GLOBALS['site_id'] ?? null; // Keep siteId for logging context, even if not used in query

        if (empty($siteId)) {
            // Attempt to synchronize from session if $GLOBALS['site_id'] is empty.
            if (isset($_SESSION['site_id']) && !empty($_SESSION['site_id'])) {
                $GLOBALS['site_id'] = $_SESSION['site_id'];
                $siteId = $GLOBALS['site_id'];
                // error_log("TranscriptionServiceClient: \$GLOBALS['site_id'] was empty, synchronized from \$_SESSION['site_id']: " . $siteId);
            } else {
                // Fallback to 'default' if session also doesn't have it.
                $GLOBALS['site_id'] = 'default';
                $siteId = $GLOBALS['site_id'];
                // error_log("TranscriptionServiceClient: \$GLOBALS['site_id'] and \$_SESSION['site_id'] were empty, defaulted to 'default'");
            }
        }

        // The Audio Processing Service Base URL is now static.
        $this->audioProcessingServiceBaseUrl = 'https://backend.audio2note.org/webhook';

        $configRow = $this->loadConfigurationFromDb();
        $encryptedLicenseKey = $configRow['encrypted_license_key'] ?? null;

        if ($encryptedLicenseKey) {
            $this->licenseKey = $this->encryptionService->decrypt($encryptedLicenseKey);
            if ($this->licenseKey === false) {
                error_log("TranscriptionServiceClient: CRITICAL - Failed to decrypt license_key. Site ID context: " . $siteId);
                $this->licenseKey = null;
            }
        } else {
            $this->licenseKey = null;
            error_log("TranscriptionServiceClient: CRITICAL - encrypted_license_key not found in config. Site ID context: " . $siteId);
        }

        if (empty($this->audioProcessingServiceBaseUrl) || empty($this->licenseKey)) {
            // This is a critical configuration error.
            error_log("TranscriptionServiceClient: CRITICAL - Audio Processing Service Base URL or License Key is not properly configured. Site ID context: " . $siteId);
        }

        $this->httpClient = new Client(['timeout' => 30.0]);
    }

    private function loadConfigurationFromDb(): array
    {
        // Fetches the single configuration row from the audio2note_config table.
        // encrypted_audio_processing_service_base_url is no longer stored here as it's static.
        $query = "SELECT encrypted_license_key FROM audio2note_config LIMIT 1";
        $row = sqlQuery($query);

        if ($row === false || empty($row)) {
            error_log("TranscriptionServiceClient: CRITICAL - Failed to fetch configuration from audio2note_config or table is empty.");
            return [];
        }
        return $row;
    }

    /**
     * Initiates a transcription job with the audio processing service.
     *
     * @param string $audioFilePath Temporary path to the uploaded audio file.
     * @param string $originalFilename Original name of the audio file.
     * @param string $noteType Type of note (e.g., 'SOAP', 'History and Physical').
     * @param int $patientId Patient ID.
     * @param int $encounterId Encounter ID.
     * @param int $formId ID of the form_audio_to_note record.
     * @param int $userId OpenEMR user ID.
     * @param string $openemrInstanceId The OpenEMR instance ID.
     * @param array $params Optional parameters.
     * @return string|null Returns the job_id if successful, null otherwise.
     * @throws \Exception On API communication errors or invalid responses.
     */
    public function initiateTranscription(string $audioFilePath, string $originalFilename, string $noteType, int $patientId, int $encounterId, int $formId, int $userId, string $openemrInstanceId, array $params = [])
    {
        if (empty($this->audioProcessingServiceBaseUrl) || empty($this->licenseKey)) {
            throw new \Exception("Transcription service client is not configured (missing URL or license key).");
        }
        if (!file_exists($audioFilePath) || !is_readable($audioFilePath)) {
            throw new \Exception("Audio file not found or not readable: " . $audioFilePath);
        }

        $url = rtrim($this->audioProcessingServiceBaseUrl, '/') . '/initiate_transcription';

        $multipartData = [
            [
                'name'     => 'audio_file',
                'contents' => Utils::tryFopen($audioFilePath, 'r'),
                'filename' => $originalFilename
            ],
            ['name' => 'note_type', 'contents' => $noteType],
            ['name' => 'patient_id', 'contents' => (string)$patientId],
            ['name' => 'encounter_id', 'contents' => (string)$encounterId],
            ['name' => 'form_id', 'contents' => (string)$formId],
            ['name' => 'user_id', 'contents' => (string)$userId],
            ['name' => 'openemr_instance_id', 'contents' => (string)$openemrInstanceId]
        ];

        foreach ($params as $key => $value) {
            $multipartData[] = ['name' => $key, 'contents' => (string)$value];
        }
        
        try {
            $response = $this->httpClient->post($url, [
                'multipart' => $multipartData,
                'headers' => [
                    'X-License-Key' => $this->licenseKey
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode >= 200 && $statusCode < 300) {
                $decodedBody = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBody['job_id'])) {
                    // error_log("Transcription job initiated successfully. Job ID: " . $decodedBody['job_id']); // Informative, but can be noisy
                    return $decodedBody['job_id'];
                } else {
                    error_log("TranscriptionServiceClient: Backend service returned non-JSON or missing job_id: " . $body);
                    throw new \Exception("Received invalid response from transcription initiation service.");
                }
            } else {
                error_log("TranscriptionServiceClient: Backend initiation service error: Status " . $statusCode . " - Body: " . $body);
                throw new \Exception("Transcription initiation service returned status code: " . $statusCode);
            }
        } catch (RequestException $e) {
            $errorMsg = "Error calling backendAudioProcess initiation API: " . $e->getMessage();
            if ($e->hasResponse()) {
                $errorMsg .= " | Response body: " . $e->getResponse()->getBody()->getContents();
            }
            error_log($errorMsg);
            throw new \Exception("Failed to communicate with transcription initiation service: " . $e->getMessage());
        } catch (\Throwable $e) {
             error_log("TranscriptionServiceClient: Unexpected error in initiateTranscription: " . $e->getMessage());
            throw new \Exception("An unexpected error occurred while initiating transcription.");
        }
        return null;
    }

    /**
     * Gets the status and results of a transcription job from the audio processing service.
     *
     * @param string $jobId The ID of the transcription job.
     * @return array|null Returns an array with job status and results, or null on error.
     * @throws \Exception On API communication errors or invalid responses.
     */
    public function getTranscriptionStatus(string $jobId): ?array
    {
        if (empty($this->audioProcessingServiceBaseUrl) || empty($this->licenseKey)) {
            throw new \Exception("Transcription service client is not configured (missing URL or license key).");
        }
        if (empty($jobId)) {
            throw new \Exception("Job ID cannot be empty for status check.");
        }

        $url = rtrim($this->audioProcessingServiceBaseUrl, '/') . '/get_transcription_status';

        try {
            $response = $this->httpClient->get($url, [
                'query' => ['job_id' => $jobId],
                'headers' => [
                    'X-License-Key' => $this->licenseKey
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode >= 400) {
                error_log("TranscriptionServiceClient: Backend status service error: Status " . $statusCode . " - Body: " . $body);
                return [
                    'status' => 'error_audio_processing_service_response',
                    'http_status_code' => $statusCode,
                    'error_message' => 'Audio processing service returned an error status code.',
                    'raw_response' => $body
                ];
            } elseif ($statusCode >= 200 && $statusCode < 300) {
                $decodedBody = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBody['status'])) {
                    // error_log("Transcription status received for Job ID " . $jobId . ": " . $decodedBody['status']); // Informative, but can be noisy
                    return $decodedBody;
                } else {
                    error_log("TranscriptionServiceClient: Backend status service returned non-JSON or missing status: " . $body);
                    return [
                        'status' => json_last_error() !== JSON_ERROR_NONE ? 'error_invalid_json' : 'error_missing_status_field',
                        'error_message' => json_last_error() !== JSON_ERROR_NONE ? 'Audio processing service returned non-JSON response.' : 'Audio processing service response missing status field.',
                        'raw_response' => $body
                    ];
                 }
            }
        } catch (RequestException $e) {
            $errorMsg = "Error calling audio processing service status API: " . $e->getMessage();
            if ($e->hasResponse()) {
                $errorMsg .= " | Response body: " . $e->getResponse()->getBody()->getContents();
            }
            error_log($errorMsg);
            return [
                'status' => 'error_request_exception',
                'error_message' => "Failed to communicate with transcription status service: " . $e->getMessage(),
                'exception_type' => get_class($e),
                'has_response' => $e->hasResponse(),
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ];
        } catch (\Throwable $e) {
             error_log("TranscriptionServiceClient: Unexpected error in getTranscriptionStatus: " . $e->getMessage());
            throw new \Exception("An unexpected error occurred while checking transcription status.");
        }
        return null;
    }
}

?>
