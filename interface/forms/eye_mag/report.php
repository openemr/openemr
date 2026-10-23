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
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/../../../library/options.inc.php");
require_once(__DIR__ . "/../../../controllers/C_Document.class.php");

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FacilityService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$form_name = "eye_mag";
global $form_folder;
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
$result = sqlStatement($query, [$session->get('authUserID')]);
while ($prefs = sqlFetchArray($result)) {
    $LOCATION = $prefs['LOCATION'];
    ${$LOCATION} = text($prefs['GOVALUE']);
}

function eye_mag_report($pid, $encounter, $cols, $id, $formname = 'eye_mag'): void
{
    global $form_folder;
    global $form_name;
    global $choice;
    global $form_id;

  /**
   * openEMR note:  eye_mag Index is id,
   * linked to encounter in form_encounter
   * whose encounter is linked to id in forms.
   * Note that without encounter in each table,
   * custom clinical reminders that wish to access Eye Form data do not work.
   */
    // Encounter summary and PDF render every form's report in ONE shared
    // request scope, so an inherited $form_id may belong to a different form
    // (or be absent) — either way the FORM_ID-keyed Wear lookup misses and
    // Wear never renders there. Always resolve this encounter's eye form.
    // $form_id is already function-global; refresh it unconditionally.
    $requestedFormId = (int) $id;
    $fidRow = $requestedFormId > 0 ? sqlQuery(
        "SELECT form_id FROM forms WHERE pid=? AND encounter=? AND formdir='eye_mag' AND deleted!='1' AND form_id=? LIMIT 1",
        [$pid ?? null, $encounter ?? null, $requestedFormId]
    ) : [];
    if (empty($fidRow['form_id'])) {
        $fidRow = sqlQuery(
            "SELECT form_id FROM forms WHERE pid=? AND encounter=? AND formdir='eye_mag' AND deleted!='1' ORDER BY form_id DESC LIMIT 1",
            [$pid ?? null, $encounter ?? null]
        );
    }
    if (!empty($fidRow['form_id'])) {
        $form_id = (int)$fidRow['form_id'];
    } else {
        $form_id = null;
    }

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
                    forms.form_id=? and
                    forms.encounter=? and
                    forms.pid=? ";
    $objQuery = sqlQuery($query, [$form_id, $encounter, $pid]);
    @extract($objQuery);
    // Default columns from joined form_eye_* tables so PHPStan can verify them after @extract.
    $encounter_date ??= null;
    // End column defaults.

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

    if ($choice == 'DRAW') { ?>
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
        <?php display_draw_section("SDRETINA", $encounter, $pid); ?>
    </div>
    <div class="borderShadow">
        <?php display_draw_section("IMPPLAN", $encounter, $pid); ?>
    </div>
        <?php
    } elseif ($choice == 'TEXT') {
        //just display HPI and A/P
        narrative($pid, $encounter, $cols, $form_id, 'TEXT');
    } else {
        narrative($pid, $encounter, $cols, $form_id, 'narrative');
        //return;
    }
}

/**
 * Collect only documented refraction sections for reports and encounter previews.
 */
function eye_mag_report_refractions($pid, $encounter, $formId): array
{
    $record = sqlQuery(
        "SELECT r.*, a.* FROM form_eye_refraction r
          JOIN form_eye_acuity a ON a.id=r.id AND a.pid=r.pid
         WHERE r.id=? AND r.pid=?",
        [$formId, $pid]
    );
    $sections = [];
    $hasValues = static function (array $row): bool {
        foreach ($row as $key => $value) {
            if ($key !== 'eye' && trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    };
    $addSection = static function (string $label, array $od, array $os, string $notes = '') use (&$sections, $hasValues): void {
        if (!$hasValues($od) && !$hasValues($os) && trim($notes) === '') {
            return;
        }
        $sections[] = ['label' => $label, 'od' => $od, 'os' => $os, 'notes' => trim($notes)];
    };
    $row = static function (string $eye, array $values): array {
        return array_merge([
            'eye' => $eye, 'sphere' => '', 'cylinder' => '', 'axis' => '',
            'prism' => '', 'base_curve' => '', 'diameter' => '', 'distance' => '',
            'mid' => '', 'add' => '', 'near' => '',
        ], $values);
    };

    $wear = sqlStatement(
        "SELECT * FROM form_eye_mag_wearing WHERE PID=? AND FORM_ID=? AND ENCOUNTER=? ORDER BY RX_NUMBER",
        [$pid, $formId, $encounter]
    );
    while ($wearing = sqlFetchArray($wear)) {
        $rxNumber = (int) ($wearing['RX_NUMBER'] ?? 0);
        $rxTypes = ['0' => '', '1' => 'Bifocals', '2' => 'Trifocals', '3' => 'Progressive'];
        $rxType = $rxTypes[(string) ($wearing['RX_TYPE'] ?? '0')] ?? (string) ($wearing['RX_TYPE'] ?? '');
        $odPrism = trim((string)($wearing['ODPRISM'] ?? '')) ?: trim(trim(($wearing['ODHPD'] ?? '') . ' ' . ($wearing['ODHBASE'] ?? '')) . ' ' . trim(($wearing['ODVPD'] ?? '') . ' ' . ($wearing['ODVBASE'] ?? '')));
        $osPrism = trim((string)($wearing['OSPRISM'] ?? '')) ?: trim(trim(($wearing['OSHPD'] ?? '') . ' ' . ($wearing['OSHBASE'] ?? '')) . ' ' . trim(($wearing['OSVPD'] ?? '') . ' ' . ($wearing['OSVBASE'] ?? '')));
        $label = 'Current Rx' . ($rxNumber ? ' #' . $rxNumber : '') . ($rxType !== '' ? ' · ' . $rxType : '');
        $addSection(
            $label,
            $row('OD', [
                'sphere' => $wearing['ODSPH'] ?? '', 'cylinder' => $wearing['ODCYL'] ?? '',
                'axis' => $wearing['ODAXIS'] ?? '', 'prism' => $odPrism,
                'distance' => $wearing['ODVA'] ?? '', 'mid' => $wearing['ODMIDADD'] ?? '',
                'add' => $wearing['ODADD'] ?? '', 'near' => $wearing['ODNEARVA'] ?? '',
            ]),
            $row('OS', [
                'sphere' => $wearing['OSSPH'] ?? '', 'cylinder' => $wearing['OSCYL'] ?? '',
                'axis' => $wearing['OSAXIS'] ?? '', 'prism' => $osPrism,
                'distance' => $wearing['OSVA'] ?? '', 'mid' => $wearing['OSMIDADD'] ?? '',
                'add' => $wearing['OSADD'] ?? '', 'near' => $wearing['OSNEARVA'] ?? '',
            ]),
            $wearing['COMMENTS'] ?? ''
        );
    }

    $definitions = [
        ['Auto Refraction', 'AR', 'ARNEARODVA', 'ARNEAROSVA', ''],
        ['Manifest Refraction', 'MR', 'MRNEARODVA', 'MRNEAROSVA', ''],
        ['Cycloplegic Refraction', 'CR', '', '', 'CRCOMMENTS'],
    ];
    foreach ($definitions as [$label, $prefix, $odNear, $osNear, $notesField]) {
        $addSection(
            $label,
            $row('OD', [
                'sphere' => $record[$prefix . 'ODSPH'] ?? '', 'cylinder' => $record[$prefix . 'ODCYL'] ?? '',
                'axis' => $record[$prefix . 'ODAXIS'] ?? '', 'prism' => $record[$prefix . 'ODPRISM'] ?? '',
                'distance' => $record[$prefix . 'ODVA'] ?? '', 'add' => $record[$prefix . 'ODADD'] ?? '',
                'near' => $odNear ? ($record[$odNear] ?? '') : '',
            ]),
            $row('OS', [
                'sphere' => $record[$prefix . 'OSSPH'] ?? '', 'cylinder' => $record[$prefix . 'OSCYL'] ?? '',
                'axis' => $record[$prefix . 'OSAXIS'] ?? '', 'prism' => $record[$prefix . 'OSPRISM'] ?? '',
                'distance' => $record[$prefix . 'OSVA'] ?? '', 'add' => $record[$prefix . 'OSADD'] ?? '',
                'near' => $osNear ? ($record[$osNear] ?? '') : '',
            ]),
            $notesField ? ($record[$notesField] ?? '') : ''
        );
    }

    $addSection(
        'Contact Lens',
        $row('OD', [
            'sphere' => $record['CTLODSPH'] ?? '', 'cylinder' => $record['CTLODCYL'] ?? '',
            'axis' => $record['CTLODAXIS'] ?? '',
            'base_curve' => $record['CTLODBC'] ?? '', 'diameter' => $record['CTLODDIAM'] ?? '',
            'distance' => $record['CTLODVA'] ?? '', 'add' => $record['CTLODADD'] ?? '',
        ]),
        $row('OS', [
            'sphere' => $record['CTLOSSPH'] ?? '', 'cylinder' => $record['CTLOSCYL'] ?? '',
            'axis' => $record['CTLOSAXIS'] ?? '',
            'base_curve' => $record['CTLOSBC'] ?? '', 'diameter' => $record['CTLOSDIAM'] ?? '',
            'distance' => $record['CTLOSVA'] ?? '', 'add' => $record['CTLOSADD'] ?? '',
        ]),
        $record['CTL_COMMENTS'] ?? ''
    );

    return $sections;
}

function eye_mag_render_refraction_summary(array $sections): void
{
    if (empty($sections)) {
        return;
    }
    ?>
    <style>
        .eye-rx-report { background:var(--white,#fff); border:1px solid var(--gray-300,#d6dbe0); border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin:10px 0; overflow:hidden; text-align:left; }
        .eye-rx-report__title { background:var(--primary,#5f8b96); color:var(--white,#fff); font-size:1rem; font-weight:600; padding:8px 12px; }
        .eye-rx-report__scroll { overflow-x:auto; }
        .eye-rx-report table { border-collapse:collapse; margin:0; min-width:680px; width:100%; }
        .eye-rx-report th { background:var(--gray-100,#f2f4f6); color:var(--body-color,#212529); font-size:.78rem; padding:6px; text-align:center; }
        .eye-rx-report td { border-top:1px solid var(--gray-200,#e7eaed); color:var(--body-color,#212529); padding:6px; text-align:center; white-space:nowrap; }
        .eye-rx-report td:first-child { background:rgba(95,139,150,.1); font-weight:600; text-align:left; white-space:normal; }
        .eye-rx-report__notes td { background:var(--gray-100,#f7f8f9); font-size:.82rem; text-align:left !important; white-space:normal; }
    </style>
    <section class="eye-rx-report">
        <div class="eye-rx-report__title"><i class="fas fa-glasses"></i> <?php echo xlt('Refraction'); ?></div>
        <div class="eye-rx-report__scroll">
            <table>
                <thead><tr>
                    <th><?php echo xlt('Type'); ?></th><th><?php echo xlt('Eye'); ?></th>
                    <th><?php echo xlt('Sphere'); ?></th><th><?php echo xlt('Cylinder'); ?></th>
                    <th><?php echo xlt('Axis'); ?></th><th><?php echo xlt('Prisms'); ?></th>
                    <th><?php echo xlt('Base Curve'); ?></th><th><?php echo xlt('Diameter'); ?></th>
                    <th><?php echo xlt('Distance VA'); ?></th><th><?php echo xlt('Mid'); ?></th>
                    <th><?php echo xlt('Add'); ?></th><th><?php echo xlt('Near VA'); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($sections as $section) {
                    foreach (['od', 'os'] as $rowIndex => $eyeKey) {
                        $rxRow = $section[$eyeKey]; ?>
                        <tr>
                            <?php if ($rowIndex === 0) {
                                ?><td rowspan="2"><?php echo text($section['label']); ?></td><?php } ?>
                            <td><b><?php echo text($rxRow['eye']); ?></b></td>
                            <?php foreach (['sphere', 'cylinder', 'axis', 'prism', 'base_curve', 'diameter', 'distance', 'mid', 'add', 'near'] as $field) { ?>
                                <td><?php echo text($rxRow[$field] !== '' ? $rxRow[$field] : '—'); ?></td>
                            <?php } ?>
                        </tr>
                    <?php }
                    if ($section['notes'] !== '') { ?>
                        <tr class="eye-rx-report__notes"><td colspan="12"><b><?php echo xlt('Notes'); ?>:</b> <?php echo text($section['notes']); ?></td></tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
}

function left_overs(): void
{
  /*
  * Keep: this could be co-opted to export an XML/HL7 type of document
  */
    global $table_name, $id;
    $count = 0;
    $data = formFetch($table_name, $id);

    if ($data) {
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }
    }
}

/*
 *  This is the core report, including Practice logo, patient ID/header.
 *  It relies on the presence of the PMSFH,IMPPLAN arrays.
 *  Rest of fields are pulled from the DB.
 */
function narrative($pid, $encounter, $cols, $form_id, $choice = 'full', $materialsPdf = null): void
{
    global $form_folder;
    global $PDF_OUTPUT;

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
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
                    forms.form_id=? and
                    forms.encounter=? and
                    forms.pid=? ";

    $encounter_data = sqlQuery($query, [$form_id, $encounter, $pid]);
    @extract($encounter_data);
    // Default columns from joined form_eye_* tables so PHPStan can verify them after @extract.
    $ACT ??= null; $ACT10CCDIST ??= null; $ACT10CCNEAR ??= null; $ACT10SCDIST ??= null; $ACT10SCNEAR ??= null;
    $ACT11CCDIST ??= null; $ACT11CCNEAR ??= null; $ACT11SCDIST ??= null; $ACT11SCNEAR ??= null;
    $ACT1CCDIST ??= null; $ACT1CCNEAR ??= null; $ACT1SCDIST ??= null; $ACT1SCNEAR ??= null; $ACT2CCDIST ??= null;
    $ACT2CCNEAR ??= null; $ACT2SCDIST ??= null; $ACT2SCNEAR ??= null; $ACT3CCDIST ??= null; $ACT3CCNEAR ??= null;
    $ACT3SCDIST ??= null; $ACT3SCNEAR ??= null; $ACT4CCDIST ??= null; $ACT4CCNEAR ??= null; $ACT4SCDIST ??= null;
    $ACT4SCNEAR ??= null; $ACT5CCDIST ??= null; $ACT5CCNEAR ??= null; $ACT5SCDIST ??= null; $ACT5SCNEAR ??= null;
    $ACT6CCDIST ??= null; $ACT6CCNEAR ??= null; $ACT6SCDIST ??= null; $ACT6SCNEAR ??= null; $ACT7CCDIST ??= null;
    $ACT7CCNEAR ??= null; $ACT7SCDIST ??= null; $ACT7SCNEAR ??= null; $ACT8CCDIST ??= null; $ACT8CCNEAR ??= null;
    $ACT8SCDIST ??= null; $ACT8SCNEAR ??= null; $ACT9CCDIST ??= null; $ACT9CCNEAR ??= null; $ACT9SCDIST ??= null;
    $ACT9SCNEAR ??= null; $AMSLEROD ??= null; $AMSLEROS ??= null; $ANTSEG_COMMENTS ??= null; $ARNEARODVA ??= null;
    $ARNEAROSVA ??= null; $ARODADD ??= null; $ARODAXIS ??= null; $ARODCYL ??= null; $ARODPRISM ??= null;
    $ARODSPH ??= null; $ARODVA ??= null; $AROSADD ??= null; $AROSAXIS ??= null; $AROSCYL ??= null;
    $AROSPRISM ??= null; $AROSSPH ??= null; $AROSVA ??= null; $ASSOCIATED1 ??= null; $ASSOCIATED2 ??= null;
    $ASSOCIATED3 ??= null; $CACCDIST ??= null; $CACCNEAR ??= null; $CC1 ??= null; $CC2 ??= null; $CC3 ??= null;
    $CHRONIC1 ??= null; $CHRONIC2 ??= null; $CHRONIC3 ??= null; $CONTEXT1 ??= null; $CONTEXT2 ??= null;
    $CONTEXT3 ??= null; $CONTRASTODVA ??= null; $CONTRASTOSVA ??= null; $CRCOMMENTS ??= null;
    $CRNEARODVA ??= null; $CRNEAROSVA ??= null; $CRODADD ??= null; $CRODAXIS ??= null; $CRODCYL ??= null;
    $CRODPRISM ??= null; $CRODSPH ??= null; $CRODVA ??= null; $CROSADD ??= null; $CROSAXIS ??= null;
    $CROSCYL ??= null; $CROSPRISM ??= null; $CROSSPH ??= null; $CROSVA ??= null; $CTLBRANDOD ??= null;
    $CTLBRANDOS ??= null; $CTLMANUFACTUREROD ??= null; $CTLMANUFACTUREROS ??= null; $CTLODADD ??= null;
    $CTLODAXIS ??= null; $CTLODBC ??= null; $CTLODCYL ??= null; $CTLODDIAM ??= null; $CTLODSPH ??= null;
    $CTLODVA ??= null; $CTLOSADD ??= null; $CTLOSAXIS ??= null; $CTLOSBC ??= null; $CTLOSCYL ??= null;
    $CTLOSDIAM ??= null; $CTLOSSPH ??= null; $CTLOSVA ??= null; $CTLSUPPLIEROD ??= null; $CTLSUPPLIEROS ??= null;
    $DACCDIST ??= null; $DACCNEAR ??= null; $DIL_MEDS ??= null; $DIMODPUPILSIZE1 ??= null;
    $DIMODPUPILSIZE2 ??= null; $DIMOSPUPILSIZE1 ??= null; $DIMOSPUPILSIZE2 ??= null; $DURATION1 ??= null;
    $DURATION2 ??= null; $DURATION3 ??= null; $EXT_COMMENTS ??= null; $GLAREODVA ??= null; $GLAREOSVA ??= null;
    $HERTELBASE ??= null; $HPI1 ??= null; $HPI2 ??= null; $HPI3 ??= null; $IOPTIME ??= null; $LADNEXA ??= null;
    $LBROW ??= null; $LCAROTID ??= null; $LCNV ??= null; $LCNVII ??= null; $LIODVA ??= null; $LIOSVA ??= null;
    $LLF ??= null; $LLL ??= null; $LMCT ??= null; $LMRD ??= null; $LOCATION1 ??= null; $LOCATION2 ??= null;
    $LOCATION3 ??= null; $LTEMPART ??= null; $LUL ??= null; $LVFISSURE ??= null; $MODIFY1 ??= null;
    $MODIFY2 ??= null; $MODIFY3 ??= null; $MOTILITYNORMAL ??= null; $MOTILITY_L0 ??= null; $MOTILITY_LI ??= null;
    $MOTILITY_LL ??= null; $MOTILITY_LLIO ??= null; $MOTILITY_LLSO ??= null; $MOTILITY_LR ??= null;
    $MOTILITY_LRSO ??= null; $MOTILITY_LS ??= null; $MOTILITY_R0 ??= null; $MOTILITY_RI ??= null;
    $MOTILITY_RL ??= null; $MOTILITY_RLIO ??= null; $MOTILITY_RLSO ??= null; $MOTILITY_RR ??= null;
    $MOTILITY_RRIO ??= null; $MOTILITY_RRSO ??= null; $MOTILITY_RS ??= null; $MRNEARODVA ??= null;
    $MRNEAROSVA ??= null; $MRODADD ??= null; $MRODAXIS ??= null; $MRODCYL ??= null; $MRODPRISM ??= null;
    $MRODSPH ??= null; $MRODVA ??= null; $MROSADD ??= null; $MROSAXIS ??= null; $MROSCYL ??= null;
    $MROSPRISM ??= null; $MROSSPH ??= null; $MROSVA ??= null; $NEURO_COMMENTS ??= null; $NPC ??= null;
    $ODAC ??= null; $ODACD ??= null; $ODAPD ??= null; $ODAXIALLENGTH ??= null; $ODCMT ??= null; $ODCOINS ??= null;
    $ODCOLOR ??= null; $ODCONJ ??= null; $ODCORNEA ??= null; $ODCUP ??= null; $ODDISC ??= null; $ODECL ??= null;
    $ODGONIO ??= null; $ODHERTEL ??= null; $ODIOPAP ??= null; $ODIOPFTN ??= null; $ODIOPTPN ??= null;
    $ODIRIS ??= null; $ODK1 ??= null; $ODK2 ??= null; $ODK2AXIS ??= null; $ODKTHICKNESS ??= null;
    $ODLENS ??= null; $ODLT ??= null; $ODMACULA ??= null; $ODNEARVA_1 ??= null; $ODNPA ??= null; $ODNPC ??= null;
    $ODPDMeasured ??= null; $ODPERIPH ??= null; $ODPUPILREACTIVITY ??= null; $ODPUPILSIZE1 ??= null;
    $ODPUPILSIZE2 ??= null; $ODREDDESAT ??= null; $ODSCHIRMER1 ??= null; $ODSCHIRMER2 ??= null; $ODTBUT ??= null;
    $ODVA_1 ??= null; $ODVESSELS ??= null; $ODVF ??= null; $ODVF1 ??= null; $ODVF2 ??= null; $ODVF3 ??= null;
    $ODVF4 ??= null; $ODVITREOUS ??= null; $ODW2W ??= null; $OSAC ??= null; $OSACD ??= null; $OSAPD ??= null;
    $OSAXIALLENGTH ??= null; $OSCMT ??= null; $OSCOINS ??= null; $OSCOLOR ??= null; $OSCONJ ??= null;
    $OSCORNEA ??= null; $OSCUP ??= null; $OSDISC ??= null; $OSECL ??= null; $OSGONIO ??= null; $OSHERTEL ??= null;
    $OSIOPAP ??= null; $OSIOPFTN ??= null; $OSIOPTPN ??= null; $OSIRIS ??= null; $OSK1 ??= null; $OSK2 ??= null;
    $OSK2AXIS ??= null; $OSKTHICKNESS ??= null; $OSLENS ??= null; $OSLT ??= null; $OSMACULA ??= null;
    $OSNEARVA_1 ??= null; $OSNPA ??= null; $OSNPC ??= null; $OSPDMeasured ??= null; $OSPERIPH ??= null;
    $OSPUPILREACTIVITY ??= null; $OSPUPILSIZE1 ??= null; $OSPUPILSIZE2 ??= null; $OSREDDESAT ??= null;
    $OSSCHIRMER1 ??= null; $OSSCHIRMER2 ??= null; $OSTBUT ??= null; $OSVA_1 ??= null; $OSVESSELS ??= null;
    $OSVF ??= null; $OSVF1 ??= null; $OSVF2 ??= null; $OSVF3 ??= null; $OSVF4 ??= null; $OSVITREOUS ??= null;
    $OSW2W ??= null; $PAMODVA ??= null; $PAMOSVA ??= null; $PHODVA ??= null; $PHOSVA ??= null;
    $PUPIL_COMMENTS ??= null; $PUPIL_NORMAL ??= null; $QUALITY1 ??= null; $QUALITY2 ??= null; $QUALITY3 ??= null;
    $RADNEXA ??= null; $RBROW ??= null; $RCAROTID ??= null; $RCNV ??= null; $RCNVII ??= null;
    $RETINA_COMMENTS ??= null; $RLF ??= null; $RLL ??= null; $RMCT ??= null; $RMRD ??= null; $RTEMPART ??= null;
    $RUL ??= null; $RVFISSURE ??= null; $RX_TYPE ??= null; $SCNEARODVA ??= null; $SCNEAROSVA ??= null;
    $SCODVA ??= null; $SCOSVA ??= null; $SEVERITY1 ??= null; $SEVERITY2 ??= null; $SEVERITY3 ??= null;
    $STEREOPSIS ??= null; $STEREO_FLY ??= null; $STEREO_ANIMALS ??= null; $STEREO_CIRCLES ??= null; $STEREO_FIXATION_PREF ??= null;
    $TIMING1 ??= null; $TIMING2 ??= null; $TIMING3 ??= null; $VERTFUSAMPS ??= null;
    $display_Add ??= null; $encounter_date ??= null; $pend ??= null;
    // End column defaults.
    $reportRefractions = eye_mag_report_refractions($pid, $encounter, $form_id);
    $hasReportRefraction = static function (string $label) use ($reportRefractions): bool {
        foreach ($reportRefractions as $section) {
            if (str_starts_with($section['label'], $label)) {
                return true;
            }
        }
        return false;
    };
    $providerID = getProviderIdOfEncounter($encounter);
    $providerNAME = getProviderName($providerID);
    $dated = new DateTime($encounter_date);
    $dated = $dated->format('Y/m/d');
    $visit_date = oeFormatShortDate($dated);
    ?>

    <?php
    // The PDF path must not load the web theme: mPDF fetches it over HTTP, and
    // Bootstrap's layout rules collapse the report's nested tables, spreading the
    // Alternate Cover Test grid one cell per page with the page footer squeezed
    // into a one-character column (18 pages instead of 2).
    if (!$PDF_OUTPUT) {
        Header::setupHeader(['no_dialog', 'no_jquery', 'fontawesome']);
    }
    ?>
    <link rel="stylesheet" href="../../forms/eye_mag/css/report.css?v=<?php echo attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes')); ?>">
    <style>
        <?php if ($PDF_OUTPUT) { ?>
        /* ONE body size. report_pdf_flush() strips every inline font-size below
           the letterhead, so text inherits 9pt from .rpt-body; the report.css
           classes that set their own size are pinned to it as well. */
        .rpt-body, .report_exam_group, .report_section, .rpt-pdf, .rpt-vitals {
            font-size: 9pt;
        }
        .rpt-letterhead {
            font-size: 10pt;
        }
        .rpt-h {
            font-weight: bold;
            color: #fff;
            background-color: #5f8b96; /* muted teal */
            padding: 1.5pt 4pt;
            margin: 0 0 3pt 0;
            width: 100%;
        }
        table.report_section { border-collapse: collapse; width: 100%; }
        .report_section td { padding: 1pt 3pt !important; vertical-align: middle; }
        .report_section td.rl-m, .report_section td:nth-child(2) { background-color: #eef3f4; }
        .report_section tr.rl-head td { background-color: #fff; font-weight: bold; text-decoration: underline; color: #333; }
        .report_section td[colspan] { background-color: #fff !important; }
        .report_section.report-additional td { background-color: #fff !important; }
        .act-tab { background-color: #5f8b96; color: #fff; padding: 1pt 3pt; }
        .report_exam_group td.act-cell { padding: 0 !important; line-height: 1; }
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
    <?php
    if ($PDF_OUTPUT) {
        ob_start();
    }
    ?>
    <div>
    <?php
    if (in_array($cols, ['Fax', 'Report', 'Fax-resend'])) {
        $letterhead = report_header($pid, 'PDF');
        echo $PDF_OUTPUT ? '<div class="rpt-letterhead">' . $letterhead . '</div><!--/rpt-letterhead-->' : $letterhead;
    }

    if ($PDF_OUTPUT) {
        $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
        $pc_facility = $session->get('pc_facility');
        $facilityService = new FacilityService();
        $facility = $pc_facility ? $facilityService->getById($pc_facility) : $facilityService->getPrimaryBillingLocation();
    }

    ?><br /><br />
    <table class="report_exam_group rpt-cc"<?php echo $PDF_OUTPUT ? ' style="width:100%;max-width:none;"' : ''; ?>>
        <tr>
            <td style="text-align:left;padding:1px;vertical-align:top;max-width:720px;">
                <table style="padding:5px;width:<?php echo $PDF_OUTPUT ? '100%' : '700px'; ?>;">
                    <tr>
                        <td style="text-align: justify;text-justify: inter-word;width:100%;">
                            <b><?php echo xlt('Chief Complaint'); ?>:</b> &nbsp;<?php echo text($CC1); ?>
                            <br/><br/>
                            <b><?php echo xlt('HPI'); ?>:</b>
                            &nbsp;<?php echo text($HPI1); ?>
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
                                            echo "&nbsp;" . text($CHRONIC2) . "<br />";
                                        }

                                        if ($CHRONIC3) {
                                            echo "&nbsp;" . text($CHRONIC3) . "<br />";
                                        }
                                } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="<?php echo $PDF_OUTPUT ? '' : 'width:220px;'; ?>padding:1px;vertical-align:top;">

                <?php
                    /**
                     * Display the patient Photo, if there is one.
                     * Using document Class does not work since if there is no patient photo,
                     * it spits back an error message saying document could not be found.
                     * Not what we want and it is above my pay grade to change a Service...
                     * Instead, we will check manually here for a patient_photograph
                     * and if it exists, display it via the Document class.
                     */
                    $result = eye_mag_patient_photo($pid);

                if (empty($result) || empty($result['id'])) {
                    //echo "no photo";
                } else {
                    //get patient photo
                    $tempDocC = new C_Document();
                    // The report embeds the file itself (disable_exit), which C_Document only
                    // allows once the caller has asked for the retrieve key.
                    $tempDocC->onReturnRetrieveKey();
                    try {
                        $fileTemp = $tempDocC->retrieve_action($pid, $result['id'], false, true, true, true, 'patient_picture');
                        if (!empty($fileTemp)) {
                            if ($PDF_OUTPUT) {
                                // tmp file in ../documents/temp since need to be available via webroot
                                $tmp_base = tempnam(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . '/documents/temp', "oer");
                                // mPDF can't decode an image unless the filename ends with a known extension.
                                $sniffed = '.jpg';
                                if (str_starts_with($fileTemp, "\x89PNG")) {
                                    $sniffed = '.png';
                                } elseif (str_starts_with($fileTemp, "GIF8")) {
                                    $sniffed = '.gif';
                                }
                                $from_file_tmp_web_name = $tmp_base . $sniffed;
                                rename($tmp_base, $from_file_tmp_web_name);
                                file_put_contents($from_file_tmp_web_name, $fileTemp);
                                echo "<img src='" . $from_file_tmp_web_name . "' style='width:220px;'>";
                                $tmp_files_remove[] = $from_file_tmp_web_name;
                            } else {
                                $filetoshow = OEGlobalsBag::getInstance()->getWebRoot() . "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture";
                                echo "<img src='" . $filetoshow . "' style='width:220px;'>";
                            }
                        }
                    } catch (\Throwable $ex) {
                        echo "No patient photo " . $ex;
                    }
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
    if ($choice === 'TEXT') {
        eye_mag_render_refraction_summary($reportRefractions);
    }
    if ($choice !== 'TEXT') {
        //exclude all of this from displaying on the summary mouseover by default
        ?>
        <table style="margin:1px auto;" class="report_exam_group rpt-pmsfh">
            <tr>
                <td style="width:680px;text-align:center;margin:1 auto;">
                    <?php
                    $PMSFH = build_PMSFH($pid);
                    if ($cols != 'Fax') {
                        show_PMSFH_report($PMSFH);
                    }

                        $count_rx = '0';

                        $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? ORDER BY RX_NUMBER";
                        $wear = sqlStatement($query, [$pid, $form_id, $encounter]);
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
                        // The glasses Rx stores horizontal and vertical prism
                        // separately; the report shows one Prism column, so
                        // compose them (the old single ODPRISM column does not
                        // exist on form_eye_mag_wearing).
                        ${"ODPRISM_$count_rx"} = trim((string)($wearing['ODPRISM'] ?? '')) ?: trim(trim(($wearing['ODHPD'] ?? '') . ' ' . ($wearing['ODHBASE'] ?? '')) . ' ' . trim(($wearing['ODVPD'] ?? '') . ' ' . ($wearing['ODVBASE'] ?? '')));
                        ${"OSPRISM_$count_rx"} = trim((string)($wearing['OSPRISM'] ?? '')) ?: trim(trim(($wearing['OSHPD'] ?? '') . ' ' . ($wearing['OSHBASE'] ?? '')) . ' ' . trim(($wearing['OSVPD'] ?? '') . ' ' . ($wearing['OSVBASE'] ?? '')));
                        ${"COMMENTS_$count_rx"} = $wearing['COMMENTS'];
                        ${"W_$count_rx"} = '1';
                        ${"RX_TYPE_$count_rx"} = $wearing['RX_TYPE'];
                    }
                    ?>

                </td>
            </tr>
        </table>
        <table class="rpt-vitals" style="background: #fff;
                      border: 1pt solid #000;
                      border-radius: 8px;
                      box-shadow: 5px 5px 9px #888;
                      font-family: Arial, Serif;
                      margin-right: 4px;
                      padding: 6px;
                      position: relative;
                      text-align: left;
                      vertical-align: top;
                      width: <?php echo $PDF_OUTPUT ? '100%' : '720px'; ?>;">
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
                            vertical-align: top;<?php echo $PDF_OUTPUT ? 'width:20%;' : ''; ?>">
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
                                    <td><?php echo xlt('Contrast{{Contrast Visual Acuity}}'); ?></td>
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
                          vertical-align: top;<?php echo $PDF_OUTPUT ? 'width:20%;' : ''; ?>">
                    <b class="underline"><?php echo xlt('Intraocular Pressures'); ?></b>
                    <table cellspacing="2" style="margin:2px;text-align:center;">
                        <tr style="font-weight:bold;">
                            <td style="text-align:center;font-weight:bold;"></td>
                            <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                            <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                        </tr>
                        <?php
                        $iopMethodLabels = eye_mag_iop_method_labels();
                        if ($ODIOPAP || $OSIOPAP) {
                            echo "<tr><td style='text-align:right;padding-right:10px;font-weight:bold;'>" . text($iopMethodLabels['AP']) . ":</td><td style='text-align:center;'>" . text($ODIOPAP) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPAP) . "</td></tr>";
                        }

                        if ($ODIOPTPN || $OSIOPTPN) {
                            echo "<tr><td style='text-align:right;padding-right:10px;font-weight:bold;'>" . text($iopMethodLabels['TPN']) . ":</td><td style='text-align:center;'>" . text($ODIOPTPN) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPTPN) . "</td></tr>";
                        }

                        if ($ODIOPFTN || $OSIOPFTN) {
                            echo "<tr><td style='text-align:right;padding-right:10px;font-weight:bold;'>" . text($iopMethodLabels['FT']) . ":</td><td style='text-align:center;'>" . text($ODIOPFTN) . "</td><td style='width:75px;text-align:center;'>" . text($OSIOPFTN) . "</td></tr>";
                        }
                        ?>
                        <tr>
                            <td colspan="3" style="text-align:center;font-weight:bold;">
                                @ <?php echo text(oeFormatTime((string) ($IOPTIME ?? ''), 'global')); ?>
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
                    if (${$ODzone} == '1') {
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
                    if (${$OSzone} == "1") {
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
                          vertical-align: top;<?php echo $PDF_OUTPUT ? 'width:20%;' : ''; ?>">
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
                          vertical-align: top;<?php echo $PDF_OUTPUT ? 'width:20%;' : ''; ?>">
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
                                <?php echo text($ODVF['1']); ?>
                            </td>
                            <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo text($ODVF['2']); ?>
                            </td>
                            <td></td>
                            <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo text($OSVF['1']); ?>
                            </td>
                            <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:center;">
                                <?php echo text($OSVF['2']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo text($ODVF['3']); ?>
                            </td>
                            <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo text($ODVF['4']); ?>
                            </td>
                            <td></td>
                            <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo text($OSVF['3']); ?>
                            </td>
                            <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:center;">
                                <?php echo text($OSVF['4']); ?>
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
                          vertical-align: top;<?php echo $PDF_OUTPUT ? 'width:20%;' : ''; ?>">
                    <b class="underline"><?php echo xlt('Motility'); ?></b>
                    <?php
                    if ($MOTILITYNORMAL == 'on') {
                        echo "<br /><br />&nbsp;" . xlt('D&V Full OU{{Ductions and Versions full both eyes}}') . "&nbsp;<br /><br />";
                    } else {
                        $motilityDisplay = eye_mag_motility_display_preference();
                        $rightMotility = [
                            'up' => $MOTILITY_RS, 'down' => $MOTILITY_RI,
                            'left' => $MOTILITY_RR, 'right' => $MOTILITY_RL,
                            'upperLeft' => $MOTILITY_RRSO, 'upperRight' => $MOTILITY_RLSO,
                            'lowerLeft' => $MOTILITY_RRIO, 'lowerRight' => $MOTILITY_RLIO,
                        ];
                        $leftMotility = [
                            'up' => $MOTILITY_LS, 'down' => $MOTILITY_LI,
                            'left' => $MOTILITY_LR, 'right' => $MOTILITY_LL,
                            'upperLeft' => $MOTILITY_LRSO, 'upperRight' => $MOTILITY_LLSO,
                            'lowerLeft' => $MOTILITY_LRIO, 'lowerRight' => $MOTILITY_LLIO,
                        ];
                        ?>
                            <table cellspacing="0" style="margin:1px auto;text-align:center;table-layout:fixed;width:100%;">
                                <tr style="font-weight:bold;">
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OD'); ?></td>
                                    <td style="text-align:center;text-decoration:underline;"><?php echo xlt('OS'); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:center;padding:0;"><?php echo report_motility_diagram($motilityDisplay, $rightMotility); ?></td>
                                    <td style="text-align:center;padding:0;"><?php echo report_motility_diagram($motilityDisplay, $leftMotility); ?></td>
                                </tr>
                            </table>
                            <?php
                    } ?>
                </td>

                <?php
                if (($PUPIL_NORMAL == '1') || $ODPUPILSIZE1 || $OSPUPILSIZE1) {
                    ?>
                        <td style="border-right:0px;<?php echo $PDF_OUTPUT ? 'width:20%;padding-left:6px;vertical-align:top;' : ''; ?>">
                        <?php
                        if (($PUPIL_NORMAL == '1') && (!$ODPUPILSIZE1 || !$OSPUPILSIZE1)) { ?>
                                    <b class="underline"><?php echo xlt('Pupils'); ?></b><br /><br />
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
            if (!empty($reportRefractions)) { ?>
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
                    <td style="font-weight:600;font-size:10px;text-align:center;"><?php echo xlt('Prisms'); ?></td>
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
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo text($RX_TYPE); ?></td>
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

                if ($hasReportRefraction('Auto Refraction')) { ?>
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


            if ($hasReportRefraction('Manifest Refraction')) { ?>
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

            if ($hasReportRefraction('Cycloplegic Refraction')) { ?>
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

            if ($hasReportRefraction('Contact Lens')) { ?>
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
                    <td id="LayerVision_ADDITIONAL" class="refraction <?php echo attr($display_Add); ?>"
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
                                <!-- <td><input type=text id="pend" name="pend"  value="<?php echo attr($pend); ?>"></td> -->
                            </tr>
                            <tr>
                                <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                                <td><?php echo text($OSAXIALLENGTH); ?></td>
                                <td><?php echo text($OSACD); ?></td>
                                <td><?php echo text($OSPDMeasured); ?></td>
                                <td><?php echo text($OSLT); ?></td>
                                <td><?php echo text($OSW2W); ?></td>
                                <td><?php echo text($OSECL); ?></td>
                                <!--  <td><input type=text id="pend" name="pend" value="<?php echo attr($pend); ?>"></td> -->
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
    <?php
    $pdf_zones = [];
    ob_start();
    report_zone_heading(xl('External Exam'));
    ?>
                    <table class="report_section">
                        <?php report_rl_header(); ?>
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
    <?php report_zone_emit(ob_get_clean(), "EXT", $encounter, $pid, $pdf_zones); ?>
    <!-- end of external exam -->
        <?php
        if ($OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS) {
            ?>
                <!-- start of Anterior Segment exam -->
                <?php
                ob_start();
                report_zone_heading(xl('Anterior Segment'));
                ?>
                            <table class="report_section">
                                <?php report_rl_header(); ?>
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
                                        width: 200px;"><?php echo text($ODGONIO); ?></td>
                                        <td style="text-align:center;font-weight:bold;"><?php echo xlt('Gonioscopy'); ?></td>
                                        <td style="text-align:left;
                                        flex-wrap: wrap;
                                        padding: 2px 10px;
                                        width: 200px;"><?php echo text($OSGONIO); ?></td>
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
                <?php report_zone_emit(ob_get_clean(), "ANTSEG", $encounter, $pid, $pdf_zones); ?>
                <!-- end of Anterior Segment exam -->

                <?php
        }
        ?>

        <!-- start of Other exam -->
        <?php
        if ($RLF || $LLF || $RMRD || $LMRD || $RVFISSURE || $LVFISSURE || $RCAROTID || $LCAROTID || $RTEMPART || $LTEMPART || $RCNV || $LCNV || $RCNVII || $LCNVII || $HERTELBASE || $ODCOLOR || $OSCOLOR || $ODREDDESAT || $OSREDDESAT || $ODCOINS || $OSCOINS || $ODNPA || $OSNPA || ($NPA ?? null) || $NPC || $STEREOPSIS || $STEREO_FLY || $STEREO_ANIMALS || $STEREO_CIRCLES || $STEREO_FIXATION_PREF || $DACCDIST || $DACCNEAR || $CACCDIST || $CACCNEAR || $VERTFUSAMPS) {
            ?>
                <?php
                ob_start();
                report_zone_heading(xl('Additional Findings'));
                ?>
                        <?php if ($ACT == 'on' and $MOTILITYNORMAL == 'on') { ?>
                                <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                            <?php echo xlt('Orthophoric'); ?>
              </span>
                            <?php } ?>
                            <table class="report_section report-additional">
                                <?php report_rl_header(); ?>
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
                                        width: 200px;"><?php echo text($LLF); ?></td>
                                        </tr>
                                        <?php
                            }

                            if (($RMRD ?? '') !== '' || ($LMRD ?? '') !== '') { ?>
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

                            if (($RVFISSURE ?? '') !== '' || ($LVFISSURE ?? '') !== '') { ?>
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

                            if ($ODCOLOR || $OSCOLOR || $ODREDDESAT || $OSREDDESAT || $ODCOINS || $OSCOINS || $ODNPA || $OSNPA || ($NPA ?? null) || $NPC || $STEREOPSIS || $STEREO_FLY || $STEREO_ANIMALS || $STEREO_CIRCLES || $STEREO_FIXATION_PREF) { ?>
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
                                                            title="<?php echo xla('Variation in red color discrimination between the eyes (eg. OD=100, OS=75)'); ?>"><?php echo xlt('Red Desaturation'); ?></span>
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
                                                            title="<?php echo xla('Near Point of Accommodation'); ?>"><?php echo xlt('NPA{{near point of Accommodation}}'); ?></span>
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

                                        if ($DACCDIST or $DACCNEAR or $CACCDIST or $CACCNEAR) { ?>
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

                                        if ($STEREOPSIS || $STEREO_FLY || $STEREO_ANIMALS || $STEREO_CIRCLES || $STEREO_FIXATION_PREF) {
                                            $stereopsisRows = [];
                                            if ($STEREO_FLY) {
                                                $stereopsisRows[] = [xl('Fly'), $STEREO_FLY];
                                            }
                                            if ($STEREO_ANIMALS !== null && $STEREO_ANIMALS !== '') {
                                                $stereopsisRows[] = [xl('Animals'), $STEREO_ANIMALS . '/3'];
                                            }
                                            if ($STEREO_CIRCLES !== null && $STEREO_CIRCLES !== '') {
                                                $stereopsisRows[] = [xl('Circles'), $STEREO_CIRCLES . '/9'];
                                            }
                                            if ($STEREO_FIXATION_PREF) {
                                                $stereopsisRows[] = [xl('Fixation Pref'), $STEREO_FIXATION_PREF];
                                            }
                                            if ($STEREOPSIS) {
                                                $stereopsisRows[] = [xl('Previous/Other'), $STEREOPSIS];
                                            }
                                            ?>
                                            <tr>
                                                <td style="text-align:right;font-weight:bold;"><?php echo xlt('Stereopsis'); ?>:&nbsp;
                                                </td>
                                                <td style="text-align:left;background-color:#fff;" colspan="2">
                                                    <table style="border-collapse:collapse;margin:0;">
                                                        <?php foreach ($stereopsisRows as [$stereopsisLabel, $stereopsisValue]) { ?>
                                                            <tr>
                                                                <td style="padding:0 5px 0 0 !important;text-align:right;white-space:nowrap;"><?php echo text($stereopsisLabel); ?>:</td>
                                                                <td style="padding:0 !important;text-align:left;white-space:nowrap;"><?php echo text($stereopsisValue); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                            } ?>
                            </table>
                <?php report_zone_emit(ob_get_clean(), "NEURO", $encounter, $pid, $pdf_zones); ?>
                <?php
        } ?>
        <!-- end of Other exam -->
        <!-- start of the Retina exam -->
        <?php
        if ($ODDISC || $OSDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
            ?>
                <?php
                ob_start();
                report_zone_heading(xl('Retina'));
                ?>
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
                                <?php report_rl_header(); ?>
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
                <?php report_zone_emit(ob_get_clean(), "RETINA", $encounter, $pid, $pdf_zones); ?>

                <?php
        } ?>
        <!-- end of Retina exam -->

        <?php
        // Scleral Depression drawing: saved with the standard zone document
        // naming but never emitted by the narrative report.  It has no exam
        // section of its own, so it renders across the FULL two-column
        // width — not squeezed into the right half of a zone row.
        $sd_doc = sqlQuery(
            "SELECT id FROM documents WHERE name LIKE ?",
            ['%' . $pid . '_' . $encounter . '_OU_SDRETINA_VIEW' . '%']
        );
        if (!empty($sd_doc['id'])) {
            ob_start();
            display_draw_image("SDRETINA", $encounter, $pid);
            $sd_drawing = trim((string) ob_get_clean());
            if ($sd_drawing !== '') {
                if ($PDF_OUTPUT) {
                    $sd_drawing = str_replace('width:78mm;', 'width:150mm;', $sd_drawing);
                    ob_start();
                    report_zone_heading(xlt('Retinal Drawing'));
                    $pdf_zones[] = ['section' => (string) ob_get_clean(), 'drawing' => $sd_drawing, 'fullwidth' => true];
                } else {
                    $sd_drawing = str_replace('width:220px;height:auto', 'width:440px;height:auto', $sd_drawing);
                    echo '<div class="report_exam_group">';
                    report_zone_heading(xlt('Retinal Drawing'));
                    echo '<div style="text-align:center;">' . $sd_drawing . '</div></div>';
                }
            }
        } ?>
    <?php
    if ($PDF_OUTPUT) {
        report_pdf_zone_layout($pdf_zones);
    }
    ?>
    </div>
    <?php
    if ($ACT != 'on') {
        $act_grids = [];
        foreach (['SCDIST' => xl('sc Distance{{without correction distance}}'), 'CCDIST' => xl('cc Distance{{with correction at distance}}'), 'SCNEAR' => xl('sc Near{{without correction near}}'), 'CCNEAR' => xl('cc Near{{with correction at Near}}')] as $act_suffix => $act_title) {
            if (${"ACT5" . $act_suffix}) {
                $act_vals = [];
                for ($k = 1; $k <= 11; $k++) {
                    $act_vals[$k] = ${"ACT" . $k . $act_suffix} ?? '';
                }
                $act_grids[] = [$act_title, $act_vals];
            }
        }
        if ($act_grids || $NEURO_COMMENTS) { ?>
        <table class="report_exam_group" style="text-align:left;<?php echo $PDF_OUTPUT ? 'width:100%;max-width:none;margin-top:10pt;' : ''; ?>">
            <tr>
                <td style="text-align:left;">
                    <?php
                    if ($act_grids) {
                        report_zone_heading(xl('Alternate Cover Test'));
                        // At the 9pt body size a grid is ~67mm wide, so two fit a PDF row.
                        $per_row = $PDF_OUTPUT ? 2 : 4;
                        foreach (array_chunk($act_grids, $per_row) as $act_row) {
                            echo '<table><tr>';
                            foreach ($act_row as [$act_title, $act_vals]) {
                                echo '<td style="vertical-align:top;padding:2px 4mm 4px 0;">';
                                report_ACT_grid($act_title, $act_vals);
                                echo '</td>';
                            }
                            echo '</tr></table>';
                        }
                    }
                    if ($NEURO_COMMENTS) { ?>
                        <b><?php echo xlt('Comments'); ?>:</b> <?php echo report_ACT($NEURO_COMMENTS); ?>
                    <?php } ?>
                </td>
            </tr>
        </table>
            <?php
        }
    }
    //end choice !== 'TEXT' -- include this in summary mouseover report.
    ?>
    <!-- start of IMPPLAN exam -->
    <table class="report_exam_group">
    <tr>
    <td style="text-align:left;padding:1px;vertical-align:top;width:480px;">
    <?php report_zone_heading(xl('Impression/Plan')); ?>
    <table style="">
    <tr>
    <td style="padding:5px;text-align: left;text-align:justify;width:475px;">
    <?php
    /**
     *  Retrieve and Display the IMPPLAN_items for the Impression/Plan zone.
     */
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? order by IMPPLAN_order ASC";
    $result = sqlStatement($query, [$form_id, $pid]);
    $i = '0';
    while ($ip_list = sqlFetchArray($result)) {
        $newdata = [
            'form_id' => $ip_list['form_id'],
            'pid' => $ip_list['pid'],
            'title' => $ip_list['title'],
            'code' => $ip_list['code'],
            'codetype' => $ip_list['codetype'],
            'codetext' => $ip_list['codetext'],
            'plan' => $ip_list['plan'],
            'IMPPLAN_order' => $ip_list['IMPPLAN_order']
        ];
        $IMPPLAN_items[$i] = $newdata;
        $i++;
    }

    if (!empty($IMPPLAN_items)) {
        //for ($i=0; $i < count($IMPPLAN_item); $i++) {
        foreach ($IMPPLAN_items as $item) {
            echo ($item['IMPPLAN_order'] + 1) . '. <b>' . text($item['title']) . '</b><br />';
            echo '<div style="padding-left:15px;">';
            $pattern = '/Code/';
            if (preg_match($pattern, (string) $item['code'])) {
                $item['code'] = '';
            }

            if ($item['codetext'] > '') {
                echo text($item['codetext']) . "<br />";
            } else {
                if ($item['code'] > '') {
                    if ($item['codetype'] > '') {
                        $item['code'] = $item['codetype'] . ": " . $item['code'];
                    }
                }
            }
            $plan = is_string($item['plan']) ? $item['plan'] : '';
            $plan = str_replace(
                ["\v", "\f", "\xC2\x85", "\u{2028}", "\u{2029}"],
                "\n",
                $plan
            );
            echo nl2br(text($plan)) . "</div><br />";
        }
            $query = "SELECT * FROM form_eye_mag_orders where form_id=? and pid=? ORDER BY id ASC";
            $PLAN_results = sqlStatement($query, [$form_id, $pid]);


        if (!empty($PLAN_results)) { ?>
                <br />
                <b><?php echo xlt('Orders') . "/" . xlt('Next Visit'); ?>:</b>
                <br />
                <div style="padding-left:15px;padding-bottom:10px;width:400px;">
                    <?php
                    while ($plan_row = sqlFetchArray($PLAN_results)) {
                        echo text($plan_row['ORDER_DETAILS']) . "<br />";
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
                    $from_file = OEGlobalsBag::getInstance()->getProjectDir() . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg";
                    if (file_exists($from_file)) {
                        echo "<img style='width:50mm;' src='$from_file'><hr style='width:40mm;' />";
                    } else {
                        echo "<br /><br />";
                    }
                } else {
                    $signature = OEGlobalsBag::getInstance()->getProjectDir() . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg";
                    if (file_exists($signature)) {
                        echo "<img style='width:50mm;' src='" . OEGlobalsBag::getInstance()->getWebRoot() . "/interface/forms/" . $form_folder . "/images/sign_" . $providerID . ".jpg'><hr style='width:40mm;' />";
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
            eye_mag_report_materials_text($pid, $form_id, $materialsPdf);
            report_pdf_flush();
            return;
    }
    eye_mag_report_materials_text($pid, $form_id, $materialsPdf);
    report_pdf_flush();
}

/** Acknowledge both kinds of instructions and preserve originals in PDF output. */
function eye_mag_report_materials_text($pid, $formId, $materialsPdf = null): void
{
    global $PDF_OUTPUT, $pdf;
    $materials = eye_mag_materials_for_report((int) $formId, (int) $pid);
    $content = (string) ($materials['content'] ?? '');
    $includeText = (int) ($materials['include_text'] ?? 0) === 1 && trim($content) !== '';
    $includeHandout = !empty($materials['handouts']);
    if (!$includeText && !$includeHandout) {
        return;
    }
    $embedded = false;
    if ($PDF_OUTPUT) {
        // Core custom_report.php owns a global mPDF; Taskman passes its local one.
        $embedded = eye_mag_embed_materials($materialsPdf ?? $pdf, (int) $formId, (int) $pid, $materials);
        echo '<pagebreak />';
    }
    echo '<div class="report_exam_group" style="text-align:left;width:100%;">';
    report_zone_heading(xl('Patient Materials'));
    if ($includeText) {
        echo '<p><b>' . xlt('Patient instructions included') . '</b></p>';
        echo '<div style="white-space:pre-wrap;padding:6px;">' . nl2br(text($content)) . '</div>';
    }
    foreach ($materials['handouts'] as $handout) {
        if (empty($handout['pdf_data']) || ($PDF_OUTPUT && !$embedded)) {
            echo '<p><b>' . xlt('Selected PDF handout could not be included. Restore or replace it and generate the report again.') . '</b></p>';
            echo '<p>' . text($handout['name'] ?: (xl('Document') . ' #' . $handout['document_id'])) . '</p>';
        } else {
            echo '<p><b>' . xlt('Patient PDF instructions included') . ':</b> ' . text($handout['name']) . '</p>';
            if ($PDF_OUTPUT) {
                echo '<p>' . xlt('An exact copy of the original PDF is embedded in this report. Use a PDF reader with an Attachments panel to open or print it.') . '</p>';
            }
        }
    }
    echo '</div>';
}

function display_draw_image($zone, $encounter, $pid): void
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
        $fname = basename((string) $d->get_url());

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
            echo '<td>' . text($note->get_note()) . '<br /><br /></td>';
            echo '</tr>';
        }

        if (!empty($notes)) {
            echo "</table>";
        }

        //               if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
        if ($PDF_OUTPUT) {
            $tempDocC = new C_Document();
            // Server-side read of the raw file (disable_exit=true) needs the
            // retrieve key, as core's custom_report.php does; without it
            // C_Document denies the request (403) and no PDF is made.
            $tempDocC->onReturnRetrieveKey();
            $fileTemp = $tempDocC->retrieve_action($pid, $doc['id'], false, true, true, true);
            if (eye_mag_drawing_is_untouched_base($fileTemp, $zone)) {
                return;
            }
            // tmp file in ../documents/temp since need to be available via webroot
            $tmp_base = tempnam(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . '/documents/temp', "oer");
            // mPDF can't decode an image unless the filename ends with a known extension.
            $sniffed = '.jpg';
            if (str_starts_with($fileTemp, "\x89PNG")) {
                $sniffed = '.png';
            } elseif (str_starts_with($fileTemp, "GIF8")) {
                $sniffed = '.gif';
            }
            $from_file_tmp_web_name = $tmp_base . $sniffed;
            rename($tmp_base, $from_file_tmp_web_name);
            file_put_contents($from_file_tmp_web_name, $fileTemp);
            echo "<img src='" . $from_file_tmp_web_name . "' style='width:78mm;display:block;margin:2mm auto;'>";
            $tmp_files_remove[] = $from_file_tmp_web_name;
        } else {
            $filetoshow = OEGlobalsBag::getInstance()->getWebRoot() . "/controller.php?document&retrieve&patient_id=" . attr_url($pid) . "&document_id=" . attr_url($doc['id']) . "&as_file=false&original_file=true&disable_exit=false&show_original=true&blahblah=" . attr_url(random_int(0, mt_getrandmax()));
            echo "<img src='" . $filetoshow . "' style='width:220px;height:auto;'>";
        }
    } else {
        //else show base_image
        $filetoshow = "../../forms/" . $form_folder . "/images/" . $side . "_" . $zone . "_BASE.jpg";
        if ($PDF_OUTPUT) {
            $filetoshow = OEGlobalsBag::getInstance()->getWebRoot() . "/interface/forms/" . $form_folder . "/images/" . $side . "_" . $zone . "_BASE.jpg";
        }

      // uncomment to show base image, no touch up by user.
      // echo "<img src='". $filetoshow."' style='width:220px;height:120px;'>";
    }

    return;
}

/**
 * Ignore legacy canvas documents produced by the old mouseout handler when
 * the clinician never marked the base image. JPEG re-encoding can alter edge
 * pixels slightly, so only differences greater than the verified compression
 * noise are treated as clinical marks.
 */
function eye_mag_drawing_is_untouched_base(string $drawingData, string $zone): bool
{
    global $form_folder;
    if (!function_exists('imagecreatefromstring')) {
        return false;
    }
    $baseFile = OEGlobalsBag::getInstance()->getProjectDir()
        . '/interface/forms/' . $form_folder . '/images/OU_' . $zone . '_BASE.jpg';
    if (!is_file($baseFile)) {
        return false;
    }
    $drawing = @imagecreatefromstring($drawingData);
    $base = @imagecreatefromjpeg($baseFile);
    if (!$drawing || !$base || imagesx($drawing) !== imagesx($base) || imagesy($drawing) !== imagesy($base)) {
        if ($drawing) {
            imagedestroy($drawing);
        }
        if ($base) {
            imagedestroy($base);
        }
        return false;
    }
    $width = imagesx($drawing);
    $height = imagesy($drawing);
    for ($y = 0; $y < $height; ++$y) {
        for ($x = 0; $x < $width; ++$x) {
            $drawingColor = imagecolorat($drawing, $x, $y);
            $baseColor = imagecolorat($base, $x, $y);
            $difference = abs((($drawingColor >> 16) & 255) - (($baseColor >> 16) & 255))
                + abs((($drawingColor >> 8) & 255) - (($baseColor >> 8) & 255))
                + abs(($drawingColor & 255) - ($baseColor & 255));
            if ($difference > 40) {
                imagedestroy($drawing);
                imagedestroy($base);
                return false;
            }
        }
    }
    imagedestroy($drawing);
    imagedestroy($base);
    return true;
}

/**
 * End of the PDF body: strip every inline font-size outside the letterhead so
 * the whole report inherits one size from .rpt-body. The markup carries over a
 * hundred inline sizes (px, em, rem), and mPDF lets them beat any stylesheet
 * rule, so they cannot be overridden from CSS alone.
 */
function report_pdf_flush(): void
{
    global $PDF_OUTPUT;
    if (!$PDF_OUTPUT) {
        return;
    }
    $html = (string) ob_get_clean();
    $parts = preg_split('~(<div class="rpt-letterhead">.*?</div><!--/rpt-letterhead-->)~s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $i => $part) {
        if (str_starts_with($part, '<div class="rpt-letterhead">')) {
            continue;
        }
        $parts[$i] = preg_replace_callback('~\bstyle\s*=\s*(["\'])(.*?)\1~s', function ($m) {
            return 'style=' . $m[1] . preg_replace('~font-size\s*:\s*[^;"\']+;?~i', '', $m[2]) . $m[1];
        }, $part);
    }
    echo '<div class="rpt-body">' . implode('', $parts) . '</div>';
}

/**
 * Section heading for an exam zone: a banded heading in the PDF, the
 * underlined label on screen.
 */
function report_zone_heading(string $title): void
{
    global $PDF_OUTPUT;
    if ($PDF_OUTPUT) {
        // A one-cell table: mPDF shrinks a div's background to its text inside a
        // table cell, and carries the white text colour into the next line.
        echo '<table style="width:100%;border-collapse:collapse;margin-bottom:3pt;"><tr><td class="rpt-h">' . text($title) . '</td></tr></table>';
    } else {
        echo '<span style="font-weight:bold;text-decoration: underline">' . text($title) . ':</span>';
    }
}

/**
 * Right / Left column header of a zone table. In the PDF every zone uses the
 * same fixed column widths, so the finding labels line up across all zones.
 */
function report_rl_header(): void
{
    global $PDF_OUTPUT;
    $w = $PDF_OUTPUT ? ['38%', '24%', '38%'] : ['150px', '100px', '150px'];
    echo '<tr class="rl-head">'
        . '<td class="rl-r" style="font-weight:bold;text-align:right;padding-right:10px;text-decoration:underline;width:' . $w[0] . ';">' . xlt('Right') . '</td>'
        . '<td class="rl-m" style="width:' . $w[1] . ';"></td>'
        . '<td class="rl-l" style="font-weight:bold;text-align:left;padding-left:10px;text-decoration:underline;width:' . $w[2] . ';">' . xlt('Left') . '</td>'
        . '</tr>';
}

/**
 * Output one exam zone (heading + findings table) with its drawing.
 * On screen the zone prints immediately, drawing to its right. For the PDF it
 * is queued for report_pdf_zone_layout(), and the pixel widths written for
 * the screen are dropped so the fixed header widths set the columns.
 */
function report_zone_emit(string $section, string $zone, $encounter, $pid, array &$pdf_zones): void
{
    global $PDF_OUTPUT;
    ob_start();
    display_draw_image($zone, $encounter, $pid);
    $drawing = trim((string) ob_get_clean());
    if ($PDF_OUTPUT) {
        $section = preg_replace_callback('~<td\b([^>]*)>~', function ($m) {
            if (str_contains($m[1], 'class="rl-')) {
                return $m[0];
            }
            return '<td' . preg_replace('~\bwidth\s*:\s*[\d.]+px\s*;?~i', '', $m[1]) . '>';
        }, $section);
        $pdf_zones[] = ['section' => $section, 'drawing' => $drawing];
        return;
    }
    echo '<div class="report_exam_group"><table><tr>'
        . '<td style="text-align:left;vertical-align:top;padding:1px;">' . $section . '</td>'
        . '<td style="text-align:center;padding:1px;vertical-align:middle;">' . $drawing . '</td>'
        . '</tr></table></div>';
}

/**
 * PDF layout of the exam zones, in two half-width columns. A zone with a
 * drawing takes a row, the drawing pinned to its right. A zone without one
 * moves up into the free right half of the row above when there is room.
 */
function report_pdf_zone_layout(array $zones): void
{
    $rows = [];
    foreach ($zones as $z) {
        if (!empty($z['fullwidth'])) {
            // No left section ever exists for this entry (Scleral
            // Depression): one centered row across both columns.
            $rows[] = [$z['section'] . $z['drawing'], null, 'full'];
            continue;
        }
        if ($z['drawing'] !== '') {
            $rows[] = [$z['section'], $z['drawing'], true];
        } elseif ($rows && end($rows)[1] === null) {
            $rows[array_key_last($rows)][1] = $z['section'];
        } else {
            $rows[] = [$z['section'], null, false];
        }
    }
    foreach ($rows as [$left, $right, $is_drawing]) {
        if ($is_drawing === 'full') {
            echo '<table class="rpt-pdf" style="width:100%;border-collapse:collapse;margin-top:10pt;"><tr>'
                . '<td colspan="2" style="text-align:center;padding:0;">' . $left . '</td>'
                . '</tr></table>';
            continue;
        }
        // The 10pt row gap and the 5mm gutter keep neighbouring zones visually apart.
        echo '<table class="rpt-pdf" style="width:100%;border-collapse:collapse;margin-top:10pt;"><tr>'
            . '<td style="width:50%;vertical-align:top;padding:0 2.5mm 0 0;">' . $left . '</td>'
            . '<td style="width:50%;vertical-align:' . ($is_drawing ? 'middle' : 'top') . ';text-align:' . ($is_drawing ? 'center' : 'left') . ';padding:0 0 0 2.5mm;">' . ($right ?? '') . '</td>'
            . '</tr></table>';
    }
}

/**
 * Render the practice-selected motility notation without relying on nested
 * tables or background images, which mPDF can clip inside the five-column
 * summary. Both X and H charts show one perpendicular tick for each 25%
 * limitation grade.
 */
function report_motility_diagram(string $display, array $values): string
{
    global $PDF_OUTPUT;
    $display = strtoupper($display) === 'H' ? 'H' : 'X';
    $grade = static fn(string $key): int => max(0, min(4, (int)($values[$key] ?? 0)));
    $line = static fn(float $x1, float $y1, float $x2, float $y2, float $width = 1.8): string => sprintf(
        '<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#111" stroke-width="%.2f" stroke-linecap="square"/>',
        $x1,
        $y1,
        $x2,
        $y2,
        $width
    );
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">';

    if ($display === 'H') {
        $svg .= $line(21, 25, 21, 95, 2.5)
            . $line(99, 25, 99, 95, 2.5)
            . $line(21, 60, 99, 60, 2.5);
        $ticks = static function (float $cx, float $cy, float $dx, float $dy, string $key, float $outer) use ($grade, $line): string {
            $output = '';
            for ($index = 0; $index < $grade($key); ++$index) {
                $distance = $outer - ($index * 6);
                $tx = $cx + ($dx * $distance);
                $ty = $cy + ($dy * $distance);
                $px = -$dy * 5;
                $py = $dx * 5;
                $output .= $line($tx - $px, $ty - $py, $tx + $px, $ty + $py, 2);
            }
            return $output;
        };
        $svg .= $ticks(21, 60, 0, -1, 'upperLeft', 30)
            . $ticks(60, 60, -1, 0, 'left', 22)
            . $ticks(21, 60, 0, 1, 'lowerLeft', 30)
            . $ticks(99, 60, 0, -1, 'upperRight', 30)
            . $ticks(60, 60, 1, 0, 'right', 22)
            . $ticks(99, 60, 0, 1, 'lowerRight', 30);
    } else {
        $directions = [
            'up' => [0, -1], 'down' => [0, 1],
            'left' => [-1, 0], 'right' => [1, 0],
            'upperLeft' => [-0.7071, -0.7071],
            'upperRight' => [0.7071, -0.7071],
            'lowerLeft' => [-0.7071, 0.7071],
            'lowerRight' => [0.7071, 0.7071],
        ];
        foreach ($directions as $key => [$dx, $dy]) {
            $svg .= $line(60, 60, 60 + ($dx * 35), 60 + ($dy * 35), 1.6);
            for ($index = 0; $index < $grade($key); ++$index) {
                $distance = 31 - ($index * 6);
                $tx = 60 + ($dx * $distance);
                $ty = 60 + ($dy * $distance);
                $px = -$dy * 5;
                $py = $dx * 5;
                $svg .= $line($tx - $px, $ty - $py, $tx + $px, $ty + $py, 2);
            }
        }
    }
    $svg .= '</svg>';
    $size = $PDF_OUTPUT ? '15mm' : '58px';
    return '<img alt="" src="data:image/svg+xml;base64,' . base64_encode($svg)
        . '" style="display:block;margin:0 auto;width:' . $size . ';height:' . $size . ';" />';
}

/**
 * One Alternate Cover Test grid, drawn with the same separators as the form:
 * a 3x3 tic-tac-toe of gaze positions with the head-tilt cells below.
 */
function report_ACT_grid(string $title, array $v): void
{
    global $PDF_OUTPUT;
    // Every cell, labels included, is the same fixed square, so all grids are
    // the same size whether a gaze position holds data or not. 5 x 13mm fits "ORTHO" at the 9pt body size.
    $side = $PDF_OUTPUT ? '13mm' : '58px';
    $size = "width:$side;height:$side;min-width:$side;max-width:$side;";
    $td = fn(string $content, string $style = '') => '<td class="act-cell" style="' . $size . 'text-align:center;vertical-align:middle;overflow:hidden;' . $style . '">' . $content . '</td>';
    $cell = fn(int $k, string $border) => $td(report_ACT($v[$k] ?? ''), $border);
    $thin = '1pt solid #000';
    $thick = '2pt solid #000';
    $icon = fn(string $cls) => $PDF_OUTPUT ? report_ACT_arrow(str_contains($cls, 'reply')) : '<i class="fas ' . $cls . '"></i>';
    echo '<table style="border:1pt solid grey;border-collapse:collapse;table-layout:fixed;"><tr><td class="act-tab" colspan="5" style="height:' . ($PDF_OUTPUT ? '4.5mm' : '25px') . ';'
        . ($PDF_OUTPUT ? '' : 'background-color:#2d6775;color:#fff;padding:2px 6px;') . '">' . text($title) . '</td></tr>';
    echo '<tr>' . $td(xlt('R{{right}}'), 'font-weight:bold;')
        . $cell(1, "border-right:$thin;border-bottom:$thin;")
        . $cell(2, "border-left:$thin;border-right:$thin;border-bottom:$thin;")
        . $cell(3, "border-left:$thin;border-bottom:$thin;")
        . $td(xlt('L{{left}}'), 'font-weight:bold;') . '</tr>';
    echo '<tr>' . $td($icon('fa-reply rotate-left'))
        . $cell(4, "border-top:$thin;border-right:$thin;border-bottom:$thin;")
        . $cell(5, "border:$thin;")
        . $cell(6, "border-top:$thin;border-left:$thin;border-bottom:$thin;")
        . $td($icon('fa-share rotate-right')) . '</tr>';
    echo '<tr>'
        . $cell(10, "border-top:$thick;border-right:$thick;")
        . $cell(7, "border-top:$thin;border-right:$thin;")
        . $cell(8, "border-top:$thin;border-left:$thin;border-right:$thin;")
        . $cell(9, "border-top:$thin;border-left:$thin;")
        . $cell(11, "border-top:$thick;border-left:$thick;")
        . '</tr></table>';
}

/**
 * mPDF draws neither Font Awesome glyphs nor CSS rotation, so the PDF gets
 * the same curved "down and outward" head-tilt arrows as inline SVG: the tail
 * starts at the grid side and the head points down on the outside.
 */
function report_ACT_arrow(bool $left_side): string
{
    $path = $left_side
        ? 'M17 6 C 10 2, 5 5, 5 13'
        : 'M3 6 C 10 2, 15 5, 15 13';
    $head = $left_side ? 'M2 11 L5 16 L8 11' : 'M12 11 L15 16 L18 11';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18">'
        . '<path d="' . $path . '" fill="none" stroke="#000" stroke-width="1.6"/>'
        . '<path d="' . $head . '" fill="none" stroke="#000" stroke-width="1.6"/></svg>';
    return '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '" style="width:5mm;height:4.5mm;" />';
}

function report_ACT($term): string
{
    $term = nl2br(htmlspecialchars((string) $term, ENT_NOQUOTES));
    return $term . "&nbsp;";
}
?>
