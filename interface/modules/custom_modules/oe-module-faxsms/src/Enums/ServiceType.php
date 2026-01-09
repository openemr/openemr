<?php

/**
 * Service Type Enum
 * Maps service type IDs to their display names and identifiers
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Enums;

enum ServiceType: int
{
    case RINGCENTRAL = 1;
    case TWILIO_SMS = 2;
    case ETHERFAX = 3;
    case EMAIL = 4;
    case CLICKATELL_SMS = 5;
    case SIGNALWIRE = 6;

    /**
     * Get the display name for this service type
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::RINGCENTRAL => 'RingCentral',
            self::TWILIO_SMS => 'Twilio SMS',
            self::ETHERFAX => 'etherFAX',
            self::EMAIL => 'Email',
            self::CLICKATELL_SMS => 'Clickatell SMS',
            self::SIGNALWIRE => 'SignalWire Fax',
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
            self::RINGCENTRAL => xlt('RingCentral'),
            self::TWILIO_SMS => xlt('Twilio SMS'),
            self::ETHERFAX => xlt('etherFAX'),
            self::EMAIL => xlt('Email'),
            self::CLICKATELL_SMS => xlt('Clickatell SMS'),
            self::SIGNALWIRE => xlt('SignalWire Fax'),
        };
    }

    /**
     * Try to create from a numeric or string value
     * Note: Use this instead of native tryFrom() when you need to handle string inputs
     *
     * @param int|string $value
     * @return self|null
     */
    public static function fromValue(int|string $value): ?self
    {
        $value = (int)$value;
        return self::tryFrom($value);
    }
}
