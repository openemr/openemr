<?php

/**
 * A contact_telecom row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class PlannedTelecom
{
    public function __construct(
        public ContactOwner $owner,
        public string $system,
        public string $use,
        public string $value,
        public int $rank,
        public bool $primary,
        public bool $active,
        public \DateTimeImmutable $periodStart,
        public ?\DateTimeImmutable $periodEnd,
        public ?string $reason,
        public ?string $note,
    ) {
    }

    public function withPrimary(bool $primary): self
    {
        return new self(
            $this->owner,
            $this->system,
            $this->use,
            $this->value,
            $this->rank,
            $primary,
            $this->active,
            $this->periodStart,
            $this->periodEnd,
            $this->reason,
            $this->note,
        );
    }
}
