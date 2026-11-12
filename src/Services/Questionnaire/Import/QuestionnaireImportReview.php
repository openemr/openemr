<?php

/**
 * A parsed questionnaire ready for an administrator to review before importing it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

final readonly class QuestionnaireImportReview
{
    /**
     * @param array<mixed> $questionnaire the FHIR resource, normalized
     * @param list<string> $tags labels found in Questionnaire.meta.tag
     * @param ?array{id: int, name: string, version: int, category: string} $existing the
     *        repository row an import would replace
     */
    public function __construct(
        public array $questionnaire,
        public string $name,
        public array $tags,
        public ?string $suggestedCategory,
        public ?array $existing,
    ) {
    }
}
