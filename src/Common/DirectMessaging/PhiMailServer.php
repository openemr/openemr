<?php

/**
 * Where and how to reach the phiMail Direct Messaging server.
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

use OpenEMR\Core\OEGlobalsBag;

final readonly class PhiMailServer
{
    /**
     * @param ?string $caFile CA bundle that verifies the server, or null to use the system defaults
     */
    public function __construct(
        public bool $enabled,
        public string $address,
        public bool $testMode,
        public ?string $caFile,
    ) {
    }

    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        // Test mode, the default, trusts EMR Direct's test CA instead of the production server certificate.
        $testMode = !$globals->getBoolean('phimail_testmode_disabled');
        $caFile = $globals->getProjectDir() . '/public/certs/phimail/'
            . ($testMode ? 'EMRDirectTestCA.pem' : 'phimail_server.pem');

        return new self(
            enabled: $globals->getBoolean('phimail_enable'),
            address: $globals->getString('phimail_server_address'),
            testMode: $testMode,
            caFile: file_exists($caFile) ? $caFile : null,
        );
    }
}
