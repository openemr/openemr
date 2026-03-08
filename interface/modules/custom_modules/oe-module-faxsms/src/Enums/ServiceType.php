<?php

/**
 * Service Type Enum
 * Maps service type IDs to their display names and identifiers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Enums;

enum ServiceType: int
{
    case DISABLED = 0;
    case RINGCENTRAL = 1;
    case TWILIO_SMS = 2;
    case ETHERFAX = 3;
    case EMAIL = 4;
    case CLICKATELL_SMS = 5;
    case SIGNALWIRE = 6;
    case VOICE = 9;

    /**
     * Get the credential storage key for this service type
     *
     * @return string
     */
    public function getVendorKey(): string
    {
        return match ($this) {
            self::DISABLED => '',
            self::RINGCENTRAL => '_ringcentral',
            self::TWILIO_SMS => '_twilio',
            self::ETHERFAX => '_etherfax',
            self::EMAIL => '_email',
            self::CLICKATELL_SMS => '_clickatell',
            self::SIGNALWIRE => '_signalwire',
            self::VOICE => '_voice',
        };
    }

    /**
     * Render a <script> tag exposing enum values as JS constants.
     *
     * Values are emitted as strings (e.g. '1') to match the output of
     * js_escape(), so JS code can use strict equality (===).
     *
     * @return string
     */
    public static function renderJsConstants(): string
    {
        $pairs = [];
        foreach (self::cases() as $case) {
            $pairs[] = $case->name . ":'" . $case->value . "'";
        }
        return '<script>const ServiceType = Object.freeze({' . implode(',', $pairs) . '});</script>';
    }

    /**
     * Get the display name for this service type
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::DISABLED => 'Disabled',
            self::RINGCENTRAL => 'RingCentral',
            self::TWILIO_SMS => 'Twilio SMS',
            self::ETHERFAX => 'etherFAX',
            self::EMAIL => 'Email',
            self::CLICKATELL_SMS => 'Clickatell SMS',
            self::SIGNALWIRE => 'SignalWire Fax',
            self::VOICE => 'Voice',
        };
    }

    /**
     * Get the translated display name for this service type
     *
     * @return string
     */
    public function getTranslatedDisplayName(): string
    {
        return match ($this) {
            self::DISABLED => xlt('Disabled'),
            self::RINGCENTRAL => xlt('RingCentral'),
            self::TWILIO_SMS => xlt('Twilio SMS'),
            self::ETHERFAX => xlt('etherFAX'),
            self::EMAIL => xlt('Email'),
            self::CLICKATELL_SMS => xlt('Clickatell SMS'),
            self::SIGNALWIRE => xlt('SignalWire Fax'),
            self::VOICE => xlt('Voice'),
        };
    }

    /**
     * Get the translated SMS menu label for this service type
     *
     * @return string
     */
    public function getSmsMenuLabel(): string
    {
        return match ($this) {
            self::RINGCENTRAL => xlt('RingCentral SMS'),
            self::TWILIO_SMS => xlt('Twilio SMS'),
            self::CLICKATELL_SMS => xlt('Clickatell SMS'),
            default => xlt('SMS'),
        };
    }

    /**
     * Get the translated fax menu label for this service type
     *
     * @return string
     */
    public function getFaxMenuLabel(): string
    {
        return match ($this) {
            self::RINGCENTRAL => xlt('RingCentral Fax'),
            self::ETHERFAX => xlt('Manage etherFAX'),
            self::SIGNALWIRE => xlt('SignalWire Fax'),
            default => xlt('FAX'),
        };
    }

    /**
     * Get the enum value as a string, suitable for js_escape() and similar string-expecting functions
     */
    public function stringValue(): string
    {
        return (string)$this->value;
    }

    /**
     * Get the cases available for a service channel (sms, fax, email, voice)
     *
     * @return array<self> Cases valid for the given channel, always starting with DISABLED
     */
    public static function casesForChannel(string $channel): array
    {
        return match ($channel) {
            'sms' => [self::DISABLED, self::RINGCENTRAL, self::TWILIO_SMS, self::CLICKATELL_SMS],
            'fax' => [self::DISABLED, self::RINGCENTRAL, self::ETHERFAX, self::SIGNALWIRE],
            'email' => [self::DISABLED, self::EMAIL],
            'voice' => [self::DISABLED, self::VOICE],
            default => [self::DISABLED],
        };
    }

    /**
     * Get the translated option label for this case within a given channel context
     */
    public function getChannelOptionLabel(string $channel): string
    {
        if ($this === self::DISABLED) {
            return xlt('Disabled');
        }
        if ($channel === 'sms') {
            return match ($this) {
                self::RINGCENTRAL => xlt('RingCentral SMS'),
                self::TWILIO_SMS => xlt('Twilio SMS'),
                self::CLICKATELL_SMS => xlt('Clickatell'),
                default => $this->getTranslatedDisplayName(),
            };
        }
        if ($channel === 'fax') {
            return match ($this) {
                self::RINGCENTRAL => xlt('RingCentral Fax'),
                self::ETHERFAX => xlt('etherFAX'),
                self::SIGNALWIRE => xlt('SignalWire Fax'),
                default => $this->getTranslatedDisplayName(),
            };
        }
        return xlt('Enabled');
    }

    /**
     * Render <option> elements for a service channel's vendor dropdown
     */
    public static function renderSelectOptions(string $channel, self $selected): string
    {
        $html = '';
        foreach (self::casesForChannel($channel) as $case) {
            $selectedAttr = $case === $selected ? ' selected' : '';
            $html .= '<option value="' . attr($case->stringValue()) . '"' . $selectedAttr . '>'
                . text($case->getChannelOptionLabel($channel)) . '</option>';
        }
        return $html;
    }

    /**
     * Create from a numeric or string value, defaulting to DISABLED for unrecognized values
     * Note: Use this instead of native tryFrom() when you need to handle string inputs
     */
    public static function fromValue(mixed $value): self
    {
        return is_numeric($value) ? self::tryFrom((int)$value) ?? self::DISABLED : self::DISABLED;
    }
}
