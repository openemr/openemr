<?php

/**
 * phiMail Direct Messaging settings: the server plus the account that uses it.
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

use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;

final readonly class PhiMailConfig
{
    private const DEFAULT_NOTIFY_USERNAME = 'admin';

    /**
     * @param string $notifyUsername OpenEMR user who receives notes about incoming messages and send failures
     */
    public function __construct(
        public PhiMailServer $server,
        public string $username,
        #[\SensitiveParameter] public string $password,
        public string $notifyUsername,
    ) {
    }

    public static function fromGlobals(OEGlobalsBag $globals, CryptoInterface $crypto): self
    {
        $notifyUsername = $globals->getString('phimail_notify');

        return new self(
            server: PhiMailServer::fromGlobals($globals),
            username: $globals->getString('phimail_username'),
            password: $crypto->decryptFromDatabase($globals->getString('phimail_password')),
            notifyUsername: $notifyUsername === '' ? self::DEFAULT_NOTIFY_USERNAME : $notifyUsername,
        );
    }
}
