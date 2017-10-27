<?php
use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
$returnurl = 'encounter_top.php';
?>
<html><head>
<?php Header::setupHeader();?>
</head>
<?php
$obj = formFetch("form_reviewofs", $_GET["id"]);
?>
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
            <form method=post action="<?php echo $rootdir;?>/forms/reviewofs/save.php?mode=update&id=<?php echo $_GET["id"];?>"  name="my_form">
                <fieldset>
                    <legend class=""><?php echo xlt('General')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="fever"  <?php if ($obj{"fever"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Fever');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="chills"  <?php if ($obj{"chills"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Chills');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="night_sweats"  <?php if ($obj{"night_sweats"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Night Sweats');?>
                                        </label>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="weight_loss"  <?php if ($obj{"weight_loss"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Weight Loss');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="poor_appetite"  <?php if ($obj{"poor_appetite"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Poor Appetite');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="insomnia"  <?php if ($obj{"insomnia"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Insomnia');?>
                                        </label>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="fatigued"  <?php if ($obj{"fatigued"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Fatigued');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="depressed"  <?php if ($obj{"depressed"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Depressed');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hyperactive"  <?php if ($obj{"hyperactive"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hyperactive');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="exposure_to_foreign_countries"  <?php if ($obj{"exposure_to_foreign_countries"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Exposure to Foreign Countries');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Skin')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="rashes"  <?php if ($obj{"rashes"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Rashes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="infections"  <?php if ($obj{"infections"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Infections');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="ulcerations"  <?php if ($obj{"ulcerations"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Ulcerations');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="pemphigus"  <?php if ($obj{"pemphigus"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Pemphigus');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="herpes"  <?php if ($obj{"herpes"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Herpes');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('HEENT')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cataracts"  <?php if ($obj{"cataracts"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cataracts');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cataract_surgery"  <?php if ($obj{"cataract_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cataract Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="glaucoma"  <?php if ($obj{"glaucoma"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Glaucoma');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="double_vision"  <?php if ($obj{"double_vision"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Double Vision');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="blurred_vision"  <?php if ($obj{"blurred_vision"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Blurred Vision');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="poor_hearing"  <?php if ($obj{"poor_hearing"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Poor Hearing');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="headaches"  <?php if ($obj{"headaches"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Headaches');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="ringing_in_ears"  <?php if ($obj{"ringing_in_ears"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Ringing in Ears');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="bloody_nose"  <?php if ($obj{"bloody_nose"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Bloody Nose');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="sinusitis"  <?php if ($obj{"sinusitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Sinusitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="sinus_surgery"  <?php if ($obj{"sinus_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Sinus Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="dry_mouth"  <?php if ($obj{"dry_mouth"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Dry Mouth');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="strep_throat"  <?php if ($obj{"strep_throat"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Strep Throat');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="tonsillectomy"  <?php if ($obj{"tonsillectomy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Tonsillectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="swollen_lymph_nodes"  <?php if ($obj{"swollen_lymph_nodes"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Swollen Lymph Nodes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="throat_cancer"  <?php if ($obj{"throat_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Throat Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="throat_cancer_surgery"  <?php if ($obj{"throat_cancer_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Throat Cancer Surgery');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Pulmonary')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="emphysema"  <?php if ($obj{"emphysema"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Emphysema');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="chronic_bronchitis"  <?php if ($obj{"chronic_bronchitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Chronic Bronchitis');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="interstitial_lung_disease"  <?php if ($obj{"interstitial_lung_disease"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Interstitial Lung Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="shortness_of_breath_2"  <?php if ($obj{"shortness_of_breath_2"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Shortness of Breath');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="lung_cancer"  <?php if ($obj{"lung_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Lung Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="lung_cancer_surgery"  <?php if ($obj{"lung_cancer_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Lung Cancer Surgery');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="pheumothorax"  <?php if ($obj{"pheumothorax"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Pheumothorax');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Cardiovascular')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="heart_attack"  <?php if ($obj{"heart_attack"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Heart Attack');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="irregular_heart_beat"  <?php if ($obj{"irregular_heart_beat"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Irregular Heart Beat');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="chest_pains"  <?php if ($obj{"chest_pains"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Chest Pains');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="shortness_of_breath"  <?php if ($obj{"shortness_of_breath"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Shortness of Breath');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="high_blood_pressure"  <?php if ($obj{"high_blood_pressure"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('High Blood Pressure');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="heart_failure"  <?php if ($obj{"heart_failure"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Heart Failure');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="poor_circulation"  <?php if ($obj{"poor_circulation"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Poor Circulation');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="vascular_surgery"  <?php if ($obj{"vascular_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Vascular Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cardiac_catheterization"  <?php if ($obj{"cardiac_catheterization"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cardiac Catheterization');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="heart_transplant"  <?php if ($obj{"heart_transplant"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Heart Transplant');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="stress_test"  <?php if ($obj{"stress_test"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Stress Test');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="stress_test"  <?php if ($obj{"stress_test"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Stress Test');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Gastrointestinal')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="stomach_pains"  <?php if ($obj{"stomach_pains"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Stomach Pains');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="peptic_ulcer_disease"  <?php if ($obj{"peptic_ulcer_disease"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Peptic Ulcer Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="gastritis"  <?php if ($obj{"gastritis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Gastritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="endoscopy"  <?php if ($obj{"endoscopy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Endoscopy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="polyps"  <?php if ($obj{"polyps"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Polyps');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="colonoscopy"  <?php if ($obj{"colonoscopy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('colonoscopy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="colon_cancer"  <?php if ($obj{"colon_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Colon Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="colon_cancer_surgery"  <?php if ($obj{"colon_cancer_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Colon Cancer Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="ulcerative_colitis"  <?php if ($obj{"ulcerative_colitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Ulcerative Colitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="crohns_disease"  <?php if ($obj{"crohns_disease"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Crohn\'s Disease');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="appendectomy"  <?php if ($obj{"appendectomy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Appendectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="divirticulitis"  <?php if ($obj{"divirticulitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Divirticulitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="divirticulitis_surgery"  <?php if ($obj{"divirticulitis_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Divirticulitis Surgery');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="gall_stones"  <?php if ($obj{"gall_stones"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Gall Stones');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cholecystectomy"  <?php if ($obj{"cholecystectomy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cholecystectomy');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hepatitis"  <?php if ($obj{"hepatitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hepatitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cirrhosis_of_the_liver"  <?php if ($obj{"cirrhosis_of_the_liver"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cirrhosis of the Liver');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="splenectomy"  <?php if ($obj{"splenectomy"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Splenectomy');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Genitourinary')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="kidney_failure"  <?php if ($obj{"kidney_failure"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Kidney Failure');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="kidney_stones"  <?php if ($obj{"kidney_stones"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Kidney Stones');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="kidney_cancer"  <?php if ($obj{"kidney_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Kidney Cancer');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="kidney_infections"  <?php if ($obj{"kidney_infections"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Kidney Infections');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="bladder_infections"  <?php if ($obj{"bladder_infections"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Bladder Infections');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="bladder_cancer"  <?php if ($obj{"bladder_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Bladder Cancer');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="prostate_problems"  <?php if ($obj{"prostate_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Prostate Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="prostate_cancer"  <?php if ($obj{"prostate_cancer"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Prostate Cancer');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="kidney_transplant"  <?php if ($obj{"kidney_transplant"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Kidney Transplant');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="sexually_transmitted_disease"  <?php if ($obj{"sexually_transmitted_disease"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Sexually Transmitted Disease');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="burning_with_urination"  <?php if ($obj{"burning_with_urination"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Burning with Urination');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="discharge_from_urethra"  <?php if ($obj{"discharge_from_urethra"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Discharge From Urethra');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Musculoskeletal')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="osetoarthritis"  <?php if ($obj{"osetoarthritis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Osetoarthritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="rheumotoid_arthritis"  <?php if ($obj{"rheumotoid_arthritis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Rheumotoid Arthritis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="lupus"  <?php if ($obj{"lupus"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Lupus');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="ankylosing_sondlilitis"  <?php if ($obj{"ankylosing_sondlilitis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Ankylosing Sondlilitis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="swollen_joints"  <?php if ($obj{"swollen_joints"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Swollen Joints');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="stiff_joints"  <?php if ($obj{"stiff_joints"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Stiff Joints');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="broken_bones"  <?php if ($obj{"broken_bones"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Broken Bones');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="neck_problems"  <?php if ($obj{"neck_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Neck Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="back_problems"  <?php if ($obj{"back_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Back Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="back_surgery"  <?php if ($obj{"back_surgery"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Back Surgery');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="scoliosis"  <?php if ($obj{"scoliosis"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Scoliosis');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="herniated_disc"  <?php if ($obj{"herniated_disc"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Herniated Disc');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="shoulder_problems"  <?php if ($obj{"shoulder_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Shoulder Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="elbow_problems"  <?php if ($obj{"elbow_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Elbow Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="wrist_problems"  <?php if ($obj{"wrist_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Wrist Problems');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hand_problems"  <?php if ($obj{"hand_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hand Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hip_problems"  <?php if ($obj{"hip_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hip Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="knee_problems"  <?php if ($obj{"knee_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Knee Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="ankle_problems"  <?php if ($obj{"ankle_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Ankle Problems');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="foot_problems"  <?php if ($obj{"foot_problems"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Foot Problems');?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class=""><?php echo xlt('Endocrine')?></legend>
                    <div class="row">
                         <div class="col-xs-12">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="insulin_dependent_diabetes"  <?php if ($obj{"insulin_dependent_diabetes"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Insulin Dependent Diabetes');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="noninsulin_dependent_diabetes"  <?php if ($obj{"noninsulin_dependent_diabetes"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Non-Insulin Dependent Diabetes');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hypothyroidism"  <?php if ($obj{"hypothyroidism"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hypothyroidism');?>
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="hyperthyroidism"  <?php if ($obj{"hyperthyroidism"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Hyperthyroidism');?>
                                        </label>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="cushing_syndrom"  <?php if ($obj{"cushing_syndrom"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Cushing Syndrome');?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input  type="checkbox" name="addison_syndrom"  <?php if ($obj{"addison_syndrom"} == "on") {
                                                echo "checked";
};?>><?php echo xlt('Addison Syndrome');?>
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
                                <div class="col-sm-10 col-sm-offset-1">
                                    <textarea name="additional_notes"    class="form-control" cols="80" rows="5" ><?php echo $obj{"additional_notes"};?></textarea>
                                </div>
                            </div>
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                            <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                            <button type="button" class="btn btn-link btn-cancel btn-separate-left"onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
formFooter();
?>