<?php

/**
 * A received 835 waiting for an unattended posting run.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

final readonly class PendingRemittance
{
    /**
     * @param int    $receivedId x12_remote_received.id
     * @param string $storedName its file name in the site's documents/era
     * @param int    $postUser   the user its partner posts as
     */
    public function __construct(
        public int $receivedId,
        public string $storedName,
        public int $postUser,
    ) {
    }
}
