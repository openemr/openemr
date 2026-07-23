<?php

/**
 * SignalWire Webhook Receiver
 * Handles incoming fax notifications from SignalWire
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Utils;

use finfo;

/**
 * SignalWire webhook input, URL, MIME, and signature validation helpers.
 */
final class SignalWireWebhookValidator
{
    /**
     * Return a safe scalar string from webhook/API values that may be arrays.
     *
     * @param mixed $value
     * @param string $default
     * @return string
     */
    public static function scalarString(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value) || is_bool($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            foreach ($value as $candidate) {
                $candidate = self::scalarString($candidate, '');
                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        return $default;
    }

    /**
     * Return true when a mixed value is an associative array.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isAssocArray(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Normalize an HTTP Content-Type header to a single MIME string.
     *
     * @param mixed $contentTypeHeader
     * @param string $mediaContent
     * @return string
     */
    public static function normalizeMimeType(mixed $contentTypeHeader, string $mediaContent): string
    {
        $contentType = self::scalarString($contentTypeHeader, '');

        if ($contentType !== '') {
            $contentType = strtolower(trim(explode(';', $contentType, 2)[0]));
        }

        if ($contentType === '' || $contentType === 'application/octet-stream') {
            $detected = (new finfo(FILEINFO_MIME_TYPE))->buffer($mediaContent);
            if (is_string($detected) && $detected !== '') {
                $contentType = $detected;
            }
        }

        return $contentType !== '' ? $contentType : 'application/pdf';
    }

    /**
     * @param string $faxId
     * @return string
     */
    public static function validateFaxId(string $faxId): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $faxId);
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
            'canceled', 'cancelled', 'unknown'
        ];
        $status = strtolower(trim($status));

        if ($status === 'success' || $status === 'completed') {
            return 'received';
        }

        return in_array($status, $allowedStatuses, true) ? $status : 'unknown';
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function validatePhoneNumber(string $phone): string
    {
        $sanitized = preg_replace('/[^0-9+]/', '', $phone);
        return substr($sanitized ?? '', 0, 20);
    }

    /**
     * @param mixed $value
     * @param int $min
     * @param int $max
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
     * @param int $maxLength
     * @return string
     */
    public static function validateString(string $input, int $maxLength): string
    {
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        return substr($sanitized ?? '', 0, $maxLength);
    }

    /**
     * @param string $siteId
     * @return string
     */
    public static function validateSiteId(string $siteId): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $siteId);
        return ($sanitized !== null && $sanitized !== '') ? $sanitized : 'default';
    }

    /**
     * @param string $url
     * @return bool
     */
    public static function isValidSignalWireUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !isset($parsedUrl['scheme'], $parsedUrl['host'])) {
            return false;
        }

        if ($parsedUrl['scheme'] !== 'https') {
            return false;
        }

        $allowedDomains = [
            'files.signalwire.com',
            'api.signalwire.com',
        ];
        $host = strtolower($parsedUrl['host']);

        foreach ($allowedDomains as $allowedDomain) {
            if ($host === $allowedDomain || str_ends_with($host, '.' . $allowedDomain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Read the SignalWire/Twilio-compatible signature header from server vars.
     *
     * @param array<string, mixed> $server
     * @return string
     */
    public static function getSignatureHeader(array $server): string
    {
        return self::scalarString(
            $server['HTTP_X_SIGNALWIRE_SIGNATURE']
                ?? $server['HTTP_X_TWILIO_SIGNATURE']
                ?? $server['X_SIGNALWIRE_SIGNATURE']
                ?? $server['X_TWILIO_SIGNATURE']
                ?? ''
        );
    }

    /**
     * Build the public request URL used for signature validation.
     *
     * @param array<string, mixed> $server
     * @return string
     */
    public static function buildRequestUrl(array $server): string
    {
        $https = self::scalarString($server['HTTPS'] ?? '');
        $scheme = ($https !== '' && strtolower($https) !== 'off') ? 'https' : 'http';
        $host = self::scalarString($server['HTTP_HOST'] ?? '');
        $requestUri = self::scalarString($server['REQUEST_URI'] ?? '');

        return $scheme . '://' . $host . $requestUri;
    }

    /**
     * Validate the SignalWire request signature.
     *
     * Supports current raw-body validation and the Compatibility/Twilio-style
     * URL + sorted POST params validation. SignalWire documents raw-body
     * validation for newer SDKs and pre-parsed form validation for cXML/
     * Compatibility webhooks, so we intentionally accept either matching form.
     *
     * @param string $signingKey
     * @param string $signatureHeader
     * @param string $requestUrl
     * @param string $rawBody
     * @param array<string, mixed> $postParams
     * @return bool
     */
    public static function validateSignature(
        string $signingKey,
        string $signatureHeader,
        string $requestUrl,
        string $rawBody,
        array $postParams = []
    ): bool {
        $signingKey = trim($signingKey);
        $signatureHeader = trim($signatureHeader);

        if ($signingKey === '' || $signatureHeader === '' || $requestUrl === '') {
            return false;
        }

        $expectedSignatures = [
            // SignalWire SDK raw-body style for JSON/SWML webhooks.
            base64_encode(hash_hmac('sha1', $requestUrl . $rawBody, $signingKey, true)),
            // Defensive compatibility for providers/SDKs that sign only body bytes.
            base64_encode(hash_hmac('sha1', $rawBody, $signingKey, true)),
            base64_encode(hash_hmac('sha256', $rawBody, $signingKey, true)),
        ];

        if ($postParams !== []) {
            $expectedSignatures[] = self::buildCompatibilitySignature($signingKey, $requestUrl, $postParams);
        }

        foreach (array_unique($expectedSignatures) as $expectedSignature) {
            if (hash_equals($expectedSignature, $signatureHeader)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build Compatibility/Twilio-style signature from URL + sorted POST params.
     *
     * @param string $signingKey
     * @param string $requestUrl
     * @param array<string, mixed> $postParams
     * @return string
     */
    private static function buildCompatibilitySignature(string $signingKey, string $requestUrl, array $postParams): string
    {
        ksort($postParams);
        $signedData = $requestUrl;

        foreach ($postParams as $key => $value) {
            $signedData .= (string)$key . self::signatureValue($value);
        }

        return base64_encode(hash_hmac('sha1', $signedData, $signingKey, true));
    }

    /**
     * Flatten webhook param values for signature validation.
     *
     * @param mixed $value
     * @return string
     */
    private static function signatureValue(mixed $value): string
    {
        if (is_array($value)) {
            $values = [];
            foreach ($value as $candidate) {
                $values[] = self::signatureValue($candidate);
            }
            return implode('', $values);
        }

        return self::scalarString($value, '');
    }
}
