<?php

/**
 * VisitListCopies
 *
 * An appointment's copies as they were before the editor saved it: the pc_multiple group
 * that joins a multi-provider appointment's copies, and when each provider's copy started.
 * The editor may delete the copy it opened, and it writes one start time to every copy, so
 * placing the saved appointment needs both from before the save.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;

final readonly class VisitListCopies
{
    /**
     * @param ?int                           $multiple        pc_multiple, or null for a single-provider appointment
     * @param array<int, DateTimeImmutable> $startByProvider when each provider's copy started, by provider ID
     * @param list<VisitList>               $lists           the Visit Lists the copies were on, to renumber
     *                                                       when a copy leaves one
     */
    public function __construct(
        public ?int $multiple,
        public array $startByProvider,
        public array $lists = [],
    ) {
        if ($multiple !== null && $multiple <= 0) {
            throw new DomainException('Visit List copies group must be positive');
        }
    }

    /**
     * When this provider's copy started before the save, if it was on the same date then;
     * null for a provider added by the save or a copy moved to another date.
     */
    public function startOn(int $providerId, DateTimeImmutable $date): ?DateTimeImmutable
    {
        $start = $this->startByProvider[$providerId] ?? null;
        if ($start === null || $start->format('Y-m-d') !== $date->format('Y-m-d')) {
            return null;
        }
        return $start;
    }
}
