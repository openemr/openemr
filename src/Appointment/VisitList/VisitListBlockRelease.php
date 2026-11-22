<?php

/**
 * VisitListBlockRelease
 *
 * A Visit List block about to stop being one, because the appointment editor deletes it or
 * saves it with another category: its lists on the dates the change affects, so afterwards
 * the patients can become timed appointments (VisitListService::release()).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListBlockRelease
{
    public function __construct(
        public VisitListBlockSave $before,
    ) {
    }
}
