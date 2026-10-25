<?php

/**
 * One row of `content_assignments`, as the management screen edits it.
 *
 * AvailabilityContext answers "what applies here"; this answers "what does the stored
 * row say". The read path resolves assignments into availability and never needs the row
 * itself, so this exists only for editing and is deliberately a flat mirror of the table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

final readonly class AssignmentRecord
{
    /**
     * @param int $id 0 for a row that has not been written yet
     * @param ?int $resourceId null with $category set means every resource in that category
     * @param ?string $category only meaningful when $resourceId is null
     * @param ?string $aclSection set together with $aclLevel, or leave both null
     */
    public function __construct(
        public int $id,
        public QuestionnaireSurface $surface,
        public ?int $resourceId = null,
        public ?string $category = null,
        public ?int $facility = null,
        public ?int $provider = null,
        public ?int $visitCategoryId = null,
        public ?string $clientId = null,
        public ?string $aclSection = null,
        public ?string $aclLevel = null,
        public bool $active = true,
        public int $seq = 0,
        public ?string $resourceName = null,
    ) {
    }

    /**
     * Whether this row covers a whole category rather than one named questionnaire.
     *
     * The management screen has to show this: a questionnaire can be offered by its own
     * row and by a category row at once, and an administrator who removes the specific
     * row and sees no change needs to know why.
     */
    public function isCategoryWide(): bool
    {
        return $this->resourceId === null && ($this->category ?? '') !== '';
    }

    /**
     * Whether the row names any target at all. A row with neither matches nothing, which
     * is a data error rather than a meaningful state.
     */
    public function hasTarget(): bool
    {
        return $this->resourceId !== null || ($this->category ?? '') !== '';
    }

    /**
     * Whether any scope narrows this row.
     */
    public function isScoped(): bool
    {
        return $this->scopedColumns() !== [];
    }

    /**
     * The scope columns this row narrows by, named as in `content_assignments`.
     *
     * @return list<'facility'|'provider'|'visit_category_id'|'client_id'>
     */
    public function scopedColumns(): array
    {
        $columns = [];
        if ($this->facility !== null) {
            $columns[] = 'facility';
        }
        if ($this->provider !== null) {
            $columns[] = 'provider';
        }
        if ($this->visitCategoryId !== null) {
            $columns[] = 'visit_category_id';
        }
        if (($this->clientId ?? '') !== '') {
            $columns[] = 'client_id';
        }

        return $columns;
    }
}
