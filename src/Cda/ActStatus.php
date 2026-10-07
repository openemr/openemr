<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cda;

/**
 * HL7 ActStatus codes for CDA documents
 *
 * @see https://terminology.hl7.org/CodeSystem-v3-ActStatus.html
 */
enum ActStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Aborted = 'aborted';
    case Held = 'held';
    case New = 'new';
    case Nullified = 'nullified';
    case Obsolete = 'obsolete';
    case Suspended = 'suspended';
}
