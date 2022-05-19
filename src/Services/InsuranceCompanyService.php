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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Validators\InsuranceCompanyValidator;
use OpenEMR\Validators\ProcessingResult;

class InsuranceCompanyService extends BaseService
{
    private const INSURANCE_TABLE = "insurance_companies";
    private $insuranceCompanyValidator;
    private $addressService = null;
    public const TYPE_FAX = 5;
    public const TYPE_WORK = 2;


    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->addressService = new AddressService();
        UuidRegistry::createMissingUuidsForTables([self::INSURANCE_TABLE]);
        $this->insuranceCompanyValidator = new InsuranceCompanyValidator();
        parent::__construct(self::INSURANCE_TABLE);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = " SELECT i.id,";
        $sql .= "        i.uuid,";
        $sql .= "        i.name,";
        $sql .= "        i.attn,";
        $sql .= "        i.cms_id,";
        $sql .= "        i.ins_type_code,";
        $sql .= "        i.x12_receiver_id,";
        $sql .= "        i.x12_default_partner_id,";
        $sql .= "        i.alt_cms_id,";
        $sql .= "        i.inactive,work_number.id as work_id,fax_number.id AS fax_id,";
        $sql .= "        CONCAT(
                            COALESCE(work_number.country_code,'')
                            ,COALESCE(work_number.area_code,'')
                            ,COALESCE(work_number.prefix,'')
                            , work_number.number
                        ) AS work_number,";
        $sql .= "        CONCAT(
                            COALESCE(fax_number.country_code,'')
                            ,COALESCE(fax_number.area_code,'')
                            ,COALESCE(fax_number.prefix,'')
                            , fax_number.number
                        ) AS fax_number,";
        $sql .= "        a.line1,";
        $sql .= "        a.line2,";
        $sql .= "        a.city,";
        $sql .= "        a.state,";
        $sql .= "        a.zip,";
        $sql .= "        a.country";
        $sql .= " FROM insurance_companies i ";
        $sql .= " JOIN addresses a ON i.id = a.foreign_id";
        // the foreign_id here is a globally unique sequence so there is no conflict.
        // I don't like the assumption here as it should be more explicit what table we are pulling
        // from since OpenEMR mixes a bunch of paradigms.  I initially worried about data corruption as phone_numbers
        // foreign id could be ambigious here... but since the sequence is globally unique @see \generate_id() we can
        // join here safely...
        $sql .= " LEFT JOIN (
                        SELECT id,foreign_id,country_code, area_code, prefix, number
                        FROM phone_numbers WHERE number IS NOT NULL AND type = " . self::TYPE_WORK . "
                    ) work_number ON i.id = work_number.foreign_id";
        $sql .= " LEFT JOIN (
                        SELECT id,foreign_id,country_code, area_code, prefix, number
                        FROM phone_numbers WHERE number IS NOT NULL AND type = " . self::TYPE_FAX . "
                    ) fax_number ON i.id = fax_number.foreign_id";

        $processingResult = new ProcessingResult();
        try {
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereFragment->getFragment();
            $records = QueryUtils::fetchRecords($sql, $whereFragment->getBoundValues());

            if (!empty($records)) {
                foreach ($records as $row) {
                    $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                    $processingResult->addData($resultRecord);
                }
            }
        } catch (SqlQueryException $exception) {
            // we shouldn't hit a query exception
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $processingResult->addInternalError("Error selecting data from database");
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error(
                $exception->getMessage(),
                ['trace' => $exception->getTraceAsString(),
                 'field' => $exception->getField()]
            );
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }

    public function getAll($search = array(), $isAndCondition = true)
    {
        // Validating and Converting UUID to ID
        if (isset($search['id'])) {
            $isValidcondition = $this->insuranceCompanyValidator->validateId(
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
        $sql = " SELECT i.id,";
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

    public function getOne($uuid): ProcessingResult
    {
        return $this->getAll(['uuid' => $uuid]);
    }

    public function getInsuranceTypes()
    {
        $types = [];
        $type = sqlStatement("SELECT `type` FROM `insurance_type_codes`");
        $i = 0;
        while ($row = sqlFetchArray($type)) {
            $i++;
            $types[$i] = $row['type'];
        }
        return $types;
    }

    public function getInsuranceClaimTypes()
    {
        $claim_types = [];
        $claim_type = sqlStatement("SELECT `claim_type` FROM `insurance_type_codes`");
        $i = 0;
        while ($row = sqlFetchArray($claim_type)) {
            $i++;
            $claim_types[$i] = $row['claim_type'];
        }
        return $claim_types;
    }

    public function getInsuranceCqmSop()
    {
        $cqm_sop = sqlStatement(
            "SELECT distinct code, description FROM `valueset` WHERE `valueset` = '2.16.840.1.114222.4.11.3591';"
        );

        $cqm_sops = [];
        while ($row = sqlFetchArray($cqm_sop)) {
            $cqm_sops[$row['code']] = $row['description'];
        }
        return $cqm_sops;
    }

    public function insert($data)
    {
        // insurance companies need to use sequences table since they share the
        // addresses table with pharmacies
        $freshId = generate_id();

        $sql = " INSERT INTO insurance_companies SET";
        $sql .= "     id=?,";
        $sql .= "     name=?,";
        $sql .= "     attn=?,";
        $sql .= "     cms_id=?,";
        $sql .= "     ins_type_code=?,";
        $sql .= "     x12_receiver_id=?,";
        $sql .= "     x12_default_partner_id=?,";
        $sql .= "     alt_cms_id=?,";
        $sql .= "     cqm_sop=?";

        sqlInsert(
            $sql,
            array(
                $freshId,
                $data["name"],
                $data["attn"],
                $data["cms_id"],
                $data["ins_type_code"],
                $data["x12_receiver_id"],
                $data["x12_default_partner_id"] ?? '',
                $data["alt_cms_id"],
                $data["cqm_sop"] ?? null,
            )
        );

        if (!empty($data["city"] ?? null) && !empty($data["state"] ?? null)) {
            $this->addressService->insert($data, $freshId);
        }

        return $freshId;
    }

    public function update($data, $iid)
    {
        $sql = " UPDATE insurance_companies SET";
        $sql .= "     name=?,";
        $sql .= "     attn=?,";
        $sql .= "     cms_id=?,";
        $sql .= "     ins_type_code=?,";
        $sql .= "     x12_receiver_id=?,";
        $sql .= "     x12_default_partner_id=?,";
        $sql .= "     alt_cms_id=?,";
        $sql .= "     cqm_sop=?";
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
                $data["cqm_sop"],
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
