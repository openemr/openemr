<?php

/**
 * The fields of a direct_message_log row that a STATUS reply needs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class DirectMessageLogEntry
{
    /**
     * @param ?int $userId the OpenEMR user who sent the message
     * @param string $createdAt when the row was written, as the database formats it
     */
    public function __construct(
        public ?int $patientId,
        public ?int $userId,
        public string $createdAt,
        public string $recipient,
    ) {
    }
}
