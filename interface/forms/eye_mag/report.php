<?php

/**
 * forms/eye_mag/report.php
 *
 * Central report form for the eye_mag form.  Here is where all new data for display
 * is created.  New reports are created via new.php and then this script is displayed.
 * Edit are performed in view.php.  Nothing is editable here, but it is scrollable
 * across time...
 *
 *   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  The HTML5 Sketch plugin stuff:
 *    Copyright (C) 2011 by Michael Bleigh and Intridea, Inc.
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 *  and associated documentation files (the "Software"), to deal in the Software without restriction,
 *  including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all copies or substantial
 *  portions of the Software.
 *   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(dirname(__FILE__) . "/../../../library/api.inc.php");
require_once(dirname(__FILE__) . "/../../../library/lists.inc.php");
require_once(dirname(__FILE__) . "/../../../library/forms.inc.php");
require_once(dirname(__FILE__) . "/../../../library/patient.inc.php");
require_once(dirname(__FILE__) . "/../../../controllers/C_Document.class.php");

use OpenEMR\Services\FacilityService;
use OpenEMR\Core\Header;

$form_name = "eye_mag";
$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once(__DIR__ . "/../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

if ($_REQUEST['CHOICE'] ?? '') {
    $choice = $_REQUEST['choice'];
}

if ($_REQUEST['ptid'] ?? '') {
    $pid = $_REQUEST['ptid'];
}

if ($_REQUEST['encid'] ?? '') {
    $encounter = $_REQUEST['encid'];
}

if ($_REQUEST['formid'] ?? '') {
    $form_id = $_REQUEST['formid'];
}

if ($_REQUEST['formname'] ?? '') {
    $form_name = $_REQUEST['formname'];
}

if (!($id ?? '')) {
    $id = $form_id ?? '';
}

// Get users preferences, for this user
// (and if not the default where a fresh install begins from, or someone else's)
$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND id=? ORDER BY ZONE_ORDER,ordering";
$result = sqlStatement($query, array($_SESSION['authUserID']));
while ($prefs = sqlFetchArray($result)) {
    $LOCATION = $prefs['LOCATION'];
    $$LOCATION = text($prefs['GOVALUE']);
}

function eye_mag_report($pid, $encounter, $cols, $id, $formname = 'eye_mag')
{
    global $form_folder;
    global $form_name;
    global $choice;
    global $form_id;

  /**
   * openEMR note:  eye_mag Index is id,
   * linked to encounter in form_encounter
   * whose encounter is linked to id in forms.
   */

    $query = "  select  *,form_encounter.date as encounter_date

               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.encounter=? and
                    forms.pid=? ";
    $objQuery = sqlQuery($query, array($encounter,$pid));
    @extract($objQuery);

    $dated = new DateTime($encounter_date);
    $dated = $dated->format('Y/m/d');
    global $visit_date;

    $visit_date = oeFormatShortDate($dated);

  /*
   * Patient/Client -> Visits -> Visit History, on mouse over this is called with variable "choice".
   * To use this feature, this "choice" variable must be added programmatically to
   * /interface/patient_file/history/encounters_ajax.php, currently line 20.
   * The variable $choice will tell us what to display.
   * If it is not present it will display everything == 'narrative'.
   * @param string $choice options NULL,TEXT,DRAW,NARRATIVE
   * @param string $encounter = encounter number
   * @param string $pid value = patient id
   * @return string => returns the HTML of the report selected
   */

    if ($choice == 'DRAW') {
        /*
      $side="OU";
      $zone = array("HPI","PMH","VISION","NEURO","EXT","ANTSEG","RETINA","IMPPLAN");
        //  for ($i = 0; $i < count($zone); ++$i) {
        //  show only 2 for now in the encounter page
      ($choice =='drawing') ? ($count = count($zone)) : ($count ='2');
      for ($i = 0; $i < $count; ++$i) {
        $file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter."/".$side."_".$zone[$i]."_VIEW.png";
        $sql = "SELECT * from documents where url='file://".$file_location."'";
        $doc = sqlQuery($sql);
        if (file_exists($file_location) && ($doc['id'] > '0')) {
        $filetoshow = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=$pid&document_id=$doc[id]&as_file=false";
        ?><div style='position:relative;float:left;width:100px;height:75px;'>
        <img src='<?php echo $filetoshow; ?>' width=100 heght=75>
        </div> <?
        } else {
             // $filetoshow = "../../forms/".$form_folder."/images/".$side."_".$zone[$i]."_BASE.png?".rand();
        }
        ?>

        <?php
      }
      } else if ($choice == "drawing") {
        */
        ?>
      <div class="borderShadow">
        <?php display_draw_section("VISION", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("NEURO", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("EXT", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("ANTSEG", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("RETINA", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("IMPPLAN", $encounter, $pid); ?>
    </div>
        <?php
    } elseif ($choice == 'TEXT') {
        //just display HPI and A/P
        narrative($pid, $encounter, $cols, $id, 'TEXT');
    } else {
        narrative($pid, $encounter, $cols, $id, 'narrative');
        //return;
    }
}
function left_overs()
{
  /*
  * Keep: this could be co-opted to export an XML/HL7 type of document
  */
    $count = 0;
    $data = formFetch($table_name, $id);

    if ($data) {
        foreach ($data as $key => $value) {
            $$key = $value;
        }
    }
}

/*
 *  This is the core report, including Practice logo, patient ID/header.
 *  It relies on the presence of the PMSFH,IMPPLAN arrays.
 *  Rest of fields are pulled from the DB.
 */
function narrative($pid, $encounter, $cols, $form_id, $choice = 'full')
{
    global $form_folder;
    global $PDF_OUTPUT;
    global $facilityService;
  //if $cols == 'Fax', we are here from taskman, making a fax and this a one page short form - leave out PMSFH, prescriptions
  //and any clinical area that is blank.
     $query = "  select  *,form_encounter.date as encounter_date

               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.encounter=? and
                    forms.pid=? ";

    $encounter_data = sqlQuery($query, array($encounter, $pid));
    @extract($encounter_data);
    $providerID = getProviderIdOfEncounter($encounter);
    $providerNAME = getProviderName($providerID);
    $dated = new DateTime($encounter_date);
    $dated = $dated->format('Y/m/d');
    $visit_date = oeFormatShortDate($dated);
    ?>

    <?php Header::setupHeader(['no_dialog', 'no_jquery', 'fontawesome']); ?>
    <link rel="stylesheet" href="../../forms/eye_mag/css/report.css">
    <style>
        <?php if ($PDF_OUTPUT) { ?>
        .mot {
            text-align: center;
            width: 3mm;
            height: 3mm;
        }

        <?php } else { ?>
        .mot {
            text-align: center;
            width: 5mm;
            height: 5mm;
        }

        <?php }  ?>
    </style>
    <div>
    <?php
    if (($cols == 'Fax') || ($cols == 'Report') || ($cols == 'Fax-resend')) {
        echo report_header($pid, 'PDF');
    }

    if ($PDF_OUTPUT) {
        $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
        $facility = null;
        if ($_SESSION['pc_facility']) {
            $facility = $facilityService->getById($_SESSION['pc_facility']);
        } else {
            $facility = $facilityService->getPrimaryBillingLocation();
        }
    }

    ?><br /><br />
    <table style="font-size:1.2em;">
        <tr>
            <td style="text-align:left;padding:1px;vertical-align:top;max-width:720px;">
                <table style="padding:5px;width:700px;">
                    <tr>
                        <td style="text-align: justify;text-justify: inter-word;width:100%;">
                            <b><?php echo xlt('Chief Complaint'); ?>:</b> &nbsp;<?php echo text($CC1); ?>
                            <br/><br/>
                            <b><?php echo xlt('HPI'); ?>:</b>
                            &nbsp;<?php echo $HPI1; ?>
                            <br/>
                            <div style="padding-left:20px;">
                                <?php
                                if ($TIMING1) {
                                    echo "<i>" . xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING1) . "<br />";
                                }

                                if ($CONTEXT1) {
                                    echo "<i>" . xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT1) . "<br />";
                                }

                                if ($SEVERITY1) {
                                    echo "<i>" . xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY1) . "<br />";
                                }

                                if ($MODIFY1) {
                                    echo "<i>" . xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY1) . "<br />";
                                }

                                if ($ASSOCIATED1) {
                                    echo "<i>" . xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED1) . "<br />";
                                }

                                if ($LOCATION1) {
                                    echo "<i>" . xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION1) . "<br />";
                                }

                                if ($QUALITY1) {
                                    echo "<i>" . xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY1) . "<br />";
                                }

                                if ($DURATION1) {
                                    echo "<i>" . xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION1) . "<br />";
                                }
                                ?>

                                <?php
                                if ($CC2) {
                                    echo "
                    ";
                                    echo "<b>" . xlt('Chief Complaint 2'); ?>:</b> &nbsp;<?php echo text($CC2); ?>
                                        <br/>
                                        <b><?php echo xlt('HPI'); ?>:</b>
                                        &nbsp;<?php echo text($HPI2); ?>
                                        <br/>

                                        <div style="padding-left:10px;">
                                            <?php
                                            if ($TIMING2) {
                                                echo "<i>" . xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING2) . "<br />";
                                            }

                                            if ($CONTEXT2) {
                                                echo "<i>" . xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT2) . "<br />";
                                            }

                                            if ($SEVERITY2) {
                                                echo "<i>" . xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY2) . "<br />";
                                            }

                                            if ($MODIFY2) {
                                                echo "<i>" . xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY2) . "<br />";
                                            }

                                            if ($ASSOCIATED2) {
                                                echo "<i>" . xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED2) . "<br />";
                                            }

                                            if ($LOCATION2) {
                                                echo "<i>" . xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION2) . "<br />";
                                            }

                                            if ($QUALITY2) {
                                                echo "<i>" . xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY2) . "<br />";
                                            }

                                            if ($DURATION2) {
                                                echo "<i>" . xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION2) . "<br />";
                                            }
                                            ?>
                                        </div>
                                        <?php
                                }

                                if ($CC3) {
                                    ?>


                                    <?php echo "<b>" . xlt('Chief Complaint 3'); ?>:</b> &nbsp;<?php echo text($CC3); ?>
                                        <br/>
                                        <?php echo xlt('HPI'); ?>&nbsp; <?php echo text($HPI3); ?>
                                        <br/>
                                        <div style="padding-left:10px;">
                                        <?php
                                        if ($TIMING3) {
                                            echo "<i>" . xlt('Timing'); ?>:</i>  &nbsp;<?php echo text($TIMING3) . "<br />";
                                        }

                                        if ($CONTEXT3) {
                                            echo "<i>" . xlt('Context'); ?>:</i> &nbsp;<?php echo text($CONTEXT3) . "<br />";
                                        }

                                        if ($SEVERITY3) {
                                            echo "<i>" . xlt('Severity'); ?>:</i> &nbsp;<?php echo text($SEVERITY3) . "<br />";
                                        }

                                        if ($MODIFY3) {
                                            echo "<i>" . xlt('Modifying'); ?>:</i> &nbsp;<?php echo text($MODIFY3) . "<br />";
                                        }

                                        if ($ASSOCIATED3) {
                                            echo "<i>" . xlt('Associated'); ?>:</i> &nbsp;<?php echo text($ASSOCIATED3) . "<br />";
                                        }

                                        if ($LOCATION3) {
                                            echo "<i>" . xlt('Location'); ?>:</i> &nbsp;<?php echo text($LOCATION3) . "<br />";
                                        }

                                        if ($QUALITY3) {
                                            echo "<i>" . xlt('Quality'); ?>:</i> &nbsp;<?php echo text($QUALITY3) . "<br />";
                                        }

                                        if ($DURATION3) {
                                            echo "<i>" . xlt('Duration'); ?>:</i> &nbsp;<?php echo text($DURATION3) . "<br />";
                                        }
                                        ?>
                                        </div>

                                        <?php
                                }
                                ?>

                                <?php
                                if (($CHRONIC1) && ($cols != 'Fax')) { ?>
                                        <br />
                                        <span style="font-weight:bold;"><?php echo xlt('Chronic or Inactive Problems'); ?>:</span> <br/>
                                        &nbsp;<?php echo text($CHRONIC1) . "<br />";
                                        if ($CHRONIC2) {
                                            echo "&nbsp;" . $CHRONIC2 . "<br />";
                                        }

                                        if ($CHRONIC3) {
                                            echo "&nbsp;" . $CHRONIC3 . "<br />";
                                        }
                                } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:220px;padding:1px;vertical-align:top;">
                <?php
                    //get patient photo
                    $tempDocC = new C_Document();
                    $fileTemp = $tempDocC->retrieve_action($pid, -1, false, true, true, true, 'patient_picture');
                if (!empty($fileTemp)) {
                    if ($PDF_OUTPUT) {
                        // tmp file in ../documents/temp since need to be available via webroot
                        $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'] . '/documents/temp', "oer");
                        file_put_contents($from_file_tmp_web_name, $fileTemp);
                        echo "<img src='" . $from_file_tmp_web_name . "' style='width:220px;'>";
                        $tmp_files_remove[] = $from_file_tmp_web_name;
                    } else {
                        $filetoshow = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture";
                        echo "<img src='" . $filetoshow . "' style='width:220px;'>";
                    }
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
    if ($choice !== 'TEXT') {
        //exclude all of this from displaying on the summary mouseover by default
        ?>
        <table style="margin:1px auto;" class="report_exam_group">
            <tr>
                <td style="width:680px;text-align:center;margin:1 auto;">
                    <?php
                    $PMSFH = build_PMSFH($pid);
                    if ($cols != 'Fax') {
                        show_PMSFH_report($PMSFH);
                    }

                        $count_rx = '0';

                        $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? ORDER BY RX_NUMBER";
                        $wear = sqlStatement($query, array($pid, $form_id, $encounter));
                    while ($wearing = sqlFetchArray($wear)) {
                        $count_rx++;
                        ${"display_W_$count_rx"} = '';
                        ${"ODSPH_$count_rx"} = $wearing['ODSPH'];
                        ${"ODCYL_$count_rx"} = $wearing['ODCYL'];
                        ${"ODAXIS_$count_rx"} = $wearing['ODAXIS'];
                        ${"OSSPH_$count_rx"} = $wearing['OSSPH'];
                        ${"OSCYL_$count_rx"} = $wearing['OSCYL'];
                        ${"OSAXIS_$count_rx"} = $wearing['OSAXIS'];
                        ${"ODMIDADD_$count_rx"} = $wearing['ODMIDADD'];
                        ${"OSMIDADD_$count_rx"} = $wearing['OSMIDADD'];
                        ${"ODADD_$count_rx"} = $wearing['ODADD'];
                        ${"OSADD_$count_rx"} = $wearing['OSADD'];
                        ${"ODVA_$count_rx"} = $wearing['ODVA'];
                        ${"OSVA_$count_rx"} = $wearing['OSVA'];
                        ${"ODNEARVA_$count_rx"} = $wearing['ODNEARVA'];
                        ${"OSNEARVA_$count_rx"} = $wearing['OSNEARVA'];
                        ${"ODPRISM_$count_rx"} = $wearing['ODPRISM'] ?? '';
                        ${"OSPRISM_$count_rx"} = $wearing['OSPRISM'] ?? '';
                        ${"COMMENTS_$count_rx"} = $wearing['COMMENTS'];
                        ${"W_$count_rx"} = '1';
                        ${"RX_TYPE_$count_rx"} = $wearing['RX_TYPE'];
                    }
                    ?>

                </td>
            </tr>
        </table>
        <table style="background: #fff;
                      border: 1pt solid #000;
                      border-radius: 8px;
                      box-shadow: 5px 5px 9px #888;
                      font-family: Arial, Serif;
                      margin-right: 4px;
                      padding: 6px;
                      position: relative;
                      text-align: left;
                      vertical-align: top;
                      width: 720px;">
            <tr>
                <!-- Start of the Vision box -->
                <td style="border-right: 1pt #000 solid;
                            float: left;
                            font-size: 0.9em;
                            margin-bottom: 5px;
                            min-height: 0.8in;
                            padding-left: 2px;
                            padding-right: 2px;
                            text-align: center;
                            vertical-align: top;">
                    <b class="underline"><?php echo xlt('Visual Acuities'); ?></b>
                    <table id="Additional_VA" cellspacing="2" style="text-align:center;font-weight:bold;">
                        <tr style="font-weight:bold;">
                            <td style="text-align:center;font-weight:bold;"></td>
                            <td style="width:50px;text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                            <td style="width:50px;text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                        </tr>
                        <?php if ($SCODVA || $SCOSVA) { ?>
                            <tr>
                                <td><?php echo xlt('sc{{without correction}}'); ?></td>
                                <td><?php echo text($SCODVA); ?></td>
                                <td><?php echo text($SCOSVA); ?></td>
                            </tr>
                        <?php }
                        if ($ODVA_1 || $OSVA_1) { ?>
                                <tr>
                                    <td><?php echo xlt('cc{{with correction}}'); ?></td>
                                    <td><?php echo text($ODVA_1); ?></td>
                                    <td><?php echo text($OSVA_1); ?></td>
                                </tr>
                            <?php }
                        if ($ARODVA || $AROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('AR{{autorefraction}}'); ?></td>
                                    <td><?php echo text($ARODVA); ?></td>
                                    <td><?php echo text($AROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($MRODVA || $MROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('MR{{Manifest Refraction}}'); ?></td>
                                    <td><?php echo text($MRODVA); ?></td>
                                    <td><?php echo text($MROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($CRODVA || $CROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('CR{{Cycloplegic Refraction}}'); ?></td>
                                    <td><?php echo text($CRODVA); ?></td>
                                    <td><?php echo text($CROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($PHODVA || $PHOSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('PH{{Pinhole Vision}}'); ?></td>
                                    <td><?php echo text($PHODVA); ?></td>
                                    <td><?php echo text($PHOSVA); ?></td>
                                </tr>
                            <?php }
                        if ($CTLODVA || $CTLOSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('CTL{{Contact Lens Vision}}'); ?></td>
                                    <td><?php echo text($CTLODVA); ?></td>
                                    <td><?php echo text($CTLOSVA); ?></td>
                                </tr>
                            <?php }
                        if ($SCNEARODVA || $SCNEAROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('scNear{{without correction near}}'); ?></td>
                                    <td><?php echo text($SCNEARODVA); ?></td>
                                    <td><?php echo text($SCNEAROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($ODNEARVA_1 || ($WNEAROSVA_1 ?? '')) { ?>
                                <tr>
                                    <td><?php echo xlt('ccNear{{with correction at near}}'); ?></td>
                                    <td><?php echo text($ODNEARVA_1); ?></td>
                                    <td><?php echo text($OSNEARVA_1); ?></td>
                                </tr>
                            <?php }
                        if ($ARNEARODVA || $ARNEAROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('ARNear{{Auto-refraction near acuity}}'); ?></td>
                                    <td><?php echo text($ARNEARODVA); ?></td>
                                    <td><?php echo text($ARNEAROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($MRNEARODVA || $MRNEAROSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('MRNear{{Manifest Near Acuity}}'); ?></td>
                                    <td><?php echo text($MRNEARODVA); ?></td>
                                    <td><?php echo text($MRNEAROSVA); ?></td>
                                </tr>
                            <?php }
                        if ($PAMODVA || $PAMOSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('PAM{{Potential Acuity Meter}}'); ?></td>
                                    <td><?php echo text($PAMODVA); ?></td>
                                    <td><?php echo text($PAMOSVA); ?></td>
                                </tr>
                            <?php }
                        if ($GLAREODVA || $GLAREOSVA) { ?>
                                <tr>
                                    <td><?php echo xlt('Glare{{Acuity under Glare conditions}}'); ?></td>
                                    <td><?php echo text($GLAREODVA); ?></td>
                                    <td><?php echo text($GLAREOSVA); ?></td>
                                </tr>
                            <?php }
                        if (($CONTRASTODVA ?? '') || ($CONTRASTOSVA ?? '')) { ?>
                                <tr>
                                    <td><?php echo xlt('Contrast{{Constrast Visual Acuity}}'); ?></td>
                                    <td><?php echo text($CONTRASTODVA); ?></td>
                                    <td><?php echo text($CONTRASTOSVA); ?></td>
                                </tr>
                            <?php }
                        if (!empty($BINOCVA)) { ?>
                                <tr>
                                    <td><?php echo xlt('VABiNoc{{Binocular Visual Acuity}}'); ?></td>
                                    <td rowspan="2"><?php echo text($BINOCVA); ?></td>
                                </tr>
                            <?php } ?>
                    </table>
                </td>
                <!-- START OF THE PRESSURE BOX -->
                <td style="border-right: 1pt #000 solid;
                          float: left;
                          font-size: 0.9em;
                          margin-bottom: 5px;
                          min-height: 0.8in;
                          padding-left: 2px;
                          padding-right: 2px;
                          text-align: center;
                          vertical-align: top;">
                    <b class="underline"><?php echo xlt('Intraocular Pressures'); ?></b>
                    <table cellspacing="2" style="margin:2px;text-align:center;">
                        <tr style="font-weight:bold;">
                            <td style="text-align:center;font-weight:bold;"></td>
                            <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                            <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                        </tr>
                        <?php
                        if ($ODIOPAP || $OSIOPAP) {
                            echo "<tr><td style='text-align:right;padding-right:10px;'>" . xlt('App{{Applanation abbreviation}}') . ":</td><td style='text-align:center;'>" . text($ODIOPAP) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPAP) . "</td></tr>";
                        }

                        if ($ODIOPTPN || $OSIOPTPN) {
                            echo "<tr><td style='text-align:right;padding-right:10px;'>" . xlt('Tpn{{Tonopen abbreviation}}') . ":</td><td style='text-align:center;'>" . text($ODIOPTPN) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPTPN) . "</td></tr>";
                        }

                        if ($ODIOPFTN || $OSIOPFTN) {
                            echo "<tr><td style='text-align:right;padding-right:10px;'>" . xlt('FTN{{Finger Tension abbreviation}}') . ":</td><td style='text-align:center;'>" . text($ODIOPFTN) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPFTN) . "</td></tr>";
                        }
                        ?>
                        <tr>
                            <td colspan="3" style="text-align:center;font-weight:bold;">
                                @ <?php echo text($IOPTIME); ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- START OF THE FIELDS BOX -->
                <?php
                    // if the VF zone is checked, display it
                    // if ODVF1 = 1 (true boolean) the value="0" checked="true"
                $bad = 0;
                for ($z = 1; $z < 5; $z++) {
                    $ODzone = "ODVF" . $z;
                    if ($$ODzone == '1') {
                        $ODVF[$z] = '<i class="fa fa-square fa-5">X</i>';
                        if ($PDF_OUTPUT) {
                            $ODVF[$z] = 'X';
                        }

                        $bad++;
                    } else {
                        $ODVF[$z] = '<i class="fa fa-square-o fa-5"></i>';
                        if ($PDF_OUTPUT) {
                            $ODVF[$z] = 'O';
                        }
                    }

                    $OSzone = "OSVF" . $z;
                    if ($$OSzone == "1") {
                        $OSVF[$z] = '<i class="fa fa-square fa-5">X</i>';
                        if ($PDF_OUTPUT) {
                            $OSVF[$z] = 'X';
                        }

                        $bad++;
                    } else {
                        $OSVF[$z] = '<i class="fa fa-square-o fa-5"></i>';
                        if ($PDF_OUTPUT) {
                            $OSVF[$z] = 'O';
                        }
                    }
                }
                ?>
                <?php
                if (($bad ?? '') < '1') { ?>
            <td style="border-right: 1pt #000 solid;
                          float: left;
                          font-size: 0.9em;
                          margin-bottom: 5px;
                          min-height: 0.8in;
                          padding-left: 2px;
                          padding-right: 2px;
                          text-align: center;
                          vertical-align: top;">
            <b class="underline"><?php echo xlt('Fields{{visual fields}}'); ?></b>
                    <?php
                    echo "<br /><br />Full to CF OU";
                } else {
                    ?>
                <td style="border-right: 1pt #000 solid;
                          float: left;
                          font-size: 0.9em;
                          margin-bottom: 5px;
                          min-height: 0.8in;
                          padding-left: 2px;
                          padding-right: 2px;
                          text-align: center;
                          vertical-align: top;">
                    <b class="underline"><?php echo xlt('Fields{{visual fields}}'); ?></b>
                    <table style="text-align:center;font-weight:bold;">
                        <tr style="font-weight:bold;">
                            <td style="width:0.5in;text-align:center;text-decoration:underline;" colspan="2">
                                <b><?php echo xlt('OD'); ?></b>
                                <br/>
                                <br/>
                            </td>
                            <td style="width:0.1in;"></td>
                            <td style="width:0.5in;text-align:center;text-decoration:underline;" colspan="2">
                                <b><?php echo xlt('OS'); ?></b>
                                <br/>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo $ODVF['1']; ?>
                            </td>
                            <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo $ODVF['2']; ?>
                            </td>
                            <td></td>
                            <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo $OSVF['1']; ?>
                            </td>
                            <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo $OSVF['2']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo $ODVF['3']; ?>
                            </td>
                            <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo $ODVF['4']; ?>
                            </td>
                            <td></td>
                            <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo $OSVF['3']; ?>
                            </td>
                            <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo $OSVF['4']; ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                } ?>
                </td>

                <!-- START OF THE MOTILITY BOX -->
                <td style="border-right: 1pt #000 solid;
                          float: left;
                          font-size: 0.9em;
                          margin-bottom: 5px;
                          min-height: 0.8in;
                          padding-left: 2px;
                          padding-right: 2px;
                          text-align: center;
                          vertical-align: top;">
                    <b class="underline"><?php echo xlt('Motility'); ?></b>
                    <?php
                    if ($MOTILITYNORMAL == 'on') {
                        echo "<br /><br />&nbsp;" . xlt('D&V Full OU{{Ductions and Versions full both eyes}}') . "&nbsp;<br /><br />";
                    } else {
                        if ($PDF_OUTPUT) {
                            $background = "url(" . $GLOBALS["fileroot"] . "/interface/forms/" . $form_folder . "/images/eom.jpg)";
                        } else {
                            $background = "url(../../forms/" . $form_folder . "/images/eom.bmp)";
                        }

                        $zone = array(
                            "MOTILITY_RRSO",
                            "MOTILITY_RS",
                            "MOTILITY_RLSO",
                            "MOTILITY_RR",
                            "MOTILITY_R0",
                            "MOTILITY_RL",
                            "MOTILITY_RRIO",
                            "MOTILITY_RI",
                            "MOTILITY_RLIO",
                            "MOTILITY_LRSO",
                            "MOTILITY_LS",
                            "MOTILITY_LLSO",
                            "MOTILITY_LR",
                            "MOTILITY_L0",
                            "MOTILITY_LL",
                            "MOTILITY_LRIO",
                            "MOTILITY_LI",
                            "MOTILITY_LLIO"
                        );
                        for ($i = 0; $i < count($zone); ++$i) {
                            ($$zone[$i] >= '1') ? ($$zone[$i] = "-" . $$zone[$i]) : ($$zone[$i] = '');
                        }
                        ?>
                            <table cellspacing="2" style="margin:2px;text-align:center;">
                                <tr style="font-weight:bold;">
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">
                                        <table style="background: <?php echo $background; ?> no-repeat center center;filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50); -moz-opacity: 0.5; -webkit-opacity: 0.5; opacity:1.0;padding-bottom:5px;">
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_RRSO; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_RS; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_RLSO; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_RR; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_R0; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_RL; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_RRIO; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_RI; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_RLIO; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="text-align:center;font-weight:600;padding-left:20px;">
                                        <table style="background: <?php echo $background; ?> no-repeat center center;background-size: 100% auto; filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50) -moz-opacity: 0.5; -webkit-opacity: 0.5; opacity:1.0;Xpadding-bottom:5px;">
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_LRSO; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_LS; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_LLSO; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_LR; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_L0; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_LL; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="mot"><?php echo $MOTILITY_LLIO; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_LI; ?></td>
                                                <td class="mot"><?php echo $MOTILITY_LLIO; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <?php
                    } ?>
                </td>

                <?php
                if (($PUPIL_NORMAL == '1') || $ODPUPILSIZE1 || $OSPUPILSIZE1) {
                    ?>
                        <td style="border-right:0px;">
                        <?php
                        if (($PUPIL_NORMAL == '1') && (!$ODPUPILSIZE1 || !$OSPUPILSIZE1)) { ?>
                                    <b class="underline"><?php echo xlt('Pupils'); ?></b>&nbsp;&nbsp;
                                    <?php echo xlt('Round and Reactive') . "<br />";
                        }

                        if ($ODPUPILSIZE1 || $OSPUPILSIZE1) { ?>
                                    <table cellspacing="0" style="margin:1px;text-align:center;">
                                        <tr class="report_vitals">
                                            <!-- start of the Pupils box -->
                                            <td>
                                                <b class="underline"><?php echo xlt('Pupils'); ?></b>
                                                <table class="report_vitals" style="text-align:middle;">
                                                    <tr>
                                                        <th> &nbsp;
                                                        </th>
                                                        <th style="padding: 2px 5px;"><?php echo xlt('size'); ?>
                                                            (<?php echo xlt('mm{{millimeters}}'); ?>)
                                                        </th>
                                                        <th style="padding: 2px;"><?php echo xlt('react{{reactivity}}'); ?>
                                                        </th>
                                                        <th style="padding: 2px;"><?php echo xlt('APD{{afferent pupillary defect}}'); ?>
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <td><b><?php echo xlt('OD'); ?></b>
                                                        </td>
                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                                            <?php echo text($ODPUPILSIZE1); ?>
                                                            --&gt;
                                                            <?php echo text($ODPUPILSIZE2); ?>
                                                        </td>
                                                        <td style="text-align:center;border-left:1pt solid black;border-right:1pt solid black;border-bottom:1pt solid black;">
                                                            <?php echo text($ODPUPILREACTIVITY); ?>
                                                        </td>
                                                        <td style="text-align:center;border-bottom:1pt solid black;">
                                                            <?php echo text($ODAPD); ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b><?php echo xlt('OS'); ?></b>
                                                        </td>
                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                                            <?php echo text($OSPUPILSIZE1); ?>
                                                            --&gt;
                                                            <?php echo text($OSPUPILSIZE2); ?>
                                                        </td>
                                                        <td style="text-align:center;border-left:1pt solid black;border-right:1pt solid black;border-top:1pt solid black;">
                                                            <?php echo text($OSPUPILREACTIVITY); ?>
                                                        </td>
                                                        <td style="text-align:center;border-top:1pt solid black;">
                                                            <?php echo text($OSAPD); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php
                        } ?>
                        </td>
                        <?php
                } ?>
            </tr>
        </table>
        <?php

        if ($DIMODPUPILSIZE1 || $DIMOSPUPILSIZE1 || $PUPIL_COMMENTS || $AMSLEROD || $AMSLEROS) { ?>
            <!-- start of slide down pupils_panel -->
            <br/>
            <table class='borderShadow' style="margin:1px;text-align:center;">
                <tr>
                    <td>
                        <b class="underline"><?php echo xlt('Pupils') ?>: <?php echo xlt('Dim'); ?></b>
                        <table style="report_vitals" style="font-size: 8px;text-align:middle;">
                            <tr>
                                <th></th>
                                <th style="padding: 2px;text-align:center;"><?php echo xlt('size'); ?>
                                    (<?php echo xlt('mm{{millimeters}}'); ?>)
                                </th>
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OD'); ?></b>
                                </td>
                                <td style="text-align:bottom;border-bottom:1pt solid black;padding-left:0.1in;">
                                    <?php echo text($DIMODPUPILSIZE1); ?>
                                    --&gt;
                                    <?php echo text($DIMODPUPILSIZE2); ?>
                                </td>

                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OS'); ?></b>
                                </td>
                                <td style="border-top:1pt solid black;padding-left:0.1in;">
                                    <?php echo text($DIMOSPUPILSIZE1); ?>
                                    --&gt;
                                    <?php echo text($DIMOSPUPILSIZE2); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-left:2px;text-align:bottom;">
                                    <?php echo text($PUPIL_COMMENTS); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <!-- end of slide down pupils_panel -->
                    <!-- START OF THE AMSLER BOX -->
                    <?php if ($AMSLEROD || $AMSLEROS) { ?>
                        <td class="report_vitals" style="border-right:0px;border-left:1pt solid black;">
                            <b class="underline"><?php echo xlt('Amsler'); ?></b>
                            <?php
                            if (!$AMSLEROD) {
                                $AMSLEROD = "0";
                            }

                            if (!$AMSLEROS) {
                                $AMSLEROS = "0";
                            }
                            ?>
                            <table style="font-size:10px;">
                                <tr style="font-weight:bold;">
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                                    <td></td>
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                                </tr>

                                <tr>
                                    <td style="text-align:center;font-weight:bold;">
                                        <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROD); ?>.jpg"
                                             id="AmslerOD" style="margin:0.05in;height:0.5in;width:0.6in;"/>
                                        <br/>
                                        <small><?php echo text($AMSLEROD); ?>/5</small>
                                    </td>
                                    <td></td>
                                    <td style="text-align:center;font-weight:bold;">
                                        <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROS); ?>.jpg"
                                             id="AmslerOS" style="margin:0.05in;height:0.5in;width:0.6in;"/>
                                        <br/>
                                        <small><?php echo text($AMSLEROS); ?>/5</small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    <?php } ?>
                </tr>
            </table>

            <?php
        }

        if ($cols != 'Fax') {
            ?><!-- start of the refraction boxes -->
            <?php
            if (
                ($ODVA ?? '') ||
                 ($OSVA ?? '') ||
                 ($ARODSPH ?? '') ||
                  ($AROSSPH ?? '') ||
                   ($MRODSPH ?? '') ||
                    ($MROSSPH ?? '') ||
                    ($CRODSPH ?? '') ||
                    ($CROSSPH ?? '') ||
                    ($CTLODSPH ?? '') ||
                    ($CTLOSSPH ?? '')
            ) { ?>
                <br/>
                <table class="refraction_tables">
                <tr>
                    <td colspan="9"
                        style="text-align:left;text-decoration:underline;font-weight:bold;"><?php echo xlt('Refractive States'); ?>
                    </td>
                </tr>
                <tr style="text-align:center;padding:5px;text-decoration:underline;">
                    <td style="font-weight:600;font-size:10px;text-align:center;"></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Eye'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Sph{{Sphere}}'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Prism'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Acuity'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Mid{{Middle Distance Add}}'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('ADD{{Near Add}}'); ?></td>
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Acuity'); ?></td>
                </tr>
                <?php
                //$count_rx++;
                for ($i = 1; $i <= $count_rx; $i++) {
                    if (${"RX_TYPE_$i"} == "0") {
                        $RX_TYPE = '';
                    } elseif (${"RX_TYPE_$i"} == "1") {
                        $RX_TYPE = xlt('Bifocals');
                    } elseif (${"RX_TYPE_$i"} == "2") {
                        $RX_TYPE = xlt('Trifocals');
                    } elseif (${"RX_TYPE_$i"} == "3") {
                        $RX_TYPE = xlt('Progressive');
                    }
                    ?>
                    <tr>
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Current RX') . " #" . $i . ": "; ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODSPH_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODCYL_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODAXIS_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODPRISM_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODVA_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODMIDADD_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODADD_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"ODNEARVA_$i"}) ?: "-"); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo $RX_TYPE; ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSSPH_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSCYL_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSAXIS_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSPRISM_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSVA_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSMIDADD_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSADD_$i"}) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text(${"OSNEARVA_$i"}) ?: "-"); ?></td>
                    </tr>
                    <?php
                    if (${"COMMENTS_$i"}) {
                        ?>
                        <tr>
                            <td></td>
                            <td colspan="2"><?php echo xlt('Comments'); ?>:</td>
                            <td colspan="7"><?php echo text(${"COMMENTS_$i"}); ?></td>
                        </tr>
                        <?php
                    }
                }

                if ($ARODSPH || $AROSSPH) { ?>
                    <tr style="border-bottom:1pt solid black;">
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Auto Refraction'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODSPH) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODCYL) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODAXIS) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODPRISM) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODVA) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARODADD) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARNEARODVA) ?: "-"); ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td style="font-weight:400;font-size:10px;text-align:right;"><?php echo xlt('OS{{left eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSSPH) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSCYL) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSAXIS) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSPRISM) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSVA) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($AROSADD) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($ARNEAROSVA) ?: "-"); ?></td>
                    </tr>
                    <?php
                }

                if (${"CRCOMMENTS"}) {
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>Comments:</td>
                        <td colspan="7"><?php echo text(${"CRCOMMENTS"}); ?></td>
                    </tr>
                    <?php
                }
            }


            if ($MRODSPH || $MROSSPH) { ?>
                <tr>
                    <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Manifest (Dry) Refraction'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODPRISM) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODVA) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRODADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRNEARODVA) ?: "-"); ?></td>
                </tr>
                <tr></tr>
                <tr>
                    <td></td>
                    <td style="font-weight:400;font-size:10px;text-align:right;"><?php echo xlt('OS{{left eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSPRISM) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSVA) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MROSADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($MRNEAROSVA) ?: "-"); ?></td>
                </tr>
                <?php
            }

            if ($CRODSPH || $CROSSPH) { ?>
                <tr>
                    <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('Cycloplegic (Wet) Refraction'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODPRISM) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODVA) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRODADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRNEARODVA) ?: "-"); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td style="font-weight:400;font-size:10px;text-align:right;"><?php echo xlt('OS{{left eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSPRISM) ?: "-"); ?>
                        &nbsp;
                    </td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSVA) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;">-</td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CROSADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CRNEAROSVA) ?: "-"); ?></td>
                </tr>
                <?php
            }

            if ($CTLODSPH || $CTLOSSPH) { ?>
                <tr style="text-align:center;padding:5px;text-decoration:underline;">
                    <td></td>
                    <td><?php echo xlt('Eye'); ?></td>
                    <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                    <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                    <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                    <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                    <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                    <td><?php echo xlt('ADD'); ?></td>
                    <td><?php echo xlt('Acuity'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight:600;font-size:0.8em;text-align:right;"><?php echo xlt('Contact Lens'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODBC) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODDIAM) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLODVA) ?: "-"); ?></td>
                </tr>
                <tr style="font-size:0.6em;">
                    <td></td>
                    <td></td>
                    <td colspan="3" style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('Brand'); ?>
                        :<?php echo(text($CTLBRANDOD) ?: "-"); ?></td>
                    <td colspan="3"
                        style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('by{{made by/manufacturer}}'); ?><?php echo(text($CTLMANUFACTUREROD) ?: "-"); ?></td>
                    <td colspan="3"
                        style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('via{{shipped by/supplier}}'); ?><?php echo(text($CTLSUPPLIEROD) ?: "-"); ?></td>

                </tr>
                <tr>
                    <td></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSSPH) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSCYL) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSAXIS) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSBC) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSDIAM) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($CTLOSADD) ?: "-"); ?></td>
                    <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo($CTLOSVA ?: "-"); ?></td>
                </tr>
                <tr style="font-size:9px;">
                    <td></td>
                    <td></td>
                    <td colspan="3" style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('Brand'); ?>
                        : <?php echo(text($CTLBRANDOS) ?: "-"); ?></td>
                    <td colspan="3"
                        style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('by{{made by/manufacturer}}'); ?><?php echo(text($CTLMANUFACTUREROS) ?: "-"); ?></td>
                    <td colspan="3"
                        style="font-weight:400;font-size:10px;text-align:left;"><?php echo xlt('via{{shipped by/supplier}}'); ?><?php echo(text($CTLSUPPLIEROS) ?: "-"); ?></td>
                </tr>

                <?php
            }
            ?>
            </table>
            <?php
        } ?>
        <br/>

        <?php
        if ($GLAREODVA || ($CONTRASTODVA ?? '') || $ODK1 || $ODK2 || $LIODVA || ($PAMODBA ?? '')) { ?>
            <table>
                <tr>
                    <td id="LayerVision_ADDITIONAL" class="refraction <?php echo $display_Add; ?>"
                        style="padding:10px;font-size:10px;">
                        <table id="Additional" style="padding:5;font-size:10px;">
                            <tr>
                                <td colspan="9"
                                    style="text-align:left;text-decoration:underline;font-weight:bold;"><?php echo xlt('Additional Data Points'); ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><?php echo xlt('PH{{Pinhole}}'); ?></td>
                                <td><?php echo xlt('PAM{{Potential Acuity Meter}}'); ?></td>
                                <td><?php echo xlt('LI{{Laser Interferometry}}'); ?></td>
                                <td><?php echo xlt('BAT{{Brightness Acuity Testing}}'); ?></td>
                                <td><?php echo xlt('K1{{Keratometry 1}}'); ?></td>
                                <td><?php echo xlt('K2{{Keratometry 2}}'); ?></td>
                                <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                                <td><?php echo text($PHODVA); ?></td>
                                <td><?php echo text($PAMODVA); ?></td>
                                <td><?php echo text($LIODVA); ?></td>
                                <td><?php echo text($GLAREODVA); ?></td>
                                <td><?php echo text($ODK1); ?></td>
                                <td><?php echo text($ODK2); ?></td>
                                <td><?php echo text($ODK2AXIS); ?></td>
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                                <td><?php echo text($PHOSVA); ?></td>
                                <td><?php echo text($PAMOSVA); ?></td>
                                <td><?php echo text($LIOSVA); ?></td>
                                <td><?php echo text($GLAREOSVA); ?></td>
                                <td><?php echo text($OSK1); ?></td>
                                <td><?php echo text($OSK2); ?></td>
                                <td><?php echo text($OSK2AXIS); ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><?php echo xlt('AxLength{{axial Length}}'); ?></td>
                                <td><?php echo xlt('ACD{{anterior chamber depth}}'); ?></td>
                                <td><?php echo xlt('PD{{pupillary distance}}'); ?></td>
                                <td><?php echo xlt('LT{{lens thickness}}'); ?></td>
                                <td><?php echo xlt('W2W{{white-to-white}}'); ?></td>
                                <td><?php echo xlt('ECL{{equivalent contact lens power at the corneal level}}'); ?></td>
                                <!-- <td><?php echo xlt('pend'); ?></td> -->
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                                <td><?php echo text($ODAXIALLENGTH); ?></td>
                                <td><?php echo text($ODACD); ?></td>
                                <td><?php echo text($ODPDMeasured); ?></td>
                                <td><?php echo text($ODLT); ?></td>
                                <td><?php echo text($ODW2W); ?></td>
                                <td><?php echo text($ODECL); ?></td>
                                <!-- <td><input type=text id="pend" name="pend"  value="<?php echo text($pend); ?>"></td> -->
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                                <td><?php echo text($OSAXIALLENGTH); ?></td>
                                <td><?php echo text($OSACD); ?></td>
                                <td><?php echo text($OSPDMeasured); ?></td>
                                <td><?php echo text($OSLT); ?></td>
                                <td><?php echo text($OSW2W); ?></td>
                                <td><?php echo text($OSECL); ?></td>
                                <!--  <td><input type=text id="pend" name="pend" value="<?php echo text($pend); ?>"></td> -->
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php
        }
    }
    ?>

    <!-- end of the refraction boxes -->

    <!-- start of external exam -->
    <div class="report_exam_group">
        <table>
            <tr>
                <td style="text-align:left;vertical-align:top;">
                    <span style="font-weight:bold;text-decoration: underline"><?php echo xlt('External Exam'); ?>:</span>
                    <table class="report_section">
                        <tr>
                            <td style="font-weight:bold;text-align:right;padding-right:10px;text-decoration:underline;width:150px;"><?php echo xlt('Right'); ?></td>
                            <td style="width:100px;"></td>
                            <td style="font-weight:bold;text-align:left;padding-left:10px;text-decoration:underline;width:150px;"><?php echo xlt('Left'); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RBROW); ?></td>
                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('Brow'); ?></td>
                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LBROW); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">
                                <?php echo text($RUL); ?>
                            </td>
                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('Upper Lids'); ?></td>
                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                style="text-align:left;"><?php echo text($LUL); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;"><?php echo text($RLL); ?></td>
                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('Lower Lids'); ?></td>
                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                style="text-align:left;"><?php echo text($LLL); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;"><?php echo text($RMCT); ?></td>
                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('Medial Canthi'); ?></td>
                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                style="text-align:left;"><?php echo text($LMCT); ?></td>
                        </tr>
                        <?php
                        if ($RADNEXA || $LADNEXA) {
                            ?>
                                <tr>
                                    <td style="text-align:right;"><?php echo text($RADNEXA); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Adnexa'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                        style="text-align:left;"><?php echo text($LADNEXA); ?></td>
                                </tr>
                                <?php
                        }

                        if ($EXT_COMMENTS) { ?>
                                <tr>
                                    <td colspan="3">
                                        <b><?php echo xlt('Comments'); ?>:</b> <?php echo text($EXT_COMMENTS); ?>
                                    </td>
                                </tr>
                                <?php
                        } ?>
                    </table>
                </td>
                <td style="text-align:center;padding:1px;vertical-align:middle;">
                    <?php
                        display_draw_image("EXT", $encounter, $pid);
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <!-- end of external exam -->
    <div class="report_exam_group">
        <?php
        if ($OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS) {
            ?>
                <!-- start of Anterior Segment exam -->
                <table>
                    <tr>
                        <td style="text-align:left;padding:1px;vertical-align:top;">
                            <span style="font-weight:bold;text-decoration: underline"><?php echo xlt('Anterior Segment'); ?>:</span>
                            <table class="report_section">
                                <tr>
                                    <td style="font-weight:bold;text-align:right;padding-right:10px;text-decoration:underline;width:150px;"><?php echo xlt('Right'); ?></td>
                                    <td style="width:100px;"></td>
                                    <td style="font-weight:bold;text-align:left;padding-left:10px;text-decoration:underline;width:150px;"><?php echo xlt('Left'); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODCONJ); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Conj{{Conjunctiva}}'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSCONJ); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODCORNEA); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Cornea'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSCORNEA); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODAC); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('A/C{{anterior chamber}}'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSAC); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODLENS); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Lens'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSLENS); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODIRIS); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Iris'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSIRIS); ?></td>
                                </tr>
                            <?php if ($ODGONIO || $OSGONIO) { ?>
                                    <tr>
                                        <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                            style="width:100px;"><?php echo text($ODGONIO); ?></td>
                                        <td style="text-align:center;font-weight:bold;"><?php echo xlt('Gonioscopy'); ?></td>
                                        <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"
                                            style="width:100px;"><?php echo text($OSGONIO); ?></td>
                                    </tr>
                                <?php }
                            if ($ODKTHICKNESS || $OSKTHICKNESS) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODKTHICKNESS); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Pachymetry'); ?>"><?php echo xlt('Pachymetry'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSKTHICKNESS); ?></td>
                                        </tr>
                                    <?php }
                            if ($ODSCHIRMER1 || $OSSCHIRMER1) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODSCHIRMER1); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Schirmers I (w/o anesthesia)'); ?>"><?php echo xlt('Schirmers I'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSSCHIRMER1); ?></td>
                                        </tr>
                                    <?php }
                            if ($ODSCHIRMER2 || $OSSCHIRMER2) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODSCHIRMER2); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>"><?php echo xlt('Schirmers II'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSSCHIRMER2); ?></td>
                                        </tr>
                                    <?php }
                            if ($ODTBUT || $OSTBUT) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODTBUT); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Tear Break Up Time'); ?>"><?php echo xlt('TBUT'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSTBUT); ?></td>
                                        </tr>
                                    <?php }

                            if ($ANTSEG_COMMENTS) { ?>
                                        <tr>
                                            <td colspan="2">
                                                <b><?php echo xlt('Comments'); ?>:</b> <?php echo text($ANTSEG_COMMENTS); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            </table>
                        </td>
                        <td style="text-align:center;padding:1px;vertical-align:middle;">
                            <?php
                            display_draw_image("ANTSEG", $encounter, $pid);
                            ?>
                        </td>
                    </tr>
                </table>
                <!-- end of Anterior Segment exam -->

                <?php
        }
        ?>
    </div>
    <div class="report_exam_group">

        <!-- start of Other exam -->
        <?php
        if ($RLF || $LLF || $RMRD || $LMRD || $RVFISSURE || $LVFISSURE || $RCAROTID || $LCAROTID || $RTEMPART || $LTEMPART || $RCNV || $LCNV || $RCNVII || $LCNVII || $HERTELBASE || $ODCOLOR || $OSCOLOR || $ODREDDESAT || $OSREDDESAT || $ODCOINS || $OSCOINS || $ODNPA || $OSNPA || ($NPA ?? null) || $NPC || $STEREOPSIS || $DACCDIST || $DACCNEAR || $CACCDIST || $CACCNEAR || $VERTFUSAMPS) {
            ?>
                <table>
                    <tr>
                        <td style="text-align:left;vertical-align:top;padding:1px;">
                            <b><u><?php echo xlt('Additional Findings'); ?>:</u></b>
                        <?php if ($ACT == 'on' and $MOTILITYNORMAL == 'on') { ?>
                                <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                            <?php echo xlt('Orthophoric'); ?>
              </span>
                            <?php } ?>
                            <table class="report_section">
                                <tr>
                                    <td style="font-weight:bold;text-align:right;padding-right:10px;text-decoration:underline;width:150px;"><?php echo xlt('Right'); ?></td>
                                    <td style="width:100px;"></td>
                                    <td style="font-weight:bold;text-align:left;padding-left:10px;text-decoration:underline;width:150px;"><?php echo xlt('Left'); ?></td>
                                </tr>
                            <?php
                            if ($RLF || $LLF) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;width: 200px;"><?php echo text($RLF); ?></td>
                                            <td style="text-align:center;font-weight:bold;width:100px;">
                                                <?php echo xlt('Levator Function'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;" style=""><?php echo text($LLF); ?></td>
                                        </tr>
                                        <?php
                            }

                            if (isset($RMRD) || isset($LMRD)) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                                        flex-wrap: wrap;
                                                        padding: 2px 10px;
                                                        width: 200px;">
                                                <?php echo text($RMRD); ?>
                                            </td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Marginal Reflex Distance'); ?>"><?php echo xlt('MRD{{marginal reflex distance}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LMRD); ?></td>
                                        </tr>
                                        <?php
                            }

                            if (isset($RVFISSURE) || isset($LVFISSURE)) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RVFISSURE); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Vertical Fissure: central height between lid margins'); ?>"><?php echo xlt('Vert Fissure{{vertical fissure}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LVFISSURE); ?></td>
                                        </tr>
                                        <?php
                            }

                            if ($RCAROTID || $LCAROTID) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RCAROTID); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Any carotid bruits appreciated?'); ?>"><?php echo xlt('Carotid{{carotid arteries}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LCAROTID); ?></td>
                                        </tr>
                                        <?php
                            }

                            if ($RTEMPART || $LTEMPART) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RTEMPART); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Temporal Arteries'); ?>"><?php echo xlt('Temp. Art.{{temporal arteries}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LTEMPART); ?></td>
                                        </tr>
                                        <?php
                            }

                            if ($RCNV || $LCNV) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RCNV); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Cranial Nerve 5: Trigeminal Nerve'); ?>"><?php echo xlt('CN V{{cranial nerve five}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LCNV); ?></td>
                                        </tr>
                                        <?php
                            }

                            if ($RCNVII || $LCNVII) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($RCNVII); ?></td>
                                            <td style="text-align:center;font-weight:bold;"
                                                title="<?php echo xla('Cranial Nerve 7: Facial Nerve'); ?>"><?php echo xlt('CN VII{{cranial nerve seven}}'); ?></td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($LCNVII); ?></td>
                                        </tr>
                                        <?php
                            }

                            if ($HERTELBASE) { ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center;padding-top:15px;">
                                                <b style="font-weight:bold;padding-bottom:5px;">
                                                    <?php echo xlt('Hertel Exophthalmometry'); ?>
                                                </b>
                                                <br/>
                                                <?php
                                                if ($HERTELBASE) { ?>
                                                        <b style="border:1pt solid black;width:30px;text-align:center;padding:0 5;">
                                                            <?php echo text($ODHERTEL); ?>
                                                        </b>
                                                        <b class="fa fa-minus">--</b>
                                                        <b style="border:1pt solid black;width:40px;text-align:center;padding:0 5;">
                                                            <?php echo text($HERTELBASE); ?>
                                                        </b>
                                                        <b class="fa fa-minus">--</b>
                                                        <b style="border:1pt solid black;width:30px;text-align:center;padding:0 5;">
                                                            <?php echo text($OSHERTEL); ?>
                                                        </b>
                                                        <?php
                                                } ?>
                                            </td>
                                        </tr>
                                        <?php
                            }

                            if ($ODCOLOR || $OSCOLOR || $ODREDDESAT || $OSREDDESAT || $ODCOINS || $OSCOINS || $ODNPA || $OSNPA || ($NPA ?? null) || $NPC || $STEREOPSIS) { ?>
                                        <!-- start of NEURO exam -->
                                        <?php
                                        if ($ODCOLOR or $OSCOLOR) { ?>
                                            <tr>
                                                <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODCOLOR); ?></td>
                                                <td style="text-align:center;font-weight:bold;"><?php echo xlt('Color Vision'); ?></td>
                                                <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSCOLOR); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($ODREDDESAT or $OSREDDESAT) { ?>
                                            <tr>
                                                <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODREDDESAT); ?></td>
                                                <td style="text-align:center;font-weight:bold;"><span
                                                            title="<?php xla('Variation in red color discrimination between the eyes (eg. OD=100, OS=75)'); ?>"><?php echo xlt('Red Desaturation'); ?></span>
                                                </td>
                                                <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSREDDESAT); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($ODCOINS or $OSCOINS) { ?>
                                            <tr>
                                                <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODCOINS); ?></td>
                                                <td style="text-align:center;font-weight:bold;"><span
                                                            title="<?php echo xla('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)'); ?>"><?php echo xlt('Coins'); ?></span>
                                                </td>
                                                <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSCOINS); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($ODNPA or $OSNPA) { ?>
                                            <tr>
                                                <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODNPA); ?></td>
                                                <td style="text-align:center;font-weight:bold;"><span
                                                            title="<?php echo xla('Near Point of Accomodation'); ?>"><?php echo xlt('NPA{{near point of accomodation}}'); ?></span>
                                                </td>
                                                <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSNPA); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($ODNPC or $OSNPC) { ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;"><?php echo xlt('NPC{{near point of convergence}}'); ?>
                                                    :&nbsp;
                                                </td>
                                                <td style="text-align:center;" colspan="2"><?php echo text($NPC); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR or $VERTFUSAMPS) { ?>
                                            <tr style="text-decoration:underline;">
                                                <td></td>
                                                <td style="text-align:center;font-weight:bold;"><?php echo xlt('Distance'); ?> </td>
                                                <td style="text-align:center;font-weight:bold;"> <?php echo xlt('Near'); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($DACCDIST or $DACCNEAR) { ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;"><?php echo xlt('Divergence Amps'); ?>:
                                                </td>
                                                <td class="center"><?php echo text($DACCDIST); ?></td>
                                                <td class="center"><?php echo text($DACCNEAR); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($CACCDIST or $CACCNEAR) { ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;"><?php echo xlt('Convergence Amps'); ?>:
                                                </td>
                                                <td class="center"><?php echo text($CACCDIST); ?></td>
                                                <td class="center"><?php echo text($CACCNEAR); ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if ($VERTFUSAMPS) { ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;">
                                                    <?php echo xlt('Vert Fusional Amps'); ?>:
                                                </td>
                                                <td colspan="2" style="text-align: center;">
                                                    <?php echo text($VERTFUSAMPS); ?>
                                                    <br/>
                                                </td>
                                            </tr>
                                            <?php
                                        }

                                        if ($STEREOPSIS) { ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;"><?php echo xlt('Stereopsis'); ?>:&nbsp;
                                                </td>
                                                <td style="text-align:center;" colspan="2"><?php echo text($STEREOPSIS); ?></td>
                                            </tr>
                                            <?php
                                        }
                            } ?>
                            </table>
                        </td>
                        <td style="text-align:center;padding:1px;vertical-align:middle;">
                            <?php
                            display_draw_image("NEURO", $encounter, $pid);
                            ?>
                        </td>
                    </tr>
                </table>
                <?php
        } ?>
        <!-- end of Other exam -->
    </div>
    <div class="report_exam_group">
        <!-- start of the Retina exam -->
        <?php
        if ($ODDISC || $OSDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
            ?>
                <table>
                    <tr>
                        <td style="text-align:left;padding:1px;vertical-align:top;">
                            <b><u><?php echo xlt('Retina'); ?>:</u></b>
                            <table class="report_section">
                            <?php
                            if ($DIL_MEDS) { ?>
                                        <tr>
                                            <td colspan="3" style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;">
                                                <b><?php echo xlt('Dilation Time'); ?>
                                                    :</b> <?php echo text($DIL_MEDS); ?>
                                            </td>

                                        </tr>
                                        <?php
                            } ?>
                                <tr>
                                    <td style="text-align:right;font-weight:bold;text-decoration:underline;width:150px;"><?php echo xlt('Right'); ?></td>
                                    <td style="width:100px;"></td>
                                    <td style="text-align:left;font-weight:bold;text-decoration:underline;width:150px;"><?php echo xlt('Left'); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODDISC); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Disc'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSDISC); ?></td>
                                </tr>
                                <?php if ($ODCUP || $OSCUP) { ?>
                                    <tr>
                                        <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODCUP); ?></td>
                                        <td style="text-align:center;font-weight:bold;"><?php echo xlt('Cup'); ?></td>
                                        <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSCUP); ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODMACULA); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Macula'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSMACULA); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODVESSELS); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Vessels'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSVESSELS); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODVITREOUS); ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('Vitreous'); ?></td>
                                    <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSVITREOUS); ?></td>
                                </tr>
                                <?php if ($ODPERIPH || $OSPERIPH) { ?>
                                    <tr>
                                        <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($ODPERIPH); ?></td>
                                        <td style="text-align:center;font-weight:bold;"><?php echo xlt('Periph{{periphery}}'); ?></td>
                                        <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSPERIPH); ?></td>
                                    </tr>
                                <?php }
                                if ($ODCMT || $OSCMT) { ?>
                                        <tr>
                                            <td style="text-align:right;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;">&nbsp;<?php echo text($ODCMT); ?></td>
                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('Central Macular Thickness'); ?> </td>
                                            <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;">&nbsp;<?php echo text($OSCMT); ?></td>
                                        </tr>
                                    <?php }

                                if ($RETINA_COMMENTS) { ?>
                                        <tr>
                                            <td colspan="2" style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;">
                                                <b><?php echo xlt('Comments'); ?>
                                                    :</b> <?php echo text($RETINA_COMMENTS); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            </table>
                        </td>
                        <td style="text-align:center;padding:1px;vertical-align:middle;">
                            <?php
                            display_draw_image("RETINA", $encounter, $pid);
                            ?>
                        </td>
                    </tr>
                </table>

                <?php
        } ?>
        <!-- end of Retina exam -->
    </div>
    <?php
    if ($ACT != 'on') { ?>
        <table style="text-align:center;font-size:10px;">
            <tr>
                <td colspan=3 style="">
                    <?php if ($ACT5SCDIST || $ACT5CCDIST || $ACT5CCNEAR) { ?>
                    <table>
                        <tr style="text-align:left;height:16px;vertical-align:top;width:880px;">
                            <td>
                                <span id="ACTTRIGGER" name="ACTTRIGGER"
                                      style="text-decoration:underline;padding-left:2px;">
                                    <?php echo xlt('Alternate Cover Test'); ?>:
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <?php
                            if ($ACT5SCDIST) { ?>
                                    <td style="text-align:center;font-weight:bold;"> <!-- scDIST -->
                                        <table cellpadding="0"
                                               style="position:relative;text-align:center;font-size:12px;margin: 7 5 10 5;border:1pt solid grey;">
                                            <tr>
                                                <td id="ACT_tab_SCDIST" name="ACT_tab_SCDIST"
                                                    style="background-color: #2d6775;
                                                          border-bottom: 1px solid #000;
                                                          border-bottom-left-radius: 0;
                                                          border-bottom-right-radius: 0;
                                                          border-top-left-radius: 7px;
                                                          border-top-right-radius: 7px;
                                                          color: #fff;
                                                          font-size: 1rem;
                                                          height: 25px;
                                                          margin: 5px;"> <?php echo xlt('sc Distance{{without correction distance}}'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" style="text-align:center;font-size:10px;">
                                                    <table>
                                                        <tr>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('R{{right}}'); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT1SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT2SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT3SCDIST); ?></td>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('L{{left}}'); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:right;"><i class="fas fa-reply rotate-left"></i></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT4SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT5SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT6SCDIST); ?></td>
                                                            <td><i class="fas fa-share rotate-right"></i></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT10SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT7SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT8SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT9SCDIST); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT11SCDIST); ?></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <?php
                            }

                            if ($ACT5CCDIST) {
                                ?>
                            <td style="text-align:center;font-weight:bold;"> <!-- ccDIST -->
                                <table cellpadding="0"
                                       style="position:relative;text-align:center;font-size:12px;margin: 7 5 10 5;border:1pt solid grey;">
                                    <tr>
                                        <td style="background-color: #2d6775;
                                                  border-bottom: 1px solid #000;
                                                  border-bottom-left-radius: 0;
                                                  border-bottom-right-radius: 0;
                                                  border-top-left-radius: 7px;
                                                  border-top-right-radius: 7px;
                                                  color: #fff;
                                                  font-size: 1rem;
                                                  height: 25px;
                                                  margin: 5px;"> <?php echo xlt('cc Distance{{with correction at distance}}'); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center;font-size:10px;">
                                            <table>
                                                <tr>
                                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('R{{right}}'); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT1CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT2CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT3CCDIST); ?></td>
                                                    <td style="text-align:center;font-weight:bold;"><?php echo xlt('L{{left}}'); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:right;"><i
                                                                class="fas fa-reply rotate-left"></i></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT4CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT5CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT6CCDIST); ?></td>
                                                    <td><i class="fas fa-share rotate-right"></i></td>
                                                </tr>
                                                <tr>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT10CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT7CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT8CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT9CCDIST); ?></td>
                                                    <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT11CCDIST); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                                <?php
                            }

                            if ($ACT5SCNEAR) {
                                ?>

                                    <td style="text-align:center;font-weight:bold;"> <!-- scNEAR -->
                                        <table cellpadding="0"
                                               style="position:relative;text-align:center;font-size:10px;margin: 7 5 10 5;border:1pt solid grey;">
                                            <tr>
                                                <td style="background-color: #2d6775;
                                                          border-bottom: 1px solid #000;
                                                          border-bottom-left-radius: 0;
                                                          border-bottom-right-radius: 0;
                                                          border-top-left-radius: 7px;
                                                          border-top-right-radius: 7px;
                                                          color: #fff;
                                                          font-size: 1rem;
                                                          height: 25px;
                                                          margin: 5px;"> <?php echo xlt('sc Near{{without correction near}}'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" style="text-align:center;font-size:10px">
                                                    <table>
                                                        <tr>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('R{{right}}'); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT1SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT2SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT3SCNEAR); ?></td>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('L{{left}}'); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:right;"><i
                                                                        class="fas fa-reply rotate-left"></i></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT4SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT5SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT6SCNEAR); ?></td>
                                                            <td><i class="fas fa-share rotate-right"></i></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT10SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT7SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT8SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT9SCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT11SCNEAR); ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <?php
                            }

                            if ($ACT5CCNEAR) {
                                ?>

                                    <td style="text-align:center;font-weight:bold;"> <!-- ccNEAR -->
                                        <table cellpadding="0"
                                               style="position:relative;text-align:center;font-size:10px;margin: 7 5 10 5;border:1pt solid grey;">
                                            <tr>
                                                <td style="background-color: #2d6775;
                                                      border-bottom: 1px solid #000;
                                                      border-bottom-left-radius: 0;
                                                      border-bottom-right-radius: 0;
                                                      border-top-left-radius: 7px;
                                                      border-top-right-radius: 7px;
                                                      color: #fff;
                                                      font-size: 1rem;
                                                      height: 25px;
                                                      margin: 5px;"> <?php echo xlt('cc Near{{with correction at Near}}'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" style="text-align:center;font-size:10px;">
                                                    <table>
                                                        <tr>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('R{{right}}'); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT1CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT2CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT3CCNEAR); ?></td>
                                                            <td style="text-align:center;font-weight:bold;"><?php echo xlt('L{{left}}'); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:right;"><i
                                                                        class="fas fa-reply rotate-left"></i></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT4CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT5CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT6CCNEAR); ?></td>
                                                            <td><i class="fas fa-share rotate-right"></i></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT10CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT7CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT8CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT9CCNEAR); ?></td>
                                                            <td style="height: 58px; width: 58px;"><?php echo report_ACT($ACT11CCNEAR); ?></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                    <?php
                            } ?>
                        </tr>
                    </table>
                        <?php
                    }
                    if ($NEURO_COMMENTS) { ?>
                        <table>
                            <tr>
                                <td colspan="2">
                                    <b><?php echo xlt('Comments'); ?>:</b> <?php echo report_ACT($NEURO_COMMENTS); ?>
                                </td>
                            </tr>
                        </table>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <?php
    }
    //end choice !== 'TEXT' -- include this in summary mouseover report.
    ?>
    <!-- start of IMPPLAN exam -->
    <table class="report_exam_group">
    <tr>
    <td style="text-align:left;padding:1px;vertical-align:top;width:480px;">
    <b><u><?php echo xlt('Impression/Plan'); ?>:</u></b>
    <table style="">
    <tr>
    <td style="padding:5px;text-align: left;text-align:justify;width:475px;">
    <?php
    /**
     *  Retrieve and Display the IMPPLAN_items for the Impression/Plan zone.
     */
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? order by IMPPLAN_order ASC";
    $result = sqlStatement($query, array($form_id, $pid));
    $i = '0';
    $order = array("\r\n", "\n", "\r", "\v", "\f", "\x85", "\u2028", "\u2029");
    $replace = "<br />";
    // echo '<ol>';
    while ($ip_list = sqlFetchArray($result)) {
        $newdata = array(
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'plan' => str_replace($order, $replace, $ip_list['plan']),
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        );
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    if (!empty($IMPPLAN_items)) {
        //for ($i=0; $i < count($IMPPLAN_item); $i++) {
        foreach ($IMPPLAN_items as $item) {
            echo ($item['IMPPLAN_order'] + 1) . '. <b>' . text($item['title']) . '</b><br />';
            echo '<div style="padding-left:15px;">';
            $pattern = '/Code/';
            if (preg_match($pattern, $item['code'])) {
                $item['code'] = '';
            }

            if ($item['codetext'] > '') {
                echo $item['codetext'] . "<br />";
            } else {
                if ($item['code'] > '') {
                    if ($item['codetype'] > '') {
                        $item['code'] = $item['codetype'] . ": " . $item['code'];
                    }
                }
            }
            echo $item['plan'] . "</div><br />";
        }
            $query = "SELECT * FROM form_eye_mag_orders where form_id=? and pid=? ORDER BY id ASC";
            $PLAN_results = sqlStatement($query, array($form_id, $pid));


        if (!empty($PLAN_results)) { ?>
                <br />
                <b><?php echo xlt('Orders') . "/" . xlt('Next Visit'); ?>:</b>
                <br />
                <div style="padding-left:15px;padding-bottom:10px;width:400px;">
                    <?php
                    while ($plan_row = sqlFetchArray($PLAN_results)) {
                        echo $plan_row['ORDER_DETAILS'] . "<br />";
                    }
                    ?>
                </div>
                <?php
        }
        ?>
            </td>
            </tr>
            </table>
            </td>
            <td style="text-align:center;vertical-align:bottom;padding:1px;">
                <?php
                    display_draw_image("IMPPLAN", $encounter, $pid);

                if ($PDF_OUTPUT) {
                    //display a stored optional electronic sig for this providerID, ie the patient's Doc not the tech
                    //Isn't there a place in sites/..default../images for a jpg signature file for Rx printing or some other openEMR task?
                    $from_file = $GLOBALS["webserver_root"] . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg";
                    if (file_exists($from_file)) {
                        echo "<img style='width:50mm;' src='$from_file'><hr style='width:40mm;' />";
                    } else {
                        echo "<br /><br />";
                    }
                } else {
                    $signature = $GLOBALS["webserver_root"] . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg";
                    if (file_exists($signature)) {
                        echo "<img style='width:50mm;' src='" . $GLOBALS['web_root'] . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg'><hr style='width:40mm;' />";
                    } else {
                        echo "<br /><br />";
                    }
                }
                    echo "<br /><i style='font-size:9px;'>" . xlt('electronically signed on') . " " . oeFormatShortDate() . "</i>";

                ?>
                <br/>
                <span style="padding-left:30px;"><?php echo text($providerNAME); ?></span>
                <?php

                ?>


            </td>
            </tr>
            </table>
            </div>
            <?php
            return;
    }
}

function display_draw_image($zone, $encounter, $pid)
{
    global $form_folder;
    global $web_root;
    global $PDF_OUTPUT;
    global $tmp_files_remove;
    $side = "OU";
    $base_name = $pid . "_" . $encounter . "_" . $side . "_" . $zone . "_VIEW";
    $filename = $base_name . ".jpg";
    $sql = "SELECT * from documents where documents.name like '%" . $filename . "'";
    $doc = sqlQuery($sql);
    $document_id = $doc['id'] ?? '';

    if (($document_id > '1') && (is_numeric($document_id))) {
        $d = new Document($document_id);
        $fname = basename($d->get_url());

        $extension = substr($fname, strrpos($fname, "."));
        $notes = $d->get_notes();
        if (!empty($notes)) {
            echo "<table>";
        }

        foreach ($notes as $note) {
            echo '<tr>';
            echo '<td>' . xlt('Note') . ' #' . $note->get_id() . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . xlt('Date') . ': ' . text(oeFormatShortDate($note->get_date())) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $note->get_note() . '<br /><br /></td>';
            echo '</tr>';
        }

        if (!empty($notes)) {
            echo "</table>";
        }

        //               if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
        if ($PDF_OUTPUT) {
            $tempDocC = new C_Document();
            $fileTemp = $tempDocC->retrieve_action($pid, $doc['id'], false, true, true);
            // tmp file in ../documents/temp since need to be available via webroot
            $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'] . '/documents/temp', "oer");
            file_put_contents($from_file_tmp_web_name, $fileTemp);
            echo "<img src='" . $from_file_tmp_web_name . "' style='width:220px;height:120px;'>";
            $tmp_files_remove[] = $from_file_tmp_web_name;
        } else {
            $filetoshow = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc['id']) . "&as_file=false&blahblah=" . attr_url(rand());
            echo "<img src='" . $filetoshow . "' style='width:220px;height:120px;'>";
        }
    } else {
        //else show base_image
        $filetoshow = "../../forms/" . $form_folder . "/images/" . $side . "_" . $zone . "_BASE.jpg";
        if ($PDF_OUTPUT) {
            $filetoshow = $GLOBALS["webroot"] . "/interface/forms/" . $form_folder . "/images/" . $side . "_" . $zone . "_BASE.jpg";
        }

      // uncomment to show base image, no touch up by user.
      // echo "<img src='". $filetoshow."' style='width:220px;height:120px;'>";
    }

    return;
}

function report_ACT($term)
{
    $term = nl2br(htmlspecialchars($term, ENT_NOQUOTES));
    return $term . "&nbsp;";
}
?>
