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
use OpenEMR\Common\DirectMessaging\PhiMailConnection;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionException;
use OpenEMR\Common\DirectMessaging\PhiMailReceiver;
use OpenEMR\Common\DirectMessaging\PhiMailServer;
use OpenEMR\Common\DirectMessaging\PhiMailStreamException;
use OpenEMR\Common\DirectMessaging\PhiMailUnexpectedResponseException;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\SoftwareVersion;

/**
 * Connect to a phiMail Direct Messaging server
 *
 * @param ?string $phimail_error
 * @param-out ?string $phimail_error the C-code of a failure; untouched on success
 * @return resource|false
 */
function phimail_connect(&$phimail_error)
{
    $globals = OEGlobalsBag::getInstance();
    $logger = ServiceContainer::getLogger();
    $server = PhiMailServer::fromGlobals($globals);
    $version = SoftwareVersion::fromGlobals($globals);
    try {
        $connection = PhiMailConnection::open($server, $version, $logger);
    } catch (PhiMailConnectionException $e) {
        $phimail_error = $e->auditCode();
        $logger->error('phimail_connect failed to connect', ['error' => $phimail_error, 'exception' => $e]);
        return false;
    }

    return $connection->stream();
}

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

/**
 * Helper functions
 */

/**
 * @param resource $fp
 * @param string $text
 */
function phimail_write($fp, $text): void
{
    try {
        (new PhiMailConnection($fp))->write($text);
    } catch (PhiMailStreamException) {
        // Callers expect a failed write to pass silently; their next read reports it.
    }
}

/**
 * @param resource $fp
 * @param string $text
 * @return true|string|false true on OK, otherwise the server's reply, or false if there was none
 */
function phimail_write_expect_OK($fp, $text): bool|string
{
    try {
        (new PhiMailConnection($fp))->expectOk($text);
    } catch (PhiMailUnexpectedResponseException $e) {
        return $e->response;
    } catch (PhiMailStreamException) {
        return false;
    }

    return true;
}

/**
 * @param resource $fp
 */
function phimail_close($fp): void
{
    (new PhiMailConnection($fp))->close();
}
