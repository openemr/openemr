<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cfapress <cfapress>
 * @author  Robert Down <robertdown@live.com>
 * @author  sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2017-2022 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php
require_once("../../globals.php");

use OpenEMR\Core\Header;

?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php
require_once("$srcdir/api.inc");
$obj = formFetch("form_reviewofs", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/reviewofs/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title">Review of Systems Checks</span><br /><br />

<table>
<tr>
<td valign=top>
<span class=bold>General</span><br />
<input type=checkbox name="fever"  <?php if ($obj["fever"] == "on") {
    echo "checked";
                                   };?>><span class=text>Fever</span><br />
<input type=checkbox name="chills"  <?php if ($obj["chills"] == "on") {
    echo "checked";
                                    };?>><span class=text>Chills</span><br />
<input type=checkbox name="night_sweats"  <?php if ($obj["night_sweats"] == "on") {
    echo "checked";
                                          };?>><span class=text>Night Sweats</span><br />
<input type=checkbox name="weight_loss"  <?php if ($obj["weight_loss"] == "on") {
    echo "checked";
                                         };?>><span class=text>Weight Loss</span><br />
<input type=checkbox name="poor_appetite"  <?php if ($obj["poor_appetite"] == "on") {
    echo "checked";
                                           };?>><span class=text>Poor Appetite</span><br />
<input type=checkbox name="insomnia"  <?php if ($obj["insomnia"] == "on") {
    echo "checked";
                                      };?>><span class=text>Insomnia</span><br />
<input type=checkbox name="fatigued"  <?php if ($obj["fatigued"] == "on") {
    echo "checked";
                                      };?>><span class=text>Fatigued</span><br />
<input type=checkbox name="depressed"  <?php if ($obj["depressed"] == "on") {
    echo "checked";
                                       };?>><span class=text>Depressed</span><br />
<input type=checkbox name="hyperactive"  <?php if ($obj["hyperactive"] == "on") {
    echo "checked";
                                         };?>><span class=text>Hyperactive</span><br />
<input type=checkbox name="exposure_to_foreign_countries"  <?php if ($obj["exposure_to_foreign_countries"] == "on") {
    echo "checked";
                                                           };?>><span class=text>Exposure to Foreign Countries</span><br />
<span class=bold>Skin</span><br />
<input type=checkbox name="rashes"  <?php if ($obj["rashes"] == "on") {
    echo "checked";
                                    };?>><span class=text>Rashes</span><br />
<input type=checkbox name="infections"  <?php if ($obj["infections"] == "on") {
    echo "checked";
                                        };?>><span class=text>Infections</span><br />
<input type=checkbox name="ulcerations"  <?php if ($obj["ulcerations"] == "on") {
    echo "checked";
                                         };?>><span class=text>Ulcerations</span><br />
<input type=checkbox name="pemphigus"  <?php if ($obj["pemphigus"] == "on") {
    echo "checked";
                                       };?>><span class=text>Pemphigus</span><br />
<input type=checkbox name="herpes"  <?php if ($obj["herpes"] == "on") {
    echo "checked";
                                    };?>><span class=text>Herpes</span><br />
</td>
<td valign=top>
<span class=bold>HEENT</span><br />
<input type=checkbox name="cataracts"  <?php if ($obj["cataracts"] == "on") {
    echo "checked";
                                       };?>><span class=text>Cataracts</span><br />
<input type=checkbox name="cataract_surgery"  <?php if ($obj["cataract_surgery"] == "on") {
    echo "checked";
                                              };?>><span class=text>Cataract Surgery</span><br />
<input type=checkbox name="glaucoma"  <?php if ($obj["glaucoma"] == "on") {
    echo "checked";
                                      };?>><span class=text>Glaucoma</span><br />
<input type=checkbox name="double_vision"  <?php if ($obj["double_vision"] == "on") {
    echo "checked";
                                           };?>><span class=text>Double Vision</span><br />
<input type=checkbox name="blurred_vision"  <?php if ($obj["blurred_vision"] == "on") {
    echo "checked";
                                            };?>><span class=text>Blurred Vision</span><br />
<input type=checkbox name="poor_hearing"  <?php if ($obj["poor_hearing"] == "on") {
    echo "checked";
                                          };?>><span class=text>Poor Hearing</span><br />
<input type=checkbox name="headaches"  <?php if ($obj["headaches"] == "on") {
    echo "checked";
                                       };?>><span class=text>Headaches</span><br />
<input type=checkbox name="ringing_in_ears"  <?php if ($obj["ringing_in_ears"] == "on") {
    echo "checked";
                                             };?>><span class=text>Ringing in Ears</span><br />
<input type=checkbox name="bloody_nose"  <?php if ($obj["bloody_nose"] == "on") {
    echo "checked";
                                         };?>><span class=text>Bloody Nose</span><br />
<input type=checkbox name="sinusitis"  <?php if ($obj["sinusitis"] == "on") {
    echo "checked";
                                       };?>><span class=text>Sinusitis</span><br />
<input type=checkbox name="sinus_surgery"  <?php if ($obj["sinus_surgery"] == "on") {
    echo "checked";
                                           };?>><span class=text>Sinus Surgery</span><br />
<input type=checkbox name="dry_mouth"  <?php if ($obj["dry_mouth"] == "on") {
    echo "checked";
                                       };?>><span class=text>Dry Mouth</span><br />
<input type=checkbox name="strep_throat"  <?php if ($obj["strep_throat"] == "on") {
    echo "checked";
                                          };?>><span class=text>Strep Throat</span><br />
<input type=checkbox name="tonsillectomy"  <?php if ($obj["tonsillectomy"] == "on") {
    echo "checked";
                                           };?>><span class=text>Tonsillectomy</span><br />
<input type=checkbox name="swollen_lymph_nodes"  <?php if ($obj["swollen_lymph_nodes"] == "on") {
    echo "checked";
                                                 };?>><span class=text>Swollen Lymph Nodes</span><br />
<input type=checkbox name="throat_cancer"  <?php if ($obj["throat_cancer"] == "on") {
    echo "checked";
                                           };?>><span class=text>Throat Cancer</span><br />
<input type=checkbox name="throat_cancer_surgery"  <?php if ($obj["throat_cancer_surgery"] == "on") {
    echo "checked";
                                                   };?>><span class=text>Throat Cancer Surgery</span><br />
</td>
<td valign=top>
<span class=bold>Cardiovascular</span><br />
<input type=checkbox name="heart_attack"  <?php if ($obj["heart_attack"] == "on") {
    echo "checked";
                                          };?>><span class=text>Heart Attack</span><br />
<input type=checkbox name="irregular_heart_beat"  <?php if ($obj["irregular_heart_beat"] == "on") {
    echo "checked";
                                                  };?>><span class=text>Irregular Heart Beat</span><br />
<input type=checkbox name="chest_pains"  <?php if ($obj["chest_pains"] == "on") {
    echo "checked";
                                         };?>><span class=text>Chest Pains</span><br />
<input type=checkbox name="shortness_of_breath"  <?php if ($obj["shortness_of_breath"] == "on") {
    echo "checked";
                                                 };?>><span class=text>Shortness of Breath</span><br />
<input type=checkbox name="high_blood_pressure"  <?php if ($obj["high_blood_pressure"] == "on") {
    echo "checked";
                                                 };?>><span class=text>High Blood Pressure</span><br />
<input type=checkbox name="heart_failure"  <?php if ($obj["heart_failure"] == "on") {
    echo "checked";
                                           };?>><span class=text>Heart Failure</span><br />
<input type=checkbox name="poor_circulation"  <?php if ($obj["poor_circulation"] == "on") {
    echo "checked";
                                              };?>><span class=text>Poor Circulation</span><br />
<input type=checkbox name="vascular_surgery"  <?php if ($obj["vascular_surgery"] == "on") {
    echo "checked";
                                              };?>><span class=text>Vascular Surgery</span><br />
<input type=checkbox name="cardiac_catheterization"  <?php if ($obj["cardiac_catheterization"] == "on") {
    echo "checked";
                                                     };?>><span class=text>Cardiac Catheterization</span><br />
<input type=checkbox name="coronary_artery_bypass"  <?php if ($obj["coronary_artery_bypass"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Coronary Artery Bypass</span><br />
<input type=checkbox name="heart_transplant"  <?php if ($obj["heart_transplant"] == "on") {
    echo "checked";
                                              };?>><span class=text>Heart Transplant</span><br />
<input type=checkbox name="stress_test"  <?php if ($obj["stress_test"] == "on") {
    echo "checked";
                                         };?>><span class=text>Stress Test</span><br />
<span class=bold>Endocrine</span><br />
<input type=checkbox name="insulin_dependent_diabetes"  <?php if ($obj["insulin_dependent_diabetes"] == "on") {
    echo "checked";
                                                        };?>><span class=text>Insulin Dependent Diabetes</span><br />
<input type=checkbox name="noninsulin_dependent_diabetes"  <?php if ($obj["noninsulin_dependent_diabetes"] == "on") {
    echo "checked";
                                                           };?>><span class=text>Non-Insulin Dependent Diabetes</span><br />
<input type=checkbox name="hypothyroidism"  <?php if ($obj["hypothyroidism"] == "on") {
    echo "checked";
                                            };?>><span class=text>Hypothyroidism</span><br />
<input type=checkbox name="hyperthyroidism"  <?php if ($obj["hyperthyroidism"] == "on") {
    echo "checked";
                                             };?>><span class=text>Hyperthyroidism</span><br />
<input type=checkbox name="cushing_syndrom"  <?php if ($obj["cushing_syndrom"] == "on") {
    echo "checked";
                                             };?>><span class=text>Cushing Syndrom</span><br />
<input type=checkbox name="addison_syndrom"  <?php if ($obj["addison_syndrom"] == "on") {
    echo "checked";
                                             };?>><span class=text>Addison Syndrom</span><br />
</td>
<td valign=top>
<span class=bold>Pulmonary</span><br />
<input type=checkbox name="emphysema"  <?php if ($obj["emphysema"] == "on") {
    echo "checked";
                                       };?>><span class=text>Emphysema</span><br />
<input type=checkbox name="chronic_bronchitis"  <?php if ($obj["chronic_bronchitis"] == "on") {
    echo "checked";
                                                };?>><span class=text>Chronic Bronchitis</span><br />
<input type=checkbox name="interstitial_lung_disease"  <?php if ($obj["interstitial_lung_disease"] == "on") {
    echo "checked";
                                                       };?>><span class=text>Interstitial Lung Disease</span><br />
<input type=checkbox name="shortness_of_breath_2"  <?php if ($obj["shortness_of_breath_2"] == "on") {
    echo "checked";
                                                   };?>><span class=text>Shortness of Breath</span><br />
<input type=checkbox name="lung_cancer"  <?php if ($obj["lung_cancer"] == "on") {
    echo "checked";
                                         };?>><span class=text>Lung Cancer</span><br />
<input type=checkbox name="lung_cancer_surgery"  <?php if ($obj["lung_cancer_surgery"] == "on") {
    echo "checked";
                                                 };?>><span class=text>Lung Cancer Surgery</span><br />
<input type=checkbox name="pheumothorax"  <?php if ($obj["pheumothorax"] == "on") {
    echo "checked";
                                          };?>><span class=text>Pheumothorax</span><br />
<span class=bold>Genitourinary</span><br />
<input type=checkbox name="kidney_failure"  <?php if ($obj["kidney_failure"] == "on") {
    echo "checked";
                                            };?>><span class=text>Kidney Failure</span><br />
<input type=checkbox name="kidney_stones"  <?php if ($obj["kidney_stones"] == "on") {
    echo "checked";
                                           };?>><span class=text>Kidney Stones</span><br />
<input type=checkbox name="kidney_cancer"  <?php if ($obj["kidney_cancer"] == "on") {
    echo "checked";
                                           };?>><span class=text>Kidney Cancer</span><br />
<input type=checkbox name="kidney_infections"  <?php if ($obj["kidney_infections"] == "on") {
    echo "checked";
                                               };?>><span class=text>Kidney Infections</span><br />
<input type=checkbox name="bladder_infections"  <?php if ($obj["bladder_infections"] == "on") {
    echo "checked";
                                                };?>><span class=text>Bladder Infections</span><br />
<input type=checkbox name="bladder_cancer"  <?php if ($obj["bladder_cancer"] == "on") {
    echo "checked";
                                            };?>><span class=text>Bladder Cancer</span><br />
<input type=checkbox name="prostate_problems"  <?php if ($obj["prostate_problems"] == "on") {
    echo "checked";
                                               };?>><span class=text>Prostate Problems</span><br />
<input type=checkbox name="prostate_cancer"  <?php if ($obj["prostate_cancer"] == "on") {
    echo "checked";
                                             };?>><span class=text>Prostate Cancer</span><br />
<input type=checkbox name="kidney_transplant"  <?php if ($obj["kidney_transplant"] == "on") {
    echo "checked";
                                               };?>><span class=text>Kidney Transplant</span><br />
<input type=checkbox name="sexually_transmitted_disease"  <?php if ($obj["sexually_transmitted_disease"] == "on") {
    echo "checked";
                                                          };?>><span class=text>Sexually Transmitted Disease</span><br />
<input type=checkbox name="burning_with_urination"  <?php if ($obj["burning_with_urination"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Burning with Urination</span><br />
<input type=checkbox name="discharge_from_urethra"  <?php if ($obj["discharge_from_urethra"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Discharge From Urethra</span><br />
</td>
<td valign=top>
<span class=bold>Gastrointestinal</span><br />
<input type=checkbox name="stomach_pains"  <?php if ($obj["stomach_pains"] == "on") {
    echo "checked";
                                           };?>><span class=text>Stomach Pains</span><br />
<input type=checkbox name="peptic_ulcer_disease"  <?php if ($obj["peptic_ulcer_disease"] == "on") {
    echo "checked";
                                                  };?>><span class=text>Peptic Ulcer Disease</span><br />
<input type=checkbox name="gastritis"  <?php if ($obj["gastritis"] == "on") {
    echo "checked";
                                       };?>><span class=text>Gastritis</span><br />
<input type=checkbox name="endoscopy"  <?php if ($obj["endoscopy"] == "on") {
    echo "checked";
                                       };?>><span class=text>Endoscopy</span><br />
<input type=checkbox name="polyps"  <?php if ($obj["polyps"] == "on") {
    echo "checked";
                                    };?>><span class=text>Polyps</span><br />
<input type=checkbox name="colonoscopy"  <?php if ($obj["colonoscopy"] == "on") {
    echo "checked";
                                         };?>><span class=text>colonoscopy</span><br />
<input type=checkbox name="colon_cancer"  <?php if ($obj["colon_cancer"] == "on") {
    echo "checked";
                                          };?>><span class=text>Colon Cancer</span><br />
<input type=checkbox name="colon_cancer_surgery"  <?php if ($obj["colon_cancer_surgery"] == "on") {
    echo "checked";
                                                  };?>><span class=text>Colon Cancer Surgery</span><br />
<input type=checkbox name="ulcerative_colitis"  <?php if ($obj["ulcerative_colitis"] == "on") {
    echo "checked";
                                                };?>><span class=text>Ulcerative Colitis</span><br />
<input type=checkbox name="crohns_disease"  <?php if ($obj["crohns_disease"] == "on") {
    echo "checked";
                                            };?>><span class=text>Crohn's Disease</span><br />
<input type=checkbox name="appendectomy"  <?php if ($obj["appendectomy"] == "on") {
    echo "checked";
                                          };?>><span class=text>Appendectomy</span><br />
<input type=checkbox name="divirticulitis"  <?php if ($obj["divirticulitis"] == "on") {
    echo "checked";
                                            };?>><span class=text>Divirticulitis</span><br />
<input type=checkbox name="divirticulitis_surgery"  <?php if ($obj["divirticulitis_surgery"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Divirticulitis Surgery</span><br />
<input type=checkbox name="gall_stones"  <?php if ($obj["gall_stones"] == "on") {
    echo "checked";
                                         };?>><span class=text>Gall Stones</span><br />
<input type=checkbox name="cholecystectomy"  <?php if ($obj["cholecystectomy"] == "on") {
    echo "checked";
                                             };?>><span class=text>Cholecystectomy</span><br />
<input type=checkbox name="hepatitis"  <?php if ($obj["hepatitis"] == "on") {
    echo "checked";
                                       };?>><span class=text>Hepatitis</span><br />
<input type=checkbox name="cirrhosis_of_the_liver"  <?php if ($obj["cirrhosis_of_the_liver"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Cirrhosis of the Liver</span><br />
<input type=checkbox name="splenectomy"  <?php if ($obj["splenectomy"] == "on") {
    echo "checked";
                                         };?>><span class=text>Splenectomy</span><br />
</td>
<td valign=top>
<span class=bold>Musculoskeletal</span><br />
<input type=checkbox name="osetoarthritis"  <?php if ($obj["osetoarthritis"] == "on") {
    echo "checked";
                                            };?>><span class=text>Osetoarthritis</span><br />
<input type=checkbox name="rheumotoid_arthritis"  <?php if ($obj["rheumotoid_arthritis"] == "on") {
    echo "checked";
                                                  };?>><span class=text>Rheumotoid Arthritis</span><br />
<input type=checkbox name="lupus"  <?php if ($obj["lupus"] == "on") {
    echo "checked";
                                   };?>><span class=text>Lupus</span><br />
<input type=checkbox name="ankylosing_sondlilitis"  <?php if ($obj["ankylosing_sondlilitis"] == "on") {
    echo "checked";
                                                    };?>><span class=text>Ankylosing Sondlilitis</span><br />
<input type=checkbox name="swollen_joints"  <?php if ($obj["swollen_joints"] == "on") {
    echo "checked";
                                            };?>><span class=text>Swollen Joints</span><br />
<input type=checkbox name="stiff_joints"  <?php if ($obj["stiff_joints"] == "on") {
    echo "checked";
                                          };?>><span class=text>Stiff Joints</span><br />
<input type=checkbox name="broken_bones"  <?php if ($obj["broken_bones"] == "on") {
    echo "checked";
                                          };?>><span class=text>Broken Bones</span><br />
<input type=checkbox name="neck_problems"  <?php if ($obj["neck_problems"] == "on") {
    echo "checked";
                                           };?>><span class=text>Neck Problems</span><br />
<input type=checkbox name="back_problems"  <?php if ($obj["back_problems"] == "on") {
    echo "checked";
                                           };?>><span class=text>Back Problems</span><br />
<input type=checkbox name="back_surgery"  <?php if ($obj["back_surgery"] == "on") {
    echo "checked";
                                          };?>><span class=text>Back Surgery</span><br />
<input type=checkbox name="scoliosis"  <?php if ($obj["scoliosis"] == "on") {
    echo "checked";
                                       };?>><span class=text>Scoliosis</span><br />
<input type=checkbox name="herniated_disc"  <?php if ($obj["herniated_disc"] == "on") {
    echo "checked";
                                            };?>><span class=text>Herniated Disc</span><br />
<input type=checkbox name="shoulder_problems"  <?php if ($obj["shoulder_problems"] == "on") {
    echo "checked";
                                               };?>><span class=text>Shoulder Problems</span><br />
<input type=checkbox name="elbow_problems"  <?php if ($obj["elbow_problems"] == "on") {
    echo "checked";
                                            };?>><span class=text>Elbow Problems</span><br />
<input type=checkbox name="wrist_problems"  <?php if ($obj["wrist_problems"] == "on") {
    echo "checked";
                                            };?>><span class=text>Wrist Problems</span><br />
<input type=checkbox name="hand_problems"  <?php if ($obj["hand_problems"] == "on") {
    echo "checked";
                                           };?>><span class=text>Hand Problems</span><br />
<input type=checkbox name="hip_problems"  <?php if ($obj["hip_problems"] == "on") {
    echo "checked";
                                          };?>><span class=text>Hip Problems</span><br />
<input type=checkbox name="knee_problems"  <?php if ($obj["knee_problems"] == "on") {
    echo "checked";
                                           };?>><span class=text>Knee Problems</span><br />
<input type=checkbox name="ankle_problems"  <?php if ($obj["ankle_problems"] == "on") {
    echo "checked";
                                            };?>><span class=text>Ankle Problems</span><br />
<input type=checkbox name="foot_problems"  <?php if ($obj["foot_problems"] == "on") {
    echo "checked";
                                           };?>><span class=text>Foot Problems</span><br />
</td>
</tr>
</table>

<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?php echo text($obj["additional_notes"]); ?></textarea><br />
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
