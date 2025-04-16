<?php

/**
 *
 * @package    OpenEMR
 * @link           https://www.open-emr.org
 * @author      Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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

    public function __construct(TelemetryRepository $repository, VersionService $versionService)
    {
        $this->repository = $repository;
        $this->versionService = $versionService;
    }

    /**
     * Reports a click event after validating the required input.
     */
    public function reportClickEvent(array $data): void
    {
        $eventType = $data['eventType'] ?? '';
        $eventLabel = $data['eventLabel'] ?? '';
        // Sanitize URL by stripping query parameters.
        $eventUrl = preg_replace('/\?.*$/', '', $data['eventUrl'] ?? '');
        $eventTarget = $data['eventTarget'] ?? '';
        $currentTime = date("Y-m-d H:i:s");

        if (empty($eventType) || empty($eventLabel)) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }

        $success = $this->repository->insertOrUpdateClickEvent(
            [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ],
            $currentTime
        );

        if ($success) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database insertion/update failed"]);
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
            'location' => '',
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
}
