<?php

/**
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @author         Michael A. Smith <michael@opencoreemr.com>
 * @copyright      Copyright (c) 2025 - 2026 <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Telemetry;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\DatabaseQueryTrait;
use OpenEMR\Common\Utils\Json;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\VersionService;
use OpenEMR\Services\VersionServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides telemetry reporting functionality.
 *
 * @package   OpenEMR\Telemetry
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 - 2026 <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class TelemetryService
{
    use DatabaseQueryTrait;

    protected LoggerInterface $logger;

    private readonly ClientInterface $httpClient;

    private readonly OEGlobalsBag $globals;

    public function __construct(
        protected TelemetryRepository $repository = new TelemetryRepository(),
        protected VersionServiceInterface $versionService = new VersionService(),
        ?LoggerInterface $logger = null,
        ?ClientInterface $httpClient = null,
        ?OEGlobalsBag $globals = null,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
        $this->httpClient = $httpClient ?? ServiceContainer::getGuzzle();
        $this->globals = $globals ?? OEGlobalsBag::getInstance();
    }

    /**
     * Checks if telemetry is enabled based on the product registration table.
     * I don't know why I didn't use telemetry_enabled in the product_registration table.
     * Uses DatabaseQueryTrait for better testability.
     *
     * @return int
     */
    public function isTelemetryEnabled(): int
    {
        // Check if telemetry is disabled in the product registration table.
        $result = $this->fetchRecords("SELECT `telemetry_disabled` FROM `product_registration` WHERE `telemetry_disabled` = 0", []);
        $isEnabled = !empty($result) ? $result[0]['telemetry_disabled'] ?? null : null;
        if (!is_null($isEnabled)) {
            // If telemetry_disabled is 0, it means telemetry is enabled.
            $isEnabled = 1;
        } else {
            // If telemetry_disabled is not 0, it means telemetry is disabled.
            $isEnabled = 0;
        }
        return $isEnabled;
    }

    /**
     * Reports a click event after validating the required input.
     * $event = [
     *    'eventType' => $eventType,
     *    'eventLabel' => $eventLabel,
     *    'eventUrl' => $eventUrl,
     *    'eventTarget' => $eventTarget,
     * ]
     *
     * @param array<string, mixed> $data
     */
    public function reportClickEvent(array $data, bool $normalizeUrl = false): false|string
    {
        $eventType = $data['eventType'] ?? '';
        $eventLabel = $data['eventLabel'] ?? '';
        // Sanitize URL by stripping query parameters.
        $rawUrl = $data['eventUrl'] ?? '';
        $eventUrl = is_string($rawUrl) ? explode('?', $rawUrl, 2)[0] : '';
        // Normalize URL, if $normalizeUrl is true
        if ($normalizeUrl) {
            $eventUrl = $this->normalizeUrl($eventUrl);
        }
        $eventTarget = $data['eventTarget'] ?? '';
        $currentTime = date("Y-m-d H:i:s");

        if (empty($eventType) || empty($eventLabel)) {
            return json_encode(["error" => "Missing required fields"]);
        }

        $success = $this->repository->saveTelemetryEvent(
            [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ],
            $currentTime
        );

        if ($success) {
            $this->logger->debug("Telemetry Event has been saved", [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ]);
            return json_encode(["success" => true]);
        } else {
            $this->logger->error("Telemetry Event failed to save", [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ]);
            return json_encode(["error" => "Database insertion/update failed"]);
        }
    }

    /**
     * Aggregates usage data and sends it to the remote endpoint.
     */
    public function reportUsageData(): int|bool
    {
        if ($this->isTelemetryEnabled() === 0) {
            $this->logger->debug("Telemetry is not enabled, so do not send a usage report.");
            return false;
        }

        $site_uuid = $this->getUniqueInstallationUuid();
        if ($site_uuid === '') {
            $this->logger->warning("Site UUID not found.");
        }

        // server geo data - don't let geo lookup failures prevent telemetry reporting
        $serverGeoData = [];
        try {
            $geo = $this->createGeoTelemetry();
            $serverGeoData = $geo->getServerGeoData();
            if (isset($serverGeoData['error'])) {
                $this->logger->warning("Telemetry: Unable to fetch server geolocation - {error}. Continuing with telemetry report.", ['error' => $serverGeoData['error']]);
                // Use null values for geo data if lookup fails
                $serverGeoData = [
                    'country' => null,
                    'region' => null,
                    'city' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'error' => $serverGeoData['error']
                ];
            }
        } catch (\Throwable $e) {
            $this->logger->warning("Telemetry: Exception during geolocation lookup - {message}. Continuing with telemetry report.", ['message' => $e->getMessage(), 'exception' => $e]);
            $serverGeoData = [
                'country' => null,
                'region' => null,
                'city' => null,
                'latitude' => null,
                'longitude' => null,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }

        $endpoint = "https://reg.open-emr.org/api/usage?SiteID=" . urlencode($site_uuid);
        $interval = date("Ym", strtotime("-33 Days"));

        $timeZoneResult = $this->querySingleRow("SELECT `gl_value` as zone FROM `globals` WHERE `gl_value` > '' AND `gl_name` = 'gbl_time_zone' LIMIT 1", []);
        $savedZone = is_array($timeZoneResult) ? $timeZoneResult['zone'] ?? null : null;
        $time_zone = is_string($savedZone) ? $savedZone : $this->globals->getString('gbl_time_zone');

        $usageRecords = $this->repository->fetchUsageRecords();
        $populationData = $this->repository->fetchSitePopulationData();
        $moduleCounts = $this->repository->fetchActiveModuleCounts();
        $encEnabledForms = $this->repository->fetchEnabledEncounterForms();

        $settings = [
            'portal_enabled' => $this->globals->getBoolean('portal_onsite_two_enable'),
        ];

        $localeData = [
            'site_uuid' => $site_uuid,
            'reporting_interval' => $interval,
            'reporting_date' => date("Ymd"),
            'location' => json_encode($serverGeoData),
            'time_zone' => $time_zone,
            'locale' => locale_get_default(),
            'version' => (string) $this->versionService->getSoftwareVersion(),
            'environment' => php_uname('s') . ', ' . php_uname('r') . ', ' . phpversion(),
            'distribution' => getenv('OPENEMR_DOCKER_ENV_TAG') ?: '',
            'settings' => json_encode($settings),
            'populationData' => json_encode($populationData),
            'moduleCounts' => json_encode($moduleCounts),
            'enabledEncounterForms' => json_encode($encEnabledForms),
        ];

        $payload_data = [
            'usageRecords' => $usageRecords,
            'localeData' => $localeData,
        ];

        try {
            $payload = Json::encode($payload_data);
        } catch (\JsonException $e) {
            // JSON cannot carry bytes that are not UTF-8, so report the usage data like a failed request.
            $this->logger->error('Telemetry usage report could not be encoded', ['exception' => $e]);
            return 0;
        }

        try {
            $reply = $this->httpClient->request('POST', $endpoint, [
                RequestOptions::BODY => $payload,
                RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::VERIFY => $this->globals->getBoolean('http_verify_ssl', true),
            ]);
        } catch (GuzzleException $e) {
            // No response, as when cURL failed to connect: report status 0.
            $this->logger->error('Telemetry usage report request failed', ['exception' => $e]);
            return 0;
        }
        $httpStatus = $reply->getStatusCode();
        $response = (string) $reply->getBody();

        if (in_array($httpStatus, [200, 201, 204])) {
            try {
                $responseData = Json::decode($response);
            } catch (\JsonException) {
                // An unreadable reply counts as an error response, like an empty one.
                $responseData = null;
            }
            if ($responseData) {
                $this->repository->clearTelemetryData(); // clear telemetry data after successful report //TODO: REMOVE comment RELEASE
            } else {
                $this->logger->error('Error in response: {response}', ['response' => $responseData]);
            }
        } else {
            $this->logger->error("HTTP error: {httpStatus}", ['httpStatus' => $httpStatus]);
        }

        $this->logger->debug('Telemetry sent: {httpStatus}: {geoData}', ['httpStatus' => $httpStatus, 'geoData' => $serverGeoData]);
        return $httpStatus;
    }

    /**
     * Sets the API event data.
     *
     * @param array<string, mixed> $event_data
     */
    public function trackApiRequestEvent(array $event_data): void
    {
        if (!empty($this->isTelemetryEnabled())) {
            $this->reportClickEvent($event_data);
        }
    }

    protected function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        $webRoot = $this->globals->getWebRoot();
        $normalized = ($webRoot !== '' && str_starts_with($path, $webRoot)) ? substr($path, strlen($webRoot)) : $path;
        return ($normalized . $fragment);
    }

    /**
     * A stubbable wrapper around a static method.
     *
     * @codeCoverageIgnore
     */
    protected function getUniqueInstallationUuid(): string
    {
        return UniqueInstallationUuid::getUniqueInstallationUuid();
    }

    /**
     * A stubbable wrapper around GeoTelemetry instantiation.
     *
     * @codeCoverageIgnore
     *
     * @return GeoTelemetryInterface
     */
    protected function createGeoTelemetry(): GeoTelemetryInterface
    {
        return new GeoTelemetry();
    }
}
