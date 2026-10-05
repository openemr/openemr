<?php

/**
 * Literal UPDATE statements for a layout field stored on the patient or the visit.
 *
 * The field id selects one of these statements. It is never copied into the SQL text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database;

final class LayoutColumnUpdate
{
    /**
     * Identity columns are refused. A layout field must not rewrite the row key.
     *
     * @var array<string, string>
     */
    private const PATIENT = [
        'title' => 'UPDATE patient_data SET `title` = ? WHERE pid = ?',
        'language' => 'UPDATE patient_data SET `language` = ? WHERE pid = ?',
        'financial' => 'UPDATE patient_data SET `financial` = ? WHERE pid = ?',
        'fname' => 'UPDATE patient_data SET `fname` = ? WHERE pid = ?',
        'lname' => 'UPDATE patient_data SET `lname` = ? WHERE pid = ?',
        'mname' => 'UPDATE patient_data SET `mname` = ? WHERE pid = ?',
        'DOB' => 'UPDATE patient_data SET `DOB` = ? WHERE pid = ?',
        'street' => 'UPDATE patient_data SET `street` = ? WHERE pid = ?',
        'postal_code' => 'UPDATE patient_data SET `postal_code` = ? WHERE pid = ?',
        'city' => 'UPDATE patient_data SET `city` = ? WHERE pid = ?',
        'state' => 'UPDATE patient_data SET `state` = ? WHERE pid = ?',
        'country_code' => 'UPDATE patient_data SET `country_code` = ? WHERE pid = ?',
        'drivers_license' => 'UPDATE patient_data SET `drivers_license` = ? WHERE pid = ?',
        'ss' => 'UPDATE patient_data SET `ss` = ? WHERE pid = ?',
        'occupation' => 'UPDATE patient_data SET `occupation` = ? WHERE pid = ?',
        'phone_home' => 'UPDATE patient_data SET `phone_home` = ? WHERE pid = ?',
        'phone_biz' => 'UPDATE patient_data SET `phone_biz` = ? WHERE pid = ?',
        'phone_contact' => 'UPDATE patient_data SET `phone_contact` = ? WHERE pid = ?',
        'phone_cell' => 'UPDATE patient_data SET `phone_cell` = ? WHERE pid = ?',
        'pharmacy_id' => 'UPDATE patient_data SET `pharmacy_id` = ? WHERE pid = ?',
        'status' => 'UPDATE patient_data SET `status` = ? WHERE pid = ?',
        'contact_relationship' => 'UPDATE patient_data SET `contact_relationship` = ? WHERE pid = ?',
        'date' => 'UPDATE patient_data SET `date` = ? WHERE pid = ?',
        'sex' => 'UPDATE patient_data SET `sex` = ? WHERE pid = ?',
        'referrer' => 'UPDATE patient_data SET `referrer` = ? WHERE pid = ?',
        'referrerID' => 'UPDATE patient_data SET `referrerID` = ? WHERE pid = ?',
        'providerID' => 'UPDATE patient_data SET `providerID` = ? WHERE pid = ?',
        'ref_providerID' => 'UPDATE patient_data SET `ref_providerID` = ? WHERE pid = ?',
        'email' => 'UPDATE patient_data SET `email` = ? WHERE pid = ?',
        'email_direct' => 'UPDATE patient_data SET `email_direct` = ? WHERE pid = ?',
        'ethnoracial' => 'UPDATE patient_data SET `ethnoracial` = ? WHERE pid = ?',
        'race' => 'UPDATE patient_data SET `race` = ? WHERE pid = ?',
        'ethnicity' => 'UPDATE patient_data SET `ethnicity` = ? WHERE pid = ?',
        'religion' => 'UPDATE patient_data SET `religion` = ? WHERE pid = ?',
        'interpreter' => 'UPDATE patient_data SET `interpreter` = ? WHERE pid = ?',
        'interpreter_needed' => 'UPDATE patient_data SET `interpreter_needed` = ? WHERE pid = ?',
        'migrantseasonal' => 'UPDATE patient_data SET `migrantseasonal` = ? WHERE pid = ?',
        'family_size' => 'UPDATE patient_data SET `family_size` = ? WHERE pid = ?',
        'monthly_income' => 'UPDATE patient_data SET `monthly_income` = ? WHERE pid = ?',
        'billing_note' => 'UPDATE patient_data SET `billing_note` = ? WHERE pid = ?',
        'homeless' => 'UPDATE patient_data SET `homeless` = ? WHERE pid = ?',
        'financial_review' => 'UPDATE patient_data SET `financial_review` = ? WHERE pid = ?',
        'pubpid' => 'UPDATE patient_data SET `pubpid` = ? WHERE pid = ?',
        'genericname1' => 'UPDATE patient_data SET `genericname1` = ? WHERE pid = ?',
        'genericval1' => 'UPDATE patient_data SET `genericval1` = ? WHERE pid = ?',
        'genericname2' => 'UPDATE patient_data SET `genericname2` = ? WHERE pid = ?',
        'genericval2' => 'UPDATE patient_data SET `genericval2` = ? WHERE pid = ?',
        'hipaa_mail' => 'UPDATE patient_data SET `hipaa_mail` = ? WHERE pid = ?',
        'hipaa_voice' => 'UPDATE patient_data SET `hipaa_voice` = ? WHERE pid = ?',
        'hipaa_notice' => 'UPDATE patient_data SET `hipaa_notice` = ? WHERE pid = ?',
        'hipaa_message' => 'UPDATE patient_data SET `hipaa_message` = ? WHERE pid = ?',
        'hipaa_allowsms' => 'UPDATE patient_data SET `hipaa_allowsms` = ? WHERE pid = ?',
        'hipaa_allowemail' => 'UPDATE patient_data SET `hipaa_allowemail` = ? WHERE pid = ?',
        'squad' => 'UPDATE patient_data SET `squad` = ? WHERE pid = ?',
        'fitness' => 'UPDATE patient_data SET `fitness` = ? WHERE pid = ?',
        'referral_source' => 'UPDATE patient_data SET `referral_source` = ? WHERE pid = ?',
        'usertext1' => 'UPDATE patient_data SET `usertext1` = ? WHERE pid = ?',
        'usertext2' => 'UPDATE patient_data SET `usertext2` = ? WHERE pid = ?',
        'usertext3' => 'UPDATE patient_data SET `usertext3` = ? WHERE pid = ?',
        'usertext4' => 'UPDATE patient_data SET `usertext4` = ? WHERE pid = ?',
        'usertext5' => 'UPDATE patient_data SET `usertext5` = ? WHERE pid = ?',
        'usertext6' => 'UPDATE patient_data SET `usertext6` = ? WHERE pid = ?',
        'usertext7' => 'UPDATE patient_data SET `usertext7` = ? WHERE pid = ?',
        'usertext8' => 'UPDATE patient_data SET `usertext8` = ? WHERE pid = ?',
        'userlist1' => 'UPDATE patient_data SET `userlist1` = ? WHERE pid = ?',
        'userlist2' => 'UPDATE patient_data SET `userlist2` = ? WHERE pid = ?',
        'userlist3' => 'UPDATE patient_data SET `userlist3` = ? WHERE pid = ?',
        'userlist4' => 'UPDATE patient_data SET `userlist4` = ? WHERE pid = ?',
        'userlist5' => 'UPDATE patient_data SET `userlist5` = ? WHERE pid = ?',
        'userlist6' => 'UPDATE patient_data SET `userlist6` = ? WHERE pid = ?',
        'userlist7' => 'UPDATE patient_data SET `userlist7` = ? WHERE pid = ?',
        'pricelevel' => 'UPDATE patient_data SET `pricelevel` = ? WHERE pid = ?',
        'regdate' => 'UPDATE patient_data SET `regdate` = ? WHERE pid = ?',
        'contrastart' => 'UPDATE patient_data SET `contrastart` = ? WHERE pid = ?',
        'completed_ad' => 'UPDATE patient_data SET `completed_ad` = ? WHERE pid = ?',
        'ad_reviewed' => 'UPDATE patient_data SET `ad_reviewed` = ? WHERE pid = ?',
        'advance_directive_user_authenticator' => 'UPDATE patient_data SET `advance_directive_user_authenticator` = ? WHERE pid = ?',
        'vfc' => 'UPDATE patient_data SET `vfc` = ? WHERE pid = ?',
        'mothersname' => 'UPDATE patient_data SET `mothersname` = ? WHERE pid = ?',
        'guardiansname' => 'UPDATE patient_data SET `guardiansname` = ? WHERE pid = ?',
        'allow_imm_reg_use' => 'UPDATE patient_data SET `allow_imm_reg_use` = ? WHERE pid = ?',
        'allow_imm_info_share' => 'UPDATE patient_data SET `allow_imm_info_share` = ? WHERE pid = ?',
        'allow_health_info_ex' => 'UPDATE patient_data SET `allow_health_info_ex` = ? WHERE pid = ?',
        'allow_patient_portal' => 'UPDATE patient_data SET `allow_patient_portal` = ? WHERE pid = ?',
        'deceased_date' => 'UPDATE patient_data SET `deceased_date` = ? WHERE pid = ?',
        'deceased_reason' => 'UPDATE patient_data SET `deceased_reason` = ? WHERE pid = ?',
        'soap_import_status' => 'UPDATE patient_data SET `soap_import_status` = ? WHERE pid = ?',
        'cmsportal_login' => 'UPDATE patient_data SET `cmsportal_login` = ? WHERE pid = ?',
        'care_team_provider' => 'UPDATE patient_data SET `care_team_provider` = ? WHERE pid = ?',
        'care_team_facility' => 'UPDATE patient_data SET `care_team_facility` = ? WHERE pid = ?',
        'care_team_status' => 'UPDATE patient_data SET `care_team_status` = ? WHERE pid = ?',
        'county' => 'UPDATE patient_data SET `county` = ? WHERE pid = ?',
        'industry' => 'UPDATE patient_data SET `industry` = ? WHERE pid = ?',
        'imm_reg_status' => 'UPDATE patient_data SET `imm_reg_status` = ? WHERE pid = ?',
        'imm_reg_stat_effdate' => 'UPDATE patient_data SET `imm_reg_stat_effdate` = ? WHERE pid = ?',
        'publicity_code' => 'UPDATE patient_data SET `publicity_code` = ? WHERE pid = ?',
        'publ_code_eff_date' => 'UPDATE patient_data SET `publ_code_eff_date` = ? WHERE pid = ?',
        'protect_indicator' => 'UPDATE patient_data SET `protect_indicator` = ? WHERE pid = ?',
        'prot_indi_effdate' => 'UPDATE patient_data SET `prot_indi_effdate` = ? WHERE pid = ?',
        'guardianrelationship' => 'UPDATE patient_data SET `guardianrelationship` = ? WHERE pid = ?',
        'guardiansex' => 'UPDATE patient_data SET `guardiansex` = ? WHERE pid = ?',
        'guardianaddress' => 'UPDATE patient_data SET `guardianaddress` = ? WHERE pid = ?',
        'guardiancity' => 'UPDATE patient_data SET `guardiancity` = ? WHERE pid = ?',
        'guardianstate' => 'UPDATE patient_data SET `guardianstate` = ? WHERE pid = ?',
        'guardianpostalcode' => 'UPDATE patient_data SET `guardianpostalcode` = ? WHERE pid = ?',
        'guardiancountry' => 'UPDATE patient_data SET `guardiancountry` = ? WHERE pid = ?',
        'guardianphone' => 'UPDATE patient_data SET `guardianphone` = ? WHERE pid = ?',
        'guardianworkphone' => 'UPDATE patient_data SET `guardianworkphone` = ? WHERE pid = ?',
        'guardianemail' => 'UPDATE patient_data SET `guardianemail` = ? WHERE pid = ?',
        'sexual_orientation' => 'UPDATE patient_data SET `sexual_orientation` = ? WHERE pid = ?',
        'gender_identity' => 'UPDATE patient_data SET `gender_identity` = ? WHERE pid = ?',
        'birth_fname' => 'UPDATE patient_data SET `birth_fname` = ? WHERE pid = ?',
        'birth_lname' => 'UPDATE patient_data SET `birth_lname` = ? WHERE pid = ?',
        'birth_mname' => 'UPDATE patient_data SET `birth_mname` = ? WHERE pid = ?',
        'dupscore' => 'UPDATE patient_data SET `dupscore` = ? WHERE pid = ?',
        'name_history' => 'UPDATE patient_data SET `name_history` = ? WHERE pid = ?',
        'suffix' => 'UPDATE patient_data SET `suffix` = ? WHERE pid = ?',
        'street_line_2' => 'UPDATE patient_data SET `street_line_2` = ? WHERE pid = ?',
        'patient_groups' => 'UPDATE patient_data SET `patient_groups` = ? WHERE pid = ?',
        'prevent_portal_apps' => 'UPDATE patient_data SET `prevent_portal_apps` = ? WHERE pid = ?',
        'provider_since_date' => 'UPDATE patient_data SET `provider_since_date` = ? WHERE pid = ?',
        'created_by' => 'UPDATE patient_data SET `created_by` = ? WHERE pid = ?',
        'updated_by' => 'UPDATE patient_data SET `updated_by` = ? WHERE pid = ?',
        'preferred_name' => 'UPDATE patient_data SET `preferred_name` = ? WHERE pid = ?',
        'nationality_country' => 'UPDATE patient_data SET `nationality_country` = ? WHERE pid = ?',
        'last_updated' => 'UPDATE patient_data SET `last_updated` = ? WHERE pid = ?',
        'tribal_affiliations' => 'UPDATE patient_data SET `tribal_affiliations` = ? WHERE pid = ?',
        'sex_identified' => 'UPDATE patient_data SET `sex_identified` = ? WHERE pid = ?',
        'pronoun' => 'UPDATE patient_data SET `pronoun` = ? WHERE pid = ?',
    ];

    /**
     * @var array<string, string>
     */
    private const ENCOUNTER = [
        'date' => 'UPDATE form_encounter SET `date` = ? WHERE pid = ? AND encounter = ?',
        'reason' => 'UPDATE form_encounter SET `reason` = ? WHERE pid = ? AND encounter = ?',
        'facility' => 'UPDATE form_encounter SET `facility` = ? WHERE pid = ? AND encounter = ?',
        'facility_id' => 'UPDATE form_encounter SET `facility_id` = ? WHERE pid = ? AND encounter = ?',
        'onset_date' => 'UPDATE form_encounter SET `onset_date` = ? WHERE pid = ? AND encounter = ?',
        'sensitivity' => 'UPDATE form_encounter SET `sensitivity` = ? WHERE pid = ? AND encounter = ?',
        'billing_note' => 'UPDATE form_encounter SET `billing_note` = ? WHERE pid = ? AND encounter = ?',
        'pc_catid' => 'UPDATE form_encounter SET `pc_catid` = ? WHERE pid = ? AND encounter = ?',
        'last_level_billed' => 'UPDATE form_encounter SET `last_level_billed` = ? WHERE pid = ? AND encounter = ?',
        'last_level_closed' => 'UPDATE form_encounter SET `last_level_closed` = ? WHERE pid = ? AND encounter = ?',
        'last_stmt_date' => 'UPDATE form_encounter SET `last_stmt_date` = ? WHERE pid = ? AND encounter = ?',
        'stmt_count' => 'UPDATE form_encounter SET `stmt_count` = ? WHERE pid = ? AND encounter = ?',
        'provider_id' => 'UPDATE form_encounter SET `provider_id` = ? WHERE pid = ? AND encounter = ?',
        'supervisor_id' => 'UPDATE form_encounter SET `supervisor_id` = ? WHERE pid = ? AND encounter = ?',
        'invoice_refno' => 'UPDATE form_encounter SET `invoice_refno` = ? WHERE pid = ? AND encounter = ?',
        'referral_source' => 'UPDATE form_encounter SET `referral_source` = ? WHERE pid = ? AND encounter = ?',
        'billing_facility' => 'UPDATE form_encounter SET `billing_facility` = ? WHERE pid = ? AND encounter = ?',
        'external_id' => 'UPDATE form_encounter SET `external_id` = ? WHERE pid = ? AND encounter = ?',
        'pos_code' => 'UPDATE form_encounter SET `pos_code` = ? WHERE pid = ? AND encounter = ?',
        'parent_encounter_id' => 'UPDATE form_encounter SET `parent_encounter_id` = ? WHERE pid = ? AND encounter = ?',
        'class_code' => 'UPDATE form_encounter SET `class_code` = ? WHERE pid = ? AND encounter = ?',
        'shift' => 'UPDATE form_encounter SET `shift` = ? WHERE pid = ? AND encounter = ?',
        'voucher_number' => 'UPDATE form_encounter SET `voucher_number` = ? WHERE pid = ? AND encounter = ?',
        'discharge_disposition' => 'UPDATE form_encounter SET `discharge_disposition` = ? WHERE pid = ? AND encounter = ?',
        'encounter_type_code' => 'UPDATE form_encounter SET `encounter_type_code` = ? WHERE pid = ? AND encounter = ?',
        'encounter_type_description' => 'UPDATE form_encounter SET `encounter_type_description` = ? WHERE pid = ? AND encounter = ?',
        'referring_provider_id' => 'UPDATE form_encounter SET `referring_provider_id` = ? WHERE pid = ? AND encounter = ?',
        'date_end' => 'UPDATE form_encounter SET `date_end` = ? WHERE pid = ? AND encounter = ?',
        'in_collection' => 'UPDATE form_encounter SET `in_collection` = ? WHERE pid = ? AND encounter = ?',
        'last_update' => 'UPDATE form_encounter SET `last_update` = ? WHERE pid = ? AND encounter = ?',
        'ordering_provider_id' => 'UPDATE form_encounter SET `ordering_provider_id` = ? WHERE pid = ? AND encounter = ?',
    ];

    public static function patientStatement(string $fieldId): string
    {
        return self::statement($fieldId, self::PATIENT);
    }

    public static function encounterStatement(string $fieldId): string
    {
        return self::statement($fieldId, self::ENCOUNTER);
    }

    /**
     * @param array<string, string> $statements
     */
    private static function statement(string $fieldId, array $statements): string
    {
        $statement = $statements[$fieldId] ?? null;
        if (!is_string($statement)) {
            throw new SqlQueryException('', 'The layout column cannot be saved.');
        }

        return $statement;
    }
}
