<?php
class ReportTypes
{
    const TYPE_INDEX = 0;
    const CLASS_INDEX = 1;
    
    const AMC = 'amc';
    const CQM = 'cqm';
    
    public static function getType( $ruleId ) {
        $type = self::$_types[$ruleId][self::TYPE_INDEX];
        return $type;
    }
    
    public static function getClassName( $ruleId ) {
        $class = self::$_types[$ruleId][self::CLASS_INDEX];
        return $class;
    }
    
    protected static $_types = array(
        "rule_htn_bp_measure_cqm" => array( ReportTypes::CQM, "NFQ_0013" ), 
        "rule_tob_use_assess_cqm" => array( ReportTypes::CQM, "NFQ_0028a" ),
    	"rule_tob_cess_inter_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_adult_wt_screen_fu_cqm" => array( ReportTypes::CQM, "NFQ_0421" ),
        "rule_wt_assess_couns_child_cqm" => array( ReportTypes::CQM, "NFQ_0024" ),
        "rule_influenza_ge_50_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_child_immun_stat_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_pneumovacc_ge_65_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
    	"rule_dm_eye_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_dm_foot_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_dm_bp_control_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
        "rule_dm_a1c_cqm" => array( ReportTypes::CQM, "NFQ_Unimplemented" ),
    	"problem_list_amc" => array( ReportTypes::AMC, "ProblemList" ),
    	"med_list_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"med_allergy_list_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"record_vitals_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"record_smoke_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"lab_result_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"med_reconc_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "patient_edu_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "cpoe_med_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "e_prescribe_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "record_dem_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "send_reminder_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "provide_rec_pat_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "timely_access_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
    	"provide_sum_pat_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" ),
        "send_sum_amc" => array( ReportTypes::AMC, "AMC_Unimplemented" )
    );
}