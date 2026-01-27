<?php

/**
 * InsuranceCompanyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\{
    AddressService,
    PhoneNumberService,
    Search\FhirSearchWhereClauseBuilder,
    Search\SearchFieldException
};
use OpenEMR\Validators\InsuranceCompanyValidator;
use OpenEMR\Validators\ProcessingResult;

class InsuranceCompanyService extends BaseService
{
    private const INSURANCE_TABLE = "insurance_companies";
    private $insuranceCompanyValidator;
    private $addressService = null;
    private $phoneNumberService = null;

    /**
     * @var null | array $cqm_sops cached CQM SOPS
     */
    private $cqm_sops = null;

    /**
     * @var null | array $types cached insurance types
     */
    private $types = null;

    /**
     * @var null | array $claim_types cached claim types
     */
    private $claim_types = null;


    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->addressService = new AddressService();
        $this->phoneNumberService = new PhoneNumberService();
        UuidRegistry::createMissingUuidsForTables([self::INSURANCE_TABLE]);
        $this->insuranceCompanyValidator = new InsuranceCompanyValidator();
        parent::__construct(self::INSURANCE_TABLE);
    }

    public function getInsuranceDisplayName($insuranceId)
    {
        $searchResults = $this->search(['id' => $insuranceId]);
        $insuranceCompany = null;
        if ($searchResults->hasData()) {
            $insuranceCompany = $searchResults->getData()[0];
        }
        if ($insuranceCompany !== null) {
            return self::getDisplayNameForInsuranceRecord($insuranceCompany);
        }
        return "";
    }
    public static function getDisplayNameForInsuranceRecord($insuranceCompany)
    {
        switch ($GLOBALS['insurance_information']) {
            case '1':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ")";
                break;
            case '2':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ", " . $insuranceCompany['zip'] . ")";
                break;
            case '3':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ", " . $insuranceCompany['state'] . ")";
                break;
            case '4':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ", " . $insuranceCompany['state'] .
                    ", " . $insuranceCompany['zip'] . ")";
                break;
            case '5':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ", " . $insuranceCompany['city'] .
                    ", " . $insuranceCompany['state'] . ", " . $insuranceCompany['zip'] . ")";
                break;
            case '6':
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['line1'] . ", " . $insuranceCompany['line2'] . ", " . $insuranceCompany['city'] .
                    ", " . $insuranceCompany['state'] . ", " . $insuranceCompany['zip'] . ", " . $insuranceCompany['cms_id'] . ")";
                break;
            case '7':
                preg_match("/\d+/", (string) $insuranceCompany['line1'], $matches);
                $returnval = $insuranceCompany['name'] . " (" . $insuranceCompany['zip'] .
                    "," . $matches[0] . ")";
                break;
            case '0':
            default:
                $returnval = $insuranceCompany['name'];
                break;
        }
        return $returnval;
    }
    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        // the foreign_id here is a globally unique sequence so there is no conflict.
        // I don't like the assumption here as it should be more explicit what table we are pulling
        // from since OpenEMR mixes a bunch of paradigms.  I initially worried about data corruption as phone_numbers
        // foreign id could be ambiguous here... but since the sequence is globally unique @see \generate_id() we can
        // join here safely...
        $sql = <<<'SQL'
            SELECT i.id,
                i.uuid,
                i.name,
                i.attn,
                i.cms_id,
                i.ins_type_code,
                i.x12_receiver_id,
                i.x12_default_partner_id,
                x12.x12_default_partner_name,
                i.alt_cms_id,
                i.inactive,
                work_number.work_id,
                fax_number.fax_id,
                CONCAT(
                    COALESCE(work_number.country_code, ''),
                    COALESCE(work_number.area_code, ''),
                    COALESCE(work_number.prefix, ''),
                    work_number.number
                ) AS work_number,
                CONCAT(
                    COALESCE(fax_number.country_code, ''),
                    COALESCE(fax_number.area_code, ''),
                    COALESCE(fax_number.prefix, ''),
                    fax_number.number
                ) AS fax_number,
                a.line1,
                a.line2,
                a.city,
                a.state,
                a.zip,
                a.plus_four,
                a.country,
                i.date_created,
                i.last_updated
            FROM insurance_companies i
            LEFT JOIN (
                SELECT line1, line2, city, state, zip, plus_four, country, foreign_id
                FROM addresses
            ) a ON i.id = a.foreign_id
            LEFT JOIN (
                SELECT id AS work_id, foreign_id, country_code, area_code, prefix, number
                FROM phone_numbers
                WHERE number IS NOT NULL AND type = ?
            ) work_number ON i.id = work_number.foreign_id
            LEFT JOIN (
                SELECT id AS fax_id, foreign_id, country_code, area_code, prefix, number
                FROM phone_numbers
                WHERE number IS NOT NULL AND type = ?
            ) fax_number ON i.id = fax_number.foreign_id
            LEFT JOIN (
                SELECT id AS x12_default_partner_id, name AS x12_default_partner_name
                FROM x12_partners
            ) x12 ON i.x12_default_partner_id = x12.x12_default_partner_id
            SQL;
        $typeBindings = [PhoneType::WORK->value, PhoneType::FAX->value];

        $processingResult = new ProcessingResult();
        try {
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereFragment->getFragment();
            $boundValues = array_merge($typeBindings, $whereFragment->getBoundValues());
            $records = QueryUtils::fetchRecords($sql, $boundValues);

            foreach ($records as $row) {
                $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                $processingResult->addData($resultRecord);
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

    public function getAll($search = [], $isAndCondition = true)
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

        $sqlBindArray = [];
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
        $sql .= " LEFT JOIN addresses a ON i.id = a.foreign_id";

        if ($search !== []) {
            $sql .= ' AND ';
            $whereClauses = [];
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
        // compatibility we will leave this here.
        $sql = "SELECT * FROM insurance_companies WHERE id=?";
        return sqlQuery($sql, [$id]);
    }

    public function getOne($uuid): ProcessingResult
    {
        return $this->getAll(['uuid' => $uuid]);
    }


    public function getInsuranceTypesCached()
    {
        if ($this->types !== null) {
            return $this->types;
        }
        $this->types = $this->getInsuranceTypes();
        return $this->types;
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

    public function getInsuranceClaimTypesCached()
    {
        if ($this->claim_types !== null) {
            return $this->claim_types;
        }
        $this->claim_types = $this->getInsuranceClaimTypes();
        return $this->claim_types;
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

    public function getInsuranceCqmSopCached()
    {
        if ($this->cqm_sops !== null) {
            return $this->cqm_sops;
        }
        $this->cqm_sops = $this->getInsuranceCqmSop();
        return $this->cqm_sops;
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
        // I don't like actually inserting a raw id... yet if we don't allow for this
        // it makes it very hard for any kind of data import that needs to maintain the same id.
        if (!isset($data["id"]) || $data["id"] === '') {
            $data["id"] = QueryUtils::generateId();
        }
        $freshId = $data['id'];

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

        // throws an exception if the record doesn't insert
        QueryUtils::sqlInsert(
            $sql,
            [
                $freshId,
                $data["name"],
                $data["attn"],
                $data["cms_id"],
                $data["ins_type_code"],
                $data["x12_receiver_id"],
                $data["x12_default_partner_id"] ?? '',
                $data["alt_cms_id"],
                $data["cqm_sop"] ?? null,
            ]
        );

        if (($data["city"] ?? '') !== '' && ($data["state"] ?? '') !== '') {
            $this->addressService->insert($data, $freshId);
        }

        if (($data["phone"] ?? '') !== '') {
            $this->phoneNumberService->insert($data, $freshId);
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
            [
                $data["name"],
                $data["attn"],
                $data["cms_id"],
                $data["ins_type_code"],
                $data["x12_receiver_id"],
                $data["x12_default_partner_id"] ?? null,
                $data["alt_cms_id"],
                $data["cqm_sop"] ?? null,
                $iid
            ]
        );

        if (!$insuranceResults) {
            return false;
        }

        $addressesResults = $this->addressService->update($data, $iid);

        if (!$addressesResults) {
            return false;
        }

        // no point in updating the phone if there is no phone record...
        if (($data['phone'] ?? '') !== '') {
            $phoneNumberResults = $this->phoneNumberService->update($data, $iid);

            if (!$phoneNumberResults) {
                return false;
            }
        }

        return $iid;
    }

    /**
     * Return an array of insurance companies with the same payer id
     *
     * @param  $cms_id  Insurance company payer id (assigned by clearinghouses)
     * @return Array Insurance company data payload.
     */
    public function getAllByPayerID($cms_id)
    {
        $insuranceCompanyResult = $this->search(['cms_id' => $cms_id]);
        if ($insuranceCompanyResult->hasData()) {
            $result = $insuranceCompanyResult->getData();
        }
        return $result;
    }
}
