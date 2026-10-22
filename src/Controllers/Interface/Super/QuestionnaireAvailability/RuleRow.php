<?php

/**
 * One stored rule as the limits panel and the category list show it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class RuleRow
{
    /**
     * @param string $where "Everywhere", or the rule's limits in words
     * @param string $permission who may see what the rule offers, in words
     * @param ?int $questionnaireId the questionnaire a rule targets, null for a category rule;
     *                              the template links the rule's Edit button with it
     * @param bool $highlighted the rule currently open for editing
     * @param ?string $categoryLabel set for a category rule, null for a questionnaire's rule
     */
    public function __construct(
        public int $id,
        public string $surfaceLabel,
        public string $where,
        public string $permission,
        public bool $active,
        public ?int $questionnaireId,
        public bool $highlighted,
        public ?string $categoryLabel = null,
    ) {
    }
}
