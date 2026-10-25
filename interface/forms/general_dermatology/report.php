<?php

/**
 * General Dermatology Form report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__) . '/../../globals.php';
require_once 'vendor/autoload.php';

use GeneralDermatology\App\FavoriteDiagnosisCodes;
use GeneralDermatology\Domain\Finding;
use GeneralDermatology\Domain\TreatmentDocument;
use OpenEMR\Common\Database\QueryUtils;

function general_dermatology_report($pid, $encounter, $cols, $id): void
{
    $data = QueryUtils::querySingleRow(
        'SELECT * FROM general_dermatology WHERE id = ? AND pid = ?',
        [$id, $pid]
    ) ?: [];

    $code = new FavoriteDiagnosisCodes();
    $codes = $code->codeArray();

    if ($data === []) {
        echo "<table class='table'><tr><td>" . text(xl('No data found.')) . '</td></tr></table>';
        return;
    }

    $treatmentRaw = $data['treatment'] ?? null;
    $findings = Finding::listFromArrays(
        TreatmentDocument::findingsForUi(TreatmentDocument::fromStorage($treatmentRaw))
    );

    if ($findings === []) {
        echo "<table class='table'><tr><td>" . text(xl('No treatment findings.')) . '</td></tr></table>';
        return;
    }

    foreach ($findings as $item => $finding) {
        if ($finding->isEmpty()) {
            continue;
        }
        $dxLabel = $codes[$finding->dxCode] ?? $finding->dxDescription;
        if ($dxLabel === '' && $finding->dxCode !== '') {
            $dxLabel = $finding->dxCode;
        }
        $procedure = $finding->procedureLabel !== '' ? $finding->procedureLabel : $finding->procedureKey;
        echo "<div><h6 style='float:left;margin: 2px;'>"
            . text(xl('Plan #')) . ' ' . (int) ($item + 1) . ' : </h6>'
            . "<p style='margin:5px;'>"
            . text($finding->findingLocation)
            . ($dxLabel !== '' ? ' - ' . text((string) $dxLabel) : '')
            . ($procedure !== '' ? ' - ' . text($procedure) : '')
            . '</p></div>';
    }
}
