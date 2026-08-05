<?php

/**
 * Email send methods available for testing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Email;

enum EmailSendMethod: string
{
    case Direct = 'direct';
    case Queue = 'queue';
    case QueueTemplated = 'queue_templated';

    public function label(): string
    {
        return match ($this) {
            self::Direct => xl('Direct Send (MyMailer::send)'),
            self::Queue => xl('Queue (MyMailer::emailServiceQueue)'),
            self::QueueTemplated => xl('Queue Templated (MyMailer::emailServiceQueueTemplatedEmail)'),
        };
    }
}
