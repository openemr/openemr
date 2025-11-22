<?php

/**
 * TelemetryServiceInterface - Interface for telemetry reporting services
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Telemetry;

interface TelemetryServiceInterface
{
    /**
     * Checks if telemetry is enabled based on the product registration table
     *
     * @return int 1 if enabled, 0 if disabled
     */
    public function isTelemetryEnabled(): int;

    /**
     * Reports a click event after validating the required input
     *
     * @param array $data Event data containing eventType, eventLabel, eventUrl, eventTarget
     * @param bool $normalizeUrl Whether to normalize the URL (default: false)
     * @return false|string JSON response indicating success or error
     */
    public function reportClickEvent(array $data, bool $normalizeUrl = false): false|string;

    /**
     * Aggregates usage data and sends it to the remote endpoint
     *
     * @return int|bool HTTP status code on success, false on failure
     */
    public function reportUsageData(): int|bool;

    /**
     * Tracks API request events for telemetry purposes
     *
     * @param array $event_data The event data to track
     */
    public function trackApiRequestEvent(array $event_data): void;
}
