<?php

/**
 * NetworkUtils utility class for network-related functions.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use Symfony\Component\HttpFoundation\IpUtils;

class NetworkUtils
{
    /**
     * Determines if a URL or hostname points to a loopback address
     *
     * Uses Symfony's IpUtils to check against standard loopback subnets:
     * - 127.0.0.0/8 (IPv4)
     * - ::1/128 (IPv6)
     *
     * @param string $url_or_host URL or hostname to check
     * @return bool True if the address is a loopback address
     */
    public function isLoopbackAddress(string $url_or_host): bool
    {
        // Extract hostname from URL if needed
        if (ValidationUtils::isValidUrl($url_or_host)) {
            $parsed = parse_url($url_or_host);
            $host = $parsed['host'] ?? '';
        } else {
            $host = $url_or_host;
        }

        // Remove brackets from IPv6 addresses
        $host = trim($host, '[]');

        // Check for localhost variations (not IP addresses)
        if (in_array(strtolower($host), ['localhost', 'localhost.localdomain'])) {
            return true;
        }

        // Check against loopback subnets using Symfony's IpUtils
        // IpUtils::checkIp internally validates that $host is a valid IP
        return IpUtils::checkIp($host, ['127.0.0.0/8', '::1/128']);
    }
}
