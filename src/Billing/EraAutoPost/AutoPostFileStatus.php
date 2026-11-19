<?php

/**
 * What an unattended posting run did with a received 835.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

/**
 * Backed because the value is stored in x12_remote_received.auto_post_status. A file
 * no run has taken has an empty status there and no case here.
 */
enum AutoPostFileStatus: string
{
    /** A run took the file and has not finished it. A file left like this is not taken again. */
    case Running = 'running';
    /** Every check in the file was posted or held, and recorded. */
    case Done = 'done';
    /** The file could not be parsed as an 835; nothing was posted from it. */
    case Unreadable = 'unreadable';
    /** An error stopped the run part way through the file. It is not taken again. */
    case Interrupted = 'interrupted';
}
