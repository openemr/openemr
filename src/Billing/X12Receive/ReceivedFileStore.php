<?php

/**
 * Puts a received file where the screens that use it read it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

interface ReceivedFileStore
{
    /**
     * @param string $localPath the downloaded file, already checked for size and type
     * @param string $remoteName the name the partner gave it
     */
    public function store(X12FileType $type, string $localPath, string $remoteName): StoreOutcome;
}
