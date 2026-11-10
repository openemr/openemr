<?php

/**
 * Every status direct_message_log.status can hold.
 *
 * PhiMailDeliveryStatus is the narrower set a STATUS reply can report; this enum adds the codes
 * written when a message is sent or received.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

enum DirectMessageLogStatus: string
{
    case Queued = 'Q';
    case Sent = 'S';
    case Dispatched = 'D';
    case Received = 'R';
    case Failed = 'F';
    case Unknown = 'U';

    /**
     * The translated label the Direct Message Log report shows, or null for Unknown, which the
     * report has always left blank.
     */
    public function label(): ?string
    {
        return match ($this) {
            self::Queued => xl('Queued'),
            self::Sent => xl('Sent'),
            self::Dispatched => xl('Sent - Confirmed'),
            self::Received => xl('Received'),
            self::Failed => xl('Failed'),
            self::Unknown => null,
        };
    }
}
