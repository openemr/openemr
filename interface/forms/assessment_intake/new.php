<?php

/**
 * assessment_intake new.php.
 *
 * @package OpenEMR
 * @linkhttp://www.open-emr.org
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: assessment_intake");
?>
<html>

<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Assessment Intake Form"); ?></title>
</head>

<body class="m-0 body_top">
    <div class="container">
        <form method="post" action="<?php echo $rootdir;?>/forms/assessment_intake/save.php?mode=new" name="my_form">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <br />
            <h3 class="title text-center">Assessment and Intake</h3>
            <div class="text-center">
                <a href="javascript:top.restoreSession();document.my_form.submit();" class="btn btn-primary"><?php echo xlt("Save"); ?></a>
                <a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt("Don't Save"); ?></a>
            </div>
            <br />

            <?php
            $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = ?", array($pid));
            $result = SqlFetchArray($res);
            ?>

            <p><label class="font-weight-bold">Name:</label>&nbsp; <?php echo text($result['fname']) . '&nbsp' . text($result['mname']) . '&nbsp;' . text($result['lname']);?></p>

            <p><label class="font-weight-bold">Date:</label>&nbsp; <?php print date('m/d/y'); ?></p>

            <p><label class="font-weight-bold">SSN:</label>&nbsp;<?php echo text($result['ss']);?></p>

            <div class="form-group">
                <label class="font-weight-bold" for="dcn">DCN:</label>
                <input type="text" class="form-control" name="dcn" id="dcn" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="location">Location:</label>
                <input type="text" class="form-control" name="location" id="location" />
            </div>

            <p><label class="font-weight-bold">Address:</label>&nbsp; <?php echo text($result['street']) . ',&nbsp' . text($result['city']) . ',&nbsp' . text($result['state']) . '&nbsp;' . text($result['postal_code']);?></p>

            <p><label class="font-weight-bold">Telephone Number:</label>&nbsp; <?php echo text($result['phone_home']);?></p>

            <p><label class="font-weight-bold">Date of Birth:</label>&nbsp;<?php echo text($result['DOB']);?></p>

            <div class="form-group">
                <label class="font-weight-bold" for="time_in">Time In:</label>
                <input type="text" class="form-control" name="time_in" id="time_in" />
            </div>
            <div class="form-group">
                <label class="font-weight-bold" for="time_out">Time Out:</label>
                <input type="text" class="form-control" name="time_out" id="time_out" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Referral Source:</label>
                <input type="text" class="form-control" name="referral_source" />
            </div>

            <p class="font-weight-bold">Purpose:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='new_client_eval' />
                <label class="font-weight-bold custom-control-label">New client evaluation</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='readmission' />
                <label class="font-weight-bold custom-control-label">Readmission</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='consultation' />
                <label class="font-weight-bold custom-control-label">Consultation</label>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Copy sent to:</label>
                <input type="text" class="form-control" name="copy_sent_to" />
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Why is Assessment being requested (Goals and treatment expectations of the individual requesting services):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="reason_why"></textarea>
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Behavior that led to Assessment:</label>
                <textarea class="form-control" cols="100" rows="5" wrap="virtual" name="behavior_led_to"></textarea>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;">Areas of Functioning:</h5>

            <div class="form-group">
                <label class="font-weight-bold">School/Work:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="school_work"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Personal Relationships (Intimate):</label>
                <textarea class="form-control" cols="100" rows="4" wrap="virtual" name="personal_relationships"></textarea>
            </div>

            <p class="font-weight-bold">Family Relationships:</p>
            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class='custom-control-input' name='fatherc' />
                    <label class="font-weight-bold custom-control-label">Father involved/present/absent (Describe relationship)</label>
                </div>
                <div class="form-group">
                    <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="father_involved"></textarea>
                </div>
            </div>
            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class='custom-control-input' name='motherc' />
                    <label class="font-weight-bold custom-control-label">Mother involved/present/absent (Describe relationship)</label>
                </div>
                <div class="form-group">
                    <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="mother_involved"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Number of children:</label>
                <input type="text" class="form-control" name="number_children" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Names, ages, quality of relationship(s):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="siblings"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Other family relationships:</label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="other_relationships"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Social Relationships (Peers/Friends):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="social_relationships"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Psychological/Personal Functioning (Current symptons):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="current_symptoms"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Personal resources and strengths (including the availability and use of family and peers):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="personal_strengths"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Spiritual:</label>
                <input type="text" class="form-control" name="spiritual" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Legal:</label>
                <input type="text" class="form-control" name="legal" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Prior Mental Health History/Treatment:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="prior_history"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Number of admissions:</label>
                <input type="text" class="form-control" name="number_admitt" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Types of admissions:</label>
                <input type="text" class="form-control" name="type_admitt" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Alcohol and substance use for the past 30 days:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="substance_use"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Substance abuse history (Include duration, patterns, and consequences of use):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="substance_abuse"></textarea>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;">Diagnoses</h5>
            <div class="form-group">
                <label class="font-weight-bold">Axis I:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="axis1"></textarea>
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Axis II:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="axis2"></textarea>
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Axis III:</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="axis3"></textarea>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;">Allergies/Adverse reactions to medications:</h5>
            <div class="form-group">
                <input type="text" class="form-control" name="allergies" />
            </div>
            <p class="font-weight-bold">Axis IV Psychosocial and environmental problems in the last year:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_prob_support_group' />
                <label class="font-weight-bold custom-control-label">Problems with primary support group</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_prob_soc_env' />
                <label class="font-weight-bold custom-control-label">Problems related to the social environment</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_educational_prob' />
                <label class="font-weight-bold custom-control-label">Educational problems</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_occ_prob' />
                <label class="font-weight-bold custom-control-label">Occupational problems</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_housing' />
                <label class="font-weight-bold custom-control-label">Housing problems</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_economic' />
                <label class="font-weight-bold custom-control-label">Economic problems</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_access_hc' />
                <label class="font-weight-bold custom-control-label">Problems with access to health care services</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='ax4_legal' />
                <label class="font-weight-bold custom-control-label">Problems related to interaction with the legal system/crime</label>
            </div>
            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class='custom-control-input' name='ax4_other_cb' />
                    <label class="font-weight-bold custom-control-label">Other (specify):</label>
                </div>
                <div class="form-group">
                    <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="ax4_other"></textarea>
                </div>
            </div>
            <p class="font-weight-bold">Axis V Global Assessment of Functioning (GAF) Scale (100 down to 0):</p>
            <div class="form-group">
                <label class="font-weight-bold">Currently</label>
                <input type="text" class="form-control" name="ax5_current" />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Past Year</label>
                <input type="text" class="form-control" name="ax5_past" />
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;">Assessment of Currently Known Risk Factors:</h5>

            <p class="font-weight-bold">Suicide:</p>
            
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>

            <p class="font-weight-bold mt-3">Behaviors:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_nk' />
                <label class="font-weight-bold custom-control-label">Not Known</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_io' />
                <label class="font-weight-bold custom-control-label">Ideation only</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_plan' />
                <label class="font-weight-bold custom-control-label">Plan</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_iwom' />
                <label class="font-weight-bold custom-control-label">Intent without means</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_suicide_iwm' />
                <label class="font-weight-bold custom-control-label">Intent with means</label>
            </div>

            <p class="font-weight-bold mt-3">Homocide:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>

            <p class="font-weight-bold mt-3">Behaviors:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_nk' />
                <label class="font-weight-bold custom-control-label">Not Known</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_io' />
                <label class="font-weight-bold custom-control-label">Ideation only</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_plan' />
                <label class="font-weight-bold custom-control-label">Plan</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_iwom' />
                <label class="font-weight-bold custom-control-label">Intent without means</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_homocide_iwm' />
                <label class="font-weight-bold custom-control-label">Intent with means</label>
            </div>

            <p class="font-weight-bold mt-3">Compliance with treatment:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_fc' />
                <label class="font-weight-bold custom-control-label">Full compliance</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_mc' />
                <label class="font-weight-bold custom-control-label">Minimal compliance</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_moc' />
                <label class="font-weight-bold custom-control-label">Moderate compliance</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_var' />
                <label class="font-weight-bold custom-control-label">Variable</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_compliance_no' />
                <label class="font-weight-bold custom-control-label">Little or no compliance</label>
            </div>

            <p class="font-weight-bold mt-3">Substance Abuse:</p>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_substance_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>

            <div class="form-inline">
                <div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_substance_none' />
                        <label class="font-weight-bold custom-control-label">None/normal use:</label>
                    </div>
                </div>
                <div class="form-group">
                    <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="risk_normal_use"></textarea>
                </div>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_substance_ou' />
                <label class="font-weight-bold custom-control-label">Overuse</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_substance_dp' />
                <label class="font-weight-bold custom-control-label">Dependence</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_substance_ur' />
                <label class="font-weight-bold custom-control-label">Unstable remission of abuse</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_substance_ab' />
                <label class="font-weight-bold custom-control-label">Abuse</label>
            </div>

            <p class="font-weight-bold mt-3">Current physical or sexual abuse:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_y' />
                <label class="font-weight-bold custom-control-label">Yes</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_n' />
                <label class="font-weight-bold custom-control-label">No</label>
            </div>

            <p class="font-weight-bold mt-3">Legally reportable?</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_ry' />
                <label class="font-weight-bold custom-control-label">Yes</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_rn' />
                <label class="font-weight-bold custom-control-label">No</label>
            </div>

            <p class="font-weight-bold mt-3">If yes, client is </p>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_cv' />
                <label class="font-weight-bold custom-control-label">victim</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_cp' />
                <label class="font-weight-bold custom-control-label">perpetrator</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_b' />
                <label class="font-weight-bold custom-control-label">Both</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_sexual_nf' />
                <label class="font-weight-bold custom-control-label">neither, but abuse exists in family</label>
            </div>

            <p class="font-weight-bold mt-3">Current child/elder abuse:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_na' />
                <label class="font-weight-bold custom-control-label">Not Assessed</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_y' />
                <label class="font-weight-bold custom-control-label">Yes</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_n' />
                <label class="font-weight-bold custom-control-label">No</label>
            </div>

            <label class="font-weight-bold mt-3">Legally reportable?</label>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_ry' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("Yes"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_rn' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("No"); ?></label>
            </div>

            <p class="font-weight-bold mt-3"><?php echo xlt("If yes, client is "); ?></p>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_cv' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("victim"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_cp' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("perpetrator"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_cb' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("Both"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='risk_neglect_cn' />
                <label class="font-weight-bold custom-control-label"><?php echo xlt("neither, but abuse exists in family"); ?></label>
            </div>

            <div class="row align-items-center">
                <div class="col-2">
                    <p class="font-weight-bold">If risk exists the client:</p>
                </div>
                <div class="col-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_exists_c' id='risk_exists_c' />
                        <label class="font-weight-bold custom-control-label">can meaningfully agree to a contract not to harm</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_exists_cn' id='risk_exists_cn' />
                        <label class="font-weight-bold custom-control-label">cannot meaningfully agree to a contract not to harm</label>
                    </div>
                </div>
                <div class="col-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_exists_s' />
                        <label class="font-weight-bold custom-control-label">self</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_exists_o' />
                        <label class="font-weight-bold custom-control-label">others</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class='custom-control-input' name='risk_exists_b' />
                        <label class="font-weight-bold custom-control-label">both</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Risk to community (criminal):</label>
                <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="risk_community"></textarea>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;">Assessment Recommendations:</h5>

            <p class="font-weight-bold">Outpatient Psychotherapy:</p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='recommendations_psy_i' />
                <label class="font-weight-bold custom-control-label">Individual</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='recommendations_psy_f' />
                <label class="font-weight-bold custom-control-label">Family</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='recommendations_psy_m' />
                <label class="font-weight-bold custom-control-label">Marital/relational</label>
            </div>

            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class='custom-control-input' name='recommendations_psy_o' />
                    <label class="font-weight-bold custom-control-label">Other</label>
                </div>
                <div class="form-group">
                    <textarea class="form-control" cols="100" rows="3" wrap="virtual" name="recommendations_psy_notes"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Date report sent to referral source:</label>
                <input type="text" class="form-control" name='refer_date' />
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Parent/Guardian:</label>
                <input type="text" class="form-control" name='parent' />
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="supervision_level">Level of supervision needed:</label>
                <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="supervision_level" id="supervision_level"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="supervision_type">Type of program:</label>
                <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="supervision_type" id="supervision_type"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="supervision_res"><?php echo xlt("Residential or long-term placement recommended:"); ?></label>
                <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="supervision_res" id="supervision_res"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="supervision_services"><?php echo xlt("Support services needed:"); ?></label>
                <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="supervision_services" id="supervision_services"></textarea>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='support_ps' id='support_ps' />
                <label class="font-weight-bold custom-control-label" for="support_ps"><?php echo xlt("Parenting skills/child management"); ?></label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='support_cs' id='support_cs' />
                <label class="font-weight-bold custom-control-label" for="support_cs"><?php echo xlt("Communication skills"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='support_sm' id='support_sm' />
                <label class="font-weight-bold custom-control-label" for="support_sm"><?php echo xlt("Stress management"); ?></label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='support_a' id='support_a' />
                <label class="font-weight-bold custom-control-label" for="support_a"><?php echo xlt("Assertiveness"); ?></label>
            </div>

            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class='custom-control-input' name='support_o' id='support_o' />
                    <label class="font-weight-bold custom-control-label" for="support_o"><?php echo xlt("Other"); ?></label>
                </div>
                <div class="ml-1">
                    <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="support_ol" id="support_ol"></textarea>
                </div>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;"><?php echo xlt("Legal Services:"); ?></h5>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class='custom-control-input' name='legal_op' id='legal_op' />
                <label class="font-weight-bold custom-control-label" for="legal_op"><?php echo xlt("Offender program"); ?></label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name='legal_so' id='legal_so' />
                <label class="font-weight-bold custom-control-label" for="legal_so"><?php echo xlt("Sex Offender Groups"); ?></label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name='legal_sa' id='legal_sa' />
                <label class="font-weight-bold custom-control-label" for="legal_sa"><?php echo xlt("Substance abuse"); ?></label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name='legal_ve' id='legal_ve' />
                <label class="font-weight-bold custom-control-label" for="legal_ve"><?php echo xlt("Victim empathy group"); ?></label>
            </div>
            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name='legal_ad' id='legal_ad' />
                    <label class="font-weight-bold custom-control-label" for="legal_ad"><?php echo xlt("Referral to advocate"); ?></label>
                </div>
                <div>
                    <input type="text" class="form-control" name='legal_adl' />
                </div>
            </div>
            <div class="form-inline">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name='legal_o' id="legal_o" />
                    <label class="font-weight-bold custom-control-label" for="legal_o"><?php echo xlt("Other:"); ?></label>
                </div>
                <div class="ml-1">
                    <textarea class="form-control" cols="100" rows="1" wrap="virtual" name="legal_ol" id="legal_ol"></textarea>
                </div>
            </div>

            <h5 class="font-weight-bold mt-3" style="text-decoration: underline;"><?php echo xlt("Referrals for Continuing Services"); ?></h5>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_pepm"><?php echo xlt("Psychiatric Evaluation Psychotropic Medications:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_pepm" id="referrals_pepm"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold"><?php echo xlt("Medical Care:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_mc" id="referrals_mc"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_vt"><?php echo xlt("Educational/vocational services:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_vt" id="referrals_vt"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_o"><?php echo xlt("Other:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_o" id="referrals_o"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_cu"><?php echo xlt("Current use of resources/services from other community agencies:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_cu" id="referrals_cu"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_docs"><?php echo xlt("Documents to be obtainded (Release of Information Required):"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_docs" id="referrals_docs"></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold" for="referrals_or"><?php echo xlt("Other needed resources and services:"); ?></label>
                <textarea class="form-control" cols="100" rows="2" wrap="virtual" name="referrals_or" id="referrals_or"></textarea>
            </div>

            <div class="text-center">
                <a href="javascript:top.restoreSession();document.my_form.submit();" class="btn btn-primary"><?php echo xlt("Save"); ?></a>
                <a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt("Don't Save"); ?></a>
            </div>
            <br />
        </form>
    </div>
    <?php
    formFooter();
    ?>
