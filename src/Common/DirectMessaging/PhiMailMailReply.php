<?php

/**
 * A MAIL reply to CHECK: a new message waiting to be downloaded.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailMailReply
{
    /**
     * @param string $line the reply as received, including its line feed, for the audit log
     */
    public function __construct(
        public string $recipient,
        public string $sender,
        public int $attachmentCount,
        public string $messageId,
        public string $line,
    ) {
    }
}
