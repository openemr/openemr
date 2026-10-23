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

use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\EyeFormData;
use OpenEMR\Forms\EyeMag\ProviderSignature;
use OpenEMR\Forms\EyeMag\ReportRenderer;
use OpenEMR\Services\FacilityService;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_embed_materials;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_labels;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_materials_for_report;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_motility_display_preference;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_patient_photo;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$form_name = "eye_mag";
global $form_folder;
$form_folder = "eye_mag";

$facilityService = new FacilityService();

require_once(__DIR__ . "/../../forms/" . $form_folder . "/php/" . $form_folder . "_functions.php");

if (CurrentRequest::get()->request->get('CHOICE', CurrentRequest::get()->query->get('CHOICE')) ?? '') {
    $choice = CurrentRequest::get()->request->get('choice', CurrentRequest::get()->query->get('choice'));
}

if (CurrentRequest::get()->request->get('ptid', CurrentRequest::get()->query->get('ptid')) ?? '') {
    $pid = CurrentRequest::get()->request->get('ptid', CurrentRequest::get()->query->get('ptid'));
}

if (CurrentRequest::get()->request->get('encid', CurrentRequest::get()->query->get('encid')) ?? '') {
    $encounter = CurrentRequest::get()->request->get('encid', CurrentRequest::get()->query->get('encid'));
}

if (CurrentRequest::get()->request->get('formid', CurrentRequest::get()->query->get('formid')) ?? '') {
    $form_id = CurrentRequest::get()->request->get('formid', CurrentRequest::get()->query->get('formid'));
}

if (CurrentRequest::get()->request->get('formname', CurrentRequest::get()->query->get('formname')) ?? '') {
    $form_name = CurrentRequest::get()->request->get('formname', CurrentRequest::get()->query->get('formname'));
}

if (!($id ?? '')) {
    $id = $form_id ?? '';
}

// Get users preferences, for this user
// (and if not the default where a fresh install begins from, or someone else's)
$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND id=? ORDER BY ZONE_ORDER,ordering";
$result = QueryUtils::fetchRecords($query, [$session->get('authUserID')]);
foreach ($result as $prefs) {
    $LOCATION = $prefs['LOCATION'];
    ${$LOCATION} = text($prefs['GOVALUE']);
}

/** Resolve the requested Eye Form record and render its encounter report. */
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
    $requestedFormId = EyeFormData::integer($id);
    $fidRow = $requestedFormId > 0 ? QueryUtils::querySingleRow(
        "SELECT form_id FROM forms WHERE pid=? AND encounter=? AND formdir='eye_mag' AND deleted!='1' AND form_id=? LIMIT 1",
        [$pid ?? null, $encounter ?? null, $requestedFormId]
    ) : [];
    if (!($fidRow['form_id'] ?? null)) {
        $fidRow = QueryUtils::querySingleRow(
            "SELECT form_id FROM forms WHERE pid=? AND encounter=? AND formdir='eye_mag' AND deleted!='1' ORDER BY form_id DESC LIMIT 1",
            [$pid ?? null, $encounter ?? null]
        );
    }
    if ((bool) ($fidRow['form_id'] ?? null)) {
        $form_id = EyeFormData::integer($fidRow['form_id']);
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
    $objQuery = QueryUtils::querySingleRow($query, [$form_id, $encounter, $pid]);
    @extract(EyeFormData::row($objQuery));
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
/** Render the selected exam narrative, prescriptions, drawings, and patient materials. */
function narrative($pid, $encounter, $cols, $form_id, $choice = 'full', ?Mpdf $materialsPdf = null): void
{
    global $form_folder;
    global $PDF_OUTPUT;

    $reportPdf = $materialsPdf ?? OEGlobalsBag::getInstance()->get('pdf');
    $reportPdf = $reportPdf instanceof Mpdf ? $reportPdf : null;
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

    $encounter_data = QueryUtils::querySingleRow($query, [$form_id, $encounter, $pid]);
    @extract(EyeFormData::row($encounter_data));
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
    $MOTILITY_LRSO ??= null; $MOTILITY_LRIO ??= null; $MOTILITY_LS ??= null; $MOTILITY_R0 ??= null; $MOTILITY_RI ??= null;
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
    $reportRefractions = ReportRenderer::refractions(EyeFormData::integer($pid), EyeFormData::integer($encounter), EyeFormData::integer($form_id));
    /** Determine whether the report contains a documented refraction section with the requested label. */
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
        /* ONE body size. ReportRenderer::flushPdf((bool) $PDF_OUTPUT) strips every inline font-size below
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

                if (!($result) || !($result['id'] ?? null)) {
                    //echo "no photo";
                } else {
                    //get patient photo
                    $tempDocC = new C_Document();
                    // The report embeds the file itself (disable_exit), which C_Document only
                    // allows once the caller has asked for the retrieve key.
                    $tempDocC->onReturnRetrieveKey();
                    try {
                        $fileTemp = $tempDocC->retrieve_action((string) $pid, $result['id'], false, true, true, true, 'patient_picture');
                        $fileTemp = EyeFormData::text($fileTemp);
                        if ((bool) ($fileTemp)) {
                            if ($PDF_OUTPUT) {
                                // tmp file in ../documents/temp since need to be available via webroot
                                $tmp_base = tempnam(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . '/documents/temp', "oer");
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
                                $filetoshow = OEGlobalsBag::getInstance()->getWebRoot() . "/controller.php?document&retrieve&patient_id=" . attr_url(EyeFormData::text($pid)) . "&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture";
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
        ReportRenderer::renderRefractionSummary($reportRefractions);
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

                        $count_rx = 0;
                        $wearingRows = [];

                        $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? ORDER BY RX_NUMBER";
                        $wear = QueryUtils::sqlStatementThrowException($query, [$pid, $form_id, $encounter]);
                    while ($wearing = QueryUtils::fetchArrayFromResultSet($wear)) {
                        $wearing = EyeFormData::textRow(EyeFormData::row($wearing));
                        $count_rx++;
                        $wearingRows[$count_rx]['display_W'] = '';
                        $wearingRows[$count_rx]['ODSPH'] = $wearing['ODSPH'];
                        $wearingRows[$count_rx]['ODCYL'] = $wearing['ODCYL'];
                        $wearingRows[$count_rx]['ODAXIS'] = $wearing['ODAXIS'];
                        $wearingRows[$count_rx]['OSSPH'] = $wearing['OSSPH'];
                        $wearingRows[$count_rx]['OSCYL'] = $wearing['OSCYL'];
                        $wearingRows[$count_rx]['OSAXIS'] = $wearing['OSAXIS'];
                        $wearingRows[$count_rx]['ODMIDADD'] = $wearing['ODMIDADD'];
                        $wearingRows[$count_rx]['OSMIDADD'] = $wearing['OSMIDADD'];
                        $wearingRows[$count_rx]['ODADD'] = $wearing['ODADD'];
                        $wearingRows[$count_rx]['OSADD'] = $wearing['OSADD'];
                        $wearingRows[$count_rx]['ODVA'] = $wearing['ODVA'];
                        $wearingRows[$count_rx]['OSVA'] = $wearing['OSVA'];
                        $wearingRows[$count_rx]['ODNEARVA'] = $wearing['ODNEARVA'];
                        $wearingRows[$count_rx]['OSNEARVA'] = $wearing['OSNEARVA'];
                        // The glasses Rx stores horizontal and vertical prism
                        // separately; the report shows one Prism column, so
                        // compose them (the old single ODPRISM column does not
                        // exist on form_eye_mag_wearing).
                        $wearingRows[$count_rx]['ODPRISM'] = trim(EyeFormData::text($wearing['ODPRISM'] ?? '')) ?: trim(trim(($wearing['ODHPD'] ?? '') . ' ' . ($wearing['ODHBASE'] ?? '')) . ' ' . trim(($wearing['ODVPD'] ?? '') . ' ' . ($wearing['ODVBASE'] ?? '')));
                        $wearingRows[$count_rx]['OSPRISM'] = trim(EyeFormData::text($wearing['OSPRISM'] ?? '')) ?: trim(trim(($wearing['OSHPD'] ?? '') . ' ' . ($wearing['OSHBASE'] ?? '')) . ' ' . trim(($wearing['OSVPD'] ?? '') . ' ' . ($wearing['OSVBASE'] ?? '')));
                        $wearingRows[$count_rx]['COMMENTS'] = $wearing['COMMENTS'];
                        $wearingRows[$count_rx]['W'] = '1';
                        $wearingRows[$count_rx]['RX_TYPE'] = $wearing['RX_TYPE'];
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
                        if ((bool) ($BINOCVA ?? null)) { ?>
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
                                @ <?php echo text(\OpenEMR\Services\Utils\DateFormatterUtils::oeFormatTime(EyeFormData::text($IOPTIME ?? ''), 'global')); ?>
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
                                    <td style="text-align:center;padding:0;"><?php echo ReportRenderer::motilityDiagram($motilityDisplay, $rightMotility, (bool) $PDF_OUTPUT); ?></td>
                                    <td style="text-align:center;padding:0;"><?php echo ReportRenderer::motilityDiagram($motilityDisplay, $leftMotility, (bool) $PDF_OUTPUT); ?></td>
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
            if ((bool) ($reportRefractions)) { ?>
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
                    if ($wearingRows[$i]['RX_TYPE'] == "0") {
                        $RX_TYPE = '';
                    } elseif ($wearingRows[$i]['RX_TYPE'] == "1") {
                        $RX_TYPE = xlt('Bifocals');
                    } elseif ($wearingRows[$i]['RX_TYPE'] == "2") {
                        $RX_TYPE = xlt('Trifocals');
                    } elseif ($wearingRows[$i]['RX_TYPE'] == "3") {
                        $RX_TYPE = xlt('Progressive');
                    }
                    ?>
                    <tr>
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo xlt('Current RX') . " #" . $i . ": "; ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODSPH']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODCYL']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODAXIS']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODPRISM']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODVA']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODMIDADD']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODADD']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['ODNEARVA']) ?: "-"); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;font-size:0.7em;text-align:right;"><?php echo text($RX_TYPE); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSSPH']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSCYL']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSAXIS']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSPRISM']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSVA']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSMIDADD']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSADD']) ?: "-"); ?></td>
                        <td style="font-weight:400;font-size:10px;text-align:center;"><?php echo(text($wearingRows[$i]['OSNEARVA']) ?: "-"); ?></td>
                    </tr>
                    <?php
                    if ($wearingRows[$i]['COMMENTS']) {
                        ?>
                        <tr>
                            <td></td>
                            <td colspan="2"><?php echo xlt('Comments'); ?>:</td>
                            <td colspan="7"><?php echo text($wearingRows[$i]['COMMENTS']); ?></td>
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
    ReportRenderer::zoneHeading(xl('External Exam'), (bool) $PDF_OUTPUT);
    ?>
                    <table class="report_section">
                        <?php ReportRenderer::rlHeader((bool) $PDF_OUTPUT); ?>
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
    <?php ReportRenderer::zoneEmit(EyeFormData::text(ob_get_clean()), "EXT", $encounter, $pid, $pdf_zones, (bool) $PDF_OUTPUT, display_draw_image(...)); ?>
    <!-- end of external exam -->
        <?php
        if ($OSCONJ || $ODCONJ || $ODCORNEA || $OSCORNEA || $ODAC || $OSAC || $ODLENS || $OSLENS || $ODIRIS || $OSIRIS) {
            ?>
                <!-- start of Anterior Segment exam -->
                <?php
                ob_start();
                ReportRenderer::zoneHeading(xl('Anterior Segment'), (bool) $PDF_OUTPUT);
                ?>
                            <table class="report_section">
                                <?php ReportRenderer::rlHeader((bool) $PDF_OUTPUT); ?>
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
                <?php ReportRenderer::zoneEmit(EyeFormData::text(ob_get_clean()), "ANTSEG", $encounter, $pid, $pdf_zones, (bool) $PDF_OUTPUT, display_draw_image(...)); ?>
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
                ReportRenderer::zoneHeading(xl('Additional Findings'), (bool) $PDF_OUTPUT);
                ?>
                        <?php if ($ACT == 'on' and $MOTILITYNORMAL == 'on') { ?>
                                <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                            <?php echo xlt('Orthophoric'); ?>
              </span>
                            <?php } ?>
                            <table class="report_section report-additional">
                                <?php ReportRenderer::rlHeader((bool) $PDF_OUTPUT); ?>
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
                                                ?>
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
                                                        ?>
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
                                                $stereopsisRows[] = [xl('Animals'), EyeFormData::text($STEREO_ANIMALS) . '/3'];
                                            }
                                            if ($STEREO_CIRCLES !== null && $STEREO_CIRCLES !== '') {
                                                $stereopsisRows[] = [xl('Circles'), EyeFormData::text($STEREO_CIRCLES) . '/9'];
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
                <?php ReportRenderer::zoneEmit(EyeFormData::text(ob_get_clean()), "NEURO", $encounter, $pid, $pdf_zones, (bool) $PDF_OUTPUT, display_draw_image(...)); ?>
                <?php
        } ?>
        <!-- end of Other exam -->
        <!-- start of the Retina exam -->
        <?php
        if ($ODDISC || $OSDISC || $ODCUP || $ODMACULA || $ODVESSELS || $ODPERIPH || $ODVITREOUS) {
            ?>
                <?php
                ob_start();
                ReportRenderer::zoneHeading(xl('Retina'), (bool) $PDF_OUTPUT);
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
                                <?php ReportRenderer::rlHeader((bool) $PDF_OUTPUT); ?>
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
                <?php ReportRenderer::zoneEmit(EyeFormData::text(ob_get_clean()), "RETINA", $encounter, $pid, $pdf_zones, (bool) $PDF_OUTPUT, display_draw_image(...)); ?>

                <?php
        } ?>
        <!-- end of Retina exam -->

        <?php
        // Scleral Depression drawing: saved with the standard zone document
        // naming but never emitted by the narrative report.  It has no exam
        // section of its own, so it renders across the FULL two-column
        // width — not squeezed into the right half of a zone row.
        $sd_doc = QueryUtils::querySingleRow(
            "SELECT id FROM documents WHERE name LIKE ?",
            ['%' . EyeFormData::text($pid) . '_' . EyeFormData::text($encounter) . '_OU_SDRETINA_VIEW' . '%']
        );
        if ((bool) ($sd_doc['id'] ?? null)) {
            ob_start();
            display_draw_image("SDRETINA", $encounter, $pid);
            $sd_drawing = trim(EyeFormData::text(ob_get_clean()));
            if ($sd_drawing !== '') {
                if ($PDF_OUTPUT) {
                    $sd_drawing = str_replace('width:78mm;', 'width:150mm;', $sd_drawing);
                    ob_start();
                    ReportRenderer::zoneHeading(xlt('Retinal Drawing'), (bool) $PDF_OUTPUT);
                    $pdf_zones[] = ['section' => EyeFormData::text(ob_get_clean()), 'drawing' => $sd_drawing, 'fullwidth' => true];
                } else {
                    $sd_drawing = str_replace('width:220px;height:auto', 'width:440px;height:auto', $sd_drawing);
                    echo '<div class="report_exam_group">';
                    ReportRenderer::zoneHeading(xlt('Retinal Drawing'), (bool) $PDF_OUTPUT);
                    echo '<div style="text-align:center;">' . $sd_drawing . '</div></div>';
                }
            }
        } ?>
    <?php
    if ($PDF_OUTPUT) {
        ReportRenderer::pdfZoneLayout($pdf_zones);
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
                        ReportRenderer::zoneHeading(xl('Alternate Cover Test'), (bool) $PDF_OUTPUT);
                        // At the 9pt body size a grid is ~67mm wide, so two fit a PDF row.
                        $per_row = $PDF_OUTPUT ? 2 : 4;
                        foreach (array_chunk($act_grids, $per_row) as $act_row) {
                            echo '<table><tr>';
                            foreach ($act_row as [$act_title, $act_vals]) {
                                echo '<td style="vertical-align:top;padding:2px 4mm 4px 0;">';
                                ReportRenderer::actGrid($act_title, $act_vals, (bool) $PDF_OUTPUT, report_ACT(...));
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
    <?php ReportRenderer::zoneHeading(xl('Impression/Plan'), (bool) $PDF_OUTPUT); ?>
    <table style="">
    <tr>
    <td style="padding:5px;text-align: left;text-align:justify;width:475px;">
    <?php
    /**
     *  Retrieve and Display the IMPPLAN_items for the Impression/Plan zone.
     */
    $query = "select * from form_" . $form_folder . "_impplan where form_id=? and pid=? order by IMPPLAN_order ASC";
    $result = QueryUtils::sqlStatementThrowException($query, [$form_id, $pid]);
    $i = '0';
    while ($ip_list = QueryUtils::fetchArrayFromResultSet($result)) {
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

    if ((bool) ($IMPPLAN_items ?? null)) {
        //for ($i=0; $i < count($IMPPLAN_item); $i++) {
        foreach ($IMPPLAN_items as $item) {
            echo ($item['IMPPLAN_order'] + 1) . '. <b>' . text($item['title']) . '</b><br />';
            echo '<div style="padding-left:15px;">';
            $pattern = '/Code/';
            if (preg_match($pattern, EyeFormData::text($item['code']))) {
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
            $PLAN_results = QueryUtils::fetchRecords($query, [$form_id, $pid]);


        if ($PLAN_results !== []) { ?>
                <br />
                <b><?php echo xlt('Orders') . "/" . xlt('Next Visit'); ?>:</b>
                <br />
                <div style="padding-left:15px;padding-bottom:10px;width:400px;">
                    <?php
                    foreach ($PLAN_results as $plan_row) {
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

                    $providerSignature = ProviderSignature::fromGlobals(OEGlobalsBag::getInstance());
                    $signature = $providerSignature->path(EyeFormData::integer($providerID));
                if (file_exists($signature)) {
                    $signatureSource = $PDF_OUTPUT ? $signature : $providerSignature->url(EyeFormData::integer($providerID));
                    echo "<img style='width:50mm;' src='" . attr($signatureSource) . "'><hr style='width:40mm;' />";
                } else {
                    echo "<br /><br />";
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
            ReportRenderer::materialsText(EyeFormData::integer($pid), EyeFormData::integer($form_id), $reportPdf, (bool) $PDF_OUTPUT, eye_mag_materials_for_report(...), eye_mag_embed_materials(...));
            ReportRenderer::flushPdf((bool) $PDF_OUTPUT);
            return;
    }
    ReportRenderer::materialsText(EyeFormData::integer($pid), EyeFormData::integer($form_id), $reportPdf, (bool) $PDF_OUTPUT, eye_mag_materials_for_report(...), eye_mag_embed_materials(...));
    ReportRenderer::flushPdf((bool) $PDF_OUTPUT);
}



/** Render a zone's saved drawing for screen or PDF output, skipping untouched base images. */
function display_draw_image($zone, $encounter, $pid): void
{
    global $form_folder;
    global $web_root;
    global $PDF_OUTPUT;
    global $tmp_files_remove;
    $zone = EyeFormData::text($zone);
    $pid = EyeFormData::integer($pid);
    $encounter = EyeFormData::integer($encounter);
    $form_folder = EyeFormData::text($form_folder);
    $side = "OU";
    $base_name = $pid . "_" . $encounter . "_" . $side . "_" . $zone . "_VIEW";
    $filename = $base_name . ".jpg";
    $sql = "SELECT * from documents where documents.name like '%" . $filename . "'";
    $doc = QueryUtils::querySingleRow($sql);
    $document_id = $doc['id'] ?? '';

    if (($document_id > '1') && (is_numeric($document_id))) {
        $d = new Document($document_id);
        $fname = basename(EyeFormData::text($d->get_url()));

        $extension = substr($fname, strrpos($fname, "."));
        $notes = $d->get_notes();
        if ((bool) ($notes)) {
            echo "<table>";
        }

        foreach ($notes as $note) {
            echo '<tr>';
            echo '<td>' . xlt('Note') . ' #' . EyeFormData::text($note->get_id()) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . xlt('Date') . ': ' . text(oeFormatShortDate($note->get_date())) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . text($note->get_note()) . '<br /><br /></td>';
            echo '</tr>';
        }

        if ((bool) ($notes)) {
            echo "</table>";
        }

        //               if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
        if ($PDF_OUTPUT) {
            $tempDocC = new C_Document();
            // Server-side read of the raw file (disable_exit=true) needs the
            // retrieve key, as core's custom_report.php does; without it
            // C_Document denies the request (403) and no PDF is made.
            $tempDocC->onReturnRetrieveKey();
            $fileTemp = $tempDocC->retrieve_action((string) $pid, $doc['id'], false, true, true, true);
                        $fileTemp = EyeFormData::text($fileTemp);
            $drawingRenderer = new ReportRenderer(OEGlobalsBag::getInstance()->getProjectDir());
            if ($drawingRenderer->drawingIsUntouchedBase($fileTemp, $zone)) {
                return;
            }
            // tmp file in ../documents/temp since need to be available via webroot
            $tmp_base = tempnam(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . '/documents/temp', "oer");
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
            $filetoshow = OEGlobalsBag::getInstance()->getWebRoot() . "/controller.php?document&retrieve&patient_id=" . attr_url(EyeFormData::text($pid)) . "&document_id=" . attr_url(EyeFormData::text($doc['id'] ?? '')) . "&as_file=false&original_file=true&disable_exit=false&show_original=true&blahblah=" . attr_url((string) random_int(0, mt_getrandmax()));
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



















/** Escape an alternate-cover-test value and preserve its line breaks in report cells. */
function report_ACT($term): string
{
    $term = nl2br(htmlspecialchars(EyeFormData::text($term), ENT_NOQUOTES));
    return $term . "&nbsp;";
}
?>
