<?php

/**
 * VisitListBlockSave
 *
 * A Visit List block as it was before the appointment editor saved it: the dates the save
 * changes and the lists on them, so the patients can be moved with the block afterwards.
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

final readonly class VisitListBlockSave
{
    /**
     * @param VisitListRepeatScope $scope        which dates the save changes
     * @param list<int>            $eventIds     pc_eid of the block and its copies for other providers
     * @param list<int>            $providerIds  the providers of those copies, sorted
     * @param string               $repeatRule   how the block repeated (VisitListService::repeatRule())
     * @param list<string>         $pattern      the dates of the block's repeat pattern the save changes
     *                                           (Y-m-d), excluded dates included, up to the last list
     * @param DateTimeImmutable    $scopeStart   the first date the save changes
     * @param list<VisitList>      $lists        every occurrence the save changes, from $scopeStart up to the
     *                                           last one with patients, with or without patients, by date
     * @param int                  $lastEventId  the highest pc_eid before the save, to find the events it adds
     */
    public function __construct(
        public VisitListRepeatScope $scope,
        public array $eventIds,
        public array $providerIds,
        public string $repeatRule,
        public array $pattern,
        public DateTimeImmutable $scopeStart,
        public array $lists,
        public int $lastEventId,
    ) {
    }
}
