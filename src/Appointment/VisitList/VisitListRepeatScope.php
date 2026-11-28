<?php

/**
 * VisitListRepeatScope
 *
 * Which dates of a repeating event a save in the appointment editor changes: the date it was
 * opened on, that date and later, or every date. The editor posts it as recurr_affect.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

enum VisitListRepeatScope: string
{
    case ThisDate = 'current';
    case ThisAndLater = 'future';
    case AllDates = 'all';

    /**
     * The scope the editor posted. An event that does not repeat posts nothing, which changes
     * the one event: every date it has.
     */
    public static function fromPosted(string $recurrAffect): self
    {
        return self::tryFrom($recurrAffect) ?? self::AllDates;
    }
}
