<?php

/*
 * EventStatusEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Enum;

enum EventStatusEnum: string
{
// preparation | in-progress | not-done | on-hold | stopped | completed | entered-in-error | unknown

    case PREPARATION = 'preparation';
    case IN_PROGRESS = 'in-progress';
    case NOT_DONE = 'not-done';
    case ON_HOLD = 'on-hold';
    case STOPPED = 'stopped';
    case COMPLETED = 'completed';
    case ENTERED_IN_ERROR = 'entered-in-error';
    case UNKNOWN = 'unknown';
}
