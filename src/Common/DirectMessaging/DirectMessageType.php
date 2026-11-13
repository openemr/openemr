<?php

/**
 * Direction of a Direct message, as stored in direct_message_log.msg_type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

enum DirectMessageType: string
{
    case Sent = 'S';
    case Received = 'R';

    /** The translated label the Direct Message Log report shows. */
    public function label(): string
    {
        return match ($this) {
            self::Sent => xl('Sent'),
            self::Received => xl('Received'),
        };
    }
}
