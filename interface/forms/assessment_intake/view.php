<?php

/**
 * assessment_intake view.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    sunsetsystems <sunsetsystems>
 * @author    cornfeed <jdough823@gmail.com>
 * @author    fndtn357 <fndtn357@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2007 sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2012 fndtn357 <fndtn357@gmail.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: Code Cleanup


?>
<?php
require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html><head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_top m-0">
<?php
require_once("$srcdir/api.inc");

$obj = formFetch("form_assessment_intake", $_GET["id"]);

?>
<form method="post" action="<?php echo $rootdir?>/forms/assessment_intake/save.php?mode=update&id=<?php echo attr_url($_GET["id"]);?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<h3 class="title text-center">Assessment and Intake</h3>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
<br /><br />

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = ?", array($pid));
$result = SqlFetchArray($res); ?>
<strong>Name:</strong>&nbsp; <?php echo text($result['fname']) . '&nbsp' . text($result['mname']) . '&nbsp;' . text($result['lname']);?>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="572" height="1">
<strong>Date:</strong>&nbsp; <?php print date('m/d/y'); ?><br /><br />
<strong>SSN:</strong>&nbsp;<?php echo text($result['ss']);?><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="172" height="1">
<strong>DCN:</strong>&nbsp;<input type="text" name="dcn" value="<?php echo attr($obj["dcn"]);?>"><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="125" height="1">
<label><strong>Location:</strong>&nbsp;<input type="text" name="location" value="<?php echo attr($obj["location"]);?>"></label><br /><br />
<strong>Address:</strong>&nbsp; <?php echo text($result['street']) . ',&nbsp' . text($result['city'])  . ',&nbsp' . text($result['state']) . '&nbsp;' . text($result['postal_code']);?><br /><br />
<strong>Telephone Number:</strong>&nbsp; <?php echo text($result['phone_home']);?><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="400" height="1">
<strong>Date of Birth:</strong>&nbsp;<?php echo text($result['DOB']);?><br /><br />
<label><strong>Time In:</strong>&nbsp;<input type="text" name="time_in" value="<?php echo attr($obj["time_in"]);?>"></label><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="65" height="1">
<label><strong>Time Out:</strong>&nbsp;<input type="text" name="time_out" value="<?php echo attr($obj["time_out"]);?>"></label><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="65" height="1">
<label><strong>Referral Source:</strong>&nbsp;<input type="text" name="referral_source" value="<?php echo attr($obj["referral_source"]);?>"></label><br /><br />
<strong>Purpose:</strong>&nbsp; <input type="checkbox" name='new_client_eval' <?php if ($obj["new_client_eval"] == "on") {
    echo "checked";
                                                                              };?>  ><strong>New client evaluation</strong><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="10" height="1">
<input type="checkbox" name='readmission' <?php if ($obj["readmission"] == "on") {
    echo "checked";
                                          };?>  ><strong>Readmission</strong><img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="35" height="1">
<input type="checkbox" name='consultation' <?php if ($obj["consultation"] == "on") {
    echo "checked";
                                           };?> ><strong>Consultation</strong><br /><br />
<label><strong>Copy sent to:</strong>&nbsp;<input type="text" name="copy_sent_to" value="<?php echo attr($obj["copy_sent_to"]);?>"></label><br /><br />
<strong>Why is Assessment being requested (Goals and treatment expectations of the individual requesting services):</strong><br />
<textarea cols="100" rows="3" name="reason_why" ><?php echo attr($obj["reason_why"]);?></textarea><br />
<strong>Behavior that led to Assessment:</strong><br />
<textarea cols="100" rows=5 wrap="virtual" name="behavior_led_to" ><?php echo text($obj["behavior_led_to"]);?></textarea><br /><br />
<strong><u></u>Areas of Functioning:</strong><br /><br />
<strong>School/Work:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="school_work" ><?php echo text($obj["school_work"]);?></textarea><br /><br />
<strong>Personal Relationships (Intimate):</strong>&nbsp;
<textarea cols="100" rows=4 wrap="virtual" name="personal_relationships" ><?php echo text($obj["personal_relationships"]);?></textarea><br /><br />
<strong>Family Relationships:</strong>&nbsp; &nbsp;
<input type="checkbox" name='fatherc' <?php if ($obj["fatherc"] == "on") {
    echo "checked";
                                      };?>  >&nbsp;<strong>Father involved/present/absent (Describe relationship)</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="father_involved" ><?php echo text($obj["father_involved"]);?></textarea><br />
<input type="checkbox" name='motherc' <?php if ($obj["motherc"] == "on") {
    echo "checked";
                                      };?>  >&nbsp;<strong>Mother involved/present/absent (Describe relationship)</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="mother_involved" ><?php echo text($obj["mother_involved"]);?></textarea><br /><br />
<strong>Number of children:</strong>&nbsp;<input type="text" name="number_children"value="<?php echo attr($obj["number_children"]);?>"><br /><strong>Names, ages, quality of relationship(s):</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="siblings" ><?php echo text($obj["siblings"]);?></textarea><br /><br />
<strong>Other family relationships:</strong><br />
<textarea cols="100" rows="2" wrap="virtual" name="other_relationships" ><?php echo text($obj["other_relationships"]);?></textarea><br /><br />
<strong>Social Relationships (Peers/Friends):</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="social_relationships" ><?php echo text($obj["social_relationships"]);?></textarea><br /><br />
<strong>Psychological/Personal Functioning (Current symptons):</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="current_symptoms" ><?php echo text($obj["current_symptoms"]);?></textarea><br /><br />
<strong>Personal resources and strengths (including the availability & use of family and peers):</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="personal_strengths" ><?php echo text($obj["personal_strengths"]);?></textarea><br /><br />
<strong>Spiritual:</strong>&nbsp;<input type="text" name="spiritual" value="<?php echo attr($obj["spiritual"]);?>">&nbsp;<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="35" height="1">
<strong>Legal:</strong>&nbsp;<input type="text" name="legal" value="<?php echo attr($obj["legal"]);?>"><br /><br />
<strong>Prior Mental Health History/Treatment:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="prior_history" ><?php echo text($obj["prior_history"]);?></textarea><br /><br />
<strong>Number of admissions:</strong>&nbsp;<input type="text" name="number_admitt" value="<?php echo attr($obj["number_admitt"]);?>">&nbsp;<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="35" height="1">
<strong>Types of admissions:</strong>&nbsp;<input type="text" name="type_admitt" value="<?php echo attr($obj["type_admitt"]);?>"><br /><br />
<strong>Alcohol and substance use for the past 30 days:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="substance_use" ><?php echo text($obj["substance_use"]);?></textarea><br /><br />
<strong>Substance abuse history (Include duration, patterns, and consequences of use):</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="substance_abuse" ><?php echo text($obj["substance_abuse"]);?></textarea><br /><br />
<strong><u>Diagnoses</u></strong><br /><br />
<strong>Axis I:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="axis1" ><?php echo text($obj["axis1"]);?></textarea><br /><br />
<strong>Axis II:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="axis2" ><?php echo text($obj["axis2"]);?></textarea><br /><br />
<strong>Axis III:</strong><br />
<textarea cols="100" rows="3" wrap="virtual" name="axis3" ><?php echo text($obj["axis3"]);?></textarea><br /><br />
<strong><u>Allergies/Adverse reactions to medications:</u></strong>&nbsp;<input type="text" name="allergies" value="<?php echo attr($obj["allergies"]);?>"><br /><br />
<strong>Axis IV Psychosocial and environmental problems in the last year:</strong><br />
<input type="checkbox" name='ax4_prob_support_group' <?php if ($obj["ax4_prob_support_group"] == "on") {
    echo "checked";
                                                     };?>  >&nbsp;<strong>Problems with primary support group</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="35" height="1">
<input type="checkbox" name='ax4_prob_soc_env' <?php if ($obj["ax4_prob_soc_env"] == "on") {
    echo "checked";
                                               };?>  >&nbsp;<strong>Problems related to the social environment</strong><br />

<input type="checkbox" name='ax4_educational_prob' <?php if ($obj["ax4_educational_prob"] == "on") {
    echo "checked";
                                                   };?>  >&nbsp;<strong>Educational problems</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
<input type="checkbox" name='ax4_occ_prob' <?php if ($obj["ax4_occ_prob"] == "on") {
    echo "checked";
                                           };?>  >&nbsp;<strong>Occupational problems</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
<input type="checkbox" name='ax4_housing' <?php if ($obj["ax4_housing"] == "on") {
    echo "checked";
                                          };?>  >&nbsp;<strong>Housing problems</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
<input type="checkbox" name='ax4_economic' <?php if ($obj["ax4_economic"] == "on") {
    echo "checked";
                                           };?>  >&nbsp;<strong>Economic problems</strong><br />
<input type="checkbox" name='ax4_access_hc' <?php if ($obj["ax4_access_hc"] == "on") {
    echo "checked";
                                            };?>  >&nbsp;<strong>Problems with access to health care services</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
<input type="checkbox" name='ax4_legal' <?php if ($obj["ax4_legal"] == "on") {
    echo "checked";
                                        };?>  >&nbsp;<strong>Problems related to interaction with the legal system/crime</strong><br />
<input type="checkbox" name='ax4_other_cb' <?php if ($obj["ax4_other_cb"] == "on") {
    echo "checked";
                                           };?>  >&nbsp;<strong>Other (specify):</strong><br />
<textarea cols="100" rows="2" wrap="virtual" name="ax4_other" ><?php echo text($obj["ax4_other"]);?></textarea><br /><br />
<strong>Axis V Global Assessment of Functioning (GAF) Scale (100 down to 0):</strong>
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1"><br />
<strong>Currently</strong><input type="text" name="ax5_current" value="<?php echo attr($obj["ax5_current"]);?>">
<img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
<strong>Past Year</strong><input type="text" name="ax5_past" value="<?php echo attr($obj["ax5_current"]);?>"><br /><br />
<strong><u>Assessment of Currently Known Risk Factors:</u></strong><br /><br />
<strong>Suicide:</strong><br /><input type="checkbox" name='risk_suicide_na' <?php if ($obj["risk_suicide_na"] == "on") {
    echo "checked";
                                                                             };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <strong>Behaviors:</strong>&nbsp;
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_suicide_nk' <?php if ($obj["risk_suicide_nk"] == "on") {
        echo "checked";
                                                  };?>  >&nbsp;<strong>Not Known</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_suicide_io' <?php if ($obj["risk_suicide_io"] == "on") {
        echo "checked";
                                                  };?>  >&nbsp;<strong>Ideation only</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_suicide_plan' <?php if ($obj["risk_suicide_plan"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Plan</strong><br />
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="100" height="1">
    <input type="checkbox" name='risk_suicide_iwom' <?php if ($obj["risk_suicide_iwom"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Intent without means</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_suicide_iwm' <?php if ($obj["risk_suicide_iwm"] == "on") {
        echo "checked";
                                                   };?>  >&nbsp;<strong>Intent with means</strong><br />
<br />
<strong>Homocide:</strong><br /><input type="checkbox" name='risk_homocide_na' <?php if ($obj["risk_homocide_na"] == "on") {
    echo "checked";
                                                                               };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <strong>Behaviors:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_homocide_nk' <?php if ($obj["risk_homocide_nk"] == "on") {
        echo "checked";
                                                   };?>  >&nbsp;<strong>Not Known</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_homocide_io' <?php if ($obj["risk_homocide_io"] == "on") {
        echo "checked";
                                                   };?>  >&nbsp;<strong>Ideation only</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_homocide_plan' <?php if ($obj["risk_homocide_plan"] == "on") {
        echo "checked";
                                                     };?>  >&nbsp;<strong>Plan</strong><br />
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="100" height="1">
    <input type="checkbox" name='risk_homocide_iwom' <?php if ($obj["risk_homocide_iwom"] == "on") {
        echo "checked";
                                                     };?>  >&nbsp;<strong>Intent without means</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_homocide_iwm' <?php if ($obj["risk_homocide_iwm"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Intent with means</strong><br />
<br />
<strong>Compliance with treatment:</strong><br /><input type="checkbox" name='risk_compliance_na' <?php if ($obj["risk_compliance_na"] == "on") {
    echo "checked";
                                                                                                  };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_compliance_fc' <?php if ($obj["risk_compliance_fc"] == "on") {
        echo "checked";
                                                     };?>  >&nbsp;<strong>Full compliance</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_compliance_mc' <?php if ($obj["risk_compliance_mc"] == "on") {
        echo "checked";
                                                     };?>  >&nbsp;<strong>Minimal compliance</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_compliance_moc' <?php if ($obj["risk_compliance_moc"] == "on") {
        echo "checked";
                                                      };?>  >&nbsp;<strong>Moderate compliance</strong><br />
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="100" height="1">
    <input type="checkbox" name='risk_compliance_var' <?php if ($obj["risk_compliance_var"] == "on") {
        echo "checked";
                                                      };?>  >&nbsp;<strong>Variable</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_compliance_no' <?php if ($obj["risk_compliance_no"] == "on") {
        echo "checked";
                                                     };?>  >&nbsp;<strong>Little or no compliance</strong><br />
<br />
<strong>Substance Abuse:</strong><br /><input type="checkbox" name='risk_substance_na' <?php if ($obj["risk_substance_na"] == "on") {
    echo "checked";
                                                                                       };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_substance_none' <?php if ($obj["risk_substance_none"] == "on") {
        echo "checked";
                                                      };?>  >&nbsp;<strong>None/normal use:</strong><br />
    <textarea cols="100" rows="1" wrap="virtual" name="risk_normal_use" ><?php echo text($obj["risk_normal_use"]);?></textarea><br />
    <input type="checkbox" name='risk_substance_ou' <?php if ($obj["risk_substance_ou"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Overuse</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_substance_dp' <?php if ($obj["risk_substance_dp"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Dependence</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_substance_ur' <?php if ($obj["risk_substance_ur"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Unstable remission of abuse</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_substance_ab' <?php if ($obj["risk_substance_ab"] == "on") {
        echo "checked";
                                                    };?>  >&nbsp;<strong>Abuse</strong><br />
<br />
<strong>Current physical or sexual abuse:</strong><br /><input type="checkbox" name='risk_sexual_na' <?php if ($obj["risk_sexual_na"] == "on") {
    echo "checked";
                                                                                                     };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_sexual_y' <?php if ($obj["risk_sexual_y"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>Yes</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_sexual_n' <?php if ($obj["risk_sexual_n"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>No</strong><br />
    <strong>Legally reportable?</strong>&nbsp;<input type="checkbox" name='risk_sexual_ry' <?php if ($obj["risk_sexual_ry"] == "on") {
        echo "checked";
                                                                                           };?>>&nbsp;<strong>Yes</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_sexual_rn' <?php if ($obj["risk_sexual_rn"] == "on") {
        echo "checked";
                                                 };?>>&nbsp;<strong>No</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <strong>If yes, client is </strong>&nbsp;<input type="checkbox" name='risk_sexual_cv' <?php if ($obj["risk_sexual_cv"] == "on") {
        echo "checked";
                                                                                          };?>>&nbsp;<strong>victum</strong>
    &nbsp;<input type="checkbox" name='risk_sexual_cp' <?php if ($obj["risk_sexual_cp"] == "on") {
        echo "checked";
                                                       };?>>&nbsp;<strong>perpetrator</strong><br />
    <input type="checkbox" name='risk_sexual_b' <?php if ($obj["risk_sexual_b"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>Both</strong>&nbsp;
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_sexual_nf' <?php if ($obj["risk_sexual_nf"] == "on") {
        echo "checked";
                                                 };?>>&nbsp;<strong>neither, but abuse exists in family</strong>&nbsp;<br />
<br />
<strong>Current child/elder abuse:</strong><br /><input type="checkbox" name='risk_neglect_na' <?php if ($obj["risk_neglect_na"] == "on") {
    echo "checked";
                                                                                               };?>  >&nbsp;<strong>Not Assessed</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_neglect_y' <?php if ($obj["risk_neglect_y"] == "on") {
        echo "checked";
                                                 };?>>&nbsp;<strong>Yes</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_neglect_n' <?php if ($obj["risk_neglect_n"] == "on") {
        echo "checked";
                                                 };?>>&nbsp;<strong>No</strong><br />
    <strong>Legally reportable?</strong>&nbsp;<input type="checkbox" name='risk_neglect_ry' <?php if ($obj["risk_neglect_ry"] == "on") {
        echo "checked";
                                                                                            };?>>&nbsp;<strong>Yes</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_neglect_rn' <?php if ($obj["risk_neglect_rn"] == "on") {
        echo "checked";
                                                  };?>>&nbsp;<strong>No</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <strong>If yes, client is </strong>&nbsp;<input type="checkbox" name='risk_neglect_cv' <?php if ($obj["risk_neglect_cv"] == "on") {
        echo "checked";
                                                                                           };?>>&nbsp;<strong>victum</strong>
    &nbsp;<input type="checkbox" name='risk_neglect_cp' <?php if ($obj["risk_neglect_cp"] == "on") {
        echo "checked";
                                                        };?>>&nbsp;<strong>perpetrator</strong><br />
    <input type="checkbox" name='risk_neglect_cb' <?php if ($obj["risk_neglect_cb"] == "on") {
        echo "checked";
                                                  };?>>&nbsp;<strong>Both</strong>&nbsp;
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_neglect_cn' <?php if ($obj["risk_neglect_cn"] == "on") {
        echo "checked";
                                                  };?>>&nbsp;<strong>neither, but abuse exists in family</strong>&nbsp;<br />
<br />

    <strong>If risk exists:</strong>&nbsp;client&nbsp;<input type="checkbox" name='risk_exists_c' <?php if ($obj["risk_exists_c"] == "on") {
        echo "checked";
                                                                                                  };?>><strong>can</strong>&nbsp;
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_exists_cn' <?php if ($obj["risk_exists_cn"] == "on") {
        echo "checked";
                                                 };?>>&nbsp;<strong>cannot</strong>&nbsp;
    <strong>meaningfully agree to a contract not to harm</strong><br />
    <input type="checkbox" name='risk_exists_s' <?php if ($obj["risk_exists_s"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>self</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_exists_o' <?php if ($obj["risk_exists_o"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>others</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='risk_exists_b' <?php if ($obj["risk_exists_b"] == "on") {
        echo "checked";
                                                };?>>&nbsp;<strong>both</strong><br /><br />

    <strong>Risk to community (criminal):</strong><br />
    <textarea cols="100" rows="3" wrap="virtual" name="risk_community" ><?php echo text($obj["risk_community"]);?></textarea><br />

<strong><u>Assessment Recommendations:</u></strong><br /><br />

<strong>Outpatient Psychotherapy:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='recommendations_psy_i' <?php if ($obj["recommendations_psy_i"] == "on") {
        echo "checked";
                                                        };?>>&nbsp;<strong>Individual</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='recommendations_psy_f' <?php if ($obj["recommendations_psy_f"] == "on") {
        echo "checked";
                                                        };?>>&nbsp;<strong>Family</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='recommendations_psy_m' <?php if ($obj["recommendations_psy_m"] == "on") {
        echo "checked";
                                                        };?>>&nbsp;<strong>Marital/relational</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='recommendations_psy_o' <?php if ($obj["recommendations_psy_o"] == "on") {
        echo "checked";
                                                        };?>>&nbsp;<strong>Other</strong><br />
    <textarea cols="100" rows="3"wrap="virtual" name="recommendations_psy_notes" ><?php echo text($obj["recommendations_psy_notes"]);?></textarea><br />

<strong>Date report sent to referral source:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="text" name='refer_date' value="<?php echo attr($obj["refer_date"]);?>">
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <strong>Parent/Guardian:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="text" name='parent' value="<?php echo attr($obj["parent"]);?>">
<br />

<strong>Level of supervision needed:</strong>
    <br />
    <textarea cols="100" rows="1" wrap="virtual" name="supervision_level" ><?php echo text($obj["supervision_level"]);?></textarea><br />
    <strong>Type of program:</strong><br />
    <textarea cols="100" rows="1" wrap="virtual" name="supervision_type" ><?php echo text($obj["supervision_type"]);?></textarea><br />

<strong>Residential or long-term placement recommended:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <textarea cols="100" rows="1" wrap="virtual" name="supervision_res" ><?php echo text($obj["supervision_res"]);?></textarea><br />
    <strong>Support services needed:</strong><br />
    <textarea cols="100" rows="1" wrap="virtual" name="supervision_services" ><?php echo text($obj["supervision_services"]);?></textarea><br />

    <input type="checkbox" name='support_ps' <?php if ($obj["support_ps"] == "on") {
        echo "checked";
                                             };?>>&nbsp;<strong>Parenting skills/child management</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='support_cs' <?php if ($obj["support_cs"] == "on") {
        echo "checked";
                                             };?>>&nbsp;<strong>Communication skills</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='support_sm' <?php if ($obj["support_sm"] == "on") {
        echo "checked";
                                             };?>>&nbsp;<strong>Stress management</strong><br />

    <input type="checkbox" name='support_a' <?php if ($obj["support_a"] == "on") {
        echo "checked";
                                            };?>>&nbsp;<strong>Assertiveness</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='support_o' <?php if ($obj["support_o"] == "on") {
        echo "checked";
                                            };?>>&nbsp;<strong>Other</strong><br />
    <textarea cols="100" rows="1" wrap="virtual" name="support_ol" ><?php echo text($obj["support_ol"]);?></textarea><br /><br />

<strong>Legal Services:</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='legal_op' <?php if ($obj["legal_op"] == "on") {
        echo "checked";
                                           };?>>&nbsp;<strong>Offender program</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='legal_so' <?php if ($obj["legal_so"] == "on") {
        echo "checked";
                                           };?>>&nbsp;<strong>Sex Offender Groups</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='legal_sa' <?php if ($obj["legal_sa"] == "on") {
        echo "checked";
                                           };?>>&nbsp;<strong>Substance abuse</strong><br />

    <input type="checkbox" name='legal_ve' <?php if ($obj["legal_ve"] == "on") {
        echo "checked";
                                           };?>>&nbsp;<strong>Victum empathy group</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="checkbox" name='legal_ad' <?php if ($obj["legal_ad"] == "on") {
        echo "checked";
                                           };?>>&nbsp;<strong>Referral to advocate</strong>
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1">
    <input type="text" name='legal_adl' value="<?php echo attr($obj["legal_adl"]);?>">
    <img src="<?php echo $GLOBALS['images_static_relative'];?>/space.gif" width="5" height="1"><br />
    <input type="checkbox" name='legal_o' <?php if ($obj["legal_o"] == "on") {
        echo "checked";
                                          };?>>&nbsp;<strong>Other:</strong>

    <br />

    <strong>Other:</strong><br />
    <textarea cols="100" rows="1" wrap="virtual" name="legal_ol" ><?php echo text($obj["legal_ol"]);?></textarea><br /><br />

<strong><u>Referrals for Continuing Services</u></strong><br /><br />

<strong>Psychiatric Evaluation Psychotropic Medications:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_pepm" ><?php echo text($obj["referrals_pepm"]);?></textarea><br /><br />

<strong>Medical Care:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_mc" ><?php echo text($obj["referrals_mc"]);?></textarea><br /><br />

<strong>Educational/vocational services:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_vt" ><?php echo text($obj["referrals_vt"]);?></textarea><br /><br />

<strong>Other:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_o" ><?php echo text($obj["referrals_o"]);?></textarea><br /><br />

<strong>Current use of resources/services from other community agencies:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_cu" ><?php echo text($obj["referrals_cu"]);?></textarea><br /><br />

<strong>Documents to be obtainded (Release of Information Required):</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_docs" ><?php echo text($obj["referrals_docs"]);?></textarea><br /><br />

<strong>Other needed resources and services:</strong><br />
    <textarea cols="100" rows="2" wrap="virtual" name="referrals_or" ><?php echo text($obj["referrals_or"]);?></textarea><br /><br />

<?php /* From New */ ?>

<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
