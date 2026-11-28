<?php

/**
 * The kind of value a Patient Finder criterion operator takes.
 *
 * The value is sent to the filter panel as JSON, which draws the matching inputs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

enum PatientFinderInput: string
{
    /** The operator takes no value ("has none"). */
    case None = 'none';
    /** One choice from the criterion's options. */
    case Select = 'select';
    /** One whole number between the operator's min and max. */
    case Number = 'number';
    /** Two whole numbers between the operator's min and max, the lower first. */
    case Range = 'range';
}
