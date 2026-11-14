<?php

/**
 * Background service entry point: receive files from every X12 partner set up for it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;

final class X12ReceiveService
{
    /**
     * The X12_SFTP_Receive background_services row names this method. The
     * runner calls it with no arguments. It does nothing, and connects to
     * nothing, unless a partner has a receive folder set.
     */
    public static function runBackgroundService(): void
    {
        $partners = (new ReceivePartnerRepository())->partnersWithFolders();
        if ($partners === []) {
            return;
        }
        $globals = OEGlobalsBag::getInstance();
        $logger = ServiceContainer::getLogger();
        $summary = (new X12RemoteReceiver(
            opener: new SftpRemoteDirectoryOpener(ServiceContainer::getCrypto(), $logger),
            log: new ReceivedFileRepository(),
            store: new SiteReceivedFileStore($globals->getString('OE_SITE_DIR')),
            clock: ServiceContainer::getClock(),
            logger: $logger,
            tempDirectory: $globals->getString('temporary_files_dir'),
        ))->receive($partners);
        $logger->info('X12 receive: run finished', $summary->toArray());
    }
}
