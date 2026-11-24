<?php

/**
 * Why a questionnaire import was refused.
 *
 * A unit enum because it never leaves the request: the screen maps each case to translated
 * text, so the wording a user sees is never built from an exception message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

enum QuestionnaireImportRefusal
{
    case InvalidJson;
    case NotQuestionnaire;
    case MissingName;
    case MissingCategory;
    case UnknownCategory;
    case UnknownTemplateCategory;
    case ExistingNotConfirmed;
}
