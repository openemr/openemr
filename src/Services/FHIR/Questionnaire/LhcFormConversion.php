<?php

/**
 * The FHIR Questionnaire made from an LHC form, and what could not be carried over.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Questionnaire;

final readonly class LhcFormConversion
{
    /**
     * @param array<string, mixed> $questionnaire
     * @param list<array{issue: LhcFormConversionIssue, question: string}> $issues
     */
    public function __construct(
        public array $questionnaire,
        public array $issues,
    ) {
    }
}
