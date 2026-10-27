<?php

/**
 * What a submitted change produced, for the page that renders after it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class PostOutcome
{
    /**
     * @param list<string> $notices translated confirmations
     * @param list<string> $errors translated refusals
     * @param bool $ruleSaved a rule form saved, so the page leaves edit mode
     */
    public function __construct(
        public array $notices = [],
        public array $errors = [],
        public bool $ruleSaved = false,
    ) {
    }
}
