<?php

/**
 * Looks up the OpenEMR users that the phiMail receiver records as senders and owners.
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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class PhiMailUserRepository
{
    public const PORTAL_USERNAME = 'portal-user';
    private const SERVICE_USERNAME = 'phimail-service';
    // Stands in for a service user that does not exist.
    private const DEFAULT_USER_ID = 1;

    /**
     * @throws SqlQueryException
     */
    public function findUsername(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }
        $rows = QueryUtils::fetchRecordsNoLog('SELECT username FROM users WHERE id = ?', [$userId]);
        $username = $rows[0]['username'] ?? null;
        return is_string($username) ? $username : null;
    }

    /**
     * The ID of the phimail-service user.
     *
     * @throws SqlQueryException
     */
    public function serviceUserId(): int
    {
        return $this->userIdOrDefault(self::SERVICE_USERNAME);
    }

    /**
     * The ID of the portal-user user, which sends messages patients request.
     *
     * @throws SqlQueryException
     */
    public function portalUserId(): int
    {
        return $this->userIdOrDefault(self::PORTAL_USERNAME);
    }

    /**
     * @throws SqlQueryException
     */
    private function userIdOrDefault(string $username): int
    {
        $rows = QueryUtils::fetchRecordsNoLog('SELECT id FROM users WHERE username = ?', [$username]);
        $id = $rows[0]['id'] ?? null;
        return match (true) {
            is_int($id) => $id,
            is_string($id) && ctype_digit($id) => (int) $id,
            default => self::DEFAULT_USER_ID,
        };
    }
}
