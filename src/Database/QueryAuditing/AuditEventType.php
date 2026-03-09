<?php

/**
 * Audit event types for SQL query auditing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Represents the high-level event type for audit logging.
 *
 * These map to the `audit_events_{value}` global settings that control
 * whether each event type is logged.
 */
enum AuditEventType: string
{
    case PatientRecord = 'patient-record';
    case SecurityAdministration = 'security-administration';
    case Scheduling = 'scheduling';
    case Order = 'order';
    case LabOrder = 'lab-order';
    case LabResults = 'lab-results';
    case Other = 'other';
}
