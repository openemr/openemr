<?php

/**
 * An X12 partner that has one or more remote folders to receive from.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

final readonly class ReceivePartner
{
    /**
     * @param array<value-of<X12FileType>, string> $folders remote folder by file type
     * @param string $workedFolder remote folder a stored file is moved to; empty leaves it in place
     */
    public function __construct(
        public int $id,
        public string $host,
        public int $port,
        public string $login,
        public ?string $encryptedPassword,
        public array $folders,
        public string $workedFolder,
    ) {
    }
}
