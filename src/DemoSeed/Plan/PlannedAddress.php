<?php

/**
 * A contact_address row (with its addresses row).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\PostalAddress;

final readonly class PlannedAddress
{
    public function __construct(
        public ContactOwner $owner,
        public string $type,
        public string $use,
        public PostalAddress $address,
        public bool $primary,
        public bool $active,
        public \DateTimeImmutable $periodStart,
        public ?\DateTimeImmutable $periodEnd,
        public ?string $reason,
        public ?string $note,
    ) {
    }
}
