<?php

/**
 * What became of a file found in a remote folder.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

/**
 * Backed because the value is stored in x12_remote_received.status.
 */
enum ReceivedStatus: string
{
    /** Downloaded whole and stored on the site. */
    case Stored = 'stored';
    /** The site already holds a file of that name; the local one was kept. */
    case Duplicate = 'duplicate';
    /** Not an X12 file of the kind its folder is for, it could not be named, or a different file holds its name. */
    case Rejected = 'rejected';
    /** The download or the local write did not complete; tried again next run. */
    case Failed = 'failed';

    /**
     * A settled file is not downloaded again.
     */
    public function isSettled(): bool
    {
        return $this !== self::Failed;
    }
}
