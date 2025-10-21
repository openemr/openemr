<?php

/*
 * FHIRMedicationStatusEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Enum;

enum FHIRMedicationStatusEnum: string
{
    const ACTIVE = "active";
    const ON_HOLD = "on-hold";
    const CANCELLED = "cancelled";
    const COMPLETED = "completed";
    const ENTERED_IN_ERROR = "draft";
    const STOPPED = "stopped";
    const DRAFT = "draft";
    const UNKNOWN = "unknown";
}
