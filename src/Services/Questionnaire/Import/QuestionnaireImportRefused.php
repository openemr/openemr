<?php

/**
 * Thrown when a questionnaire cannot be imported as submitted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

use DomainException;
use Throwable;

final class QuestionnaireImportRefused extends DomainException
{
    public function __construct(
        public readonly QuestionnaireImportRefusal $refusal,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Questionnaire import refused: ' . $refusal->name, 0, $previous);
    }
}
