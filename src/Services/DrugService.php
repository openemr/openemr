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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class DrugService extends BaseService
{
    private const DRUG_TABLE = "drugs";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::DRUG_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::DRUG_TABLE]);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    /**
     * Returns a list of drugs matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param $puuidBind - Patient uuid to return drug resources that are only visible to the current patient
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }
        // so if we have a puuid we need to make sure we only return drugs that are connected to the current patient.
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($search, $isAndCondition);
    }

    /**
     * Returns a single drug record by id.
     * @param $uuid - The drug uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $search = [
            'uuid' => new TokenSearchField('uuid', [new TokenSearchValue($uuid, null, false)])
        ];
        // so if we have a puuid we need to make sure we only return drugs that are connected to the current patient.
        if (isset($puuid)) {
            $search['puuid'] = new ReferenceSearchField('puuid', [new ReferenceSearchValue($puuid, 'Patient', true)]);
        }
        return $this->search($search);
    }

    public function search($search, $isAndCondition = true)
    {
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
                IF(drug_prescriptions.rxnorm_drugcode!=''
                        ,drug_prescriptions.rxnorm_drugcode
                        ,IF(drug_code IS NULL, '', concat('RXCUI:',drug_code))
                ) AS 'rxnorm_drugcode',
                drug_inventory.manufacturer,
                drug_inventory.lot_number,
                drug_inventory.expiration
                FROM drugs
                LEFT JOIN drug_inventory
                    ON drugs.drug_id = drug_inventory.drug_id
                LEFT JOIN (
                    select 
                        uuid AS prescription_uuid
                        ,rxnorm_drugcode
                        ,drug_id
                        ,patient_id as prescription_patient_id
                    FROM
                    prescriptions
                ) drug_prescriptions
                    ON drug_prescriptions.drug_id = drugs.drug_id
                LEFT JOIN (
                    select uuid AS puuid
                    ,pid
                    FROM patient_data
                ) patient
                ON patient.pid = drug_prescriptions.prescription_patient_id";

        $processingResult = new ProcessingResult();
        try {
            $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

            $sql .= $whereClause->getFragment();
            $sqlBindArray = $whereClause->getBoundValues();
            $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

            while ($row = sqlFetchArray($statementResults)) {
                $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                $processingResult->addData($resultRecord);
            }
        } catch (SqlQueryException $exception) {
            // we shouldn't hit a query exception
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $processingResult->addInternalError("Error selecting data from database");
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'field' => $exception->getField()]);
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);

        if ($record['rxnorm_drugcode'] != "") {
            $codes = $this->addCoding($row['rxnorm_drugcode']);
            $updatedCodes = [];
            foreach ($codes as $code => $codeValues) {
                if (empty($codeValues['description'])) {
                    // use the drug name if for some reason we have no rxnorm description from the lookup
                    $codeValues['description'] = $row['drug'];
                }
                $updatedCodes[$code] = $codeValues;
            }
            $record['drug_code'] = $updatedCodes;
        }

        if ($row['rxnorm_drugcode'] != "") {
            $row['drug_code'] = $this->addCoding($row['drug_code']);
        }
        return $record;
    }
}
