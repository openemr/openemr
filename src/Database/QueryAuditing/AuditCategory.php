<?php

/**
 * Audit categories for SQL query auditing.
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
 * Represents the detailed category for audit logging.
 *
 * These provide more granular classification than AuditEventType,
 * identifying the specific type of clinical or administrative data involved.
 */
enum AuditCategory: string
{
    case ProblemList = 'Problem List';
    case Medication = 'Medication';
    case Allergy = 'Allergy';
    case Immunization = 'Immunization';
    case Vitals = 'Vitals';
    case SocialAndFamilyHistory = 'Social and Family History';
    case EncounterForm = 'Encounter Form';
    case PatientInsurance = 'Patient Insurance';
    case PatientDemographics = 'Patient Demographics';
    case Billing = 'Billing';
    case ClinicalMail = 'Clinical Mail';
    case Referral = 'Referral';
    case Amendments = 'Amendments';
    case Scheduling = 'Scheduling';
    case LabOrder = 'Lab Order';
    case LabResult = 'Lab Result';
    case Security = 'Security';
}
