<?php

/**
 * FhirSpecimenService.php
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRSpecimen;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimen\FHIRSpecimenCollection;
use OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimen\FHIRSpecimenContainer;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirSpecimenService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;

    const USCDI_PROFILE = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-specimen';

    /**
     * @var ProcedureService
     */
    private ProcedureService $procedureService;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->procedureService = new ProcedureService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'identifier' => new FhirSearchParameterDefinition('identifier', SearchFieldType::TOKEN, ['specimen_identifier']),
            'accession' => new FhirSearchParameterDefinition('accession', SearchFieldType::TOKEN, ['accession_identifier']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['specimen_type_code']),
            'collected' => new FhirSearchParameterDefinition('collected', SearchFieldType::DATETIME, ['collected_date']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['deleted']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date_updated'])
        ];
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Extract custom parameters
            $includeDeleted = false;
            if (isset($openEMRSearchParameters['_include_deleted'])) {
                $includeDeleted = filter_var(
                    $openEMRSearchParameters['_include_deleted'],
                    FILTER_VALIDATE_BOOLEAN
                );
                unset($openEMRSearchParameters['_include_deleted']);
            }

            // Handle status search
            $requestedStatus = null;
            if (isset($openEMRSearchParameters['status'])) {
                $requestedStatus = $openEMRSearchParameters['status'];
                unset($openEMRSearchParameters['status']);
            }

            // Get specimens from procedure service
            $specimens = $this->searchSpecimens($openEMRSearchParameters);

            foreach ($specimens as $specimen) {
                // Filter deleted specimens unless explicitly requested
                $isDeleted = !empty($specimen['deleted']) && $specimen['deleted'] == '1';

                if ($isDeleted && !$includeDeleted) {
                    // Skip deleted specimens by default
                    continue;
                }

                // Filter by requested status if specified
                if ($requestedStatus !== null) {
                    $fhirStatus = $this->mapDeletedToFhirStatus($specimen['deleted'] ?? '0');
                    if (!$this->matchesRequestedStatus($fhirStatus, $requestedStatus)) {
                        continue;
                    }
                }

                $processingResult->addData($specimen);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    /**
     * Search for specimens across all procedure orders
     *
     * @param array $searchParams
     * @return array
     */
    private function searchSpecimens(array $searchParams): array
    {
        $sql = "SELECT 
                ps.procedure_specimen_id,
                ps.uuid,
                ps.procedure_order_id,
                ps.procedure_order_seq,
                ps.specimen_identifier,
                ps.accession_identifier,
                ps.specimen_type_code,
                ps.specimen_type,
                ps.collection_method_code,
                ps.collection_method,
                ps.specimen_location_code,
                ps.specimen_location,
                ps.collected_date,
                ps.collection_date_low,
                ps.collection_date_high,
                ps.volume_value,
                ps.volume_unit,
                ps.condition_code,
                ps.specimen_condition,
                ps.comments,
                ps.deleted,
                ps.date_created,
                ps.date_updated,
                po.patient_id,
                p.uuid AS patient_uuid
            FROM procedure_specimen ps
            INNER JOIN procedure_order po ON po.procedure_order_id = ps.procedure_order_id
            INNER JOIN patient_data p ON p.pid = po.patient_id
            WHERE 1=1";

        $bindings = [];

        // Apply search filters
        if (isset($searchParams['puuid'])) {
            $sql .= " AND p.uuid = ?";
            $bindings[] = $searchParams['puuid'];
        }

        if (isset($searchParams['specimen_identifier'])) {
            $sql .= " AND ps.specimen_identifier = ?";
            $bindings[] = $searchParams['specimen_identifier'];
        }

        if (isset($searchParams['accession_identifier'])) {
            $sql .= " AND ps.accession_identifier = ?";
            $bindings[] = $searchParams['accession_identifier'];
        }

        if (isset($searchParams['specimen_type_code'])) {
            $sql .= " AND ps.specimen_type_code = ?";
            $bindings[] = $searchParams['specimen_type_code'];
        }

        if (isset($searchParams['uuid'])) {
            $sql .= " AND ps.uuid = ?";
            $bindings[] = $searchParams['uuid'];
        }

        $sql .= " ORDER BY ps.collected_date DESC, ps.procedure_specimen_id DESC";

        $result = sqlStatement($sql, $bindings);
        $specimens = [];

        while ($row = sqlFetchArray($result)) {
            $specimens[] = $this->createSpecimenRecordFromDatabaseResult($row);
        }

        return $specimens;
    }

    /**
     * Create specimen record from database result
     *
     * @param array $row
     * @return array
     */
    private function createSpecimenRecordFromDatabaseResult(array $row): array
    {
        return [
            'id' => $row['procedure_specimen_id'],
            'uuid' => \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($row['uuid']),
            'procedure_order_id' => $row['procedure_order_id'],
            'procedure_order_seq' => $row['procedure_order_seq'],
            'identifier' => $row['specimen_identifier'],
            'accession' => $row['accession_identifier'],
            'type_code' => $row['specimen_type_code'],
            'type' => $row['specimen_type'],
            'method_code' => $row['collection_method_code'],
            'method' => $row['collection_method'],
            'location_code' => $row['specimen_location_code'],
            'location' => $row['specimen_location'],
            'collected_date' => $row['collected_date'],
            'collection_start' => $row['collection_date_low'],
            'collection_end' => $row['collection_date_high'],
            'volume' => $row['volume_value'],
            'volume_unit' => $row['volume_unit'],
            'condition_code' => $row['condition_code'],
            'condition' => $row['specimen_condition'],
            'comments' => $row['comments'],
            'deleted' => $row['deleted'],
            'date_created' => $row['date_created'],
            'date_updated' => $row['date_updated'],
            'patient_id' => $row['patient_id'],
            'patient_uuid' => \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($row['patient_uuid'])
        ];
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string
     * @return FHIRSpecimen
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $specimen = new FHIRSpecimen();

        // Set metadata
        $meta = new FHIRMeta();
        $meta->setVersionId('1');

        if (!empty($dataRecord['date_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_updated']));
        } elseif (!empty($dataRecord['date_created'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date_created']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }

        $meta->addProfile(self::USCDI_PROFILE);
        $specimen->setMeta($meta);

        // Set ID
        if (!empty($dataRecord['uuid'])) {
            $id = new FHIRId();
            $id->setValue($dataRecord['uuid']);
            $specimen->setId($id);
        }

        // Set status based on deleted flag
        $status = $this->mapDeletedToFhirStatus($dataRecord['deleted'] ?? '0');
        $specimen->setStatus($status);

        // Set identifier
        if (!empty($dataRecord['identifier'])) {
            $identifier = new FHIRIdentifier();
            $identifier->setValue($dataRecord['identifier']);
            $identifier->setSystem(FhirCodeSystemConstants::SPECIMEN_IDENTIFIER);
            $specimen->addIdentifier($identifier);
        }

        // Set accession identifier
        if (!empty($dataRecord['accession'])) {
            $accession = new FHIRIdentifier();
            $accessionType = UtilsService::createCodeableConcept([
                'ACSN' => [
                    'code' => 'ACSN',
                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                    'display' => 'Accession ID'
                ]
            ]);
            $accession->setType($accessionType);
            $accession->setValue($dataRecord['accession']);
            $specimen->setAccessionIdentifier($accession);
        }

        // Set type
        if (!empty($dataRecord['type_code']) || !empty($dataRecord['type'])) {
            $type = UtilsService::createCodeableConcept([
                    $dataRecord['type_code'] ?? $dataRecord['type'] => [
                    'code' => $dataRecord['type_code'] ?? null,
                    'display' => $dataRecord['type'] ?? null,
                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0487'
                    ]
            ]);
            $specimen->setType($type);
        }

        // Set subject (patient reference)
        if (!empty($dataRecord['patient_uuid'])) {
            $specimen->setSubject(
                UtilsService::createRelativeReference('Patient', $dataRecord['patient_uuid'])
            );
        }

        // Set collection details
        if (!empty($dataRecord['collected_date']) || !empty($dataRecord['method'])) {
            $collection = new FHIRSpecimenCollection();

            // Collection time
            if (!empty($dataRecord['collected_date'])) {
                $collection->setCollectedDateTime(
                    UtilsService::getLocalDateAsUTC($dataRecord['collected_date'])
                );
            } elseif (!empty($dataRecord['collection_start']) || !empty($dataRecord['collection_end'])) {
                // Use period if available
                $period = UtilsService::createPeriod(
                    $dataRecord['collection_start'] ?? null,
                    $dataRecord['collection_end'] ?? null
                );
                $collection->setCollectedPeriod($period);
            }

            // Collection method
            if (!empty($dataRecord['method_code']) || !empty($dataRecord['method'])) {
                $method = UtilsService::createCodeableConcept([
                        $dataRecord['method_code'] ?? $dataRecord['method'] => [
                        'code' => $dataRecord['method_code'] ?? null,
                        'display' => $dataRecord['method'] ?? null,
                        'system' => 'http://terminology.hl7.org/CodeSystem/v2-0488'
                        ]
                ]);
                $collection->setMethod($method);
            }

            // Body site (specimen location)
            if (!empty($dataRecord['location_code']) || !empty($dataRecord['location'])) {
                $bodySite = UtilsService::createCodeableConcept([
                        $dataRecord['location_code'] ?? $dataRecord['location'] => [
                        'code' => $dataRecord['location_code'] ?? null,
                        'display' => $dataRecord['location'] ?? null,
                        'system' => 'http://snomed.info/sct'
                        ]
                ]);
                $collection->setBodySite($bodySite);
            }

            $specimen->setCollection($collection);
        }

        // Set container/volume if available
        if (!empty($dataRecord['volume'])) {
            $container = new FHIRSpecimenContainer();

            $capacity = UtilsService::createQuantity(
                $dataRecord['volume'],
                $dataRecord['volume_unit'] ?? 'mL',
                $dataRecord['volume_unit'] ?? 'mL'
            );
            $container->setCapacity($capacity);

            $specimen->addContainer($container);
        }

        // Set condition
        if (!empty($dataRecord['condition_code']) || !empty($dataRecord['condition'])) {
            $condition = UtilsService::createCodeableConcept([
                    $dataRecord['condition_code'] ?? $dataRecord['condition'] => [
                    'code' => $dataRecord['condition_code'] ?? null,
                    'display' => $dataRecord['condition'] ?? null,
                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0493'
                    ]
            ]);
            $specimen->addCondition($condition);
        }

        // Add note for comments
        if (!empty($dataRecord['comments'])) {
            $specimen->addNote(['text' => $dataRecord['comments']]);
        }

        // Add note if deleted
        if (!empty($dataRecord['deleted']) && $dataRecord['deleted'] == '1') {
            $specimen->addNote([
                'text' => 'This specimen was marked as entered-in-error or unavailable'
            ]);
        }

        if ($encode) {
            return json_encode($specimen);
        }

        return $specimen;
    }

    /**
     * Map OpenEMR deleted flag to FHIR Specimen status
     *
     * @param string $deleted The deleted flag ('0' or '1')
     * @return string FHIR Specimen status code
     */
    private function mapDeletedToFhirStatus(string $deleted): string
    {
        if ($deleted == '1') {
            return 'entered-in-error';
        }
        return 'available';
    }

    /**
     * Check if FHIR status matches requested status parameter
     *
     * @param string $fhirStatus
     * @param mixed $requestedStatus
     * @return bool
     */
    private function matchesRequestedStatus(string $fhirStatus, $requestedStatus): bool
    {
        if (is_object($requestedStatus) && method_exists($requestedStatus, 'getValues')) {
            $values = $requestedStatus->getValues();
            foreach ($values as $value) {
                if (method_exists($value, 'getCode') && $value->getCode() === $fhirStatus) {
                    return true;
                }
            }
            return false;
        }

        return $fhirStatus === $requestedStatus;
    }

    /**
     * Creates the Provenance resource for the equivalent FHIR Resource
     *
     * @param mixed $dataRecord The source data record (FHIRSpecimen or array)
     * @param bool $encode Indicates if the returned resource is encoded into a string
     * @return FHIRProvenance|null
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRSpecimen)) {
            throw new \BadMethodCallException("Data record should be FHIRSpecimen instance");
        }

        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);

        if ($encode) {
            return json_encode($fhirProvenance);
        }

        return $fhirProvenance;
    }

    /**
     * Get patient context search field
     *
     * @return FhirSearchParameterDefinition
     */
    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition(
            'patient',
            SearchFieldType::REFERENCE,
            [new ServiceField('puuid', ServiceField::TYPE_UUID)]
        );
    }

    /**
     * Get profile URIs for US Core Specimen
     *
     * @return array
     */
    public function getProfileURIs(): array
    {
        return [self::USCDI_PROFILE];
    }
}
