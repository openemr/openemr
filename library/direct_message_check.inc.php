<?php

/**
 * Background receive function for phiMail Direct Messaging service.
 *
 * This script is called by the background service manager
 * at /library/ajax/execute_background_services.php
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
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
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\DirectMessaging\PhiMailCheckException;
use OpenEMR\Common\DirectMessaging\PhiMailReceiver;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Connect to a phiMail Direct Messaging server and check for any incoming status
 * messages related to previously transmitted messages or any new messages received.
 * The background_services row names this function, so it stays global.
 *
 * @throws PhiMailCheckException when the check fails, after writing its audit row
 * @throws DbalException when an audit row cannot be written on a path that is not already failing
 */
function phimail_check(): void
{
    PhiMailReceiver::fromGlobals(
        OEGlobalsBag::getInstance(),
        ServiceContainer::getCrypto(),
        ServiceContainer::getLogger(),
        EventAuditLogger::getInstance(),
    )->check();
}
