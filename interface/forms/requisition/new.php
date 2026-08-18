<?php

/**
 *  Lab Requisition Form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2023 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\DateFormatterUtils;

// Prefer absolute require paths so PHPStan can resolve library includes.
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/patient.inc.php");
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php");
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/lab.inc.php");

formHeader("Form:Lab Requisition");

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$returnurl = 'encounter_top.php';

$formIdInput = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formid = is_int($formIdInput) && $formIdInput >= 0 ? $formIdInput : 0;
EncounterFormAccess::assertFormBelongsToSessionPatient($formid, 'requisition');
$obj = $formid ? formFetch("form_requisition", $formid) : [];

global $pid;

// Narrow mixed session/global values before helper calls (PHPStan).
if (is_int($pid)) {
    $safePid = $pid;
} elseif (is_string($pid) && ctype_digit($pid)) {
    $safePid = (int) $pid;
} else {
    $safePid = 0;
}

$encounterRaw = $session->get('encounter');
if (is_int($encounterRaw)) {
    $encounter = $encounterRaw;
} elseif (is_string($encounterRaw) && ctype_digit($encounterRaw)) {
    $encounter = (int) $encounterRaw;
} else {
    $encounter = 0;
}

$oidRaw = fetchProcedureId($safePid, $encounter);
if (is_int($oidRaw)) {
    $oid = $oidRaw;
} elseif (is_string($oidRaw) && ctype_digit($oidRaw)) {
    $oid = (int) $oidRaw;
} else {
    $oid = 0;
}

$patient_id = $safePid;
$pdata = getPatientData($safePid);
$facility = getFacility();
$ins = getAllinsurances($safePid);
if (!is_array($ins)) {
    $ins = [];
}
$orders = $oid > 0 ? getProceduresInfo($oid, $encounter) : [];
// Order-level fields are identical across rows; read from the first row.
$firstRow = $orders[0] ?? [];

$provIdRaw = $firstRow['provider_id'] ?? null;
if (is_int($provIdRaw)) {
    $prov_id = $provIdRaw;
} elseif (is_string($provIdRaw) && $provIdRaw !== '') {
    $prov_id = $provIdRaw;
} else {
    $prov_id = '';
}

$labRaw = $firstRow['lab_id'] ?? null;
if (is_int($labRaw)) {
    $lab = $labRaw;
} elseif (is_string($labRaw) && ctype_digit($labRaw)) {
    $lab = (int) $labRaw;
} else {
    $lab = null;
}

$provider = $prov_id !== '' ? (getLabProviders($prov_id) ?? []) : [];
$npi = $prov_id !== '' ? getNPI($prov_id) : ['', ''];
$pp = $lab !== null ? getProcedureProvider($lab) : [];
$provLabId = $lab !== null ? getLabconfig($lab) : false;

// Collect AOE Q&A pairs across all ordered procedure codes.
$aoeAnswers = [];
if ($oid > 0 && $lab !== null) {
    foreach ($orders as $codeRow) {
        $codeRaw = $codeRow['procedure_code'] ?? '';
        $seqRaw = $codeRow['procedure_order_seq'] ?? '';
        $code = is_int($codeRaw) || is_string($codeRaw) ? (string) $codeRaw : '';
        $seq = is_int($seqRaw) || is_string($seqRaw) ? (string) $seqRaw : '';
        if ($code === '' || $seq === '') {
            continue;
        }
        foreach (getProcedureOrderAnswers($oid, $lab, $code, $seq) as $aoeRow) {
            $aoeAnswers[] = $aoeRow;
        }
    }
}

// Determine responsible party from the procedure order billing_type.
// 'C' = Client/Clinic, 'P' = Patient, 'T' = Third Party/Insurance
$billingType = $oid > 0 ? getProcedureBillingType($oid) : '';
$facilityData = is_array($facility) ? $facility : [];
$patientData = is_array($pdata) ? $pdata : [];
$primaryIns = (isset($ins[0]) && is_array($ins[0])) ? $ins[0] : [];
$secondaryIns = (isset($ins[1]) && is_array($ins[1])) ? $ins[1] : [];
$responsibleParty = buildResponsibleParty($billingType, $facilityData, $patientData, $primaryIns);
$hasResponsibleParty = $responsibleParty !== [];
?>

<?php
$bar = '';
$clientNumber = '';
$facilityCityLine = '';
$labCityLine = '';
$providerName = '';
$patientName = '';
$patientDob = '';
$collectionDate = '';
$orderDate = '';
$relationshipDisplay = '/';
$billingLabel = xl('Not Specified');

if ($orders !== [] && $oid > 0) {
    /**
     * Persist the requisition barcode the first time the form is viewed.
     */
    $labIdRaw = $firstRow['procedure_order_id'] ?? $oid;
    $lab_id = is_int($labIdRaw) || is_string($labIdRaw) ? $labIdRaw : $oid;
    $storeBar = getBarId($lab_id, $safePid);

    if (is_array($storeBar) && isset($storeBar['req_id']) && $storeBar['req_id'] !== '' && $storeBar['req_id'] !== null) {
        $bar = (string) $storeBar['req_id'];
    } else {
        $bar = (string) random_int(1000, 999999);
        saveBarCode($bar, $safePid, $lab_id);
    }

    $clientNumber = is_array($provLabId) ? (string) ($provLabId['recv_fac_id'] ?? '') : '';

    $facilityCity = (string) ($facilityData['city'] ?? '');
    $facilityState = (string) ($facilityData['state'] ?? '');
    $facilityZip = (string) ($facilityData['postal_code'] ?? '');
    $facilityCityLine = trim(
        $facilityCity .
        ($facilityCity !== '' && $facilityState !== '' ? ', ' : '') .
        $facilityState .
        ' ' .
        $facilityZip
    );

    $labCity = (string) ($pp['city'] ?? '');
    $labState = (string) ($pp['state'] ?? '');
    $labZip = (string) ($pp['zip'] ?? '');
    $labCityLine = trim(
        $labCity .
        ($labCity !== '' && $labState !== '' ? ', ' : '') .
        $labState .
        ' ' .
        $labZip
    );

    $providerName = trim((string) ($provider['fname'] ?? '') . ' ' . (string) ($provider['lname'] ?? ''));
    $patientName = trim((string) ($patientData['fname'] ?? '') . ' ' . (string) ($patientData['lname'] ?? ''));
    $patientDobRaw = $patientData['DOB'] ?? '';
    $patientDob = is_string($patientDobRaw) && $patientDobRaw !== ''
        ? (string) DateFormatterUtils::oeFormatShortDate($patientDobRaw)
        : '';
    $collectionRaw = $firstRow['date_collected'] ?? null;
    $collectionDate = is_string($collectionRaw) && $collectionRaw !== ''
        ? DateFormatterUtils::oeFormatDateTime($collectionRaw)
        : '';
    $orderRaw = $firstRow['date_ordered'] ?? null;
    $orderDate = is_string($orderRaw) && $orderRaw !== ''
        ? DateFormatterUtils::oeFormatDateTime($orderRaw)
        : '';

    if ($hasResponsibleParty && ($responsibleParty['relationship'] ?? '') !== '') {
        $relationshipDisplay = ($responsibleParty['relationship_is_list'] ?? false)
            ? text(getListItemTitle('sub_relation', (string) $responsibleParty['relationship']))
            : text((string) xl((string) $responsibleParty['relationship']));
    }

    $billingLabel = match ($billingType) {
        'C' => xl('Clinic Billing'),
        'P' => xl('Patient Billing'),
        'T' => xl('Third Party / Insurance'),
        default => xl('Not Specified'),
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Lab Requisition'); ?></title>
    <style>
        :root {
            --req-ink: #111;
            --req-muted: #555;
            --req-line: #222;
            --req-soft: #f2f2f2;
            --req-accent: #1f3a5f;
            --req-band: #e8eef5;
        }

        body.requisition-page {
            background: #e9ecef;
            color: var(--req-ink);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.35;
        }

        .req-toolbar {
            max-width: 960px;
            margin: 1rem auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0 0.75rem;
        }

        .req-sheet {
            max-width: 960px;
            margin: 0.75rem auto 2rem;
            background: #fff;
            border: 1px solid #c5c9d0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            padding: 1.25rem 1.5rem 1.75rem;
        }

        .req-titlebar {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            border-bottom: 3px solid var(--req-accent);
            padding-bottom: 0.85rem;
            margin-bottom: 0.85rem;
        }

        .req-brand h1 {
            margin: 0;
            color: var(--req-accent);
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .req-brand .req-subtitle {
            margin: 0.2rem 0 0;
            color: var(--req-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .req-barcode {
            text-align: right;
            min-width: 180px;
        }

        .req-barcode img {
            max-height: 54px;
            width: auto;
        }

        .req-barcode .req-bar-number {
            margin-top: 0.25rem;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .req-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1rem;
            margin-bottom: 0.85rem;
        }

        .req-meta-card {
            border: 1px solid var(--req-line);
            background: var(--req-soft);
            padding: 0.65rem 0.75rem;
            min-height: 5.5rem;
        }

        .req-meta-card h2 {
            margin: 0 0 0.35rem;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--req-accent);
            border-bottom: 1px solid #c9d0d9;
            padding-bottom: 0.25rem;
        }

        .req-meta-card p {
            margin: 0;
            font-size: 0.92rem;
        }

        .req-id-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .req-id-box {
            border: 1px solid var(--req-line);
            padding: 0.45rem 0.55rem;
        }

        .req-id-box .label {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--req-muted);
            margin-bottom: 0.15rem;
        }

        .req-id-box .value {
            display: block;
            font-size: 0.98rem;
            font-weight: 600;
            min-height: 1.2rem;
            word-break: break-word;
        }

        .req-section {
            border: 1px solid var(--req-line);
            margin-bottom: 0.75rem;
            page-break-inside: avoid;
        }

        .req-section-title {
            margin: 0;
            padding: 0.4rem 0.65rem;
            background: var(--req-band);
            border-bottom: 1px solid var(--req-line);
            color: var(--req-accent);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .req-section-body {
            padding: 0.55rem 0.65rem 0.7rem;
        }

        .req-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .req-fields {
            width: 100%;
            border-collapse: collapse;
        }

        .req-fields th,
        .req-fields td {
            border: 0;
            padding: 0.22rem 0.3rem;
            vertical-align: top;
            font-size: 0.9rem;
        }

        .req-fields th {
            width: 38%;
            color: var(--req-muted);
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
        }

        .req-fields td {
            border-bottom: 1px dotted #b7b7b7;
            font-weight: 500;
            min-width: 4rem;
        }

        .req-empty {
            color: #888;
            font-style: italic;
        }

        .req-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .req-table th,
        .req-table td {
            border: 1px solid #9aa3ad;
            padding: 0.4rem 0.5rem;
            vertical-align: top;
            font-size: 0.9rem;
        }

        .req-table thead th {
            background: #f7f8fa;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--req-accent);
        }

        .req-table tbody tr:nth-child(even) {
            background: #fbfbfc;
        }

        .req-notes-box {
            min-height: 3.5rem;
            border: 1px solid #c5c9d0;
            background: #fafafa;
            padding: 0.5rem 0.65rem;
            white-space: pre-wrap;
        }

        .req-aoe-list {
            margin: 0;
            padding-left: 1.1rem;
        }

        .req-aoe-list li {
            margin-bottom: 0.35rem;
        }

        .req-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.75rem;
            margin-top: 0.85rem;
        }

        .req-sign-box {
            border: 1px solid var(--req-line);
            min-height: 4.5rem;
            padding: 0.45rem 0.55rem;
        }

        .req-sign-box .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--req-muted);
            margin-bottom: 1.8rem;
        }

        .req-sign-box .line {
            border-top: 1px solid #333;
            margin-top: 1.6rem;
            padding-top: 0.25rem;
            font-size: 0.72rem;
            color: var(--req-muted);
        }

        .req-footer {
            margin-top: 0.9rem;
            border-top: 2px solid var(--req-accent);
            padding-top: 0.55rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            color: var(--req-muted);
            font-size: 0.78rem;
        }

        .req-alert {
            max-width: 720px;
            margin: 3rem auto;
            background: #fff;
            border: 1px solid #d0d5dd;
            border-radius: 0.35rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        @media print {
            body.requisition-page {
                background: #fff;
            }

            .req-toolbar,
            .d-print-none {
                display: none !important;
            }

            .req-sheet {
                max-width: none;
                margin: 0;
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .req-section,
            .req-meta-card,
            .req-id-box,
            .req-sign-box,
            .req-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            a[href]::after {
                content: none !important;
            }
        }

        @media screen and (max-width: 800px) {
            .req-meta,
            .req-grid-2,
            .req-id-row,
            .req-signatures {
                grid-template-columns: 1fr;
            }

            .req-titlebar {
                flex-direction: column;
            }

            .req-barcode {
                text-align: left;
            }
        }
    </style>
</head>
<body class="requisition-page">
<?php if ($orders === [] || $oid <= 0) : ?>
    <div class="req-alert">
        <h2 class="h5 mb-2"><?php echo xlt('Lab Requisition Unavailable'); ?></h2>
        <p class="mb-0">
            <?php
            echo $oid <= 0
                ? xlt('No order found. Please enter a procedure order first.')
                : xlt('Procedure order not found in database. Contact technical support.');
            ?>
        </p>
    </div>
<?php else : ?>
    <div class="req-toolbar d-print-none">
        <button type="button" class="btn btn-primary btn-print" onclick="window.print()">
            <?php echo xlt('Print Requisition'); ?>
        </button>
    </div>

    <main class="req-sheet" id="printableArea">
        <header class="req-titlebar">
            <div class="req-brand">
                <h1><?php echo xlt('Laboratory Requisition'); ?></h1>
                <p class="req-subtitle"><?php echo xlt('Official Order Document'); ?></p>
            </div>
            <div class="req-barcode">
                <img src="../../forms/requisition/barcode.php?text=<?php echo attr_url($bar); ?>" alt="<?php echo xla('Requisition barcode'); ?>" />
                <div class="req-bar-number"><?php echo text($bar); ?></div>
            </div>
        </header>

        <section class="req-meta">
            <div class="req-meta-card">
                <h2><?php echo xlt('Ordering Facility'); ?></h2>
                <p><strong><?php echo text((string) ($facilityData['name'] ?? '')); ?></strong></p>
                <p><?php echo text((string) ($facilityData['street'] ?? '')); ?></p>
                <p><?php echo text($facilityCityLine); ?></p>
                <p><?php echo text((string) ($facilityData['phone'] ?? '')); ?></p>
            </div>
            <div class="req-meta-card">
                <h2><?php echo xlt('Performing Laboratory'); ?></h2>
                <p><strong><?php echo text((string) ($pp['organization'] ?? '')); ?></strong></p>
                <p><?php echo text((string) ($pp['street'] ?? '')); ?></p>
                <p><?php echo text($labCityLine); ?></p>
                <p>
                    <?php echo xlt('Phone'); ?>: <?php echo text((string) ($pp['phone'] ?? '')); ?>
                    &nbsp;|&nbsp;
                    <?php echo xlt('Fax'); ?>: <?php echo text((string) ($pp['fax'] ?? '')); ?>
                </p>
            </div>
        </section>

        <section class="req-id-row">
            <div class="req-id-box">
                <span class="label"><?php echo xlt('Requisition Number'); ?></span>
                <span class="value"><?php echo text($bar); ?></span>
            </div>
            <div class="req-id-box">
                <span class="label"><?php echo xlt('Client Number'); ?></span>
                <span class="value"><?php echo text($clientNumber !== '' ? $clientNumber : '—'); ?></span>
            </div>
            <div class="req-id-box">
                <span class="label"><?php echo xlt('Lab Reference ID'); ?></span>
                <span class="value"><?php echo text((string) ($firstRow['procedure_order_id'] ?? $oid)); ?></span>
            </div>
            <div class="req-id-box">
                <span class="label"><?php echo xlt('Billing Type'); ?></span>
                <span class="value"><?php echo text($billingLabel); ?></span>
            </div>
        </section>

        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('Patient Information'); ?></h2>
            <div class="req-section-body">
                <div class="req-grid-2">
                    <table class="req-fields">
                        <tr>
                            <th><?php echo xlt('Patient Name'); ?></th>
                            <td><?php echo text($patientName !== '' ? $patientName : '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Patient ID'); ?></th>
                            <td><?php echo text((string) $safePid); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Date of Birth'); ?></th>
                            <td><?php echo text($patientDob !== '' ? $patientDob : '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Sex'); ?></th>
                            <td><?php echo text(getListItemTitle('sex', (string) ($patientData['sex'] ?? '')) ?: '—'); ?></td>
                        </tr>
                    </table>
                    <table class="req-fields">
                        <tr>
                            <th><?php echo xlt('Collection Date/Time'); ?></th>
                            <td><?php echo text($collectionDate !== '' ? $collectionDate : '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Order Date/Time'); ?></th>
                            <td><?php echo text($orderDate !== '' ? $orderDate : '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Specimen Type'); ?></th>
                            <td><?php echo text((string) ($firstRow['specimen_type'] ?? '') ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Specimen Location'); ?></th>
                            <td><?php echo text((string) ($firstRow['specimen_location'] ?? '') ?: '—'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>

        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('Ordering Physician & Responsible Party'); ?></h2>
            <div class="req-section-body">
                <div class="req-grid-2">
                    <table class="req-fields">
                        <tr>
                            <th><?php echo xlt('Physician Name'); ?></th>
                            <td><?php echo text($providerName !== '' ? $providerName : '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('NPI'); ?></th>
                            <td><?php echo text((string) ($npi[0] ?? '') ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('UPIN'); ?></th>
                            <td><?php echo text((string) ($npi[1] ?? '') ?: '—'); ?></td>
                        </tr>
                    </table>
                    <table class="req-fields">
                        <tr>
                            <th><?php echo xlt('Name'); ?></th>
                            <td><?php echo $hasResponsibleParty && ($responsibleParty['name'] ?? '') !== '' ? text((string) $responsibleParty['name']) : '<span class="req-empty">/</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Address'); ?></th>
                            <td><?php echo $hasResponsibleParty && ($responsibleParty['address'] ?? '') !== '' ? text((string) $responsibleParty['address']) : '<span class="req-empty">/</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('City, St, Zip'); ?></th>
                            <td><?php echo $hasResponsibleParty && ($responsibleParty['city_st_zip'] ?? '') !== '' ? text((string) $responsibleParty['city_st_zip']) : '<span class="req-empty">/</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo xlt('Relationship'); ?></th>
                            <td><?php echo $relationshipDisplay === '/' ? '<span class="req-empty">/</span>' : $relationshipDisplay; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>

        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('Insurance Information'); ?></h2>
            <div class="req-section-body">
                <div class="req-grid-2">
                    <div>
                        <strong><?php echo xlt('Primary Insurance'); ?></strong>
                        <?php if ($billingType === 'T' && $primaryIns !== []) : ?>
                            <table class="req-fields mt-1">
                                <tr>
                                    <th><?php echo xlt('Bill Type'); ?></th>
                                    <td><?php echo xlt('Insurance'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Insurance Name'); ?></th>
                                    <td><?php echo text((string) ($primaryIns['name'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Insurance Address'); ?></th>
                                    <td><?php echo text((string) ($primaryIns['line1'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('City, St, Zip'); ?></th>
                                    <td><?php echo text(trim((string) ($primaryIns['city'] ?? '') . ', ' . (string) ($primaryIns['state'] ?? '') . ' ' . (string) ($primaryIns['zip'] ?? ''))); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Subscriber/Policy #'); ?></th>
                                    <td><?php echo text((string) ($primaryIns['policy_number'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Group #'); ?></th>
                                    <td><?php echo text((string) ($primaryIns['group_number'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Employer'); ?></th>
                                    <td><?php echo text((string) ($primaryIns['subscriber_employer'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Relationship'); ?></th>
                                    <td><?php echo text(getListItemTitle('sub_relation', (string) ($primaryIns['subscriber_relationship'] ?? ''))); ?></td>
                                </tr>
                            </table>
                        <?php else : ?>
                            <p class="mb-0 mt-1"><?php echo $billingType === 'C' ? xlt('Clinic Billing') : xlt('Patient Billing'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?php echo xlt('Secondary Insurance'); ?></strong>
                        <?php if ($billingType === 'T' && $secondaryIns !== []) : ?>
                            <table class="req-fields mt-1">
                                <tr>
                                    <th><?php echo xlt('Bill Type'); ?></th>
                                    <td><?php echo xlt('Insurance'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Insurance Name'); ?></th>
                                    <td><?php echo text((string) ($secondaryIns['name'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Insurance Address'); ?></th>
                                    <td><?php echo text((string) ($secondaryIns['line1'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('City, St, Zip'); ?></th>
                                    <td><?php echo text(trim((string) ($secondaryIns['city'] ?? '') . ', ' . (string) ($secondaryIns['state'] ?? '') . ' ' . (string) ($secondaryIns['zip'] ?? ''))); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Subscriber/Policy #'); ?></th>
                                    <td><?php echo text((string) ($secondaryIns['policy_number'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Group #'); ?></th>
                                    <td><?php echo text((string) ($secondaryIns['group_number'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Employer'); ?></th>
                                    <td><?php echo text((string) ($secondaryIns['subscriber_employer'] ?? '')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo xlt('Relationship'); ?></th>
                                    <td><?php echo text(getListItemTitle('sub_relation', (string) ($secondaryIns['subscriber_relationship'] ?? ''))); ?></td>
                                </tr>
                            </table>
                        <?php else : ?>
                            <p class="mb-0 mt-1 req-empty"><?php echo xlt('None'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('Tests Ordered'); ?></h2>
            <div class="req-section-body p-0">
                <table class="req-table">
                    <thead>
                        <tr>
                            <th style="width: 18%;"><?php echo xlt('Code'); ?></th>
                            <th><?php echo xlt('Test / Procedure'); ?></th>
                            <th style="width: 28%;"><?php echo xlt('Diagnosis Codes'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $codeRow) : ?>
                            <tr>
                                <td><?php echo text((string) ($codeRow['procedure_code'] ?? '')); ?></td>
                                <td><?php echo text((string) ($codeRow['procedure_name'] ?? '')); ?></td>
                                <td><?php echo text((string) ($codeRow['diagnoses'] ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('Clinical History / Order Notes'); ?></h2>
            <div class="req-section-body">
                <div class="req-notes-box">
                    <?php
                    $clinicalHx = trim((string) ($firstRow['clinical_hx'] ?? ''));
                    $patientInstructions = trim((string) ($firstRow['patient_instructions'] ?? ''));
                    if ($clinicalHx === '' && $patientInstructions === '') {
                        echo '<span class="req-empty">' . xlt('No clinical notes provided.') . '</span>';
                    } else {
                        if ($clinicalHx !== '') {
                            echo text($clinicalHx);
                        }
                        if ($clinicalHx !== '' && $patientInstructions !== '') {
                            echo "\n\n";
                        }
                        if ($patientInstructions !== '') {
                            echo '<strong>' . xlt('Patient Instructions') . ':</strong> ' . text($patientInstructions);
                        }
                    }
                    ?>
                </div>
            </div>
        </section>

        <?php if ($aoeAnswers !== []) : ?>
        <section class="req-section">
            <h2 class="req-section-title"><?php echo xlt('AOE Questions & Answers'); ?></h2>
            <div class="req-section-body">
                <ol class="req-aoe-list">
                    <?php foreach ($aoeAnswers as $aoeRow) : ?>
                        <li>
                            <strong><?php echo text($aoeRow['question_text'] !== '' ? $aoeRow['question_text'] : xl('Question')); ?>:</strong>
                            <?php echo text($aoeRow['answer'] !== '' ? $aoeRow['answer'] : '—'); ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
        <?php endif; ?>

        <section class="req-signatures">
            <div class="req-sign-box">
                <div class="label"><?php echo xlt('Collector Signature'); ?></div>
                <div class="line"><?php echo xlt('Signature / Date'); ?></div>
            </div>
            <div class="req-sign-box">
                <div class="label"><?php echo xlt('Ordering Provider Signature'); ?></div>
                <div class="line"><?php echo xlt('Signature / Date'); ?></div>
            </div>
            <div class="req-sign-box">
                <div class="label"><?php echo xlt('Lab Receipt'); ?></div>
                <div class="line"><?php echo xlt('Received By / Date-Time'); ?></div>
            </div>
        </section>

        <footer class="req-footer">
            <div><?php echo xlt('End of Requisition'); ?> #<?php echo text($bar); ?></div>
            <div><?php echo xlt('Generated'); ?>: <?php echo text(DateFormatterUtils::oeFormatDateTime(date('Y-m-d H:i:s'))); ?></div>
        </footer>
    </main>
<?php endif; ?>
</body>
</html>
