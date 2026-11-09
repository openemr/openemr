<?php

/**
 * One direct_message_log row, as the Direct Message Log report shows it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class DirectMessageLogRecord
{
    /**
     * @param string $msgType the raw msg_type code: S for sent, R for received
     * @param string $status the raw status code; the report shows codes it does not know as blank
     * @param string $createdAt create_ts as the database formats it
     * @param ?string $statusChangedAt status_ts as the database formats it
     */
    public function __construct(
        public int $id,
        public string $msgType,
        public string $createdAt,
        public string $sender,
        public string $recipient,
        public string $status,
        public ?string $statusChangedAt,
    ) {
    }
}
