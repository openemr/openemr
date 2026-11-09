<?php

/**
 * A STATUS reply to CHECK: news about a message this system sent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailStatusReply
{
    /**
     * @param string $info the server's additional information, or '' when it sent none
     * @param string $line the reply as received, including its line feed, for the audit log
     */
    public function __construct(
        public string $messageId,
        public PhiMailDeliveryStatus $status,
        public string $info,
        public string $line,
    ) {
    }
}
