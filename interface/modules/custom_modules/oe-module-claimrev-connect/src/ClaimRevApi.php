<?php

/**
 * ClaimRev API client using PSR-18 HTTP client.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
    public function __construct(
        private ClientInterface $client,
        #[SensitiveParameter] private string $accessToken,
    ) {
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
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
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
        $client = new Client();
        try {
            $response = $client->request('POST', $authority, [
                'form_params' => [
                    'client_id' => $clientId,
                    'scope' => $scope,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new ClaimRevAuthenticationException(
                'Failed to acquire ClaimRev access token: ' . $e->getMessage(),
                0,
                $e
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
     * Search for claims.
     *
     * @return array<string, mixed>
     * @throws ClaimRevApiException on API error
     */
    public function searchClaims(object $claimSearch): array
    {
        return $this->post('/api/ClaimView/v1/SearchClaims', $claimSearch);
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
        /** @var array<string, mixed> */
        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }
}
