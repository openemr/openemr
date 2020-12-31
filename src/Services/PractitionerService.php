<?php

/**
 * PractitionerService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\PractitionerValidator;
use OpenEMR\Validators\ProcessingResult;

class PractitionerService extends BaseService
{

    private const PRACTITIONER_TABLE = "users";
    private $practitionerValidator;
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('users');
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]);
        $this->uuidRegistry->createMissingUuids();
        $this->practitionerValidator = new PractitionerValidator();
    }

    /**
     * Returns a list of practitioners matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        $sqlBindArray = array();

        $sql = "SELECT  id,
                        uuid,
                        users.title as title,
                        fname,
                        lname,
                        mname,
                        federaltaxid,
                        federaldrugid,
                        upin,
                        facility_id,
                        facility,
                        npi,
                        email,
                        active,
                        specialty,
                        billname,
                        url,
                        assistant,
                        organization,
                        valedictory,
                        street,
                        streetb,
                        city,
                        state,
                        zip,
                        phone,
                        fax,
                        phonew1,
                        phonecell,
                        users.notes,
                        state_license_number,
                        abook.title as abook_title,
                        physician.title as physician_title,
                        physician.codes as physician_code
                FROM  users
                LEFT JOIN list_options as abook ON abook.option_id = users.abook_type
                LEFT JOIN list_options as physician ON physician.option_id = users.physician_type
                WHERE npi is not null";

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
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single practitioner record by id.
     * @param $uuid - The practitioner uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->practitionerValidator->validateId("uuid", "users", $uuid, true);

        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT  id,
                        uuid,
                        users.title as title,
                        fname,
                        lname,
                        mname,
                        federaltaxid,
                        federaldrugid,
                        upin,
                        facility_id,
                        facility,
                        npi,
                        email,
                        active,
                        specialty,
                        billname,
                        url,
                        assistant,
                        organization,
                        valedictory,
                        street,
                        streetb,
                        city,
                        state,
                        zip,
                        phone,
                        fax,
                        phonew1,
                        phonecell,
                        users.notes,
                        state_license_number,
                        abook.title as abook_title,
                        physician.title as physician_title,
                        physician.codes as physician_code
                FROM  users
                LEFT JOIN list_options as abook ON abook.option_id = users.abook_type
                LEFT JOIN list_options as physician ON physician.option_id = users.physician_type
                WHERE uuid = ? AND npi is not null";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);

        $sqlResult['uuid'] = $uuid;
        $processingResult->addData($sqlResult);
        return $processingResult;
    }


    /**
     * Inserts a new practitioner record.
     *
     * @param $data The practitioner fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        $processingResult = $this->practitionerValidator->validate(
            $data,
            PractitionerValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $data['uuid'] = (new UuidRegistry(['table_name' => 'users']))->createUuid();

        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO users SET ";
        $sql .= $query['set'];

        $results = sqlInsert(
            $sql,
            $query['bind']
        );

        if ($results) {
            $processingResult->addData(array(
                'id' => $results,
                'uuid' => UuidRegistry::uuidToString($data['uuid'])
            ));
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }


    /**
     * Updates an existing practitioner record.
     *
     * @param $uuid - The practitioner uuid identifier in string format used for update.
     * @param $data - The updated practitioner data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function update($uuid, $data)
    {
        if (empty($data)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages("Invalid Data");
            return $processingResult;
        }
        $data["uuid"] = $uuid;
        $processingResult = $this->practitionerValidator->validate(
            $data,
            PractitionerValidator::DATABASE_UPDATE_CONTEXT
        );
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE users SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `uuid` = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        array_push($query['bind'], $uuidBinary);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if (!$sqlResult) {
            $processingResult->addErrorMessage("error processing SQL Update");
        } else {
            $processingResult = $this->getOne($uuid);
        }
        return $processingResult;
    }
}
