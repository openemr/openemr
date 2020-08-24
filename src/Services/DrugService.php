<?php

/**
 * DrugService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class DrugService extends BaseService
{

    private const DRUG_TABLE = "drugs";
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::DRUG_TABLE);
        $this->uuidRegistry = new UuidRegistry([
            'table_name' => self::DRUG_TABLE,
            'table_id' => 'drug_id'
        ]);
        $this->uuidRegistry->createMissingUuids();
    }

    /**
     * Returns a list of drugs matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $codeRequired = false)
    {
        $sqlBindArray = array();

        $sql = "SELECT drugs.drug_id,
                uuid,
                name,
                ndc_number,
                form,
                size,
                unit,
                route,
                related_code,
                active,
                drug_code,
                drug_inventory.manufacturer,
                drug_inventory.lot_number,
                drug_inventory.expiration
                FROM drugs
                LEFT JOIN drug_inventory
                ON drugs.drug_id = drug_inventory.drug_id";

        if (!empty($search)) {
            $sql .= ' WHERE ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            if (!$codeRequired || $row['drug_code'] != "") {
                $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
                if ($row['drug_code'] != "") {
                    $row['drug_code'] = $this->addCoding($row['drug_code']);
                }
                $processingResult->addData($row);
            }
        }

        return $processingResult;
    }

    /**
     * Returns a single drug record by id.
     * @param $uuid - The drug uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $codeRequired = false)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", self::DRUG_TABLE, $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT drugs.drug_id,
                uuid,
                name,
                ndc_number,
                form,
                size,
                unit,
                route,
                related_code,
                active,
                drug_code,
                drug_inventory.manufacturer,
                drug_inventory.lot_number,
                drug_inventory.expiration
                FROM drugs
                LEFT JOIN drug_inventory
                ON drugs.drug_id = drug_inventory.drug_id
                WHERE drugs.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        if (!$codeRequired || $sqlResult['drug_code'] != "") {
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            if ($sqlResult['drug_code'] != "") {
                $sqlResult['drug_code'] = $this->addCoding($sqlResult['drug_code']);
            }
            $processingResult->addData($sqlResult);
        }

        return $processingResult;
    }
}
