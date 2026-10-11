<?php

/**
 * The final value cqm-execution reports for a CQL statement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Parity;

enum StatementFinal: string
{
    case True = 'TRUE';
    case False = 'FALSE';
    /** The statement is not relevant to the population set. */
    case NotApplicable = 'NA';
    /** The statement is relevant but evaluation never reached it. */
    case Unhit = 'UNHIT';
}
