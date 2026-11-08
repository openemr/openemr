<?php

/**
 * Per-row delivery outcomes from AppointmentNotificationRunner.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Notification;

enum DeliveryOutcome
{
    case Sent;
    case SkippedInvalid;
    case Failed;
    case DryRun;
}
