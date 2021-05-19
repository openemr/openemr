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

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
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

    public function getSelectJoinTables(): array
    {
        return
            [
                ['table' => 'list_options', 'alias' => 'abook', 'type' => 'LEFT JOIN', 'column' => 'abook_type', 'join_column' => 'option_id']
                ,['table' => 'list_options', 'alias' => 'physician', 'type' => 'LEFT JOIN', 'column' => 'physician_type', 'join_column' => 'option_id']
            ];
    }

    public function getSelectFields(): array
    {
        // since we are joining a bunch of fields we need to make sure we normalize our regular field array by adding
        // the table name for our own table values.
        $fields = $this->getFields();
        $normalizedFields = [];
        // processing is cheap
        foreach ($fields as $field) {
            $normalizedFields[] = '`' . $this->getTable() . '`.`' . $field . '`';
        }

        return array_merge($normalizedFields, ['abook.title as abook_title', 'physician.title as physician_title', 'physician.codes as physician_code']);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function selectHelper($sqlUpToFromStatement, $map)
    {
        // TODO: adunsulag we only are putting this in here until we can get the npi:missing modifier to work properly
        // and then we will remove this stuff.
        if (!empty($map['where'])) {
            $map['where'] .= " AND NPI IS NOT null";
        } else {
            $map['where'] = "WHERE npi IS NOT null";
        }
        return parent::selectHelper($sqlUpToFromStatement, $map);
    }

    public function search($search, $isAndCondition = true)
    {
        // we make sure we only get NPI values
        // TODO: adunsulag when we can get the Missing modifier working we can do that here...
//        $search['npi'] = new TokenSearchField('npi');
//        $search['npi']->setModifier(SearchModifier::MISSING);
        return parent::search($search, $isAndCondition);
    }

    /**
     * Returns a list of practitioners matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = ISearchField|primitive value
     *
     * If a primitive value is provided it will do an exact match on that field.  If an ISearchField is provided it will
     * use whatever modifiers, comparators, and composite search settings that are specified in the search field.
     *
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        if (!empty($search)) {
            $fields = $this->getFields();
            $validKeys = array_combine($fields, $fields);

            // We need to be backwards compatible with all other uses of the service so we are going to make this a
            // exact match string param on everything, but only if they are not sending in any Search Field options
            foreach ($search as $fieldName => $fieldValue) {
                if (isset($validKeys[$fieldName]) && !($fieldValue instanceof ISearchField)) {
                    $search[$fieldName] = new StringSearchField($fieldName, $fieldValue, SearchModifier::EXACT, $isAndCondition);
                }
            }
        }
        return $this->search($search, $isAndCondition);
    }

    public function getAllWithIds(array $ids)
    {
        $idField = new TokenSearchField('id', $ids);
        return $this->search(['id' => $idField]);
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

        // there should not be a single duplicate id so we will grab that
        $search = ['uuid' => new TokenSearchField('uuid', new TokenSearchValue(UuidRegistry::uuidToBytes($uuid)))];
        $results = $this->search($search);
        $data = $results->getData();
        if (count($data) > 1) {
            // we will log this error and return just the single value
            $results->setData([$data[0]]);
            (new SystemLogger())->error("PractionerService->getOne() Duplicate records found for uuid", ['uuid' => $uuid]);
        }
        return $results;
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
