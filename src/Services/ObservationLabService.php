<?php

/**
 * ObservationLabService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Ramsey\Uuid\UuidFactory;

class ObservationLabService extends BaseService
{
    private const PROCEDURE_RESULT_TABLE = "procedure_result";
    private const PATIENT_TABLE = "patient_data";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROCEDURE_RESULT_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PROCEDURE_RESULT_TABLE, self::PATIENT_TABLE]);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    private function getSampleLaboratoryResults()
    {
        $factory = new UuidFactory();
        $uuid = $factory->uuid4()->toString();
        $processingResult = new ProcessingResult();
        $data = [
            "uuid" => $uuid
            ,"date_report" => date("Y-m-d H:i:s")
            ,"puuid" => $factory->uuid4()->toString()
            ,"result_status" => "final"
            ,"procedure_code" => "24357-6"
            ,"procedure_name" => "Urinanalysis macro (dipstick) panel"
            ,"units" => "[pH]"
            ,"range" => "5.0-8.0"
            ,"result" => "5.0"
            ,"comments" => "Test comments"
        ];
        $processingResult->addData($data);
        return $processingResult;
    }

    public function isValidProcedureResultCode($code)
    {
        $sql = "SELECT result_code FROM procedure_result WHERE result_code = ? LIMIT 1";
        $code = QueryUtils::fetchSingleValue($sql, 'result_code', [$code]);
        return !empty($code);
    }

    public function isValidProcedureCode($code)
    {
        $sql = "SELECT procedure_code FROM procedure_order_code WHERE procedure_code = ? LIMIT 1";
        $code = QueryUtils::fetchSingleValue($sql, 'procedure_code', [$code]);
        return !empty($code);
    }

    public function search($search, $isAndCondition = true)
    {
        // note that these are Laboratory tests & values/results as mapped in USCDI Data elements v1
        // @see https://www.healthit.gov/isa/sites/isa/files/2020-07/USCDI-Version-1-July-2020-Errata-Final.pdf
        // To see the mappings you can see here: https://www.hl7.org/fhir/us/core/general-guidance.html
        $sql = "SELECT
                    presult.procedure_result_id
                    ,presult.uuid
                    ,presult.result_code
                    ,presult.result_text
                    ,presult.units
                    ,presult.result
                    ,presult.range
                    ,presult.abnormal
                    ,presult.comments
                    ,presult.document_id
                    ,presult.result_status
                    ,order_codes.procedure_name
                    ,order_codes.procedure_code ,
                    patients.puuid
                    ,preport.date_report
                FROM
                    procedure_result AS presult
                    -- we mix and match quantities with string values and in order to handle complex searches we break
                    -- them apart so we can apply different operators to each
                JOIN (
                    SELECT
                        uuid
                        ,result AS result_quantity
                        ,result AS result_string
                    FROM
                        procedure_result
                ) typed_procedure_result ON presult.uuid = typed_procedure_result.uuid
                LEFT JOIN
                    procedure_report AS preport
                ON
                    preport.procedure_report_id = presult.procedure_report_id
                LEFT JOIN
                    procedure_order AS porder
                ON
                    porder.procedure_order_id = preport.procedure_order_id
                LEFT JOIN
                    procedure_order_code AS order_codes
                ON
                    order_codes.procedure_order_id = porder.procedure_order_id
                LEFT JOIN (
                    select
                        pid
                        ,uuid AS puuid
                    FROM
                        patient_data
                 ) patients
                ON
                    patients.pid = porder.patient_id ";

        $excludeDNR_TNP = new StringSearchField('result_string', ['DNR','TNP'], SearchModifier::NOT_EQUALS_EXACT, true);
        if (isset($search['result_string']) && $search['result_string'] instanceof ISearchField) {
            $compoundColumn = new CompositeSearchField('result_string', [], true);
            $compoundColumn->addChild($search['result_string']);
            $compoundColumn->addChild($excludeDNR_TNP);
            $search['result_string'] = $compoundColumn;
        } else {
            $search['result_string'] = $excludeDNR_TNP;
        }

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    public function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);
        if (!empty($record['range'])) {
            $highlow = preg_split("/[\s,-\--]+/", $record['range']);
            $low = $highlow[0];
            $high = $highlow[1];
            $record['range_low'] = $low;
            $record['range_high'] = $high;
        }
        return $record;
    }

    /**
     * Returns a list of observation-lab matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
        $searchArgs = [];
        if (isset($puuidBind)) {
            $searchArgs['puuid'] = new TokenSearchField('uuid', [$puuidBind], true);
        }

        if (!empty($search)) {
            // we want to be backwards compatible for now... so we set everything to be a string search with exact
            // modifier
            foreach ($search as $fieldName => $fieldValue) {
                $searchArgs[] = new StringSearchField($fieldName, $fieldValue, SearchModifier::EXACT);
            }
        }

        return $this->search($searchArgs);
    }

    /**
     * Returns a single observation-lab record by id.
     * @param $uuid - The observation-lab uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $search = [
            'uuid' => new TokenSearchField('uuid', [$uuid], true)
        ];
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('uuid', [$puuidBind], true);
        }
        return $this->search($search);
    }
}
