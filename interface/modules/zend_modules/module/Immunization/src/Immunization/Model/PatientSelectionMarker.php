<?php

/**
 * interface/modules/zend_modules/module/Immunization/src/Immunization/Model/PatientSelectionMarker.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Immunization\Model;

class PatientSelectionMarker
{
    /**
     * Mark only the first immunization row for each patient as selectable.
     *
     * @param list<array<mixed, mixed>> $rows
     * @return list<array<mixed, mixed>>
     */
    public static function markFirstRowForEachPatient(array $rows): array
    {
        $seenPatientIds = [];

        foreach ($rows as &$row) {
            $patientId = $row['patientid'] ?? '';
            $patientKey = 'patient:' . (is_scalar($patientId) ? (string) $patientId : '');
            $row['isPatientSelectable'] = !isset($seenPatientIds[$patientKey]);
            $seenPatientIds[$patientKey] = true;
        }
        unset($row);

        return $rows;
    }
}
