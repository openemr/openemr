<?php

/**
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Telemetry;

use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Services\VersionService;

/**
 * Provides telemetry reporting functionality.
 */
class TelemetryService
{
    protected TelemetryRepository $repository;
    protected VersionService $versionService;

    public function __construct(TelemetryRepository $repository = null, VersionService $versionService = null)
    {
        if (!($versionService instanceof VersionService) || !($repository instanceof TelemetryRepository)) {
            $repository = new TelemetryRepository();
            $versionService = new VersionService();
        }
        $this->repository = $repository;
        $this->versionService = $versionService;
    }

    /**
     * Checks if telemetry is enabled based on the product registration table.
     * I don't know why I didn't use telemetry_enabled in the product_registration table.
     *
     * @return int
     */
    public static function isTelemetryEnabled(): int
    {
        // Check if telemetry is disabled in the product registration table.
        $isEnabled = sqlQuery("SELECT `telemetry_disabled` FROM `product_registration` WHERE `telemetry_disabled` = 0")['telemetry_disabled'] ?? null;
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
     */
    public function reportClickEvent(array $data): false|string
    {
        $eventType = $data['eventType'] ?? '';
        $eventLabel = $data['eventLabel'] ?? '';
        // Sanitize URL by stripping query parameters.
        $eventUrl = preg_replace('/\?.*$/', '', $data['eventUrl'] ?? '');
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
            return json_encode(["success" => true]);
        } else {
            return json_encode(["error" => "Database insertion/update failed"]);
        }
    }

    /**
     * Aggregates usage data and sends it to the remote endpoint.
     */
    public function reportUsageData(): int|bool
    {
        $site_uuid = UniqueInstallationUuid::getUniqueInstallationUuid() ?? '';
        if (empty($site_uuid)) {
            error_log("Site UUID not found.");
            return false;
        }

        // server geo data
        $geo = new GeoTelemetry();
        $serverGeoData = $geo->getServerGeoData();
        if (isset($serverGeo['error'])) {
            error_log("Error fetching server geolocation: " . $serverGeo['error']);
        }

        $endpoint = "https://reg.open-emr.org/api/usage?SiteID=" . urlencode($site_uuid);
        $interval = date("Ym", strtotime("-33 Days"));

        $timeZoneResult = sqlQuery("SELECT `gl_value` as zone FROM `globals` WHERE `gl_value` > '' AND `gl_name` = 'gbl_time_zone' LIMIT 1");
        $time_zone = $timeZoneResult['zone'] ?? $GLOBALS['gbl_time_zone'] ?? '';

        $usageRecords = $this->repository->fetchUsageRecords();

        $settings = [
            'portal_enabled' => $GLOBALS['portal_onsite_two_enable'] ?? false,
        ];

        $localeData = [
            'site_uuid' => $site_uuid,
            'reporting_interval' => $interval,
            'reporting_date' => date("Ymd"),
            'location' => json_encode($serverGeoData),
            'time_zone' => $time_zone,
            'locale' => locale_get_default(),
            'version' => $this->versionService->asString(),
            'environment' => php_uname('s') . ', ' . php_uname('r') . ', ' . phpversion(),
            'distribution' => getenv('OPENEMR_DOCKER_ENV_TAG') ?: '',
            'settings' => json_encode($settings),
        ];

        $payload_data = [
            'usageRecords' => $usageRecords,
            'localeData' => $localeData,
        ];

        $payload = json_encode($payload_data);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen($payload)
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            error_log("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        if (in_array($httpStatus, [200, 201, 204])) {
            $responseData = json_decode($response, true);
            if ($responseData) {
                $this->repository->clearTelemetryData(); // clear telemetry data after successful report
            } else {
                error_log("Error in response: " . json_encode($responseData));
            }
        } else {
            error_log("HTTP error: " . $httpStatus);
        }

        return $httpStatus;
    }

    /**
     * Sets the API event data.
     *
     * @param mixed $event_data The event data to set.
     */
    public function trackApiRequestEvent(mixed $event_data): void
    {
        if (!empty($this->isTelemetryEnabled())) {
            $this->reportClickEvent($event_data);
        }
    }
}
