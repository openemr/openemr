<?php

/**
 * InsuranceCompanyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Services\AddressService;
use OpenEMR\Validators\InsuranceValidator;

class InsuranceCompanyService extends BaseService
{
    private const INSURANCE_TABLE = "insurance_companies";
    private const ADDRESS_TABLE = "addresses";
    private $uuidRegistry;
    private $insuranceValidator;
    private $addressService = null;


    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->addressService = new AddressService();
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::INSURANCE_TABLE]);
        $this->uuidRegistry->createMissingUuids();
        $this->insuranceValidator = new InsuranceValidator();
        parent::__construct(self::INSURANCE_TABLE);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $sqlBindArray = array();
        $sql  = " SELECT i.id,";
        $sql .= "        i.uuid,";
        $sql .= "        i.name,";
        $sql .= "        i.attn,";
        $sql .= "        i.cms_id,";
        $sql .= "        i.ins_type_code,";
        $sql .= "        i.x12_receiver_id,";
        $sql .= "        i.x12_default_partner_id,";
        $sql .= "        i.alt_cms_id,";
        $sql .= "        i.inactive,";
        $sql .= "        a.line1,";
        $sql .= "        a.line2,";
        $sql .= "        a.city,";
        $sql .= "        a.state,";
        $sql .= "        a.zip,";
        $sql .= "        a.country";
        $sql .= " FROM insurance_companies i";
        $sql .= " JOIN addresses a ON i.id = a.foreign_id";
        $processingResult = new ProcessingResult();
        try {
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereFragment->getFragment();
            sqlStatementThrowException($sql, $whereFragment->getBoundValues());

            if (!empty($records)) {
                foreach ($records as $row) {
                    $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                    $processingResult->addData($resultRecord);
                }
            }
        } catch (SqlQueryException $exception) {
            // we shouldn't hit a query exception
            (new SystemLogger())->error($exception->getMessage(), $exception);
            $processingResult->addInternalError("Error selecting data from database");
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error($exception->getMessage(), $exception);
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }

    public function getAll($search = array(), $isAndCondition = true)
    {
        // Validating and Converting UUID to ID
        if (isset($search['id'])) {
            $isValidcondition = $this->insuranceValidator->validateId(
                'uuid',
                self::INSURANCE_TABLE,
                $search['id'],
                true
            );
            if ($isValidcondition !== true) {
                return $isValidcondition;
            }
            $uuidBytes = UuidRegistry::uuidToBytes($search['id']);
            $search['id'] = $this->getIdByUuid($uuidBytes, self::INSURANCE_TABLE, "id");
        }

        $sqlBindArray = array();
        $sql  = " SELECT i.id,";
        $sql .= "        i.uuid,";
        $sql .= "        i.name,";
        $sql .= "        i.attn,";
        $sql .= "        i.cms_id,";
        $sql .= "        i.ins_type_code,";
        $sql .= "        i.x12_receiver_id,";
        $sql .= "        i.x12_default_partner_id,";
        $sql .= "        i.alt_cms_id,";
        $sql .= "        i.inactive,";
        $sql .= "        a.line1,";
        $sql .= "        a.line2,";
        $sql .= "        a.city,";
        $sql .= "        a.state,";
        $sql .= "        a.zip,";
        $sql .= "        a.country";
        $sql .= " FROM insurance_companies i";
        $sql .= " JOIN addresses a ON i.id = a.foreign_id";

        if (!empty($search)) {
            $sql .= ' AND ';
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
            $row = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($row);
        }
        return $processingResult;
    }

    public function getOneById($id)
    {
        // TODO: this should be refactored to use getAll but its selecting all the columns and for backwards
        // compatibility we will live this here.
        $sql = "SELECT * FROM insurance_companies WHERE id=?";
        return sqlQuery($sql, array($id));
    }

    public function getOne($uuid)
    {
        return $this->getAll(['uuid' => $uuid]);
    }

    public function getInsuranceTypes()
    {
        return array(
            1 => xl('Other HCFA'),
            2 => xl('Medicare Part B'),
            3 => xl('Medicaid'),
            4 => xl('ChampUSVA'),
            5 => xl('ChampUS'),
            6 => xl('Blue Cross Blue Shield'),
            7 => xl('FECA'),
            8 => xl('Self Pay'),
            9 => xl('Central Certification'),
            10 => xl('Other Non-Federal Programs'),
            11 => xl('Preferred Provider Organization (PPO)'),
            12 => xl('Point of Service (POS)'),
            13 => xl('Exclusive Provider Organization (EPO)'),
            14 => xl('Indemnity Insurance'),
            15 => xl('Health Maintenance Organization (HMO) Medicare Risk'),
            16 => xl('Automobile Medical'),
            17 => xl('Commercial Insurance Co.'),
            18 => xl('Disability'),
            19 => xl('Health Maintenance Organization'),
            20 => xl('Liability'),
            21 => xl('Liability Medical'),
            22 => xl('Other Federal Program'),
            23 => xl('Title V'),
            24 => xl('Veterans Administration Plan'),
            25 => xl('Workers Compensation Health Plan'),
            26 => xl('Mutually Defined')
        );
    }


    public function insert($data)
    {
        $freshId = $this->getFreshId("id", "insurance_companies");

        $sql  = " INSERT INTO insurance_companies SET";
        $sql .= "     id=?,";
        $sql .= "     name=?,";
        $sql .= "     attn=?,";
        $sql .= "     cms_id=?,";
        $sql .= "     ins_type_code=?,";
        $sql .= "     x12_receiver_id=?,";
        $sql .= "     x12_default_partner_id=?,";
        $sql .= "     alt_cms_id=?";

        $insuranceResults = sqlInsert(
            $sql,
            array(
                $freshId,
                $data["name"],
                $data["attn"],
                $data["cms_id"],
                $data["ins_type_code"],
                $data["x12_receiver_id"],
                $data["x12_default_partner_id"],
                $data["alt_cms_id"]
            )
        );

        if (!$insuranceResults) {
            return false;
        }

        $addressesResults = $this->addressService->insert($data, $freshId);

        if (!$addressesResults) {
            return false;
        }

        return $freshId;
    }

    public function update($data, $iid)
    {
        $sql  = " UPDATE insurance_companies SET";
        $sql .= "     name=?,";
        $sql .= "     attn=?,";
        $sql .= "     cms_id=?,";
        $sql .= "     ins_type_code=?,";
        $sql .= "     x12_receiver_id=?,";
        $sql .= "     x12_default_partner_id=?,";
        $sql .= "     alt_cms_id=?";
        $sql .= "     WHERE id = ?";

        $insuranceResults = sqlStatement(
            $sql,
            array(
                $data["name"],
                $data["attn"],
                $data["cms_id"],
                $data["ins_type_code"],
                $data["x12_receiver_id"],
                $data["x12_default_partner_id"],
                $data["alt_cms_id"],
                $iid
            )
        );

        if (!$insuranceResults) {
            return false;
        }

        $addressesResults = $this->addressService->update($data, $iid);

        if (!$addressesResults) {
            return false;
        }

        return $iid;
    }
}
