<?php

/**
 * Things in an LHC form definition that could not be carried into FHIR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Questionnaire;

enum LhcFormConversionIssue
{
    case UnknownDataType;
    case SkipLogicDropped;
    case ScoreDropped;
    case RestrictionDropped;
}
