<?php

/**
 * Notification Channel Enum
 *
 * Identifies the channel used for an appointment-reminder notification
 * (currently SMS or email). Used to replace stringly-typed `$TYPE` parameters
 * in the legacy faxsms reminder helpers (e.g. faxsms_getAlertPatientData()).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Enums;

enum NotificationChannel: string
{
    case SMS = 'SMS';
    case EMAIL = 'EMAIL';

    /**
     * Parse a legacy `$TYPE` string (case-insensitive) into a channel,
     * defaulting to SMS for unrecognized values to match the historical
     * fall-through behavior of `cron_GetAlertPatientData()`.
     */
    public static function fromLegacyType(?string $type): self
    {
        return self::tryFrom(strtoupper((string) $type)) ?? self::SMS;
    }

    /**
     * The value for `notification_log.type`, an enum('SMS','Email').
     *
     * MySQL matches enum values by the column's collation, so 'EMAIL' only
     * finds 'Email' under a case-insensitive one. Under a binary collation
     * the insert stores '' and the dedup lookup never matches.
     */
    public function logType(): string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::EMAIL => 'Email',
        };
    }
}
