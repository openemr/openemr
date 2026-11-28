<?php

/**
 * FlowBoardChangeResult
 *
 * The outcome of a status or room change on the Flow Board.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

enum FlowBoardChangeResult
{
    case Saved;
    /** No patient appointment with that event ID. */
    case NotFound;
    /** A repeating appointment: its date has to be split off in the appointment dialog first. */
    case Repeating;
    case UnknownStatus;
    case UnknownRoom;
}
