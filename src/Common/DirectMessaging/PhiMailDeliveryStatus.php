<?php

/**
 * Delivery status of a sent Direct message, as stored in direct_message_log.status.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

enum PhiMailDeliveryStatus: string
{
    case Dispatched = 'D';
    case Failed = 'F';
    case Unknown = 'U';

    /**
     * Map the status code of a STATUS reply. Codes other than dispatched and failed are Unknown.
     */
    public static function fromStatusCode(string $code): self
    {
        return match ($code) {
            'dispatched' => self::Dispatched,
            'failed' => self::Failed,
            default => self::Unknown,
        };
    }
}
