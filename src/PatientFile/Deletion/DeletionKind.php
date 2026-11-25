<?php

/**
 * The one record a patient-file delete is allowed to name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PatientFile\Deletion;

enum DeletionKind
{
    case Patient;
    case Encounter;
    case Form;
    case Issue;
    case Document;
    case Payment;
    case Billing;
    case Transaction;
}
