<?php

/**
 * A Direct message that came back as a reply to one sent with a referral (#15137), filed with that
 * referral, as its page lists it for staff to confirm.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final readonly class ReferralDirectReply
{
    /**
     * @param int $id its direct_message_log id
     * @param string $receivedAt as the database formats it
     * @param ?int $noteId the note it was filed as
     */
    public function __construct(
        public int $id,
        public string $receivedAt,
        public string $sender,
        public ?int $noteId,
    ) {
    }
}
