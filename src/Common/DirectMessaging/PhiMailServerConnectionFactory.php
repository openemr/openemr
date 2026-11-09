<?php

/**
 * Opens connections to a phiMail server over the network.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use OpenEMR\Services\SoftwareVersion;
use Psr\Log\LoggerInterface;

final readonly class PhiMailServerConnectionFactory implements PhiMailConnectionFactory
{
    public function __construct(
        private PhiMailServer $server,
        private SoftwareVersion $version,
        private LoggerInterface $logger,
    ) {
    }

    public function open(): PhiMailConnection
    {
        return PhiMailConnection::open($this->server, $this->version, $this->logger);
    }
}
