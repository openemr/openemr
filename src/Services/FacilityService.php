<?php

/**
 * FacilityService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\FacilityValidator;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Events\Facility\FacilityCreatedEvent;
use OpenEMR\Events\Facility\FacilityUpdatedEvent;
use Particle\Validator\Validator;

class FacilityService extends BaseService
{
    private $facilityValidator;
    private const FACILITY_TABLE = "facility";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::FACILITY_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::FACILITY_TABLE]);
        $this->facilityValidator = new FacilityValidator();
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function validate($facility)
    {
        $validator = new Validator();

        $validator->required('name')->lengthBetween(2, 255);
        $validator->required('phone')->lengthBetween(3, 30);
        $validator->required('city')->lengthBetween(2, 255);
        $validator->required('state')->lengthBetween(2, 50);
        $validator->required('street')->lengthBetween(2, 255);
        $validator->required('postal_code')->lengthBetween(2, 11);
        $validator->required('email')->email();
        $validator->required('fax')->lengthBetween(3, 30);
        $validator->optional('country_code')->lengthBetween(2, 30);
        $validator->optional('federal_ein')->lengthBetween(2, 15);
        $validator->optional('website')->url();
        $validator->optional('color')->lengthBetween(4, 7);
        $validator->optional('service_location')->numeric();
        $validator->optional('billing_location')->numeric();
        $validator->optional('accepts_assignment')->numeric();
        $validator->optional('pos_code')->numeric();
        $validator->optional('domain_identifier')->lengthBetween(2, 60);
        $validator->optional('attn')->lengthBetween(2, 65);
        $validator->optional('tax_id_type')->lengthBetween(2, 31);
        $validator->optional('primary_business_entity')->numeric();
        $validator->optional('facility_npi')->lengthBetween(2, 15);
        $validator->optional('facility_code')->lengthBetween(2, 31);
        $validator->optional('facility_taxonomy')->lengthBetween(2, 15);
        $validator->optional('iban')->lengthBetween(2, 34);

        return $validator->validate($facility);
    }

    public function getAllFacility()
    {
        return $this->get(array("order" => "ORDER BY FAC.name ASC"));
    }

    public function getPrimaryBusinessEntity($options = null)
    {
        if (!empty($options) && !empty($options["useLegacyImplementation"])) {
            return $this->getPrimaryBusinessEntityLegacy();
        }
        $searchArgs = ['primary_business_entity' => new StringSearchField('primary_business_entity', [1], SearchModifier::EXACT)];

        if (!empty($options) && !empty($options["excludedId"])) {
            $searchArgs['id'] = new TokenSearchField('id', $options['excludedId']);
            $searchArgs['id']->setModifier(SearchModifier::NOT_EQUALS_EXACT);
        }

        $results = $this->search($searchArgs);
        if (!empty($results->getData())) {
            $pbe_results = $results->getData();
            return array_pop($pbe_results);
        }
        return null;
    }

    public function getAllServiceLocations($options = null)
    {
        $args = array(
            "where" => null,
            "order" => "ORDER BY FAC.name ASC"
        );

        if (!empty($options) && !empty($options["orderField"])) {
            $args["order"] = "ORDER BY FAC." . escape_sql_column_name($options["orderField"], array("facility")) . " ASC";
        }

        $args["where"] = "WHERE FAC.service_location = 1";

        return $this->get($args);
    }

    public function getPrimaryBillingLocation()
    {
        $record = $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.id DESC",
            "limit" => 1
        ));
        return $record;
    }

    public function getAllBillingLocations()
    {
        return $this->get(array(
            "where" => "WHERE FAC.billing_location = 1",
            "order" => "ORDER BY FAC.id ASC"
        ));
    }

    public function getById($id)
    {
        if (empty($id)) {
            // Not okay to throw exception here. Most UI are pulldowns which init to empty.
            return false;
        }
        $result = $this->search(['id' => new TokenSearchField('id', $id, false)]);
        if (!empty($result->getData())) {
            $facility_result = $result->getData();
            $facility = array_pop($facility_result);
            return $facility;
        }
        return null;
    }

    public function getFacilityForUser($userId)
    {
        $record = $this->get(array(
            "where" => "WHERE USER.id = ?",
            "data" => array($userId),
            "join" => "JOIN users USER ON FAC.id = USER.facility_id",
            "limit" => 1
        ));
        return $record;
    }

    public function getFacilityForUserFormatted($userId)
    {
        $facility = $this->getFacilityForUser($userId);

        if (!empty($facility)) {
            $formatted = "";
            $formatted .= $facility["name"];
            $formatted .= "\n";
            $formatted .= $facility["street"];
            $formatted .= "\n";
            $formatted .= $facility["city"];
            $formatted .= "\n";
            $formatted .= $facility["state"];
            $formatted .= "\n";
            $formatted .= $facility["postal_code"];

            return array("facility_address" => $formatted);
        }

        return array("facility_address" => "");
    }

    public function getFacilityForEncounter($encounterId)
    {
        $record = $this->get(array(
            "where" => "WHERE ENC.encounter = ?",
            "data" => array($encounterId),
            "join" => "JOIN form_encounter ENC ON FAC.id = ENC.facility_id",
            "limit" => 1
        ));
        return $record;
    }

    public function updateFacility($data)
    {
        $dataBeforeUpdate = $this->getById($data['id']);
        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE facility SET ";
        $sql .= $query['set'];
        $sql .= " WHERE id = ?";
        array_push($query['bind'], $data['id']);
        $result = sqlStatement(
            $sql,
            $query['bind']
        );

        $facilityUpdatedEvent = new FacilityUpdatedEvent($dataBeforeUpdate, $data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch(FacilityUpdatedEvent::EVENT_HANDLE, $facilityUpdatedEvent, 10);

        return $result;
    }

    public function insertFacility($data)
    {
        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO facility SET ";
        $sql .= $query['set'];
        $facilityId = sqlInsert(
            $sql,
            $query['bind']
        );

        $facilityCreatedEvent = new FacilityCreatedEvent(array_merge($data, ['id' => $facilityId]));
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch(FacilityCreatedEvent::EVENT_HANDLE, $facilityCreatedEvent, 10);

        return $facilityId;
    }

    public function updateUsersFacility($facility_name, $facility_id)
    {
        $sql = " UPDATE users SET";
        $sql .= " facility=?";
        $sql .= " WHERE facility_id=?";

        return sqlStatement($sql, array($facility_name, $facility_id));
    }

    /**
     * Shared getter for the various specific facility getters.
     * NOTE: if a limit of 1 is specified the associative array is returned
     *
     * @param $map - Query information.
     * @return array of associative arrays | one associative array.
     */
    private function get($map)
    {
        try {
            $sql = " SELECT FAC.id,";
            $sql .= "        FAC.uuid,";
            $sql .= "        FAC.name,";
            $sql .= "        FAC.phone,";
            $sql .= "        FAC.fax,";
            $sql .= "        FAC.street,";
            $sql .= "        FAC.city,";
            $sql .= "        FAC.state,";
            $sql .= "        FAC.postal_code,";
            $sql .= "        FAC.country_code,";
            $sql .= "        FAC.federal_ein,";
            $sql .= "        FAC.website,";
            $sql .= "        FAC.email,";
            $sql .= "        FAC.service_location,";
            $sql .= "        FAC.billing_location,";
            $sql .= "        FAC.accepts_assignment,";
            $sql .= "        FAC.pos_code,";
            $sql .= "        FAC.x12_sender_id,";
            $sql .= "        FAC.attn,";
            $sql .= "        FAC.domain_identifier,";
            $sql .= "        FAC.facility_npi,";
            $sql .= "        FAC.facility_taxonomy,";
            $sql .= "        FAC.tax_id_type,";
            $sql .= "        FAC.color,";
            $sql .= "        FAC.primary_business_entity,";
            $sql .= "        FAC.facility_code,";
            $sql .= "        FAC.extra_validation,";
            $sql .= "        FAC.mail_street,";
            $sql .= "        FAC.mail_street2,";
            $sql .= "        FAC.mail_city,";
            $sql .= "        FAC.mail_state,";
            $sql .= "        FAC.mail_zip,";
            $sql .= "        FAC.oid,";
            $sql .= "        FAC.iban,";
            $sql .= "        FAC.info";
            $sql .= " FROM facility FAC";

            $records = self::selectHelper($sql, $map);
            $returnRecords = [];
            if (!empty($records)) {
                // base service method returns just the associative array which messes with our methods for LIMIT etc.
                if (!empty($map['limit']) && $map['limit'] == 1) {
                    $returnRecords = $this->createResultRecordFromDatabaseResult($records);
                } else {
                    foreach ($records as $record) {
                        $returnRecords[] = $this->createResultRecordFromDatabaseResult($record);
                    }
                }
            }
            return $returnRecords;
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            throw $exception;
        }
    }

    private function getPrimaryBusinessEntityLegacy()
    {
        $record = $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.accepts_assignment DESC, FAC.id ASC",
            "limit" => 1
        ));
        return $record;
    }

    public function getAllWithIds(array $ids)
    {
        $idField = new TokenSearchField('id', $ids);
        return $this->search(['id' => $idField]);
    }

    /**
     * Returns a list of facilities matching optional search criteria.
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
        $querySearch = [];
        if (!empty($search)) {
            if (isset($search['uuid'])) {
                $querySearch['uuid'] = new TokenSearchField('uuid', $search['uuid']);
                unset($search['uuid']);
            }
            foreach ($search as $field => $value) {
                if (isset($search[$field])) {
                    $querySearch[$field] = new StringSearchField($field, $search[$field], SearchModifier::EXACT, $isAndCondition);
                }
            }
        }
        return $this->search($querySearch, $isAndCondition);
    }

    /**
     * Returns a single facility record by facility uuid.
     * @param $uuid - The facility uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $isValid = $this->facilityValidator->validateId('uuid', self::FACILITY_TABLE, $uuid, true);
        if ($isValid !== true) {
            return $isValid;
        }
        return $this->search(['uuid' => new TokenSearchField('uuid', $uuid, true)]);
    }

    /**
     * Inserts a new facility record.
     *
     * @param $data The facility fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        $processingResult = $this->facilityValidator->validate(
            $data,
            FacilityValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $data['uuid'] = (new UuidRegistry(['table_name' => self::FACILITY_TABLE]))->createUuid();

        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO " . self::FACILITY_TABLE . " SET ";
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
     * Updates an existing facility record.
     *
     * @param $uuid - The facility uuid identifier in string format used for update.
     * @param $data - The updated facility data fields
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
        $processingResult = $this->facilityValidator->validate(
            $data,
            FacilityValidator::DATABASE_UPDATE_CONTEXT
        );
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE " . self::FACILITY_TABLE . " SET ";
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
