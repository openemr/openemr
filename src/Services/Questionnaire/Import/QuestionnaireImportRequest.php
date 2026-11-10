<?php

/**
 * One questionnaire an administrator has reviewed and asked to import.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

use OpenEMR\Services\Questionnaire\QuestionnaireSurface;

final readonly class QuestionnaireImportRequest
{
    /**
     * @param list<string> $tags labels for Questionnaire.meta.tag
     * @param ?list<QuestionnaireSurface> $surfaces where a new questionnaire is offered, or null
     *        when this user may not choose and the default assignment stands
     * @param ?string $portalTemplateCategory category of a portal template to create alongside
     *        the import ('' is General), or null for no template
     */
    public function __construct(
        public string $json,
        public string $name,
        public string $category,
        public array $tags = [],
        public bool $replaceExisting = false,
        public ?array $surfaces = null,
        public ?string $portalTemplateCategory = null,
    ) {
    }
}
