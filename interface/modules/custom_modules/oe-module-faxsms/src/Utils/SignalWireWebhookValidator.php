<?php

/**
 * SignalWire Webhook Receiver
 * Handles incoming fax notifications from SignalWire
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Utils;

final class SignalWireWebhookValidator
{
    public static function validateFaxId(?string $value): ?string
    {
        return self::validateString($value, 1, 64);
    }

    public static function validateFaxStatus(?string $value): ?string
    {
        $allowed = ['queued', 'sending', 'sent', 'failed', 'received'];
        return in_array($value, $allowed, true) ? $value : null;
    }

    public static function validatePhoneNumber(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $value);
        return preg_match('/^\+?\d{7,15}$/', $normalized) ? $normalized : null;
    }

    public static function validateInteger(mixed $value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int)$value
            : null;
    }

    public static function validateDirection(?string $value): ?string
    {
        return in_array($value, ['inbound', 'outbound'], true) ? $value : null;
    }

    public static function validateString(?string $value, int $min = 1, int $max = 255): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $len = strlen($value);

        return ($len >= $min && $len <= $max) ? $value : null;
    }

    public static function validateSiteId(?string $value): ?string
    {
        return self::validateString($value, 1, 32);
    }

    public static function isValidSignalWireUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = parse_url($value, PHP_URL_HOST);
        return is_string($host) && str_ends_with($host, '.signalwire.com');
    }
}
