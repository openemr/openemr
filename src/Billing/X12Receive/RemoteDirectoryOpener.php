<?php

/**
 * Opens the connection to an X12 partner's file server.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

interface RemoteDirectoryOpener
{
    /**
     * @return ?RemoteDirectory null when no connection could be made or the login was refused
     */
    public function open(ReceivePartner $partner): ?RemoteDirectory;
}
