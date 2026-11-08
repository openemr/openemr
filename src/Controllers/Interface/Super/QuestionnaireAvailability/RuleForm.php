<?php

/**
 * Values a rule form starts with: empty for an add, the stored rule for an edit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class RuleForm
{
    /**
     * @param string $prefix element id prefix, so the two forms on the page do not collide
     * @param ?int $assignmentId the rule being edited, or null for an add
     * @param ?string $acl "section|level", or null for the surface default
     * @param ?string $aclUnknownLabel set when $acl is not in the permission list, so it
     *                                  stays selectable instead of silently resetting
     */
    public function __construct(
        public string $prefix,
        public ?int $assignmentId = null,
        public int $seq = 0,
        public ?string $surface = null,
        public ?string $acl = null,
        public ?string $aclUnknownLabel = null,
        public ?int $facility = null,
        public ?int $provider = null,
        public ?int $visitCategory = null,
        public ?string $clientId = null,
        public ?string $category = null,
    ) {
    }

    public function isEdit(): bool
    {
        return $this->assignmentId !== null;
    }
}
