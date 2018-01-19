<?php
/**
* Review of Systems Checks form
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    sunsetsystems <sunsetsystems>
* @author    cfapress <cfapress>
* @author    Brady Miller <brady.g.miller@gmail.com>
* @copyright Copyright (c) 2009 sunsetsystems <sunsetsystems>
* @copyright Copyright (c) 2008 cfapress <cfapress>
* @copyright Copyright (c) 2016-2017 Brady Miller <brady.g.miller@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/


require_once("../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Review of Systems Checks"); ?></title>

    <?php Header::setupHeader();?>
</head>
<body class="body_top">
<div class="container">
    <div class="row">
        <div class="">
            <div class="page-header">
                <h2><?php echo xlt("Review of Systems Checks");?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <form method=post action="<?php echo $rootdir;?>/forms/reviewofs/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
            <fieldset>
                <legend><?php echo xlt('General')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='fever'><?php echo xlt('Fever');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='chills'><?php echo xlt('Chills');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='night_sweats'><?php echo xlt('Night Sweats');?>
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='weight_loss'><?php echo xlt('Weight Loss');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='poor_appetite'><?php echo xlt('Poor Appetite');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='insomnia'><?php echo xlt('Insomnia');?>
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='fatigued'><?php echo xlt('Fatigued');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='depressed'><?php echo xlt('Depressed');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hyperactive'><?php echo xlt('Hyperactive');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='exposure_to_foreign_countries'><?php echo xlt('Exposure to Foreign Countries');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Skin')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='rashes'><?php echo xlt('Rashes');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='infections'><?php echo xlt('Infections');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='ulcerations'><?php echo xlt('Ulcerations');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='pemphigus'><?php echo xlt('Pemphigus');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='herpes'><?php echo xlt('Herpes');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('HEENT')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cataracts'><?php echo xlt('Cataracts');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cataract_surgery'><?php echo xlt('Cataract Surgery');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='glaucoma'><?php echo xlt('Glaucoma');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='double_vision'><?php echo xlt('Double Vision');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='blurred_vision'><?php echo xlt('Blurred Vision');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='poor_hearing'><?php echo xlt('Poor Hearing');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='headaches'><?php echo xlt('Headaches');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='ringing_in_ears'><?php echo xlt('Ringing in Ears');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='bloody_nose'><?php echo xlt('Bloody Nose');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='sinusitis'><?php echo xlt('Sinusitis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='sinus_surgery'><?php echo xlt('Sinus Surgery');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='dry_mouth'><?php echo xlt('Dry Mouth');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='strep_throat'><?php echo xlt('Strep Throat');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='tonsillectomy'><?php echo xlt('Tonsillectomy');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='swollen_lymph_nodes'><?php echo xlt('Swollen Lymph Nodes');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='throat_cancer'><?php echo xlt('Throat Cancer');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='throat_cancer_surgery'><?php echo xlt('Throat Cancer Surgery');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Pulmonary')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='emphysema'><?php echo xlt('Emphysema');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='chronic_bronchitis'><?php echo xlt('Chronic Bronchitis');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='interstitial_lung_disease'><?php echo xlt('Interstitial Lung Disease');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='shortness_of_breath_2'><?php echo xlt('Shortness of Breath');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='lung_cancer'><?php echo xlt('Lung Cancer');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='lung_cancer_surgery'><?php echo xlt('Lung Cancer Surgery');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='pheumothorax'><?php echo xlt('Pheumothorax');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Cardiovascular')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='heart_attack'><?php echo xlt('Heart Attack');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='irregular_heart_beat'><?php echo xlt('Irregular Heart Beat');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='chest_pains'><?php echo xlt('Chest Pains');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='shortness_of_breath'><?php echo xlt('Shortness of Breath');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='high_blood_pressure'><?php echo xlt('High Blood Pressure');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='heart_failure'><?php echo xlt('Heart Failure');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='poor_circulation'><?php echo xlt('Poor Circulation');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='vascular_surgery'><?php echo xlt('Vascular Surgery');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cardiac_catheterization'><?php echo xlt('Cardiac Catheterization');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='coronary_artery_bypass'><?php echo xlt('Coronary Artery Bypass');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='heart_transplant'><?php echo xlt('Heart Transplant');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='stress_test'><?php echo xlt('Stress Test');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Gastrointestinal')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='stomach_pains'><?php echo xlt('Stomach Pains');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='peptic_ulcer_disease'><?php echo xlt('Peptic Ulcer Disease');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='gastritis'><?php echo xlt('Gastritis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='endoscopy'><?php echo xlt('Endoscopy');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='polyps'><?php echo xlt('Polyps');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='colonoscopy'><?php echo xlt('Colonoscopy');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='colon_cancer'><?php echo xlt('Colon Cancer');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='colon_cancer_surgery'><?php echo xlt('Colon Cancer Surgery');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='ulcerative_colitis'><?php echo xlt('Ulcerative Colitis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='crohns_disease'><?php echo xlt('Crohn\'s Disease');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='appendectomy'><?php echo xlt('Appendectomy');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='divirticulitis'><?php echo xlt('Diverticulitis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='divirticulitis_surgery'><?php echo xlt('Diverticulitis Surgery');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='gall_stones'><?php echo xlt('Gall Stones');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cholecystectomy'><?php echo xlt('Cholecystectomy');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hepatitis'><?php echo xlt('Hepatitis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cirrhosis_of_the_liver'><?php echo xlt('Cirrhosis of the Liver');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='splenectomy'><?php echo xlt('Splenectomy');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Genitourinary')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='kidney_failure'><?php echo xlt('Kidney Failure');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='kidney_stones'><?php echo xlt('Kidney Stones');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='kidney_cancer'><?php echo xlt('Kidney Cancer');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='kidney_infections'><?php echo xlt('Kidney Infections');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='bladder_infections'><?php echo xlt('Bladder Infections');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='bladder_cancer'><?php echo xlt('Bladder Cancer');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='prostate_problems'><?php echo xlt('Prostate Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='prostate_cancer'><?php echo xlt('Prostate Cancer');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='kidney_transplant'><?php echo xlt('Kidney Transplant');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='sexually_transmitted_disease'><?php echo xlt('Sexually Transmitted Disease');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='burning_with_urination'><?php echo xlt('Burning with Urination');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='discharge_from_urethra'><?php echo xlt('Discharge From Urethra');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Musculoskeletal')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='osetoarthritis'><?php echo xlt('Osetoarthritis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='rheumotoid_arthritis'><?php echo xlt('Rheumotoid Arthritis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='lupus'><?php echo xlt('Lupus');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='ankylosing_sondlilitis'><?php echo xlt('Ankylosing Spondlilitis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='swollen_joints'><?php echo xlt('Swollen Joints');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='stiff_joints'><?php echo xlt('Stiff Joints');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='broken_bones'><?php echo xlt('Broken Bones');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='neck_problems'><?php echo xlt('Neck Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='back_problems'><?php echo xlt('Back Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='back_surgery'><?php echo xlt('Back Surgery');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='scoliosis'><?php echo xlt('Scoliosis');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='herniated_disc'><?php echo xlt('Herniated Disc');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='shoulder_problems'><?php echo xlt('Shoulder Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='elbow_problems'><?php echo xlt('Elbow Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='wrist_problems'><?php echo xlt('Wrist Problems');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hand_problems'><?php echo xlt('Hand Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hip_problems'><?php echo xlt('Hip Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='knee_problems'><?php echo xlt('Knee Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='ankle_problems'><?php echo xlt('Ankle Problems');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='foot_problems'><?php echo xlt('Foot Problems');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Endocrine')?></legend>
                <div class="row">
                     <div class="col-xs-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='insulin_dependent_diabetes'><?php echo xlt('Insulin Dependent Diabetes');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='noninsulin_dependent_diabetes'><?php echo xlt('Non-Insulin Dependent Diabetes');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hypothyroidism'><?php echo xlt('Hypothyroidism');?>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='hyperthyroidism'><?php echo xlt('Hyperthyroidism');?>
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='cushing_syndrom'><?php echo xlt('Cushing Syndrome');?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name='addison_syndrom'><?php echo xlt('Addison Syndrome');?>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </fieldset>
            <fieldset>
                    <legend><?php echo xlt('Additional Notes');?></legend>
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <textarea name="additional_notes" class="form-control" cols="80" rows="5" ></textarea>
                            </div>
                        </div>
            </fieldset>
                <div class="form-group clearfix">
                    <div class="col-sm-12 col-sm-offset-1 position-override">
                        <div class="btn-group oe-opt-btn-group-pinch" role="group">
                        <button type="submit" onclick="top.restoreSession()" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                        <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
