<?php

/**
 * A file in a remote folder, as the listing describes it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

final readonly class RemoteFile
{
    public function __construct(
        public string $name,
        public int $size,
        public int $modifiedAt,
    ) {
    }
}
