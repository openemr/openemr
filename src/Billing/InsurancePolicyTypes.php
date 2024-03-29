<?php

/**
 * OpenEMR Insurance Policy Types - OpenEMR specific insurance policy types.
 * This class provides a list of insurance policy types that are used in the 837p standard.
 * The list is hard-coded because its values and meanings are fixed by the 837p standard and we don't want people messing with them.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

class InsurancePolicyTypes
{
    // Hard-coding this array because its values and meanings are fixed by the 837p
// standard and we don't want people messing with them.
    const POLICY_TYPE_NOT_APPLICABLE = '';
    const POLICY_TYPE_WORKING_AGED_BENEFICIARY_EMPLOYER_GROUP_HEALTH_PLAN = '12';

    const POLICY_TYPE_END_STAGE_RENAL_DISEASE_BENEFICIARY_IN_MCP_WITH_EMPLOYERS_GROUP_PLAN = '13';

    const POLICY_TYPE_NO_FAULT_INSURANCE_INCLUDING_AUTO_IS_PRIMARY = '14';

    const POLICY_TYPE_WORKERS_COMPENSATION = '15';
    const POLICY_TYPE_PUBLIC_HEALTH_SERVICE_OR_OTHER_FEDERAL_AGENCY = '16';
    const POLICY_TYPE_BLACK_LUNG = '41';
    const POLICY_TYPE_VETERANS_ADMINISTRATION = '42';
    const POLICY_TYPE_DISABLED_BENEFICIARY_UNDER_AGE_65_WITH_LARGE_GROUP_HEALTH_PLAN_LGHP = '43';
    const POLICY_TYPE_OTHER_LIABILITY_INSURANCE_IS_PRIMARY = '47';
    const POLICY_TYPES = [self::POLICY_TYPE_NOT_APPLICABLE, self::POLICY_TYPE_WORKING_AGED_BENEFICIARY_EMPLOYER_GROUP_HEALTH_PLAN, self::POLICY_TYPE_END_STAGE_RENAL_DISEASE_BENEFICIARY_IN_MCP_WITH_EMPLOYERS_GROUP_PLAN, self::POLICY_TYPE_NO_FAULT_INSURANCE_INCLUDING_AUTO_IS_PRIMARY, self::POLICY_TYPE_WORKERS_COMPENSATION, self::POLICY_TYPE_PUBLIC_HEALTH_SERVICE_OR_OTHER_FEDERAL_AGENCY, self::POLICY_TYPE_BLACK_LUNG, self::POLICY_TYPE_VETERANS_ADMINISTRATION, self::POLICY_TYPE_DISABLED_BENEFICIARY_UNDER_AGE_65_WITH_LARGE_GROUP_HEALTH_PLAN_LGHP, self::POLICY_TYPE_OTHER_LIABILITY_INSURANCE_IS_PRIMARY];

    public static function getTranslatedPolicyTypes()
    {
        return [
            self::POLICY_TYPE_NOT_APPLICABLE => xlt('N/A'),
            self::POLICY_TYPE_WORKING_AGED_BENEFICIARY_EMPLOYER_GROUP_HEALTH_PLAN => xlt('Working Aged Beneficiary or Spouse with Employer Group Health Plan'),
            self::POLICY_TYPE_END_STAGE_RENAL_DISEASE_BENEFICIARY_IN_MCP_WITH_EMPLOYERS_GROUP_PLAN => xlt('End-Stage Renal Disease Beneficiary in MCP with Employer`s Group Plan'),
            self::POLICY_TYPE_NO_FAULT_INSURANCE_INCLUDING_AUTO_IS_PRIMARY => xlt('No-fault Insurance including Auto is Primary'),
            self::POLICY_TYPE_WORKERS_COMPENSATION => xlt('Worker`s Compensation'),
            self::POLICY_TYPE_PUBLIC_HEALTH_SERVICE_OR_OTHER_FEDERAL_AGENCY => xlt('Public Health Service (PHS) or Other Federal Agency'),
            self::POLICY_TYPE_BLACK_LUNG => xlt('Black Lung'),
            self::POLICY_TYPE_VETERANS_ADMINISTRATION => xlt('Veteran`s Administration'),
            self::POLICY_TYPE_DISABLED_BENEFICIARY_UNDER_AGE_65_WITH_LARGE_GROUP_HEALTH_PLAN_LGHP => xlt('Disabled Beneficiary Under Age 65 with Large Group Health Plan (LGHP)'),
            self::POLICY_TYPE_OTHER_LIABILITY_INSURANCE_IS_PRIMARY => xlt('Other Liability Insurance is Primary')
        ];
    }
}
