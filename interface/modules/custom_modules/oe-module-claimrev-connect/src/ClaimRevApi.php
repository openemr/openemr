<?php

/**
 * ClaimRev API client using PSR-18 HTTP client.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

/**
 * API client for interacting with the ClaimRev clearinghouse service.
 */
readonly class ClaimRevApi
{
    /**
     * Max seconds to wait on a TCP/TLS connect. Cloud Run cold starts can
     * take ~60s before the first byte comes back, so give it room.
     */
    private const HTTP_CONNECT_TIMEOUT = 30;

    /** Max seconds to wait for a full response after the connection is up. */
    private const HTTP_TIMEOUT = 60;

    /** OAuth token POST attempts (initial + retries). */
    private const TOKEN_MAX_ATTEMPTS = 3;

    public function __construct(
        private ClientInterface $client,
        #[SensitiveParameter] private string $accessToken,
    ) {
    }

    /**
     * Build version-tracking headers for API calls.
     *
     * @return array<string, string>
     */
    private static function getVersionHeaders(): array
    {
        // Use include (not include_once) so local vars are always set,
        // even if version.php was already loaded elsewhere in the request.
        $fileroot = OEGlobalsBag::getInstance()->getString('fileroot');
        @include($fileroot . "/version.php");
        $oemrVersion = TypeCoerce::asString($v_major ?? '?') . '.'
            . TypeCoerce::asString($v_minor ?? '?') . '.'
            . TypeCoerce::asString($v_patch ?? '?')
            . TypeCoerce::asString($v_tag ?? '');

        return [
            'X-Module-Version' => Bootstrap::MODULE_VERSION,
            'X-OpenEMR-Version' => $oemrVersion,
            'X-Client-Platform' => 'openemr',
        ];
    }

    /**
     * Create a ClaimRevApi instance using global configuration.
     *
     * Acquires an OAuth access token and returns a configured client.
     *
     * @throws ClaimRevAuthenticationException if token acquisition fails
     * @throws ModuleNotConfiguredException if required settings are missing
     */
    public static function makeFromGlobals(): self
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $globalsConfig = $bootstrap->getGlobalConfig();

        $authority = $globalsConfig->getClientAuthority();
        $clientId = $globalsConfig->getClientId();
        $scope = $globalsConfig->getClientScope();
        $clientSecret = $globalsConfig->getClientSecret();
        $apiServer = $globalsConfig->getApiServer();

        if (!is_string($clientId) || $clientId === '') {
            throw new ModuleNotConfiguredException('ClaimRev client ID is not configured');
        }
        if (!is_string($clientSecret) || $clientSecret === '') {
            throw new ClaimRevAuthenticationException('ClaimRev client secret could not be decrypted');
        }

        $token = self::acquireAccessToken($authority, $clientId, $scope, $clientSecret);

        $client = new Client([
            'base_uri' => $apiServer,
            'headers' => array_merge([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ], self::getVersionHeaders()),
            'connect_timeout' => self::HTTP_CONNECT_TIMEOUT,
            'timeout' => self::HTTP_TIMEOUT,
        ]);

        return new self($client, $token);
    }

    /**
     * Acquire an OAuth access token from the ClaimRev authority.
     *
     * @throws ClaimRevAuthenticationException if token acquisition fails
     */
    private static function acquireAccessToken(
        string $authority,
        string $clientId,
        string $scope,
        #[SensitiveParameter] string $clientSecret,
    ): string {
        $client = new Client([
            'connect_timeout' => self::HTTP_CONNECT_TIMEOUT,
            'timeout' => self::HTTP_TIMEOUT,
        ]);
        $params = [
            'form_params' => [
                'client_id' => $clientId,
                'scope' => $scope,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
            ],
        ];

        // The B2C token endpoint occasionally returns transient TLS resets or
        // 5xx responses, especially when the client side is on a Cloud Run
        // instance just waking up. Retry a couple of times with brief backoff
        // so the user-facing "Check Now" doesn't bubble those up as errors.
        $lastException = null;
        for ($attempt = 1; $attempt <= self::TOKEN_MAX_ATTEMPTS; $attempt++) {
            try {
                $response = $client->request('POST', $authority, $params);
                break;
            } catch (GuzzleException $e) {
                $lastException = $e;
                if ($attempt === self::TOKEN_MAX_ATTEMPTS) {
                    throw new ClaimRevAuthenticationException(
                        'Failed to acquire ClaimRev access token after ' . self::TOKEN_MAX_ATTEMPTS . ' attempts: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
                // Backoff: 200ms, 400ms.
                usleep(200_000 * $attempt);
            }
        }

        if (!isset($response)) {
            // Defensive — the loop above either sets $response or throws.
            throw new ClaimRevAuthenticationException(
                'Failed to acquire ClaimRev access token',
                0,
                $lastException,
            );
        }

        $data = self::parseResponse($response);
        if (!isset($data['access_token']) || !is_string($data['access_token'])) {
            throw new ClaimRevAuthenticationException(
                'ClaimRev token response missing access_token'
            );
        }

        return $data['access_token'];
    }

    /**
     * Test connectivity by checking if authentication succeeds.
     */
    public function canConnect(): bool
    {
        return $this->accessToken !== '';
    }

    /**
     * Get the default account information.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function getDefaultAccount(): array
    {
        return $this->get('/api/UserProfile/v1/GetDefaultAccount');
    }

    /**
     * Upload an EDI claim file to ClaimRev.
     *
     * @throws ClaimRevApiException on API error
     */
    public function uploadClaimFile(string $ediContents, string $fileName): void
    {
        $model = new UploadEdiFileContentModel('', $ediContents, $fileName);
        $response = $this->post('/api/InputFile/v1', $model);

        if (isset($response['isError']) && $response['isError']) {
            throw new ClaimRevApiException(
                'ClaimRev reported an error uploading file',
                200,
                json_encode($response, JSON_THROW_ON_ERROR),
                '/api/InputFile/v1'
            );
        }
    }

    /**
     * Get report files of a specific type.
     *
     * @return list<array<string, mixed>>
     * @throws ClaimRevApiException on API error
     */
    public function getReportFiles(string $reportType): array
    {
        $result = $this->get('/api/EdiResponseFile/v1/GetReport', ['ediType' => $reportType]);
        /** @var list<array<string, mixed>> */
        return array_values($result);
    }

    /**
     * Anonymous call (no auth token required) to get ClaimRev contact info.
     *
     * @return array<string, mixed>|false Returns false on failure
     */
    public static function getSupportInfo(): array|false
    {
        try {
            $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
            $globalsConfig = $bootstrap->getGlobalConfig();
            $apiServer = $globalsConfig->getApiServer();
        } catch (\RuntimeException | \LogicException) {
            return false;
        }

        $client = new Client([
            'headers' => array_merge([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ], self::getVersionHeaders()),
            'timeout' => 5,
        ]);

        try {
            $response = $client->request('GET', $apiServer . '/api/SupportInfo/v1/GetSupportInfo');
            if ($response->getStatusCode() !== 200) {
                return false;
            }
            return self::parseResponse($response);
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * Search for payment advice / ERA claim-level payment info (paginated).
     *
     * @return array<string, mixed> Contains 'results' and 'totalRecords'
     * @throws ClaimRevApiException on API error
     */
    public function searchPaymentInfo(object $search): array
    {
        return $this->post('/api/PaymentAdvice/v1/SearchPaymentInfo', $search);
    }

    /**
     * Toggle the isWorked flag on a payment advice in ClaimRev.
     *
     * The API toggles the current value, so only call this when you want to flip it.
     *
     * @param array<string, mixed> $paymentAdvice The full ClaimPaymentAggregation object
     * @return bool True if the toggle succeeded
     * @throws ClaimRevApiException on API error
     */
    public function markPaymentAdviceWorked(array $paymentAdvice): bool
    {
        $this->post('/api/PaymentAdvice/v1/UpdateClaimPaymentAdviceIsWorked', (object) $paymentAdvice);
        return true;
    }

    /**
     * Search for claims (paginated).
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function searchClaims(object $claimSearch): array
    {
        return $this->post('/api/ClaimView/v1/SearchClaimsPaged', $claimSearch);
    }

    /**
     * Export claims search results as CSV.
     *
     * @return array<string, mixed> Contains 'fileText' and 'fileName'
     * @throws ClaimRevApiException on API error
     */
    public function searchClaimsCsv(object $claimSearch): array
    {
        return $this->post('/api/ClaimView/v1/SearchClaimsCsv', $claimSearch);
    }

    /**
     * Get errors for a specific claim.
     *
     * @return list<array<string, mixed>>
     * @throws ClaimRevApiException on API error
     */
    public function getClaimErrors(string $claimId): array
    {
        return self::asListOfRecords($this->get('/api/ClaimView/v1/GetClaimErrors', ['claimId' => $claimId]));
    }

    /**
     * Get available claim statuses.
     *
     * @return list<array<string, mixed>>
     * @throws ClaimRevApiException on API error
     */
    public function getClaimStatuses(): array
    {
        return self::asListOfRecords($this->get('/api/ClaimView/v1/GetClaimStatuses'));
    }

    /**
     * Get portal notifications.
     *
     * @return list<array<string, mixed>>
     * @throws ClaimRevApiException on API error
     */
    public function getPortalNotifications(bool $isReadFilter = false): array
    {
        return self::asListOfRecords($this->get('/api/NotificationMgmt/v1/GetPortalNotifications', [
            'isReadFilter' => $isReadFilter ? 'true' : 'false',
        ]));
    }

    /**
     * Coerce a response body that should be a JSON array of objects into a
     * list of array<string, mixed>. The internal `get()` helper returns the
     * full decoded body typed as array<string, mixed>; this drops any
     * non-array entries so the caller's list type holds.
     *
     * @param array<int|string, mixed> $body
     * @return list<array<string, mixed>>
     */
    private static function asListOfRecords(array $body): array
    {
        $out = [];
        foreach ($body as $entry) {
            if (is_array($entry)) {
                /** @var array<string, mixed> $entry */
                $out[] = $entry;
            }
        }
        return $out;
    }

    /**
     * Set notification read status on ClaimRev.
     *
     * @throws ClaimRevApiException on API error
     */
    public function setNotificationReadStatus(int|string $portalNotificationId, bool $isRead = true): bool
    {
        $payload = (object) [
            'portalNotificationId' => $portalNotificationId,
            'isRead' => $isRead,
        ];
        $this->post('/api/NotificationMgmt/v1/SetNotificationReadStatus', $payload);
        return true;
    }

    /**
     * Mark a claim as worked or unworked.
     *
     * @throws ClaimRevApiException on API error
     */
    public function markClaimAsWorked(string $objectId, bool $isWorked): bool
    {
        $payload = (object) [
            'objectId' => $objectId,
            'statusId' => $isWorked ? 1 : 0,
        ];
        $this->post('/api/ClaimManager/v1/MarkClaimAsWorked', $payload);
        return true;
    }

    /**
     * Search for downloadable files (ERA/835 files).
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function searchDownloadableFiles(object $downloadSearch): array
    {
        return $this->post('/FileManagement/SearchOutboundClientFiles', $downloadSearch);
    }

    /**
     * Get a file for download by object ID.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function getFileForDownload(string $objectId): array
    {
        return $this->get('/FileManagement/GetFileForDownload', ['id' => $objectId]);
    }

    /**
     * Get eligibility result by originating system ID.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function getEligibilityResult(string $originatingSystemId): array
    {
        return $this->get('/api/Eligibility/v1/GetEligibilityRequest', [
            'originatingSystemId' => $originatingSystemId,
        ]);
    }

    /**
     * Get a SharpRevenue visit result by claimRevResultId.
     *
     * Used to poll for async results (e.g. coverage discovery).
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function getSharpRevenueVisit(string $claimRevResultId): array
    {
        return $this->get('/api/SharpRevenue/v1/GetEligibilityVisit', [
            'sharpRevenueRtEligibilityObjectId' => $claimRevResultId,
        ]);
    }

    /**
     * Upload an eligibility request.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function uploadEligibility(object $eligibility): array
    {
        return $this->post('/api/SharpRevenue/v1/RunSharpRevenue', $eligibility);
    }

    /**
     * Ask an AI question about an eligibility response.
     *
     * The API streams back a JSON array of chunks. This method collects the
     * full response and concatenates the text parts into a single string.
     *
     * @return string The AI-generated answer text
     * @throws ClaimRevApiException on API error
     */
    public function askEligibilityQuestion(string $sharpRevenueObjectId, string $question, ?string $payerCode = null): string
    {
        $payload = (object) [
            'sharpRevenueObjectId' => $sharpRevenueObjectId,
            'question' => $question,
            'payerCode' => $payerCode,
            'model' => null,
        ];

        try {
            $response = $this->client->request('POST', '/api/SharpRevenue/v1/AskEligibilityQuestion', [
                'json' => $payload,
                'headers' => array_merge($this->getAuthHeaders(), [
                    'Accept' => 'text/event-stream',
                ]),
                'timeout' => 120,
            ]);
        } catch (GuzzleException $e) {
            throw new ClaimRevApiException(
                'ClaimRev API request failed: ' . $e->getMessage(),
                0,
                '',
                '/api/SharpRevenue/v1/AskEligibilityQuestion',
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new ClaimRevApiException(
                "ClaimRev API returned HTTP {$statusCode}",
                $statusCode,
                (string) $response->getBody(),
                '/api/SharpRevenue/v1/AskEligibilityQuestion'
            );
        }

        $body = (string) $response->getBody();
        $chunks = json_decode($body, true);
        if (!is_array($chunks)) {
            return $body;
        }

        // Concatenate text from all chunks: candidates[0].content.parts[0].text
        $text = '';
        foreach ($chunks as $chunk) {
            if (!is_array($chunk)) {
                continue;
            }
            $candidates = $chunk['candidates'] ?? [];
            if (!is_array($candidates)) {
                continue;
            }
            foreach ($candidates as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                $content = $candidate['content'] ?? null;
                $parts = is_array($content) ? ($content['parts'] ?? []) : [];
                if (!is_array($parts)) {
                    continue;
                }
                foreach ($parts as $part) {
                    if (is_array($part)) {
                        $text .= TypeCoerce::asString($part['text'] ?? '');
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Perform a GET request.
     *
     * @param array<string, string> $query
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    private function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client->request('GET', $path, [
                'query' => $query,
                'headers' => $this->getAuthHeaders(),
            ]);
        } catch (GuzzleException $e) {
            throw new ClaimRevApiException(
                'ClaimRev API request failed: ' . $e->getMessage(),
                0,
                '',
                $path,
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new ClaimRevApiException(
                "ClaimRev API returned HTTP {$statusCode}",
                $statusCode,
                (string) $response->getBody(),
                $path
            );
        }

        return self::parseResponse($response);
    }

    /**
     * Perform a POST request.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    private function post(string $path, object $payload): array
    {
        try {
            $response = $this->client->request('POST', $path, [
                'json' => $payload,
                'headers' => $this->getAuthHeaders(),
            ]);
        } catch (GuzzleException $e) {
            throw new ClaimRevApiException(
                'ClaimRev API request failed: ' . $e->getMessage(),
                0,
                '',
                $path,
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new ClaimRevApiException(
                "ClaimRev API returned HTTP {$statusCode}",
                $statusCode,
                (string) $response->getBody(),
                $path
            );
        }

        return self::parseResponse($response);
    }

    /**
     * Get authorization headers for API requests.
     *
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    /**
     * Parse a JSON response.
     *
     * @return array<string, mixed>
     */
    private static function parseResponse(ResponseInterface $response): array
    {
        $json = (string) $response->getBody();
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        // API may return a scalar (e.g. a plain string); wrap it so callers
        // always receive an array.
        if (!is_array($decoded)) {
            return ['value' => $decoded];
        }
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
