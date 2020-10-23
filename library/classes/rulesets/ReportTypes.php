<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class ReportTypes
{
    const TYPE_INDEX = 0;
    const CLASS_INDEX = 1;

    const AMC = 'amc';
    const CQM = 'cqm';

    public static function getType($ruleId)
    {
        $type = self::$_types[$ruleId][self::TYPE_INDEX];
        return $type;
    }

    public static function getClassName($ruleId)
    {
        $class = self::$_types[$ruleId][self::CLASS_INDEX];
        return $class;
    }

    protected static $_types = array(
        "rule_htn_bp_measure_cqm" => array( ReportTypes::CQM, "NQF_0013" ),
        "rule_tob_use_assess_cqm" => array( ReportTypes::CQM, "NQF_0028a" ),
        "rule_tob_cess_inter_cqm" => array( ReportTypes::CQM, "NQF_0028b" ),
        "rule_adult_wt_screen_fu_cqm" => array( ReportTypes::CQM, "NQF_0421" ),
        "rule_wt_assess_couns_child_cqm" => array( ReportTypes::CQM, "NQF_0024" ),
        "rule_influenza_ge_50_cqm" => array( ReportTypes::CQM, "NQF_0041" ),
        "rule_child_immun_stat_cqm" => array( ReportTypes::CQM, "NQF_0038" ),
        "rule_pneumovacc_ge_65_cqm" => array( ReportTypes::CQM, "NQF_0043" ),
        "rule_dm_eye_cqm" => array( ReportTypes::CQM, "NQF_Unimplemented" ),
        "rule_dm_foot_cqm" => array( ReportTypes::CQM, "NQF_Unimplemented" ),
        "rule_dm_bp_control_cqm" => array( ReportTypes::CQM, "NQF_Unimplemented" ),
        "rule_dm_a1c_cqm" => array( ReportTypes::CQM, "NQF_0059" ),
        "rule_dm_ldl_cqm" => array( ReportTypes::CQM, "NQF_0064" ),
        "rule_children_pharyngitis_cqm" => array( ReportTypes::CQM, "NQF_0002" ),
        "rule_fall_screening_cqm" => array( ReportTypes::CQM, "NQF_0101" ),
        "rule_pain_intensity_cqm" => array( ReportTypes::CQM, "NQF_0384" ),
        "rule_child_immun_stat_2014_cqm" => array( ReportTypes::CQM, "NQF_0038_2014" ), //MU-2014-CQM Immunization Status
        "rule_tob_use_2014_cqm" => array( ReportTypes::CQM, "NQF_0028_2014" ),
        "problem_list_amc" => array( ReportTypes::AMC, "AMC_302c" ), // MU-2014-AMC: 170.314(g)(1)/(2)–4
        "med_list_amc" => array( ReportTypes::AMC, "AMC_302d" ), // MU-2014-AMC: 170.314(g)(1)/(2)–5
        "med_allergy_list_amc" => array( ReportTypes::AMC, "AMC_302e" ), // MU-2014-AMC: 170.314(g)(1)/(2)–6
        "record_vitals_amc" => array( ReportTypes::AMC, "AMC_302f" ),
        "record_smoke_amc" => array( ReportTypes::AMC, "AMC_302g" ), // MU-2014-AMC: 170.314(g)(1)/(2)–11
        "lab_result_amc" => array( ReportTypes::AMC, "AMC_302h" ), // MU-2014-AMC: 170.314(g)(1)/(2)–12
        "med_reconc_amc" => array( ReportTypes::AMC, "AMC_302j" ), // MU-2014-AMC: 170.314(g)(1)/(2)–17
        "patient_edu_amc" => array( ReportTypes::AMC, "AMC_302m" ),
        "cpoe_med_amc" => array( ReportTypes::AMC, "AMC_304a" ),
        "e_prescribe_amc" => array( ReportTypes::AMC, "AMC_304b" ),
        "record_dem_amc" => array( ReportTypes::AMC, "AMC_304c" ), // MU-2014-AMC: 170.314(g)(1)/(2)–9
        "send_reminder_amc" => array( ReportTypes::AMC, "AMC_304d" ),
        "provide_rec_pat_amc" => array( ReportTypes::AMC, "AMC_304f" ),
        "timely_access_amc" => array( ReportTypes::AMC, "AMC_304g" ),
        "provide_sum_pat_amc" => array( ReportTypes::AMC, "AMC_304h" ),
        "send_sum_amc" => array( ReportTypes::AMC, "AMC_304i" ),
        "image_results_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_20" ),
        "family_health_history_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_21" ),
        "electronic_notes_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_22" ),
        "secure_messaging_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_19" ),
        "view_download_transmit_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_14" ),  //Stage 1&2 View Download Transmit
        "cpoe_radiology_amc" => array( ReportTypes::AMC, "AMC_304a_1" ),   //Stage 2 CPOE Radiology Orders
        "cpoe_proc_orders_amc" => array( ReportTypes::AMC, "AMC_304a_2" ), //Stage 2 CPOE Procedure Orders
        "cpoe_med_stage2_amc" => array( ReportTypes::AMC, "AMC_304a_3" ), //Stage 2 CPOE Medication Orders
        "cpoe_med_stage1_amc_alternative" => array( ReportTypes::AMC, "AMC_304a_3" ), //Stage 1 CPOE Medication Orders. Alternative
        "send_reminder_stage2_amc" => array( ReportTypes::AMC, "AMC_304d_STG2" ), //Stage 2 Patient Reminders
        "patient_edu_stage2_amc" => array( ReportTypes::AMC, "AMC_302m_STG2" ), //Stage 2 patient education
        "record_vitals_1_stage1_amc" => array( ReportTypes::AMC, "AMC_302f_1_STG1" ),//Stage 1 vitals set1
        "record_vitals_2_stage1_amc" => array( ReportTypes::AMC, "AMC_302f_2_STG1" ),//Stage 1 vitals set2
        "record_vitals_3_stage1_amc" => array( ReportTypes::AMC, "AMC_302f_3_STG1" ),//Stage 1 vitals set3
        "record_vitals_4_stage1_amc" => array( ReportTypes::AMC, "AMC_302f_4_STG1" ),//Stage 1 vitals set4
        "record_vitals_stage2_amc" => array( ReportTypes::AMC, "AMC_302f_STG2" ),//Stage 2 vitals
        "provide_sum_pat_stage2_amc" => array( ReportTypes::AMC, "AMC_304h_STG2" ), //Stage 2 Clinical Summary
        "vdt_stage2_amc" => array( ReportTypes::AMC, "AMC_314g_1_2_14_STG2" ),  //Stage 1&2 View Download Transmit
        "send_sum_stage1_amc" => array( ReportTypes::AMC, "AMC_304i_STG1" ), //Stage 1 Summary of care
        "send_sum_1_stage2_amc" => array( ReportTypes::AMC, "AMC_304i_STG1" ), //Stage 2 Summary of care Measure A(Same as Stage1)
        "send_sum_stage2_amc" => array( ReportTypes::AMC, "AMC_304i_STG2" ), //Stage 2 Summary of care Measure B
        "e_prescribe_stage1_amc" => array( ReportTypes::AMC, "AMC_304b_STG1" ),//Stage 1 eRx
        "e_prescribe_1_stage2_amc" => array( ReportTypes::AMC, "AMC_304b_1_STG2" ),//Stage 2 eRx(Controlled Substances)
        "e_prescribe_2_stage2_amc" => array( ReportTypes::AMC, "AMC_304b_2_STG2" ),//Stage 2 eRx(UnControlled Substances)
    );
}
