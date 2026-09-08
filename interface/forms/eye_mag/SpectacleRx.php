<?php

/**
 * forms/eye_mag/SpectacleRx.php
 *
 * Functions for printing a glasses prescription
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

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\RefType;
use OpenEMR\Forms\EyeMag\RxType;
use OpenEMR\Services\FacilityService;
use Symfony\Component\HttpFoundation\Response;

$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
require_once($srcdir . "/options.inc.php");
require_once($srcdir . "/report.inc.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$facilityService = new FacilityService();

if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for patients/med: SpectacleRx", xl("Spectacle Rx"));
}

$form_name = "Eye Form";
$form_folder = "eye_mag";
require_once("php/" . $form_folder . "_functions.php");

$RX_expir = "+1 years";
$CTL_expir = "+6 months";

// pid comes from the session (patient context), not the request. The prior
// fallback chain (request pid -> request id -> session) is collapsed here into
// a single session-driven read so every query below scopes to the opened patient.
$pid = PatientSessionUtil::getPid();
if ($pid <= 0) {
    (new RequestTerminator())->error(Response::HTTP_BAD_REQUEST, xlt('Missing PID.'));
}
$form_id = $_REQUEST['form_id'] ?? null;

$query = "select  *,form_encounter.date as encounter_date
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

    $data = QueryUtils::querySingleRow($query, [$_REQUEST['encounter'] ?? '', $pid]) ?: [];
    $data['ODMPDD'] = $data['ODPDMeasured'] ?? null;
    $data['OSMPDD'] = $data['OSPDMeasured'] ?? null;
    $data['BPDD']   = (int) $data['ODMPDD'] + (int) $data['OSMPDD'];
    @extract($data);

    // Defaults for the form columns extracted from the joined form_eye_*
    // tables above. The query always returns a row for the encounter, but
    // individual columns can be NULL when never populated in the UI; PHPStan
    // can't see through @extract() so declare every read up-front.
    $ODPDMeasured ??= '';
    $OSPDMeasured ??= '';
    $ODHPD ??= '';
    $ODHBASE ??= '';
    $ODVPD ??= '';
    $ODVBASE ??= '';
    $ODSLABOFF ??= '';
    $ODVERTEXDIST ??= '';
    $OSHPD ??= '';
    $OSHBASE ??= '';
    $OSVPD ??= '';
    $OSVBASE ??= '';
    $OSSLABOFF ??= '';
    $OSVERTEXDIST ??= '';
    $ODMPDN ??= '';
    $OSMPDN ??= '';
    $BPDN ??= '';
    $LENS_MATERIAL ??= '';
    $LENS_TREATMENTS ??= '';
    $CTL_COMMENTS ??= '';
    $CTLODQUANTITY ??= '';
    $CTLOSQUANTITY ??= '';

    $ODMPDD     = $ODPDMeasured;
    $OSMPDD     = $OSPDMeasured;
    $BPDD       = (int) $ODMPDD + (int) $OSMPDD;

    // Mutation branches below (mode=update/remove, RXTYPE=..., dispensed=1)
    // do not send an encounter, so the querySingleRow above returns [] and
    // these lookups run with missing keys. Fall back to null rather than
    // emit undefined-array-key warnings on every mutation request.
    $query      = "SELECT * FROM users where id = ?";
    $prov_data  = sqlQuery($query, [$data['provider_id'] ?? null]);

    $query      = "SELECT * FROM patient_data where pid=?";
    $pat_data   = sqlQuery($query, [$data['pid'] ?? null]);

    $practice_data = $facilityService->getPrimaryBusinessEntity();

    $visit_date = oeFormatShortDate($data['encounter_date'] ?? null);

$RXTYPE ??= '';
$encounter ??= '';

if (($_REQUEST['mode'] ?? '') == "update") {  //store any changed fields in dispense table
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    $table_name = "form_eye_mag_dispense";
    // Confirm the target dispense row belongs to the session patient before
    // handing it to formUpdate(); formUpdate scopes only by id and rewrites
    // the row's pid to the session pid, so a submission with another
    // patient's row id would silently pull that row into the current chart.
    $updateId = CurrentRequest::get()->request->getInt('id');
    if ($updateId <= 0) {
        (new RequestTerminator())->error(Response::HTTP_BAD_REQUEST, 'Missing dispense row id.');
    }
    $ownerPid = QueryUtils::fetchSingleValue(
        'SELECT pid FROM form_eye_mag_dispense WHERE id = ? AND pid = ?',
        'pid',
        [$updateId, $pid]
    );
    if ($ownerPid === null) {
        AccessDeniedHelper::deny('SpectacleRx dispense update: row does not belong to session pid');
    }
    $query = "show columns from " . $table_name;
    $dispense_fields = sqlStatement($query);
    $fields = [];

    if (sqlNumRows($dispense_fields) > 0) {
        while ($row = sqlFetchArray($dispense_fields)) {
            //exclude critical columns/fields, define below as needed
            if (
                in_array($row['Field'], ['id', 'pid', 'user', 'groupname', 'authorized', 'activity', 'date'])
            ) {
                continue;
            }

            if (isset($_POST[$row['Field']])) {
                $fields[$row['Field']] = $_POST[$row['Field']];
            }
        }
        $fields['RXTYPE'] = $RXTYPE;
        $insert_this_id = formUpdate($table_name, $fields, $updateId, $session->get('userauthorized'));
    }

    exit;
} elseif (($_REQUEST['mode'] ?? '') == "remove") {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    // Scope the dispense mutation to the session patient so the request-supplied
    // delete_id cannot reach dispense rows outside the opened chart.
    $query = "DELETE FROM form_eye_mag_dispense where id=? AND pid=?";
    sqlStatement($query, [$_REQUEST['delete_id'], $pid]);
    echo xlt('Prescription successfully removed.');
    exit;
} elseif ($_REQUEST['RXTYPE'] ?? '') {  //store any changed fields
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    // Scope the dispense mutation to the session patient (see remove branch above).
    $query = "UPDATE form_eye_mag_dispense set RXTYPE=? where id=? AND pid=?";
    sqlStatement($query, [$_REQUEST['RXTYPE'], $_REQUEST['id'], $pid]);
    exit;
}

    formHeader("OpenEMR Eye: " . text($prov_data['facility']));

// Pre-initialize variables that the REFTYPE branches below populate selectively.
// Each REFTYPE (W / AR / MR / CR / CTL) sets a different subset, but the form
// markup further down reads them all unconditionally, so default everything to ''.
$REFTYPE = '';
$RXTYPE = '';
$Single = '';
$Bifocal = '';
$Trifocal = '';
$Progressive = '';
$ODSPH = '';
$ODCYL = '';
$ODAXIS = '';
$ODPRISM = '';
$ODADD = '';
$ODADD2 = '';
$ODMIDADD = '';
$ODBC = '';
$ODDIAM = '';
$ODVA = '';
$OSSPH = '';
$OSCYL = '';
$OSAXIS = '';
$OSPRISM = '';
$OSADD = '';
$OSADD2 = '';
$OSMIDADD = '';
$OSBC = '';
$OSDIAM = '';
$OSVA = '';
$COMMENTS = '';
$CTLBRANDOD = '';
$CTLBRANDOS = '';
$CTLMANUFACTUREROD = '';
$CTLMANUFACTUREROS = '';
$CTLSUPPLIEROD = '';
$CTLSUPPLIEROS = '';
$insert_this_id = null;

// Parsed once here; $REFTYPE stays a string because the form posts it back verbatim.
// Anything that is not a string (REFTYPE[]=W) is treated as absent rather than
// flowing on to be echoed into the form markup.
$requestedRefType = $_REQUEST['REFTYPE'] ?? '';
if (!is_string($requestedRefType)) {
    $requestedRefType = '';
}
$refType = RefType::tryFrom($requestedRefType);

// A REFTYPE the form doesn't recognize used to reach the dispense insert below
// with every field still at its blank default, writing an empty prescription
// record. Only a known refraction method gets that far.
if ($refType !== null) {
    $REFTYPE = $refType->value;

    // Map the rx_type numeric code passed from view.php to a display string and
    // set the corresponding checkbox state. Default to Single (0) if the value
    // is missing or not one of the four expected codes.
    $requestedRxType = $_REQUEST['rx_type'] ?? '';
    $rxType = is_string($requestedRxType) ? RxType::tryFrom($requestedRxType) ?? RxType::DEFAULT : RxType::DEFAULT;
    $RXTYPE = $rxType->name;

    $Single = RxType::Single->checkedAttribute($rxType);
    $Bifocal = RxType::Bifocal->checkedAttribute($rxType);
    $Trifocal = RxType::Trifocal->checkedAttribute($rxType);
    $Progressive = RxType::Progressive->checkedAttribute($rxType);

    $id = $_REQUEST['id'] ?? null;
    $table_name = "form_eye_mag";
    $encounter = !($_REQUEST['encounter'] ?? '') ? $session->get('encounter') : $_REQUEST['encounter'];



    $prefix = $refType->columnPrefix();
    if ($prefix === null) {
        // A wearing prescription re-prescribes the patient's current glasses, so
        // it is read back out of form_eye_mag_wearing rather than the refraction.
        // We have rx_number 1-5 to process...
        $wearingRow = QueryUtils::querySingleRow(
            <<<'SQL'
            SELECT *
              FROM form_eye_mag_wearing
             WHERE ENCOUNTER = ?
               AND FORM_ID = ?
               AND PID = ?
               AND RX_NUMBER = ?
            SQL,
            [$encounter, $_REQUEST['form_id'], $pid, $_REQUEST['rx_number']],
        );

        // A field the wearing prescription does not carry reads null, so the
        // dispense insert below leaves it out rather than recording it blank.
        $wearing = is_array($wearingRow) ? $wearingRow : [];
        $ODSPH = $wearing['ODSPH'] ?? null;
        $ODAXIS = $wearing['ODAXIS'] ?? null;
        $ODCYL = $wearing['ODCYL'] ?? null;
        $OSSPH = $wearing['OSSPH'] ?? null;
        $OSCYL = $wearing['OSCYL'] ?? null;
        $OSAXIS = $wearing['OSAXIS'] ?? null;
        $COMMENTS = $wearing[$refType->commentsColumn()] ?? null;
        $ODMIDADD = $wearing['ODMIDADD'] ?? null;
        $ODADD2 = $wearing['ODADD'] ?? null;
        $OSMIDADD = $wearing['OSMIDADD'] ?? null;
        $OSADD2 = $wearing['OSADD'] ?? null;

        //do LT and Lens materials
    } else {
        // Every refraction stores the same eight measurements on the joined
        // record under its own column prefix, so the method picks the columns
        // instead of a branch per method.
        $ODSPH      = $data[$prefix . 'ODSPH'];
        $ODAXIS     = $data[$prefix . 'ODAXIS'];
        $ODCYL      = $data[$prefix . 'ODCYL'];
        $ODPRISM    = $data[$prefix . 'ODPRISM'];
        $OSSPH      = $data[$prefix . 'OSSPH'];
        $OSCYL      = $data[$prefix . 'OSCYL'];
        $OSAXIS     = $data[$prefix . 'OSAXIS'];
        $OSPRISM    = $data[$prefix . 'OSPRISM'];
        $COMMENTS   = $data[$refType->commentsColumn()];

        if ($refType->hasAddPower()) {
            $ODADD2 = $data[$prefix . 'ODADD'];
            $OSADD2 = $data[$prefix . 'OSADD'];
        }

        // A contact lens is fitted, not just refracted, so it carries the lens
        // geometry and the brand/supplier list selections as well.
        if ($refType->isContactLens()) {
            $ODBC       = $data[$prefix . 'ODBC'];
            $ODDIAM     = $data[$prefix . 'ODDIAM'];
            $ODADD      = $data[$prefix . 'ODADD'];
            $ODVA       = $data[$prefix . 'ODVA'];

            $OSBC       = $data[$prefix . 'OSBC'];
            $OSDIAM     = $data[$prefix . 'OSDIAM'];
            $OSADD      = $data[$prefix . 'OSADD'];
            $OSVA       = $data[$prefix . 'OSVA'];

            $CTLMANUFACTUREROD  = getListItemTitle('CTLManufacturer', $data[$prefix . 'MANUFACTUREROD']);
            $CTLMANUFACTUREROS  = getListItemTitle('CTLManufacturer', $data[$prefix . 'MANUFACTUREROS']);
            $CTLSUPPLIEROD      = getListItemTitle('CTLManufacturer', $data[$prefix . 'SUPPLIEROD']);
            $CTLSUPPLIEROS      = getListItemTitle('CTLManufacturer', $data[$prefix . 'SUPPLIEROS']);
            $CTLBRANDOD         = getListItemTitle('CTLManufacturer', $data[$prefix . 'BRANDOD']);
            $CTLBRANDOS         = getListItemTitle('CTLManufacturer', $data[$prefix . 'BRANDOS']);
        }
    }

    //Since we selected the Print Icon, we must be dispensing this - add to dispensed table now
    $table_name      = "form_eye_mag_dispense";
    $dispense_fields = QueryUtils::listTableFields($table_name);
    $fields          = [];

    if (count($dispense_fields) > 0) {
        //exclude critical columns/fields, define below as needed
        $reserved = ['id', 'pid', 'user', 'groupname', 'authorized', 'activity', 'RXTYPE', 'REFDATE', 'date'];
        foreach ($dispense_fields as $dispense_field) {
            if (in_array($dispense_field, $reserved, true)) {
                continue;
            }
            if (isset(${$dispense_field})) {
                $fields[$dispense_field] = ${$dispense_field};
            }
        }

        $fields['RXTYPE'] = $RXTYPE;
        $fields['REFDATE'] = $data['date'];
        $insert_this_id = formSubmit($table_name, $fields, $form_id, $session->get('userauthorized'));
    }
}

if ($_REQUEST['dispensed'] ?? '') {
    $dispensed = QueryUtils::fetchRecords(
        'SELECT * FROM form_eye_mag_dispense WHERE pid = ? ORDER BY date DESC',
        [$pid],
    );
    ?><html>
    <title><?php echo xlt('Rx Dispensed History'); ?></title>
    <head>

        <?php Header::setupHeader(['opener', 'pure', 'jscolor']); ?>

        <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/style.css" type="text/css">

        <style>
            .title {
                font-size:1em;
                position:absolute;
                right:10px;
                top:30px;
                font-size: 1em;
            }
            .refraction {
                width:95%;
                display: -moz-stack;
                vertical-align: middle;
                min-height:unset;
            }
            .refraction td {
                text-align:center;
                font-size:12px;
                width:0.9in;
                vertical-align: text-middle;
                text-decoration: unset;
            }
            table {
                font-size: 1.0em;
                padding: 12px;
                color: black;
                vertical-align: text-top;
            }

            input[type=text] {
                text-align: center;
                width: 60px;
            }

            .refraction b {
                font-weight: bold;
            }

            .refraction td.right {
                text-align: right;
                text-decoration: unset;
                width: 0.7in;
                vertical-align: middle;    font-size:12px;
            }

            .refraction td.left {
                vertical-align: middle;
                text-align: left;
                font-size:12px;
            }

            .right {
                text-align: right;
                vertical-align: middle;}

            .left {
                vertical-align: middle;
                text-align: left;
            }

            .title {
                font-size: 0.9em;
                font-weight: normal;
            }

            .bold {
                font-weight: 600;
            }

            input {
                width: 60px;
            }

            input[type="radio"] {
                width: 15px;
            }
            .underline {
                text-decoration:underline !important
            }
            #CTLODQUANTITY, #CTLOSQUANTITY {
                width: 300px;
            }
        </style>
        <script language="JavaScript">
        <?php
        require_once($srcdir . "/restoreSession.php");  ?>

            function delete_me(delete_id) {
                top.restoreSession();
                var url = "../../forms/eye_mag/SpectacleRx.php";
                $.ajax({
                           type: 'POST',
                           url: url,
                           data: {
                               mode: 'remove',
                               delete_id: delete_id,
                               dispensed: '1',
                               csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>
                           } // our data object
                       }).done(function (o) {
                    $('#RXID_' + delete_id).hide();
                    alert(o);
                });
            }

        </script>
    </head>
    <?php echo report_header((string) $pid, "web"); ?>
    <div class="row">
        <div class="col-sm-8 offset-sm-2 text-center m-3">
            <table>
                <tr>
                    <td colspan="2"><h4 class="underline"><?php echo xlt('Rx History'); ?></h4></td>
                </tr>
                <?php
                if (count($dispensed) === 0) {
                    echo "<tr><td colspan='2' class='text-center p-3' style='font-size:1.2em;'>" . xlt('There are no Glasses or Contact Lens Presciptions on file for this patient') . "</td></tr>";
                }
                ?>
            </table>
            <?php
            $i = 0;
            foreach ($dispensed as $row) {
                $i++;
                // The dispense table records the lens type by name, so it reads
                // back through fromLabel() rather than the numeric-code tryFrom().
                $rowRxType = is_string($row['RXTYPE']) ? RxType::fromLabel($row['RXTYPE']) : null;
                $Single = RxType::Single->checkedAttribute($rowRxType);
                $Bifocal = RxType::Bifocal->checkedAttribute($rowRxType);
                $Trifocal = RxType::Trifocal->checkedAttribute($rowRxType);
                $Progressive = RxType::Progressive->checkedAttribute($rowRxType);

                $rowRefType = is_string($row['REFTYPE']) ? RefType::tryFrom($row['REFTYPE']) : null;

                $row['date'] = oeFormatShortDate(date('Y-m-d', strtotime((string) $row['date'])));
                if ($rowRefType?->isContactLens()) {
                    $expir = date("Y-m-d", strtotime($CTL_expir, strtotime((string) $row['REFDATE'])));
                } else {
                    $expir = date("Y-m-d", strtotime($RX_expir, strtotime((string) $row['REFDATE'])));
                }
                $expir_date = oeFormatShortDate($expir);
                $row['REFDATE'] = oeFormatShortDate($row['REFDATE']);

                ?>
                    <div class="position-relative text-center mt-2 mb-2 mx-auto" id="RXID_<?php echo attr($row['id']); ?>">
                        <i class="float-right fas fa-times"
                           onclick="delete_me(<?php echo attr(js_escape((string) $row['id'])); ?>);"
                           title="<?php echo xla('Remove this Prescription from the list of RXs dispensed'); ?>"></i>
                        <div class="table-responsive">
                            <table class="table mt-1 mb-1 mx-auto">
                                <tr>
                                    <td class="text-right align-middle font-weight-bold" style="width:250px;">
                                        <?php echo xlt('RX Print Date'); ?>:
                                    </td>
                                    <td>&nbsp;&nbsp;<?php echo text($row['date']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right align-middle font-weight-bold">
                                        <?php echo xlt('Visit Date'); ?>:
                                    </td>
                                    <td>&nbsp;&nbsp;<?php echo text($row['REFDATE']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-right align-middle font-weight-bold">
                                        <?php echo xlt('Expiration Date'); ?>:
                                    </td>
                                    <td>&nbsp;&nbsp;<?php echo text($expir_date); ?></td>
                                </tr>

                                <tr>
                                    <td class="text-right align-middle font-weight-bold"><?php echo xlt('Refraction Method'); ?>:</td>
                                    <td>&nbsp;&nbsp;<?php
                                    echo $rowRefType?->displayName() ?? text($row['REFTYPE']); ?>
                                        <input type="hidden" name="REFTYPE" value="<?php echo attr($row['REFTYPE']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-center"> <?php
                                    if (!$rowRefType?->isContactLens()) { ?>
                                                <table id="SpectacleRx" name="SpectacleRx" class="refraction" style="top:0px;">
                                                    <tr class="font-weight-bold">
                                                        <td></td>
                                                        <td></td>
                                                        <td class="center font-weight-bold underline"><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                        <td class="center font-weight-bold underline"><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                        <td class="center font-weight-bold underline"><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                        <td rowspan="5" class="text-right align-middle font-weight-bold underline" colspan="2"
                                                            style="min-width:100px;">
                                                            <?php echo xlt('Rx Type'); ?><br/><br/>
                                                            <?php echo xlt('Single'); ?>
                                                            <input type="radio" disabled <?php echo text($Single); ?>><br/>
                                                            <?php echo xlt('Bifocal'); ?>
                                                            <input type="radio" disabled <?php echo text($Bifocal); ?>><br/>
                                                            <?php echo xlt('Trifocal'); ?>
                                                            <input type="radio" disabled <?php echo text($Trifocal); ?>><br/>
                                                            <?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                                            <input type="radio" disabled <?php echo text($Progressive); ?>><br/>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold text-right" rowspan="2"><?php echo xlt('Distance'); ?></td>
                                                        <td class="font-weight-bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                                                        <td><?php echo text($row['ODSPH']); ?></td>
                                                        <td><?php echo text($row['ODCYL']); ?></td>
                                                        <td><?php echo text($row['ODAXIS']); ?></td>
                                                        <td><?php echo text($row['ODPRISM']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="font-weight-bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                                                        <td><?php echo text($row['OSSPH']); ?></td>
                                                        <td><?php echo text($row['OSCYL']); ?></td>
                                                        <td><?php echo text($row['OSAXIS']); ?></td>
                                                        <td><?php echo text($row['OSPRISM']); ?></td>
                                                    </tr>
                                                    <tr class="NEAR">
                                                        <td class="text-nowrap" rowspan="2"><span class="font-weight-bold text-decoration-none"><?php echo xlt('ADD'); ?>
                                                                :<br/><?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>
                                                                /<?php echo xlt("Near"); ?></span></td>
                                                        <td class="font-weight-bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                                                        <td class="WMid"><?php echo text($row['ODMIDADD']); ?></td>
                                                        <td class="WAdd2"><?php echo text($row['ODADD']); ?></td>
                                                    </tr>
                                                    <tr class="NEAR">
                                                        <td class="font-weight-bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                                                        <td class="WMid"><?php echo text($row['OSMIDADD']); ?></td>
                                                        <td class="WAdd2"><?php echo text($row['OSADD']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="up" class="font-weight-bold text-right align-top"
                                                            style="top:0px;"><?php echo xlt('Comments'); ?>
                                                            :
                                                        </td>
                                                        <td colspan="4" class="up text-left"></td>
                                                        <?php echo text($row['CRCOMMENTS']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <?php
                                    } else {
                                        $adds = !empty($row['ODADD']) || !empty($row['OSADD']) ? 1 : '';
                                        ?>
                                                <table id="CTLRx" name="CTLRx" class="refraction">
                                                    <tr>
                                                        <td colspan="4"
                                                            class="font-weight-bold text-left align-middle text-uppercase text-top" style="display: flex;
                                                align-items:top"><u><?php echo xlt('Right Lens'); ?></u>
                                                        </td>
                                                    </tr>
                                                    <tr class="font-weight-bold underline">
                                                        <td></td>
                                                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                        <td><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                        <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                                        <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                                                <?php
                                                if ($adds) {
                                                    ?>
                                                                <td><?php echo xlt('ADD'); ?></td>
                                                        <?php }
                                                ?>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><?php echo text($row['ODSPH']); ?></td>
                                                        <td><?php echo text($row['ODCYL']); ?></td>
                                                        <td><?php echo text($row['ODAXIS']); ?></td>
                                                        <td><?php echo text($row['ODBC']); ?></td>
                                                        <td><?php echo text($row['ODDIAM']); ?></td>
                                                        <?php
                                                        if ($adds) {
                                                            ?>
                                                                <td><?php echo text($row['ODADD']); ?></td>
                                                            <?php } ?>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="text-right align-middle font-weight-bold">
                                                            <?php echo xlt('Brand'); ?>: <br />
                                                            <?php echo xlt('Quantity'); ?>: <br />
                                                            <?php echo xlt('Supplier'); ?>: </td>
                                                        <td colspan="5" class="text-left align-middle align-middle align-top" style="padding-left:10px;">
                                                            <?php echo text($row['CTLBRANDOD']); ?>
                                                            <?php
                                                            if (!empty($row['CTLMANUFACTUREROD'])) {
                                                                echo "(" . text($row['CTLMANUFACTUREROD']) . ")";
                                                            } ?>
                                                            <br />
                                                            <?php echo text($row['CTLODQUANTITY']); ?><br />
                                                            <?php echo text($row['CTLSUPPLIEROD']); ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7">
                                                            <hr />
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4"
                                                            class="font-weight-bold text-left align-middle text-uppercase text-top d-flex align-items-start">
                                                            <u><?php echo xlt('Left Lens'); ?></u>
                                                        </td>
                                                    </tr>
                                                    <tr class="font-weight-bold underline">
                                                        <td></td>
                                                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                        <td><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                        <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                                        <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                                                        <?php
                                                        if ($adds) {
                                                            ?>
                                                                <td><?php echo xlt('ADD'); ?></td>
                                                            <?php }
                                                        ?>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><?php echo text($row['OSSPH']); ?></td>
                                                        <td><?php echo text($row['OSCYL']); ?></td>
                                                        <td><?php echo text($row['OSAXIS']); ?></td>
                                                        <td><?php echo text($row['OSBC']); ?></td>
                                                        <td><?php echo text($row['OSDIAM']); ?></td>
                                                        <?php
                                                        if ($adds) {
                                                            ?>
                                                                <td><?php echo text($row['OSADD']); ?></td>
                                                                <?php
                                                        } ?>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="text-right align-middle font-weight-bold">
                                                            <?php echo xlt('Brand'); ?>: <br />
                                                            <?php echo xlt('Quantity'); ?>: <br />
                                                            <?php echo xlt('Supplier'); ?>: </td>
                                                        <td colspan="5" class="text-left align-middle align-top" style="padding-left:10px;"><?php echo text($row['CTLBRANDOS']); ?>
                                                            <?php
                                                            if (!empty($row['CTLMANUFACTUREROS'])) {
                                                                echo "(" . text($row['CTLMANUFACTUREROS']) . ")";
                                                            } ?>
                                                            <br />
                                                            <?php echo text($row['CTLOSQUANTITY']); ?><br />
                                                            <?php echo text($row['CTLSUPPLIEROS']); ?>
                                                        </td>
                                                    </tr>
                                                    <?php if (!empty($row['COMMENTS'])) { ?>
                                                        <tr><td colspan="7"><hr /></td></tr>
                                                        <tr>
                                                            <td colspan="3" class="font-weight-bold text-right align-middle"><?php echo xlt('Comments'); ?>:
                                                            </td>
                                                            <td colspan="3" class="text-left align-middle" style="padding-left:10px;top:0px;">
                                                                <?php echo text($row['COMMENTS']); ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>

                                                <?php
                                    } ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <hr>

                    </div>
                    <?php
            } ?>
        </div>
    </div>
    </body>
        </html>
        <?php
        exit;
}

    ob_start();
?>
<html>
<head>
    <?php Header::setupHeader([ 'opener', 'jquery-ui', 'jquery-ui-redmond', 'pure', 'jscolor' ]); ?>
    <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/style.css">

    <style>
        .title {
            font-size:1em;
            position:absolute;
            right:10px;
            top:30px;
            font-size: 1em;
        }
        .refraction {
            width:95%;
            display: -moz-stack;
            vertical-align: middle;
            min-height:unset;
        }
        .refraction td {
            text-align:center;
            font-size:12px;
            padding:5;
            width:0.35in;
            vertical-align: text-middle;
            text-decoration: unset;
        }
        table {
            font-size: 1.0em;
            padding: 12px;
            color: black;
            vertical-align: text-top;
        }

        input[type=text] {
            text-align: center;
            width: 60px;
            padding: 0.2em 0.4em !important;
        }

        .refraction b {
            font-weight: bold;
        }

        .refraction td.right {
            text-align: right;
            text-decoration: unset;
            width: 0.7in;
            vertical-align: middle;    font-size:12px;
        }

        .refraction td.left {
            vertical-align: middle;
            text-align: left;
            font-size:12px;
        }

        .right {
            text-align: right;
            vertical-align: middle;}

        .left {
            vertical-align: middle;
            text-align: left;
        }

        .title {
            font-size: 0.9em;
            font-weight: normal;
        }

        .bold {
            font-weight: 600;
        }

        input {
            width: 60px;
        }

        input[type="radio"] {
            width: 15px;
        }
        .underline {
            text-decoration:underline !important
        }
        #CTLODQUANTITY, #CTLOSQUANTITY {
            width: 300px;
            text-align: left;
            padding-left: 10px !important;
        }
        hr {
            margin:1px;
        }
        label {
            font-size:12px;
        }

        /* specifically include & exclude from printing */
        @media print {
                .no-print {
                    display: none;
                }
        }

    </style>
    <!-- jQuery library -->

    <script language="JavaScript">
        <?php require_once($srcdir . "/restoreSession.php"); ?>
        function pick_rxType(rxtype, id) {
            var url = "../../forms/eye_mag/SpectacleRx.php";
            var formData = {
                'RXTYPE': rxtype,
                'id': id,
                'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>
            };
            top.restoreSession();
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: formData
                   });
            if (rxtype == 'Trifocal') {
                $("[name$='MIDADD']").show();
                $("[name$='ADD2']").show();
            } else if (rxtype == 'Bifocal') {
                $("[name$='MIDADD']").hide().val('');
                $("[name$='ADD2']").show();
            } else if (rxtype == 'Progressive') {
                $("[name$='MIDADD']").hide().val('');
                $("[name$='ADD2']").show();
            } else if (rxtype == "Single") {
                $("[name$='MIDADD']").hide().val('');
                $("[name$='ADD2']").hide().val('');
            }
        }

        function submit_form() {
            var url = "../../forms/eye_mag/SpectacleRx.php?mode=update";
            formData = $("form#Spectacle").serialize();
            top.restoreSession();
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: formData
                   });
        }

        //add sph and cyl, flip cyl sign, rotate axis 90.
        function reverse_cylinder() {
            var Rsph = $('#ODSPH').val();
            var Rcyl = $('#ODCYL').val();
            var Raxis = $('#ODAXIS').val();
            var Lsph = $('#OSSPH').val();
            var Lcyl = $('#OSCYL').val();
            var Laxis = $('#OSAXIS').val();
            if (Rsph == '' && Rcyl == '' && Lsph == '' && lcyl == '') return;
            if ((!Rcyl.match(/SPH/i)) && (Rcyl > '')) {
                if (Rsph.match(/plano/i)) Rsph = '0';
                Rsph = Number(Rsph);
                Rcyl = Number(Rcyl);
                Rnewsph = Rsph + Rcyl;
                if (Rnewsph == 0) Rnewsph = "PLANO";
                Rnewcyl = Rcyl * -1;
                if (Rnewcyl > 0) Rnewcyl = "+" + Rnewcyl;
                if (parseInt(Raxis) < 90) {
                    Rnewaxis = parseInt(Raxis) + 90;
                } else {
                    Rnewaxis = parseInt(Raxis) - 90;
                }
                if (Rnewcyl == '0') Rnewcyl = "SPH";
                if (Rnewsph == '0') {
                    Rnewsph = "PLANO";
                    if (Rnewcyl == "SPH") Rnewcyl = '';
                }
                $("#ODSPH").val(Rnewsph);
                $("#ODCYL").val(Rnewcyl);
                $("#ODAXIS").val(Rnewaxis);
                $('#ODAXIS').trigger('blur');
                $('#ODSPH').trigger('blur');
                $('#ODCYL').trigger('blur');
            }
            if ((!Lcyl.match(/SPH/i)) && (Lcyl > '')) {
                if (!Lsph.match(/\d/)) Lsph = '0';
                Lsph = Number(Lsph);
                Lcyl = Number(Lcyl);
                Lnewsph = Lsph + Lcyl;
                Lnewcyl = Lcyl * -1;
                if (Lnewcyl > 0) Lnewcyl = "+" + Lnewcyl;
                if (parseInt(Laxis) < 90) {
                    Lnewaxis = parseInt(Laxis) + 90;
                } else {
                    Lnewaxis = parseInt(Laxis) - 90;
                }

                if (Lnewcyl == '0') Lnewcyl = "SPH";
                if (Lnewsph == '0') {
                    Lnewsph = "PLANO";
                    if (Lnewcyl == "SPH") Lnewcyl = '';
                }

                $("#OSSPH").val(Lnewsph);
                $("#OSCYL").val(Lnewcyl);
                $("#OSAXIS").val(Lnewaxis);
                $('#OSAXIS').trigger('blur');
                $('#OSSPH').trigger('blur');
                $('#OSCYL').trigger('blur');
            }
        }
        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });
    </script>
</head>
<body>
<?php echo report_header((string) $pid, "web");  ?>
<br/><br/>
<?php
if ($refType?->isContactLens()) {
    $expir = date("Y-m-d", strtotime($CTL_expir, strtotime((string) $data['date'])));
} else {
    $expir = date("Y-m-d", strtotime($RX_expir, strtotime((string) $data['date'])));
}
    $expir_date = oeFormatShortDate($expir);
?>
<p><span class="font-weight-bold"><?php echo xlt('Expiration Date'); ?>: </span>
    &nbsp;&nbsp;     <?php echo text($expir_date); ?>
</p>

<form method="post" action="<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/forms/<?php echo text($form_folder); ?>/SpectacleRx.php?mode=update"
      id="Spectacle" class="eye_mag pure-form text-center" name="Spectacle">
    <!-- start container for the main body of the form -->
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
    <input type="hidden" name="REFDATE" id="REFDATE" value="<?php echo attr($data['date']); ?>">
    <input type="hidden" name="RXTYPE" id="RXTYPE" value="<?php echo attr($RXTYPE); ?>">
    <input type="hidden" name="REFTYPE" value="<?php echo attr($REFTYPE); ?>"/>
    <input type="hidden" name="pid" id="pid" value="<?php echo attr((string) $pid); ?>">
    <input type="hidden" name="id" id="id" value="<?php echo attr($insert_this_id); ?>">
    <input type="hidden" name="encounter" id="encounter" value="<?php echo attr($encounter); ?>">

    <div style="width: 650px;">
        <table class="mx-auto">
            <tr>
                <td>
                    <?php
                    if (!$refType?->isContactLens()) { ?>
                            <table id="SpectacleRx" name="SpectacleRx" class="refraction bordershadow"
                                   style="min-width:610px;top:0px;">
                                <tr class="font-weight-bold text-center">
                                    <td><i name="reverse" id="reverse" class="fa fa-gamepad fa-2x"></i></td>
                                    <td></td>
                                    <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                    <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                    <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                                    <td rowspan="5" class="text-right align-middle font-weight-bold" colspan="1" >
                                        <span class="font-weight-bold underline"><?php echo xlt('Rx Type'); ?></span>
                                        <br/><br/>
                                        <span id="SingleVision_span" name="SingleVision_span">
                                            <label for="RXTYPE_Single"><?php echo xlt('Single'); ?></label>
                                            <input type="radio" disabled
                                                   onclick="pick_rxType('Single',<?php echo attr_js($insert_this_id); ?>);"
                                                   value="Single" id="RXTYPE_Single" name="RXTYPE"
                                                   <?php echo attr($Single); ?> />
                                        </span>
                                        <br/>
                                        <span id="Bifocal_span" name="Bifocal_span">
                                            <label for="RXTYPE_Bifocal"><?php echo xlt('Bifocal'); ?></label>
                                            <input type="radio" disabled
                                                   onclick="pick_rxType('Bifocal',<?php echo attr_js($insert_this_id); ?>);"
                                                   value="Bifocal" id="RXTYPE_Bifocal" name="RXTYPE" <?php echo attr($Bifocal); ?> />
                                        </span>
                                        <br/>
                                        <span id="Trifocal_span" name="Trifocal_span">
                                            <label for="RXTYPE_Trifocal"><?php echo xlt('Trifocal'); ?></label>
                                            <input type="radio" disabled
                                                   onclick="pick_rxType('Trifocal',<?php echo attr_js($insert_this_id); ?>);"
                                                   value="Trifocal" id="RXTYPE_Trifocal"
                                                   name="RXTYPE" <?php echo attr($Trifocal); ?>>
                                        </span>
                                        <br/>
                                        <span id="Progressive_span">
                                            <label for="RXTYPE_Progressive">
                                                <?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                            </label>
                                            <input type="radio" disabled
                                                   onclick="pick_rxType('Progressive',<?php echo attr_js($insert_this_id); ?>);"
                                                   value="Progressive" id="RXTYPE_Progressive"
                                                   name="RXTYPE" <?php echo attr($Progressive); ?>>
                                        </span>
                                        <br/>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="center">
                                    <td rowspan="2" colspan="1" class="text-right align-middle font-weight-bold"><?php echo xlt('Distance'); ?>: </td>
                                    <td class="text-right align-middle font-weight-bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                                    <td><input type="text" id="ODSPH" name="ODSPH" disabled value="<?php echo attr($ODSPH); ?>"></td>
                                    <td><input type="text" id="ODCYL" name="ODCYL" disabled value="<?php echo attr($ODCYL); ?>"></td>
                                    <td><input type="text" id="ODAXIS" name="ODAXIS" disabled value="<?php echo attr($ODAXIS); ?>">
                                    </td>
                                </tr>
                                <tr class="center">
                                    <td name="W_wide" class="text-right align-middle font-weight-bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                                    <td><input type="text" id="OSSPH" name="OSSPH" disabled value="<?php echo attr($OSSPH); ?>"></td>
                                    <td><input type="text" id="OSCYL" name="OSCYL" disabled value="<?php echo attr($OSCYL); ?>"></td>
                                    <td><input type="text" id="OSAXIS" name="OSAXIS" disabled value="<?php echo attr($OSAXIS); ?>">
                                    </td>
                                </tr>
                                <tr class="NEAR center">
                                    <td rowspan="2" colspan="1" class="text-right align-middle font-weight-bold text-nowrap"><?php echo xlt('ADD'); ?>:<br/>
                                        <?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>
                                        /<?php echo xlt("Near"); ?></td>
                                    <td class="text-right align-middle font-weight-bold"><?php echo xlt('OD{{right eye}}'); ?></td>
                                    <td name="COLADD1"><input type="text" id="ODMIDADD" name="ODMIDADD" disabled
                                                              value="<?php echo attr($ODMIDADD); ?>"></td>
                                    <td class="WAdd2"><input type="text" id="ODADD2" name="ODADD2" disabled
                                                             value="<?php echo attr($ODADD2); ?>"></td>
                                </tr>
                                <tr class="NEAR center">
                                    <td class="text-right align-middle font-weight-bold"><?php echo xlt('OS{{left eye}}'); ?></td>
                                    <td name="COLADD1">
                                        <input type="text" id="OSMIDADD" name="OSMIDADD" disabled value="<?php echo attr($OSMIDADD); ?>"></td>
                                    <td class="WAdd2">
                                        <input type="text" id="OSADD2" name="OSADD2" disabled value="<?php echo attr($OSADD2); ?>"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="center font-weight-bold"><?php echo xlt('Comments'); ?>: </td>
                                    <td colspan="4">
                                    <textarea class="w-100" style="height:3em;" id="CRCOMMENTS" disabled
                                              name="CRCOMMENTS"><?php echo text($COMMENTS); ?></textarea>
                                    </td>
                                </tr>
                                <!-- start Dispense data -->
                                <tr class="header closeButton">
                                    <td colspan="9" class="right">
                                                <span><?php
                                                if (
                                                    $ODHPD || $ODHBASE || $ODVPD || $ODVBASE || $ODSLABOFF || $ODVERTEXDIST ||
                                                        $OSHPD || $OSHBASE || $OSVPD || $OSVBASE || $OSSLABOFF || $OSVERTEXDIST ||
                                                        $ODMPDD || $ODMPDN || $OSMPDD || $OSMPDN || $BPDD || $BPDN ||
                                                        $LENS_MATERIAL || $LENS_TREATMENTS
                                                ) {
                                                    $detailed = '1';
                                                    ?><i class="fa fa-minus-square-o"></i><?php
                                                } else {
                                                    $detailed = '0';
                                                    ?><i class="fa fa-plus-square-o"></i><?php
                                                }
                                                ?>
                                                </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7">
                                        <hr/>
                                    </td>
                                </tr>
                                <tr class="dispense_data" style="font-weight:bold;text-align:center;">
                                    <td name="W_wide" colspan="1"></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Horizontal Prism Power'); ?>"><?php echo xlt('Horiz Prism{{abbreviation for Horizontal Prism Power}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Horizontal Prism Base'); ?>"><?php echo xlt('Horiz Base{{abbreviation for Horizontal Prism Base}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Vertical Prism Power'); ?>"><?php echo xlt('Vert Prism{{abbreviation for Vertical Prism Power}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Vertical Prism Base'); ?>"><?php echo xlt('Vert Base{{abbreviation for Vertical Prism Base}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Slab Off'); ?>"><?php echo xlt('Slab Off'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Vertex Distance'); ?>"><?php echo xlt('Vert Distance{{abbreviation for Vertex Distance}}'); ?></td>
                                </tr>
                                <tr class="dispense_data">
                                    <td name="W_wide" style="text-align:right;font-weight:bold;"
                                        colspan="1"><?php echo xlt('OD{{right eye}}'); ?></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODHPD" name="ODHPD"
                                                             value="<?php echo attr($ODHPD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODHBASE" name="ODHBASE"
                                                             value="<?php echo attr($ODHBASE); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODVPD" name="ODVPD"
                                                             value="<?php echo attr($ODVPD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODVBASE" name="ODVBASE"
                                                             value="<?php echo attr($ODVBASE); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODSLABOFF" name="ODSLABOFF"
                                                             value="<?php echo attr($ODSLABOFF); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODVERTEXDIST" name="ODVERTEXDIST"
                                                             value="<?php echo attr($ODVERTEXDIST); ?>"></td>
                                </tr>
                                <tr class="dispense_data">
                                    <td name="W_wide" style="text-align:right;font-weight:bold;"
                                        colspan="1"><?php echo xlt('OS{{left eye}}'); ?></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSHPD" name="OSHPD"
                                                             value="<?php echo attr($OSHPD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSHBASE" name="OSHBASE"
                                                             value="<?php echo attr($OSHBASE); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSVPD" name="OSVPD"
                                                             value="<?php echo attr($OSVPD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSVBASE" name="OSVBASE"
                                                             value="<?php echo attr($OSVBASE); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSSLABOFF" name="OSSLABOFF"
                                                             value="<?php echo attr($OSSLABOFF); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSVERTEXDIST" name="OSVERTEXDIST"
                                                             value="<?php echo attr($OSVERTEXDIST); ?>"></td>
                                </tr>
                                <tr class="dispense_data">
                                    <td colspan="7" class="center">
                                        <hr/>
                                    </td>
                                </tr>
                                <tr class="dispense_data" style="font-weight:bold;text-align:center;">
                                    <td></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Monocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('MPD-D{{abbreviation for Monocular Pupillary Diameter - Distance}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Monocular Pupillary Diameter - Near'); ?>"><?php echo xlt('MPD-N{{abbreviation for Monocular Pupillary Diameter - Near}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Binocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('BPD-D{{abbreviation for Binocular Pupillary Diameter - Distance}}'); ?></td>
                                    <td name="W_wide"
                                        title="<?php echo xla('Binocular Pupillary Diameter - Near'); ?>"><?php echo xlt('BPD-N{{abbreviation for Binocular Pupillary Diameter - Near}}'); ?></td>

                                    <td colspan="2">Lens Material:</td>
                                </tr>
                                <tr>
                                    <td name="W_wide" style="text-align:right;font-weight:bold;"
                                        colspan="1"><?php echo xlt('OD{{right eye}}'); ?></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODMPDD" name="ODMPDD"
                                                             value="<?php echo attr($ODMPDD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="ODMPDN" name="ODMPDN"
                                                             value="<?php echo attr($ODMPDN); ?>"></td>
                                    <td name="W_wide" rowspan="2" style="vertical-align:middle;"><input type="text"
                                                                                                        class="prism"
                                                                                                        id="BPDD"
                                                                                                        name="BPDD"
                                                                                                        value="<?php echo attr($BPDD); ?>">
                                    </td>
                                    <td name="W_wide" rowspan="2" style="vertical-align:middle;"><input type="text"
                                                                                                        class="prism"
                                                                                                        id="BPDN"
                                                                                                        name="BPDN"
                                                                                                        value="<?php echo attr($BPDN); ?>">
                                    </td>
                                    <td colspan="2">   <?php
                                        echo generate_select_list("LENS_MATERIAL", "Eye_Lens_Material", "$LENS_MATERIAL", '', ' ', '', 'restoreSession;submit_form();', '', ['style' => 'width:120px']);
                                    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td name="W_wide" style="text-align:right;font-weight:bold;"
                                        colspan="1"><?php echo xlt('OS{{left eye}}'); ?></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSMPDD" name="OSMPDD"
                                                             value="<?php echo attr($OSMPDD); ?>"></td>
                                    <td name="W_wide"><input type="text" class="prism" id="OSMPDN" name="OSMPDN"
                                                             value="<?php echo attr($OSMPDN); ?>"></td>
                                </tr>
                                <tr style="font-weight:bold;text-align:center;">
                                    <td colspan="3"><?php echo xlt('Lens Treatments'); ?>
                                    </td>
                                </tr>
                                <tr style="text-align:left;vertical-align:top;">
                                    <td colspan="4" class="bold left">
                                        <?php echo generate_lens_treatments($W ?? '', $LENS_TREATMENTS); ?>
                                    </td>
                                </tr>
                            </table>&nbsp;<br/><br/><br/>
                            <?php
                    } else {
                        $adds = !empty($ODADD) || !empty($OSADD) ? 1 : '';
                        ?>
                            <table id="CTLRx" name="CTLRx" class="refraction bordershadow">
                                <tr class="bold center">
                                    <td class="right bold underline"><?php echo xlt('Right Lens'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="right bold text-uppercase"><?php echo xlt('Brand'); ?>:</td>
                                    <td colspan="4" class="left"><?php echo text($CTLBRANDOD); ?> <?php if ($CTLMANUFACTUREROD) {
                                        echo "(" . text($CTLMANUFACTUREROD) . ")";} ?></td>
                                </tr>
                                <tr class="bold">
                                    <td><?php echo xlt('SPH{{Sphere}}'); ?></td>
                                    <td><?php echo xlt('CYL{{Cylinder}}'); ?></td>
                                    <td><?php echo xlt('AXIS{{Axis of a glasses prescription}}'); ?></td>
                                    <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                    <td><?php echo xlt('DIAM{{Diameter}}'); ?></td>
                                <?php
                                if ($adds) {
                                    ?>
                                            <td><?php echo xlt('ADD{{Bifocal Add}}'); ?></td>
                                        <?php } ?>
                                </tr>
                                <tr>
                                    <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($ODSPH); ?>">
                                    </td>
                                    <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($ODCYL); ?>">
                                    </td>
                                    <td><input type=text id="CTLODAXIS" name="CTLODAXIS"
                                               value="<?php echo attr($ODAXIS); ?>"></td>
                                    <td><input type=text id="CTLODBC" name="CTLODBC" value="<?php echo attr($ODBC); ?>">
                                    </td>
                                    <td><input type=text id="CTLODDIAM" name="CTLODDIAM"
                                               value="<?php echo attr($ODDIAM); ?>"></td>
                                    <?php
                                    if ($adds) {
                                        ?>
                                            <td><input type=text id="CTLODADD" name="CTLODADD" value="<?php echo attr($ODADD); ?>">
                                            </td>
                                        <?php } ?>
                                </tr>
                                <tr>
                                    <td colspan="2" class="right bold text-uppercase"><?php echo xlt('Quantity:'); ?></td>
                                    <td colspan="4" class="left"><input id="CTLODQUANTITY" name="CTLODQUANTITY" value="<?php echo attr($CTLODQUANTITY); ?>" type="text" class="left" /></td>
                                </tr>
                                <tr>
                                    <td colspan="7">
                                        <hr />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right bold large underline"><?php echo xlt('Left Lens'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="right bold text-uppercase"><?php echo xlt('Brand'); ?>:</td>
                                    <td colspan="4" class="left"><?php echo text($CTLBRANDOS); ?> <?php if ($CTLMANUFACTUREROS) {
                                        echo "(" . text($CTLMANUFACTUREROS) . ")";} ?></td>
                                </tr>
                                <tr class="bold" style="line-height:0.3em;font-size:0.6em;">
                                    <td><?php echo xlt('SPH{{Sphere}}'); ?></td>
                                    <td><?php echo xlt('CYL{{Cylinder}}'); ?></td>
                                    <td><?php echo xlt('AXIS{{Axis of a glasses prescription}}'); ?></td>
                                    <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                    <td><?php echo xlt('DIAM{{Diameter}}'); ?></td>
                                    <?php
                                    if ($adds) {
                                        ?>
                                            <td><?php echo xlt('ADD{{Bifocal Add}}'); ?></td>
                                        <?php } ?>
                                </tr>
                                <tr>
                                    <td><input type=text id="CTLOSSPH" name="CTLOSSPH" value="<?php echo attr($OSSPH); ?>">
                                    </td>
                                    <td><input type=text id="CTLOSCYL" name="CTLOSCYL" value="<?php echo attr($OSCYL); ?>">
                                    </td>
                                    <td><input type=text id="CTLOSAXIS" name="CTLOSAXIS"
                                               value="<?php echo attr($OSAXIS); ?>"></td>
                                    <td><input type=text id="CTLOSBC" name="CTLOSBC" value="<?php echo attr($OSBC); ?>">
                                    </td>
                                    <td><input type=text id="CTLOSDIAM" name="CTLOSDIAM"
                                               value="<?php echo attr($OSDIAM); ?>"></td>
                                    <?php
                                    if ($adds) {
                                        ?>
                                            <td><input type=text id="CTLOSADD" name="CTLOSADD" value="<?php echo attr($OSADD); ?>"></td>
                                        <?php } ?>
                                </tr>
                                <tr>
                                    <td colspan="2" class="right bold text-uppercase"><?php echo xlt('Quantity:'); ?></td>
                                    <td colspan="4" class="left"><input id="CTLOSQUANTITY" name="CTLOSQUANTITY" value="<?php echo attr($CTLOSQUANTITY); ?>" type="text" class="left" /></td>
                                </tr>

                                <?php if ($CTL_COMMENTS > '') { ?>
                                    <tr>
                                        <td colspan="7">
                                            <hr />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right bold red" colspan="2"
                                            style="vertical-align:top;"><?php echo xlt('Comments'); ?>:</u></td>
                                        <td colspan="4" class="left">
                                            <textarea cols="30" rows="4" id="COMMENTS" name="COMMENTS"><?php echo text($CTL_COMMENTS); ?></textarea>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                            <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <?php
                    $signature = OEGlobalsBag::getInstance()->get("webserver_root") . "/interface/forms/eye_mag/images/sign_" . attr($session->get('authUserID')) . ".jpg";
                if (file_exists($signature)) {
                    ?>
                <td class="center" style="margin:25px auto;">
                            <span style="position:relative;padding-left:40px;">
                                <img src='<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/forms/eye_mag/images/sign_<?php echo attr($session->get('authUserID')); ?>.jpg'
                                     style="width:240px;height:85px;border-block-end: 1pt solid black;margin:5px;"/>
                                    </span><br/>

                    <?php
                } else {
                    ?>
                <td class="center">
                    <hr style="border:solid 1px black;width:50%;margin:0.5in auto 0;" />
                    <?php
                } ?>

                    <?php echo xlt('Provider'); ?>
                    : <?php echo text($prov_data['fname']); ?> <?php echo text($prov_data['lname']);
                    if ($prov_data['suffix']) {
                        echo ", " . $prov_data['suffix'];
                    } ?>
                    <?php if (isset($prov_data['state_license_number']) && is_string( $prov_data['state_license_number'])) { ?>
                        <br/><?php echo xlt('State License Number'); ?>: <?php echo text($prov_data['state_license_number']); ?>
                    <?php } ?>

                    <small><?php echo xlt('e-signed'); ?> <input type="checkbox" checked="checked" disabled></small>
                </td>
            </tr>
            <tr class="no-print">
                <td>
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-secondary btn-print mt-3' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</form>

</body>
<script>
    $(function () {
        $('.header').click(function () {
            var $this = $(this);
            $(this).nextUntil('tr.header').slideToggle(100).promise().done(function () {
                $this.find('span').html(function (_, value) {
                    return value == '<i class="fa fa-plus-square-o"></i>' ? '<i class="fa fa-minus-square-o"></i>' : '<i class="fa fa-plus-square-o"></i>';
                });
            });
        });
        <?php
        // $detailed is set inside the CTL/non-CTL table branches above; default
        // to '0' for the case where neither branch executes (no PD/prism data
        // and no contact-lens row), so the header stays collapsed by default.
        $detailed ??= '0';
        if (!$detailed) {
            echo "$('.header').trigger('click');";
        } ?>

        $("input[name$='PD']").blur(function () {
            //make it all caps
            var str = $(this).val();
            str = str.toUpperCase();
            $(this).val(str);
        });
        $('input[name$="SPH"]').blur(function () {
            var mid = $(this).val();
            if (mid.match(/PLANO/i)) {
                $(this).val('PLANO');
                return;
            }
            if (mid.match(/^[\+\-]?\d{1}$/)) {
                mid = mid + ".00";
            }
            if (mid.match(/\.[27]$/)) {
                mid = mid + '5';
            }
            if (mid.match(/\.\d$/)) {
                mid = mid + '0';
            }
            //if near is +2. make it +2.00
            if (mid.match(/\.$/)) {
                mid = mid + '00';
            }
            if ((!mid.match(/\./)) && (mid.match(00 | 25 | 50 | 75))) {
                var front = mid.match(/(\d{0,2})(00|25|50|75)/)[1];
                var back = mid.match(/(\d{0,2})(00|25|50|75)/)[2];
                if (front == '') front = '0';
                mid = front + "." + back;
            }
            if (!mid.match(/\./)) {
                var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                var back = mid.match(/(\d{0,2})(\d{2})/)[2];
                if (front == '') front = '0';
                if (front == '-') front = '-0';
                mid = front + "." + back;
            }
            if (!mid.match(/^(\+|\-){1}/)) {
                mid = "+" + mid;
            }
            $(this).val(mid);
        });

        $("input[name$='ADD'],input[name$='ADD2']").blur(function () {
            var add = $(this).val();
            add = add.replace(/=/g, "+");
            //if add is one digit, eg. 2, make it +2.00
            if (add.match(/^\d{1}$/)) {
                add = "+" + add + ".00";
            }
            //if add is '+'one digit, eg. +2, make it +2.00
            if (add.match(/^\+\d{1}$/)) {
                add = add + ".00";
            }
            //if add is 2.5 or 2.0 make it 2.50 or 2.00
            if (add.match(/\.[05]$/)) {
                add = add + '0';
            }
            //if add is 2.2 or 2.7 make it 2.25 or 2.75
            if (add.match(/\.[27]$/)) {
                add = add + '5';
            }
            //if add is +2. make it +2.00
            if (add.match(/\.$/)) {
                add = add + '00';
            }
            if ((!add.match(/\./)) && (add.match(/(0|25|50|75)$/))) {
                var front = add.match(/([\+]?\d{0,1})(00|25|50|75)/)[1];
                var back = add.match(/([\+]?\d{0,1})(00|25|50|75)/)[2];
                if (front == '') front = '0';
                add = front + "." + back;
            }
            if (!add.match(/^(\+)/) && (add.length > 0)) {
                add = "+" + add;
            }
            $(this).val(add);
            if (this.id == "ODADD2") $('#OSADD2').val(add);
            if (this.id == "ODMIDADD") $('#OSMIDADD').val(add);
            if (this.id == "CTLODADD") $('#CTLOSADD').val(add);
        });

        $("input[name$='AXIS']").blur(function () {
            // Make this a 3 digit leading zeros number.
            // we are not translating text to numbers, just numbers to
            // a 3 digit format with leading zeroes as needed.
            // assume the end user KNOWS there are only numbers presented and
            // more than 3 digits is a mistake...
            // (although this may change with topographic answer)
            var axis = $(this).val();
            var group = this.name.replace("AXIS", "CYL");
            ;
            var cyl = $("#" + group).val();
            if ((cyl > '') && (cyl != 'SPH')) {
                if (!axis.match(/\d\d\d/)) {
                    if (!axis.match(/\d\d/)) {
                        if (!axis.match(/\d/)) {
                            axis = '0';
                        }
                        axis = '0' + axis;
                    }
                    axis = '0' + axis;
                }
            } else {
                axis = '';
            }
            $(this).val(axis);
        });
        $("[name$='CYL']").blur(function () {
            var mid = $(this).val();
            var group = this.name.replace("CYL", "SPH");
            ;
            var sphere = $("#" + group).val();
            if (((mid.length == 0) && (sphere.length > 0)) || (mid.match(/sph/i))) {
                $(this).val('SPH');
                var axis = this.name.replace("CYL", "AXIS");
                $("#" + axis).val('');
                return;
            } else if (sphere.length > 0) {
                if (mid.match(/^[\+\-]?\d{1}$/)) {
                    mid = mid + ".00";
                }
                if (mid.match(/^(\d)(\d)$/)) {
                    mid = mid[0] + '.' + mid[1];
                }
                //if mid is 2.5 or 2.0 make it 2.50 or 2.00
                if (mid.match(/\.[05]$/)) {
                    mid = mid + '0';
                }
                //if mid is 2.2 or 2.7 make it 2.25 or 2.75
                if (mid.match(/\.[27]$/)) {
                    mid = mid + '5';
                }
                //if mid is +2. make it +2.00
                if (mid.match(/\.$/)) {
                    mid = mid + '00';
                }
                if (mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)) {
                    var front = mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)[1];
                    var back = mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)[2];
                    if (front == '') front = '0';
                    mid = front + "." + back;
                }
                if (!mid.match(/^(\+|\-){1}/) && (sphere.length > 0)) {
                    //Since it doesn't start with + or - then give it '+'
                    mid = "+" + mid;
                }
                $(this).val(mid);
            }
        });
        $("input,textarea,text,checkbox").change(function () {
            submit_form($(this));
        });
        $("#reverse").click(function () {
            //alert('Start');
            reverse_cylinder('');
            //alert('Finish');

        });
        $("input[name$='SPH'],input[name$='CYL']").on('keyup', function (e) {
            if (e.keyCode == '61' || e.keyCode == '74') {
                now = $(this).val();
                now = now.replace(/=/g, "+").replace(/^j/g, "J");
                $(this).val(now);
            }
        });

    });
</script>
</html>

<?php
    $content = ob_get_clean();
    echo $content;
    exit;
?>
