<?php

/**
 * Review of Systems Checks form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    cfapress <cfapress>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<html>
<head>
    <title><?php echo xlt("Review of Systems Checks"); ?></title>

    <?php Header::setupHeader();?>
</head>
<?php
$obj = formFetch("form_reviewofs", $_GET["id"]);
?>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt("Review of Systems Checks");?></h2>
            </div>
        </div>
        <div class="row">
            <form method=post action="<?php echo $rootdir; ?>/forms/reviewofs/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <fieldset>
                    <legend><?php echo xlt('General')?></legend>
                    <div class="row">
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="fever" <?php echo ($obj["fever"] == "on") ? "checked" : ""; ?>><?php echo xlt('Fever');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="chills" <?php echo ($obj["chills"] == "on") ? "checked" : ""; ?>><?php echo xlt('Chills');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="night_sweats" <?php echo ($obj["night_sweats"] == "on") ? "checked" : ""; ?>><?php echo xlt('Night Sweats');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="weight_loss" <?php echo ($obj["weight_loss"] == "on") ? "checked" : ""; ?>><?php echo xlt('Weight Loss');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="poor_appetite" <?php echo ($obj["poor_appetite"] == "on") ? "checked" : ""; ?>><?php echo xlt('Poor Appetite');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="insomnia" <?php echo ($obj["insomnia"] == "on") ? "checked" : ""; ?>><?php echo xlt('Insomnia');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="fatigued" <?php echo ($obj["fatigued"] == "on") ? "checked" : ""; ?>><?php echo xlt('Fatigued');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="depressed" <?php echo ($obj["depressed"] == "on") ? "checked" : ""; ?>><?php echo xlt('Depressed');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hyperactive" <?php echo ($obj["hyperactive"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hyperactive');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="exposure_to_foreign_countries" <?php echo ($obj["exposure_to_foreign_countries"] == "on") ? "checked" : ""; ?>><?php echo xlt('Exposure to Foreign Countries');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="rashes" <?php echo ($obj["rashes"] == "on") ? "checked" : ""; ?>><?php echo xlt('Rashes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="infections" <?php echo ($obj["infections"] == "on") ? "checked" : ""; ?>><?php echo xlt('Infections');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ulcerations" <?php echo ($obj["ulcerations"] == "on") ? "checked" : ""; ?>><?php echo xlt('Ulcerations');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="pemphigus" <?php echo ($obj["pemphigus"] == "on") ? "checked" : ""; ?>><?php echo xlt('Pemphigus');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="herpes" <?php echo ($obj["herpes"] == "on") ? "checked" : ""; ?>><?php echo xlt('Herpes');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cataracts" <?php echo ($obj["cataracts"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cataracts');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cataract_surgery" <?php echo ($obj["cataract_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cataract Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="glaucoma" <?php echo ($obj["glaucoma"] == "on") ? "checked" : ""; ?>><?php echo xlt('Glaucoma');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="double_vision" <?php echo ($obj["double_vision"] == "on") ? "checked" : ""; ?>><?php echo xlt('Double Vision');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="blurred_vision" <?php echo ($obj["blurred_vision"] == "on") ? "checked" : ""; ?>><?php echo xlt('Blurred Vision');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="poor_hearing" <?php echo ($obj["poor_hearing"] == "on") ? "checked" : ""; ?>><?php echo xlt('Poor Hearing');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="headaches" <?php echo ($obj["headaches"] == "on") ? "checked" : ""; ?>><?php echo xlt('Headaches');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ringing_in_ears" <?php echo ($obj["ringing_in_ears"] == "on") ? "checked" : ""; ?>><?php echo xlt('Ringing in Ears');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="bloody_nose" <?php echo ($obj["bloody_nose"] == "on") ? "checked" : ""; ?>><?php echo xlt('Bloody Nose');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="sinusitis" <?php echo ($obj["sinusitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Sinusitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="sinus_surgery" <?php echo ($obj["sinus_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Sinus Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="dry_mouth" <?php echo ($obj["dry_mouth"] == "on") ? "checked" : ""; ?>><?php echo xlt('Dry Mouth');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="strep_throat" <?php echo ($obj["strep_throat"] == "on") ? "checked" : ""; ?>><?php echo xlt('Strep Throat');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="tonsillectomy" <?php echo ($obj["tonsillectomy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Tonsillectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="swollen_lymph_nodes" <?php echo ($obj["swollen_lymph_nodes"] == "on") ? "checked" : ""; ?>><?php echo xlt('Swollen Lymph Nodes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="throat_cancer" <?php echo ($obj["throat_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Throat Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="throat_cancer_surgery" <?php echo ($obj["throat_cancer_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Throat Cancer Surgery');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="emphysema" <?php echo ($obj["emphysema"] == "on") ? "checked" : ""; ?>><?php echo xlt('Emphysema');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="chronic_bronchitis" <?php echo ($obj["chronic_bronchitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Chronic Bronchitis');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="interstitial_lung_disease" <?php echo ($obj["interstitial_lung_disease"] == "on") ? "checked" : ""; ?>><?php echo xlt('Interstitial Lung Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="shortness_of_breath_2" <?php echo ($obj["shortness_of_breath_2"] == "on") ? "checked" : ""; ?>><?php echo xlt('Shortness of Breath');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="lung_cancer" <?php echo ($obj["lung_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Lung Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="lung_cancer_surgery" <?php echo ($obj["lung_cancer_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Lung Cancer Surgery');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="pheumothorax" <?php echo ($obj["pheumothorax"] == "on") ? "checked" : ""; ?>><?php echo xlt('Pheumothorax');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="heart_attack" <?php echo ($obj["heart_attack"] == "on") ? "checked" : ""; ?>><?php echo xlt('Heart Attack');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="irregular_heart_beat" <?php echo ($obj["irregular_heart_beat"] == "on") ? "checked" : ""; ?>><?php echo xlt('Irregular Heart Beat');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="chest_pains" <?php echo ($obj["chest_pains"] == "on") ? "checked" : ""; ?>><?php echo xlt('Chest Pains');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="shortness_of_breath" <?php echo ($obj["shortness_of_breath"] == "on") ? "checked" : ""; ?>><?php echo xlt('Shortness of Breath');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="high_blood_pressure" <?php echo ($obj["high_blood_pressure"] == "on") ? "checked" : ""; ?>><?php echo xlt('High Blood Pressure');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="heart_failure" <?php echo ($obj["heart_failure"] == "on") ? "checked" : ""; ?>><?php echo xlt('Heart Failure');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="poor_circulation" <?php echo ($obj["poor_circulation"] == "on") ? "checked" : ""; ?>><?php echo xlt('Poor Circulation');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="vascular_surgery" <?php echo ($obj["vascular_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Vascular Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cardiac_catheterization" <?php echo ($obj["cardiac_catheterization"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cardiac Catheterization');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="heart_transplant" <?php echo ($obj["heart_transplant"] == "on") ? "checked" : ""; ?>><?php echo xlt('Heart Transplant');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="stress_test" <?php echo ($obj["stress_test"] == "on") ? "checked" : ""; ?>><?php echo xlt('Stress Test');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="stress_test" <?php echo ($obj["stress_test"] == "on") ? "checked" : ""; ?>><?php echo xlt('Stress Test');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="stomach_pains" <?php echo ($obj["stomach_pains"] == "on") ? "checked" : ""; ?>><?php echo xlt('Stomach Pains');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="peptic_ulcer_disease" <?php echo ($obj["peptic_ulcer_disease"] == "on") ? "checked" : ""; ?>><?php echo xlt('Peptic Ulcer Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="gastritis" <?php echo ($obj["gastritis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Gastritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="endoscopy" <?php echo ($obj["endoscopy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Endoscopy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="polyps" <?php echo ($obj["polyps"] == "on") ? "checked" : ""; ?>><?php echo xlt('Polyps');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="colonoscopy" <?php echo ($obj["colonoscopy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Colonoscopy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="colon_cancer" <?php echo ($obj["colon_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Colon Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="colon_cancer_surgery" <?php echo ($obj["colon_cancer_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Colon Cancer Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ulcerative_colitis" <?php echo ($obj["ulcerative_colitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Ulcerative Colitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="crohns_disease" <?php echo ($obj["crohns_disease"] == "on") ? "checked" : ""; ?>><?php echo xlt('Crohn\'s Disease');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="appendectomy" <?php echo ($obj["appendectomy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Appendectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="divirticulitis" <?php echo ($obj["divirticulitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Divirticulitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="divirticulitis_surgery" <?php echo ($obj["divirticulitis_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Diverticulitis Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="gall_stones" <?php echo ($obj["gall_stones"] == "on") ? "checked" : ""; ?>><?php echo xlt('Gall Stones');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cholecystectomy" <?php echo ($obj["cholecystectomy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cholecystectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hepatitis" <?php echo ($obj["hepatitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hepatitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cirrhosis_of_the_liver" <?php echo ($obj["cirrhosis_of_the_liver"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cirrhosis of the Liver');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="splenectomy" <?php echo ($obj["splenectomy"] == "on") ? "checked" : ""; ?>><?php echo xlt('Splenectomy');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="kidney_failure" <?php echo ($obj["kidney_failure"] == "on") ? "checked" : ""; ?>><?php echo xlt('Kidney Failure');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="kidney_stones" <?php echo ($obj["kidney_stones"] == "on") ? "checked" : ""; ?>><?php echo xlt('Kidney Stones');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="kidney_cancer" <?php echo ($obj["kidney_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Kidney Cancer');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="kidney_infections" <?php echo ($obj["kidney_infections"] == "on") ? "checked" : ""; ?>><?php echo xlt('Kidney Infections');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="bladder_infections" <?php echo ($obj["bladder_infections"] == "on") ? "checked" : ""; ?>><?php echo xlt('Bladder Infections');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="bladder_cancer" <?php echo ($obj["bladder_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Bladder Cancer');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="prostate_problems" <?php echo ($obj["prostate_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Prostate Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="prostate_cancer" <?php echo ($obj["prostate_cancer"] == "on") ? "checked" : ""; ?>><?php echo xlt('Prostate Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="kidney_transplant" <?php echo ($obj["kidney_transplant"] == "on") ? "checked" : ""; ?>><?php echo xlt('Kidney Transplant');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="sexually_transmitted_disease" <?php echo ($obj["sexually_transmitted_disease"] == "on") ? "checked" : ""; ?>><?php echo xlt('Sexually Transmitted Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="burning_with_urination" <?php echo ($obj["burning_with_urination"] == "on") ? "checked" : ""; ?>><?php echo xlt('Burning with Urination');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="discharge_from_urethra" <?php echo ($obj["discharge_from_urethra"] == "on") ? "checked" : ""; ?>><?php echo xlt('Discharge From Urethra');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="osetoarthritis" <?php echo ($obj["osetoarthritis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Osetoarthritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="rheumotoid_arthritis" <?php echo ($obj["rheumotoid_arthritis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Rheumatoid Arthritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="lupus" <?php echo ($obj["lupus"] == "on") ? "checked" : ""; ?>><?php echo xlt('Lupus');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ankylosing_sondlilitis" <?php echo ($obj["ankylosing_sondlilitis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Ankylosing Sondlilitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="swollen_joints" <?php echo ($obj["swollen_joints"] == "on") ? "checked" : ""; ?>><?php echo xlt('Swollen Joints');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="stiff_joints" <?php echo ($obj["stiff_joints"] == "on") ? "checked" : ""; ?>><?php echo xlt('Stiff Joints');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="broken_bones" <?php echo ($obj["broken_bones"] == "on") ? "checked" : ""; ?>><?php echo xlt('Broken Bones');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="neck_problems" <?php echo ($obj["neck_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Neck Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="back_problems" <?php echo ($obj["back_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Back Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="back_surgery" <?php echo ($obj["back_surgery"] == "on") ? "checked" : ""; ?>><?php echo xlt('Back Surgery');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="scoliosis" <?php echo ($obj["scoliosis"] == "on") ? "checked" : ""; ?>><?php echo xlt('Scoliosis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="herniated_disc" <?php echo ($obj["herniated_disc"] == "on") ? "checked" : ""; ?>><?php echo xlt('Herniated Disc');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="shoulder_problems" <?php echo ($obj["shoulder_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Shoulder Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="elbow_problems" <?php echo ($obj["elbow_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Elbow Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="wrist_problems" <?php echo ($obj["wrist_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Wrist Problems');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hand_problems" <?php echo ($obj["hand_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hand Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hip_problems" <?php echo ($obj["hip_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hip Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="knee_problems" <?php echo ($obj["knee_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Knee Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="ankle_problems" <?php echo ($obj["ankle_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Ankle Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="foot_problems" <?php echo ($obj["foot_problems"] == "on") ? "checked" : ""; ?>><?php echo xlt('Foot Problems');?>
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
                        <div class="col-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="insulin_dependent_diabetes" <?php echo ($obj["insulin_dependent_diabetes"] == "on") ? "checked" : ""; ?>><?php echo xlt('Insulin Dependent Diabetes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="noninsulin_dependent_diabetes" <?php echo ($obj["noninsulin_dependent_diabetes"] == "on") ? "checked" : ""; ?>><?php echo xlt('Non-Insulin Dependent Diabetes');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hypothyroidism" <?php echo ($obj["hypothyroidism"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hypothyroidism');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="hyperthyroidism" <?php echo ($obj["hyperthyroidism"] == "on") ? "checked" : ""; ?>><?php echo xlt('Hyperthyroidism');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cushing_syndrom" <?php echo ($obj["cushing_syndrom"] == "on") ? "checked" : ""; ?>><?php echo xlt('Cushing Syndrome');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="addison_syndrom" <?php echo ($obj["addison_syndrom"] == "on") ? "checked" : ""; ?>><?php echo xlt('Addison Syndrome');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                        <legend class=""><?php echo xlt('Additional Notes');?></legend>
                            <div class="form-group">
                                <div class="col-sm-10 offset-sm-1">
                                    <textarea name="additional_notes" class="form-control" cols="80" rows="5" ><?php echo text($obj["additional_notes"]); ?></textarea>
                                </div>
                            </div>
                </fieldset>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 offset-sm-1 position-override">
                            <div class="btn-group" role="group">
                            <button type="submit" onclick="top.restoreSession()" class="btn btn-secondary btn-save"><?php echo xlt('Save'); ?></button>
                            <button type="button" class="btn btn-link btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
