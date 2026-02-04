<?php

/**
 * SignalWire Webhook Receiver
 * Handles incoming fax notifications from SignalWire
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Utils;

/**
 * SignalWire webhook input validator helpers
 *
 */
final class SignalWireWebhookValidator
{
    /**
     * @param string $faxId
     * @return string
     */
    public static function validateFaxId(string $faxId): string
    {
        // Remove any characters that aren't alphanumeric, hyphens, or underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $faxId);
        // Limit length to prevent DoS
        return substr($sanitized ?? '', 0, 255);
    }

    /**
     * @param string $status
     * @return string
     */
    public static function validateFaxStatus(string $status): string
    {
        $allowedStatuses = [
            'queued', 'processing', 'sending', 'sent', 'delivered',
            'receiving', 'received', 'failed', 'no-answer', 'busy',
            'canceled', 'unknown'
        ];
        $status = strtolower(trim($status));
        return in_array($status, $allowedStatuses, true) ? $status : 'unknown';
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function validatePhoneNumber(string $phone): string
    {
        // Remove all characters except digits and + for international format
        $sanitized = preg_replace('/[^0-9+]/', '', $phone);
        // Limit length (E.164 max is 15 digits + country code)
        return substr($sanitized ?? '', 0, 20);
    }

    /**
     * @param mixed $value
     * @param int   $min
     * @param int   $max
     * @return int
     */
    public static function validateInteger(mixed $value, int $min, int $max): int
    {
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($intValue === false) {
            return $min;
        }
        return max($min, min($max, $intValue));
    }

    /**
     * @param string $direction
     * @return string
     */
    public static function validateDirection(string $direction): string
    {
        $allowedDirections = ['inbound', 'outbound', 'outbound-api', 'outbound-call'];
        $direction = strtolower(trim($direction));
        return in_array($direction, $allowedDirections, true) ? $direction : 'inbound';
    }

    /**
     * @param string $input
     * @param int    $maxLength
     * @return string
     */
    public static function validateString(string $input, int $maxLength): string
    {
        // Remove control characters but preserve newlines for error messages
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        return substr($sanitized ?? '', 0, $maxLength);
    }

    /**
     * @param string $siteId
     * @return string
     */
    public static function validateSiteId(string $siteId): string
    {
        // Sanitize to prevent path traversal and injection attacks
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $siteId);
        return !empty($sanitized) ? $sanitized : 'default';
    }

    /**
     * @param string $url
     * @return bool
     */
    public static function isValidSignalWireUrl(string $url): bool
    {
        // Parse and validate URL structure
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }

        // Only allow HTTPS protocol to prevent file:// and other protocol attacks
        if ($parsedUrl['scheme'] !== 'https') {
            return false;
        }

        // Whitelist of allowed SignalWire domains to prevent SSRF
        $allowedDomains = [
            'files.signalwire.com',
            'api.signalwire.com'
        ];

        $host = strtolower($parsedUrl['host']);

        // Check if host matches allowed domains exactly or is a subdomain
        foreach ($allowedDomains as $allowedDomain) {
            if ($host === $allowedDomain || str_ends_with($host, '.' . $allowedDomain)) {
                return true;
            }
        }

        return false;
    }
}
