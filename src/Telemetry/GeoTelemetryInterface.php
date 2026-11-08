<?php

/**
 * Interface for GeoTelemetry implementations
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Telemetry;

interface GeoTelemetryInterface
{
    /**
     * Anonymize IP using SHA-256 hashing
     *
     * @param string $ip
     * @return string
     */
    public function anonymizeIp(string $ip): string;

    /**
     * Get geolocation data from IP using a lightweight external API
     *
     * @param string $ip
     * @return array
     */
    public function getGeoData(string $ip): array;

    /**
     * Get geolocation of the current server (public-facing IP)
     *
     * @return array
     */
    public function getServerGeoData(): array;
}
