<?php

/**
 * Logs in to an X12 partner over SFTP with the credentials on its record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

use OpenEMR\Billing\BillingProcessor\SftpConnector;
use OpenEMR\Common\Crypto\CryptoInterface;
use Psr\Log\LoggerInterface;

final readonly class SftpRemoteDirectoryOpener implements RemoteDirectoryOpener
{
    public function __construct(
        private CryptoInterface $crypto,
        private LoggerInterface $logger,
    ) {
    }

    public function open(ReceivePartner $partner): ?RemoteDirectory
    {
        $connector = new SftpConnector(logger: $this->logger);
        $sftp = $connector->connect(
            $partner->host,
            $partner->port,
            $partner->login,
            $this->crypto->decryptFromDatabase($partner->encryptedPassword),
        );
        if ($sftp === null) {
            $this->logger->warning('X12 receive: could not connect to the partner', ['partner' => $partner->id]);
            return null;
        }
        if ($connector->credentialsRejected()) {
            $this->logger->warning('X12 receive: the partner rejected the login', ['partner' => $partner->id]);
            return null;
        }
        return new SftpRemoteDirectory($sftp);
    }
}
