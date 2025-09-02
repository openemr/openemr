<?php

/**
 * ObservationService handles all database operations for the observation form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude.AI Generated Refactor
 * @copyright Copyright (c) 2024 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

class ObservationService
{
    /**
     * Get observation data by form id
     *
     * @param int $formId
     * @param int $pid
     * @param int $encounter
     * @return array
     */
    public function getObservationsByFormId(int $formId, int $pid, int $encounter): array
    {
        $sql = "SELECT * FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?";
        $res = sqlStatement($sql, array($formId, $pid, $encounter));

        $observations = [];
        while ($row = sqlFetchArray($res)) {
            $observations[] = $row;
        }

        return $observations;
    }

    /**
     * Get the next available form ID
     *
     * @return int
     */
    public function getNextFormId(): int
    {
        $res = sqlStatement("SELECT MAX(id) as largestId FROM `form_observation`");
        $getMaxid = sqlFetchArray($res);

        if ($getMaxid['largestId']) {
            return $getMaxid['largestId'] + 1;
        }

        return 1;
    }

    /**
     * Delete existing observations for a form
     *
     * @param int $formId
     * @param int $pid
     * @param int $encounter
     * @return void
     */
    public function deleteObservationsByFormId(int $formId, int $pid, int $encounter): void
    {
        sqlStatement(
            "DELETE FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?",
            array($formId, $pid, $encounter)
        );
    }

    /**
     * Save observation data
     *
     * @param array $observationData
     * @return void
     */
    public function saveObservation(array $observationData): void
    {
        $sets = "id     = ?,
            pid         = ?,
            groupname   = ?,
            user        = ?,
            encounter   = ?,
            authorized  = ?,
            activity    = 1,
            observation = ?,
            code        = ?,
            code_type   = ?,
            description = ?,
            table_code  = ?,
            ob_type     = ?,
            ob_value    = ?,
            ob_unit     = ?,
            date        = ?,
            ob_reason_code = ?,
            ob_reason_status = ?,
            ob_reason_text = ?,
            date_end    = ?";

        sqlStatement("INSERT INTO form_observation SET $sets", [
            $observationData['id'],
            $observationData['pid'],
            $observationData['groupname'],
            $observationData['user'],
            $observationData['encounter'],
            $observationData['authorized'],
            $observationData['observation'],
            $observationData['code'],
            $observationData['code_type'],
            $observationData['description'],
            $observationData['table_code'],
            $observationData['ob_type'],
            $observationData['ob_value'],
            $observationData['ob_unit'],
            $observationData['date'],
            $observationData['ob_reason_code'],
            $observationData['ob_reason_status'],
            $observationData['ob_reason_text'],
            $observationData['date_end']
        ]);
    }

    /**
     * Process observation unit value based on code
     *
     * @param string $code
     * @param array $obUnit
     * @param array $obValuePhin
     * @param int $key
     * @return string
     */
    public function processObservationUnit(string $code, array $obUnit, array $obValuePhin, int $key): string
    {
        $obUnitValue = $obUnit[$key] ?? '';

        if ($code == 'SS003') {
            $obUnitValue = "";
        } elseif ($code == '8661-1') {
            $obUnitValue = "";
        } elseif ($code == '21612-7') {
            if (!empty($obUnit)) {
                foreach ($obUnit as $key1 => $val) {
                    if ($key1 == 0) {
                        $obUnitValue = $obUnit[$key1];
                    } else {
                        if ($key1 == $key) {
                            $obUnitValue = $obUnit[$key1];
                        }
                    }
                }
            }
        }

        return $obUnitValue;
    }

    /**
     * Process observation value based on code
     *
     * @param string $code
     * @param array $obValue
     * @param array $obValuePhin
     * @param int $key
     * @return string
     */
    public function processObservationValue(string $code, array $obValue, array $obValuePhin, int $key): string
    {
        if ($code == 'SS003') {
            return $obValuePhin[$key] ?? '';
        }

        return $obValue[$key] ?? '';
    }

    /**
     * Get observation types from list options
     *
     * @return array
     */
    public function getObservationTypes(): array
    {
        $obTypes = [];
        $res = sqlStatement("SELECT `option_id`, `title` FROM `list_options` WHERE `list_id` = 'Observation_Types' ORDER BY `seq`");

        while ($type = sqlFetchArray($res)) {
            $obTypes[] = $type;
        }

        return $obTypes;
    }

    /**
     * Format observation value for display in reports
     *
     * @param array $observation
     * @return array
     */
    public function formatObservationForDisplay(array $observation): array
    {
        // Format SS003 values
        if ($observation['code'] == 'SS003') {
            $valueMap = [
                '261QE0002X' => 'Emergency Care',
                '261QM2500X' => 'Medical Specialty',
                '261QP2300X' => 'Primary Care',
                '261QU0200X' => 'Urgent Care'
            ];

            if (isset($valueMap[$observation['ob_value']])) {
                $observation['ob_value'] = $valueMap[$observation['ob_value']];
            }
        }

        // Format 21612-7 units
        if ($observation['code'] == '21612-7') {
            $unitMap = [
                'd' => 'Day',
                'mo' => 'Month',
                'UNK' => 'Unknown',
                'wk' => 'Week',
                'a' => 'Year'
            ];

            if (isset($unitMap[$observation['ob_unit']])) {
                $observation['ob_unit'] = $unitMap[$observation['ob_unit']];
            }
        }

        return $observation;
    }

    /**
     * Validate observation data
     *
     * @param array $data
     * @return array Array of validation errors
     */
    public function validateObservationData(array $data): array
    {
        $errors = [];

        if (empty($data['code'])) {
            $errors[] = 'Code is required';
        }

        if (empty($data['description'])) {
            $errors[] = 'Description is required';
        }

        if (empty($data['date'])) {
            $errors[] = 'Date is required';
        }

        return $errors;
    }
}
