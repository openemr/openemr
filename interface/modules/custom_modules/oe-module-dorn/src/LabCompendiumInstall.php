<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Modules\Dorn\LabRouteSetup;

class LabCompendiumInstall
{
    /**
     * Echoes a string wrapped in an HTML <li>.
     */
    protected static function echoLi(string $msg): void
    {
        echo '<li>' . text($msg) . '</li>';
        ob_flush();
        flush();
    }

    public static function install($labGuid)
    {
        $compendiumResponse = ConnectorApi::getCompendium($labGuid);
        if ($compendiumResponse->isSuccess && $compendiumResponse->compendium) {
            $result = LabRouteSetup::getProcedureIdProviderByLabGuid($labGuid);
            while ($record = sqlFetchArray($result)) {
                $lab_id = $record["ppid"];
                LabCompendiumInstall::uninstall($lab_id);
                $parentId = LabCompendiumInstall::loadGroupRecord($compendiumResponse->compendium, $lab_id);
                foreach ($compendiumResponse->compendium->orderableItems as $item) {
                    LabCompendiumInstall::loadOrderableItem($item, $parentId, $lab_id);
                }
            }
            ConnectorApi::setCompendiumLastUpdate($labGuid);
            self::echoLi("Compendium has been updated for lab: " . ($compendiumResponse->compendium->labName));
        } else {
            self::echoLi("Error Getting Compendium! " . ($compendiumResponse->responseMessage));
        }
    }

    public static function loadGroupRecord($compendium, $lab_id)
    {
        $sql = "SELECT * FROM procedure_type WHERE parent = ? AND lab_id = ? AND procedure_type = ?";
        $parentRecord = sqlQuery($sql, [0, $lab_id, 'grp']);

        if ($parentRecord) {
            $sql = "SELECT * FROM procedure_type WHERE parent = ? AND lab_id = ? AND procedure_type = ? AND description = ?";
            $orderingTests = sqlQuery($sql, [$parentRecord["procedure_type_id"], $lab_id, "grp", "Ordering Tests"]);
            if ($orderingTests) {
                $orderingTestsId = $orderingTests["procedure_type_id"];
                return $orderingTestsId;
            }
        }


        $sql = "INSERT INTO procedure_type (name, lab_id, procedure_type, description)
        VALUES (?, ?, ?, ?)";

        $sqlArr = [$compendium->labName, $lab_id, 'grp', 'DORN:' . $compendium->labName . ' Orders'];
        $id = sqlInsert($sql, $sqlArr);

        $sql = "INSERT INTO procedure_type (parent,name, lab_id, procedure_type, description)
                VALUES (?, ?, ?, ?, ?)";

        $sqlArr = [$id, $compendium->labName, $lab_id, 'grp', 'Ordering Tests'];
        $id = sqlInsert($sql, $sqlArr);

        return $id;
    }

    public static function loadOrderableItem($item, $parentId, $lab_id)
    {
        if (!$item->loinc) {
            $item->loinc = "";
        }

        $sql = "SELECT procedure_type_id FROM procedure_type
            WHERE lab_id = ? AND parent = ? AND procedure_code = ? AND procedure_type = ? AND standard_code = ?";
        $procOrder = sqlQuery($sql, [$lab_id, $parentId, $item->code, "ord", $item->loinc]);
        if ($procOrder) {
            $id = $procOrder["procedure_type_id"];
            $sql = "UPDATE procedure_type SET Activity = ? WHERE procedure_type_id = ?";
            sqlStatement($sql, [1, $id]);
        } else {
            $sql = "INSERT INTO procedure_type (parent, name, lab_id, procedure_type, procedure_code, standard_code)
            VALUES (?, ?, ?, ?, ?, ?)";

            $sqlArr = [$parentId, $item->name ?? '', $lab_id ?? '', 'ord', $item->code ?? '', $item->loinc ?? ''];
            $id = sqlInsert($sql, $sqlArr);
        }

        foreach ($item->components as $component) {
            LabCompendiumInstall::loadResult($component, $id, $lab_id);
        }
        $aoeCount = 1;
        foreach ($item->aoe as $aoe) {
            LabCompendiumInstall::loadAoe($aoe, $lab_id, $aoeCount, $item->code);
            $aoeCount++;
        }
    }

    public static function loadResult($component, $parentId, $lab_id)
    {
        $sql = "INSERT INTO procedure_type (parent, name, lab_id, procedure_type, procedure_code, standard_code)
        VALUES (?, ?, ?, ?, ?, ?)";
        $sqlArr = [$parentId, $component->name ?? '', $lab_id ?? '', 'res', $component->code ?? '', $component->loinc ?? ''];
        $id = sqlInsert($sql, $sqlArr);
    }

    public static function loadAoe($aoe, $lab_id, $aoeCount, $pcode)
    {
        $fldtype = LabCompendiumInstall::getQuestionType($aoe->questionType);
        $qcode = $aoe->originalQuestionCode;
        $question = $aoe->question;
        $required = $aoe->answerRequired;
        $activity = 1;
        $maxSize = 15;
        $options = LabCompendiumInstall::formatAnswers($aoe->answers, $aoe->verboseAnswers) ?: null;

        // check for existing record
        $qrow = sqlQuery(
            "SELECT * FROM procedure_questions WHERE lab_id = ? AND procedure_code = ? AND question_code = ?",
            [
                $lab_id,
                $pcode,
                $qcode
            ]
        );

        // new record
        if (empty($qrow ['procedure_code'])) {
            sqlStatement(
                "INSERT INTO procedure_questions SET seq = ?, lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, fldtype = ?, required = ?, tips = ?, activity = ?, options = ?, maxsize = ?",
                [
                    $aoeCount,
                    $lab_id,
                    $pcode,
                    $qcode,
                    $question,
                    $fldtype,
                    $required,
                    "",
                    $activity,
                    $options,
                    $maxSize
                ]
            );
        } else { // update record
            sqlStatement(
                "UPDATE procedure_questions SET seq = ?, question_text = ?, fldtype = ?, required = ?, tips = ?, activity = ?, options = ?, maxsize = ?  WHERE lab_id = ? AND procedure_code = ? AND question_code = ?",
                [
                    $aoeCount,
                    $question,
                    $fldtype,
                    $required,
                    "",
                    $activity,
                    $options,
                    $maxSize,
                    $lab_id,
                    $pcode,
                    $qcode,
                ]
            );
        }
    }

    public static function formatAnswers($answers, $v_answers): string
    {
        $returnValue = "";
        foreach ($answers as $k => $answer) {
            if (empty($v_answers[$k] ?? '')) {
                $v_answers[$k] = $answer; // if no verbose answer, use the answer as the verbose answer
            }
            $value = $v_answers[$k] . ":" . $answer;
            $returnValue .= $value . ";";
        }
        return $returnValue;
    }

    public static function getQuestionType($questionType)
    {
        /*
        text field = T
        numeric field = N
        Date feild = D
        Gestational age in weeks and days. = G
        List of Check boxes = M
        Radio buttons or drop-list, depending on the number of choices. = anything else (maybe S) for a single select
        */
        return match ($questionType) {
            'Free Text' => 'T',
            'List' => 'S',
            'Multi-Select List' => 'M',
            default => 'T',
        };
    }

    public static function uninstall($lab_id)
    {
        sqlStatement("DELETE FROM procedure_type WHERE lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", [$lab_id]);
        // Mark everything else for the indicated lab as inactive.
        sqlStatement("UPDATE procedure_type SET activity = 0, related_code = '' WHERE lab_id = ? AND procedure_type != 'grp' ", [$lab_id]);
    }
}
