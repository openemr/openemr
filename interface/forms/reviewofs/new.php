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
* @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Review of Systems Checks"); ?></title>

    <?php Header::setupHeader();?>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt("Review of Systems Checks");?></h2>
                <form method="post" action="<?php echo $rootdir;?>/forms/reviewofs/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('General')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="fever" name='fever' />
                                                <label class="form-check-label" for="fever"><?php echo xlt('Fever');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="chills" name='chills' />
                                                <label class="form-check-label" for="chills"><?php echo xlt('Chills');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="night_sweats" name='night_sweats' />
                                                <label class="form-check-label" for="night_sweats"><?php echo xlt('Night Sweats');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="weight_loss" name='weight_loss' />
                                                <label class="form-check-label" for="weight_loss"><?php echo xlt('Weight Loss');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="poor_appetite" name='poor_appetite' />
                                                <label class="form-check-label" for="poor_appetite"><?php echo xlt('Poor Appetite');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="insomnia" name='insomnia' />
                                                <label class="form-check-label" for="insomnia"><?php echo xlt('Insomnia');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="fatigued" name='fatigued' />
                                                <label class="form-check-label" for="fatigued"><?php echo xlt('Fatigued');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="depressed" name='depressed' />
                                                <label class="form-check-label" for="depressed"><?php echo xlt('Depressed');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hyperactive" name='hyperactive' />
                                                <label class="form-check-label" for="hyperactive"><?php echo xlt('Hyperactive');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="exposure_to_foreign_countries" name='exposure_to_foreign_countries' />
                                                <label class="form-check-label" for="exposure_to_foreign_countries"><?php echo xlt('Exposure to Foreign Countries');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Skin')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="rashes" name='rashes' />
                                                <label class="form-check-label" for="rashes"><?php echo xlt('Rashes');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="infections" name='infections' />
                                                <label class="form-check-label" for="infections"><?php echo xlt('Infections');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ulcerations" name='ulcerations' />
                                                <label class="form-check-label" for="ulcerations"><?php echo xlt('Ulcerations');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="pemphigus" name='pemphigus' />
                                                <label class="form-check-label" for="pemphigus"><?php echo xlt('Pemphigus');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="herpes" name='pemphigus' />
                                                <label class="form-check-label" for="herpes"><?php echo xlt('Herpes');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('HEENT')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cataracts" name='cataracts' />
                                                <label class="form-check-label" for="cataracts"><?php echo xlt('Cataracts');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cataract_surgery" name='cataract_surgery' />
                                                <label class="form-check-label" for="cataract_surgery"><?php echo xlt('Cataract Surgery');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="glaucoma" name='glaucoma' />
                                                <label class="form-check-label" for="glaucoma"><?php echo xlt('Glaucoma');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="double_vision" name='double_vision' />
                                                <label class="form-check-label" for="double_vision"><?php echo xlt('Double Vision');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="blurred_vision" name='blurred_vision' />
                                                <label class="form-check-label" for="blurred_vision"><?php echo xlt('Blurred Vision');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="poor_hearing" name='poor_hearing' />
                                                <label class="form-check-label" for="poor_hearing"><?php echo xlt('Poor Hearing');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="headaches" name='headaches' />
                                                <label class="form-check-label" for="headaches"><?php echo xlt('Headaches');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ringing_in_ears" name='ringing_in_ears' />
                                                <label class="form-check-label" for="ringing_in_ears"><?php echo xlt('Ringing in Ears');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="bloody_nose" name='bloody_nose' />
                                                <label class="form-check-label" for="bloody_nose"><?php echo xlt('Bloody Nose');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="sinusitis" name='sinusitis' />
                                                <label class="form-check-label" for="sinusitis"><?php echo xlt('Sinusitis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="sinus_surgery" name='sinus_surgery' />
                                                <label class="form-check-label" for="sinus_surgery"><?php echo xlt('Sinus Surgery');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="dry_mouth" name='dry_mouth' />
                                                <label class="form-check-label" for="dry_mouth"><?php echo xlt('Dry Mouth');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="strep_throat" name='strep_throat' />
                                                <label class="form-check-label" for="strep_throat"><?php echo xlt('Strep Throat');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="tonsillectomy" name='tonsillectomy' />
                                                <label class="form-check-label" for="tonsillectomy"><?php echo xlt('Tonsillectomy');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="swollen_lymph_nodes" name='swollen_lymph_nodes' />
                                                <label class="form-check-label" for="swollen_lymph_nodes"><?php echo xlt('Swollen Lymph Nodes');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="throat_cancer" name='throat_cancer' />
                                                <label class="form-check-label" for="throat_cancer"><?php echo xlt('Throat Cancer');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="throat_cancer_surgery" name='throat_cancer_surgery' />
                                                <label class="form-check-label" for="throat_cancer_surgery"><?php echo xlt('Throat Cancer Surgery');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Pulmonary')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="emphysema" name='emphysema' />
                                                <label class="form-check-label" for="emphysema"><?php echo xlt('Emphysema');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="chronic_bronchitis" name='chronic_bronchitis' />
                                                <label class="form-check-label" for="chronic_bronchitis"><?php echo xlt('Chronic Bronchitis');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="interstitial_lung_disease" name='interstitial_lung_disease' />
                                                <label class="form-check-label" for="interstitial_lung_disease"><?php echo xlt('Interstitial Lung Disease');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="shortness_of_breath_2" name='shortness_of_breath_2' />
                                                <label class="form-check-label" for="shortness_of_breath_2"><?php echo xlt('Shortness of Breath');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="lung_cancer" name='lung_cancer' />
                                                <label class="form-check-label" for="lung_cancer"><?php echo xlt('Lung Cancer');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="lung_cancer_surgery" name='lung_cancer_surgery' />
                                                <label class="form-check-label" for="lung_cancer_surgery"><?php echo xlt('Lung Cancer Surgery');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="pheumothorax" name='pheumothorax' />
                                                <label class="form-check-label" for="pheumothorax"><?php echo xlt('Pheumothorax');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Cardiovascular')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="heart_attack" name='heart_attack' />
                                                <label class="form-check-label" for="heart_attack"><?php echo xlt('Heart Attack');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="irregular_heart_beat" name='irregular_heart_beat' />
                                                <label class="form-check-label" for="irregular_heart_beat"><?php echo xlt('Irregular Heart Beat');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="chest_pains" name='chest_pains' />
                                                <label class="form-check-label" for="chest_pains"><?php echo xlt('Chest Pains');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="shortness_of_breath" name='shortness_of_breath' />
                                                <label class="form-check-label" for="shortness_of_breath"><?php echo xlt('Shortness of Breath');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="high_blood_pressure" name='high_blood_pressure' />
                                                <label class="form-check-label" for="high_blood_pressure"><?php echo xlt('High Blood Pressure');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="heart_failure" name='heart_failure' />
                                                <label class="form-check-label" for="heart_failure"><?php echo xlt('Heart Failure');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="poor_circulation" name='poor_circulation' />
                                                <label class="form-check-label" for="poor_circulation"><?php echo xlt('Poor Circulation');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="vascular_surgery" name='vascular_surgery' />
                                                <label class="form-check-label" for="vascular_surgery"><?php echo xlt('Vascular Surgery');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cardiac_catheterization" name='cardiac_catheterization' />
                                                <label class="form-check-label" for="cardiac_catheterization"><?php echo xlt('Cardiac Catheterization');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="coronary_artery_bypass" name='coronary_artery_bypass' />
                                                <label class="form-check-label" for="coronary_artery_bypass"><?php echo xlt('Coronary Artery Bypass');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="heart_transplant" name='heart_transplant' />
                                                <label class="form-check-label" for="heart_transplant"><?php echo xlt('Heart Transplant');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="stress_test" name='stress_test' />
                                                <label class="form-check-label" for="stress_test"><?php echo xlt('Stress Test');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Gastrointestinal')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="stomach_pains" name='stomach_pains' />
                                                <label class="form-check-label" for="stomach_pains"><?php echo xlt('Stomach Pains');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="peptic_ulcer_disease" name='peptic_ulcer_disease' />
                                                <label class="form-check-label" for="peptic_ulcer_disease"><?php echo xlt('Peptic Ulcer Disease');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="gastritis" name='gastritis' />
                                                <label class="form-check-label" for="gastritis"><?php echo xlt('Gastritis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="endoscopy" name='endoscopy' />
                                                <label class="form-check-label" for="endoscopy"><?php echo xlt('Endoscopy');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="polyps" name='polyps' />
                                                <label class="form-check-label" for="polyps"><?php echo xlt('Polyps');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="colonoscopy" name='colonoscopy' />
                                                <label class="form-check-label" for="colonoscopy"><?php echo xlt('Colonoscopy');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="colon_cancer" name='colon_cancer' />
                                                <label class="form-check-label" for="colon_cancer"><?php echo xlt('Colon Cancer');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="colon_cancer_surgery" name='colon_cancer_surgery' />
                                                <label class="form-check-label" for="colon_cancer_surgery"><?php echo xlt('Colon Cancer Surgery');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ulcerative_colitis" name='ulcerative_colitis' />
                                                <label class="form-check-label" for="ulcerative_colitis"><?php echo xlt('Ulcerative Colitis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="crohns_disease" name='crohns_disease' />
                                                <label class="form-check-label" for="crohns_disease"><?php echo xlt('Crohn\'s Disease');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="appendectomy" name='appendectomy' />
                                                <label class="form-check-label" for="appendectomy"><?php echo xlt('Appendectomy');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="divirticulitis" name='divirticulitis' />
                                                <label class="form-check-label" for="divirticulitis"><?php echo xlt('Diverticulitis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="divirticulitis_surgery" name='divirticulitis_surgery' />
                                                <label class="form-check-label" for="divirticulitis_surgery"><?php echo xlt('Diverticulitis Surgery');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="gall_stones" name='gall_stones' />
                                                <label class="form-check-label" for="gall_stones"><?php echo xlt('Gall Stones');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cholecystectomy" name='cholecystectomy' />
                                                <label class="form-check-label" for="cholecystectomy"><?php echo xlt('Cholecystectomy');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hepatitis" name='hepatitis' />
                                                <label class="form-check-label" for="hepatitis"><?php echo xlt('Hepatitis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cirrhosis_of_the_liver" name='cirrhosis_of_the_liver' />
                                                <label class="form-check-label" for="cirrhosis_of_the_liver"><?php echo xlt('Cirrhosis of the Liver');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="splenectomy" name='splenectomy' />
                                                <label class="form-check-label" for="splenectomy"><?php echo xlt('Splenectomy');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Genitourinary')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="kidney_failure" name='kidney_failure' />
                                                <label class="form-check-label" for="kidney_failure"><?php echo xlt('Kidney Failure');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="kidney_stones" name='kidney_stones' />
                                                <label class="form-check-label" for="kidney_stones"><?php echo xlt('Kidney Stones');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="kidney_cancer" name='kidney_cancer' />
                                                <label class="form-check-label" for="kidney_cancer"><?php echo xlt('Kidney Cancer');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="kidney_infections" name='kidney_infections' />
                                                <label class="form-check-label" for="kidney_infections"><?php echo xlt('Kidney Infections');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="bladder_infections" name='bladder_infections' />
                                                <label class="form-check-label" for="bladder_infections"><?php echo xlt('Bladder Infections');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="bladder_cancer" name='bladder_cancer' />
                                                <label class="form-check-label" for="bladder_cancer"><?php echo xlt('Bladder Cancer');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="prostate_problems" name='prostate_problems' />
                                                <label class="form-check-label" for="prostate_problems"><?php echo xlt('Prostate Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="prostate_cancer" name='prostate_cancer' />
                                                <label class="form-check-label" for="prostate_cancer"><?php echo xlt('Prostate Cancer');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="kidney_transplant" name='kidney_transplant' />
                                                <label class="form-check-label" for="kidney_transplant"><?php echo xlt('Kidney Transplant');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="sexually_transmitted_disease" name='sexually_transmitted_disease' />
                                                <label class="form-check-label" for="sexually_transmitted_disease"><?php echo xlt('Sexually Transmitted Disease');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="burning_with_urination" name='burning_with_urination' />
                                                <label class="form-check-label" for="burning_with_urination"><?php echo xlt('Burning with Urination');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="discharge_from_urethra" name='discharge_from_urethra' />
                                                <label class="form-check-label" for="discharge_from_urethra"><?php echo xlt('Discharge From Urethra');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Musculoskeletal')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="osetoarthritis" name='osetoarthritis' />
                                                <label class="form-check-label" for="osetoarthritis"><?php echo xlt('Osetoarthritis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="rheumotoid_arthritis" name='rheumotoid_arthritis' />
                                                <label class="form-check-label" for="rheumotoid_arthritis"><?php echo xlt('Rheumotoid Arthritis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="lupus" name='lupus' />
                                                <label class="form-check-label" for="lupus"><?php echo xlt('Lupus');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ankylosing_sondlilitis" name='ankylosing_sondlilitis' />
                                                <label class="form-check-label" for="ankylosing_sondlilitis"><?php echo xlt('Ankylosing Spondlilitis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="swollen_joints" name='swollen_joints' />
                                                <label class="form-check-label" for="swollen_joints"><?php echo xlt('Swollen Joints');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="stiff_joints" name='stiff_joints' />
                                                <label class="form-check-label" for="stiff_joints"><?php echo xlt('Stiff Joints');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="broken_bones" name='broken_bones' />
                                                <label class="form-check-label" for="broken_bones"><?php echo xlt('Broken Bones');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="neck_problems" name='neck_problems' />
                                                <label class="form-check-label" for="neck_problems"><?php echo xlt('Neck Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="back_problems" name='back_problems' />
                                                <label class="form-check-label" for="back_problems"><?php echo xlt('Back Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="back_surgery" name='back_surgery' />
                                                <label class="form-check-label" for="back_surgery"><?php echo xlt('Back Surgery');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="scoliosis" name='scoliosis' />
                                                <label class="form-check-label" for="scoliosis"><?php echo xlt('Scoliosis');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="herniated_disc" name='herniated_disc' />
                                                <label class="form-check-label" for="herniated_disc"><?php echo xlt('Herniated Disc');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="shoulder_problems" name='shoulder_problems' />
                                                <label class="form-check-label" for="shoulder_problems"><?php echo xlt('Shoulder Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="elbow_problems" name='elbow_problems' />
                                                <label class="form-check-label" for="elbow_problems"><?php echo xlt('Elbow Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="wrist_problems" name='wrist_problems' />
                                                <label class="form-check-label" for="wrist_problems"><?php echo xlt('Wrist Problems');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hand_problems" name='hand_problems' />
                                                <label class="form-check-label" for="hand_problems"><?php echo xlt('Hand Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hip_problems" name='hip_problems' />
                                                <label class="form-check-label" for="hip_problems"><?php echo xlt('Hip Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="knee_problems" name='knee_problems' />
                                                <label class="form-check-label" for="knee_problems"><?php echo xlt('Knee Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="ankle_problems" name='ankle_problems' />
                                                <label class="form-check-label" for="ankle_problems"><?php echo xlt('Ankle Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="foot_problems" name='foot_problems' />
                                                <label class="form-check-label" for="foot_problems"><?php echo xlt('Foot Problems');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Endocrine')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="insulin_dependent_diabetes" name='insulin_dependent_diabetes' />
                                                <label class="form-check-label" for="insulin_dependent_diabetes"><?php echo xlt('Insulin Dependent Diabetes');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="noninsulin_dependent_diabetes" name='noninsulin_dependent_diabetes' />
                                                <label class="form-check-label" for="noninsulin_dependent_diabetes"><?php echo xlt('Non-Insulin Dependent Diabetes');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hypothyroidism" name='hypothyroidism' />
                                                <label class="form-check-label" for="hypothyroidism"><?php echo xlt('Hypothyroidism');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="hyperthyroidism" name='hyperthyroidism' />
                                                <label class="form-check-label" for="hyperthyroidism"><?php echo xlt('Hyperthyroidism');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="cushing_syndrom" name='cushing_syndrom' />
                                                <label class="form-check-label" for="cushing_syndrom"><?php echo xlt('Cushing Syndrome');?></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="addison_syndrom" name='addison_syndrom' />
                                                <label class="form-check-label" for="addison_syndrom"><?php echo xlt('Addison Syndrome');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Additional Notes');?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea name="additional_notes" class="form-control" cols="80" rows="5" ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 position-override">
                                <div class="btn-group" role="group">
                                    <button type="submit" onclick="top.restoreSession()" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
