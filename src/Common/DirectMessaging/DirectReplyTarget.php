<?php

/**
 * Where a received Direct message that answers one we sent is filed (#15137): the sent message's
 * patient, and its referral if it carried one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class DirectReplyTarget
{
    /**
     * @param string $messageId the id of the sent message it answers, as the log has it
     */
    public function __construct(
        public string $messageId,
        public int $patientId,
        public ?int $referralId,
    ) {
    }
}
