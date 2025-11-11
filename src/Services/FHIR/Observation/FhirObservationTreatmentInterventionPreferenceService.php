<?php

namespace OpenEMR\Services\FHIR\Observation;

use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IResourceSearchableService;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationTreatmentInterventionPreferenceService extends FhirServiceBase implements IResourceSearchableService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    private const PROFILE = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-treatment-intervention-preference';
    private const CATEGORY_SYSTEM = 'http://hl7.org/fhir/us/core/CodeSystem/us-core-category';
    private const CATEGORY_CODE   = 'treatment-intervention-preference';
    private const LOINC_SYSTEM    = FhirCodeSystemConstants::LOINC ?? 'http://loinc.org';
    
    // FIXED: Support ALL treatment intervention preference LOINC codes from your schema
    private const SUPPORTED_LOINC_CODES = [
        '75773-2',  // Goals, preferences, and priorities for medical treatment [Reported]
        '81329-5',  // Thoughts on resuscitation (CPR)
        '81330-3',  // Thoughts on intubation
        '81331-1',  // Thoughts on tube feeding
        '81332-9',  // Thoughts on IV fluid and support
        '81333-7',  // Thoughts on antibiotics
        '81336-0',  // Patient's thoughts on cardiopulmonary bypass
        '81337-8',  // Patient's thoughts on mechanical ventilation
        '81376-6',  // Upon death organ donation consent
        '81378-2',  // Patient Healthcare goals
    ];

    private const TABLE = 'patient_treatment_intervention_preferences';
    private const DEFAULT_STATUS = 'final';

    public function loadSearchParameters(): array
    {
        return [
            'patient' => new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [
                new ServiceField('uuid', ServiceField::TYPE_UUID)
            ]),
            '_lastUpdated' => new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated'])
        ];
    }

    public function getProfileURIs(): array
    {
        return [self::PROFILE];
    }

    public function supportsCategory($category): bool
    {
        return $category === self::CATEGORY_CODE;
    }

    /**
     * FIXED: Check if code is one of our supported LOINC codes
     */
    public function supportsCode($code): bool
    {
        return in_array($code, self::SUPPORTED_LOINC_CODES, true);
    }

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $result = new ProcessingResult();

        // Handle category filter
        if (isset($openEMRSearchParameters['category'])) {
            $cat = $openEMRSearchParameters['category'];
            if (!($cat instanceof TokenSearchField) || !$cat->hasCodeValue(self::CATEGORY_CODE)) {
                return $result;
            }
            unset($openEMRSearchParameters['category']);
        }

        // FIXED: Handle code filter - check against ALL supported codes
        $requestedCodes = null;
        if (isset($openEMRSearchParameters['code'])) {
            $tok = $openEMRSearchParameters['code'];
            if (!($tok instanceof TokenSearchField)) {
                throw new SearchFieldException('code', 'Invalid code token');
            }
            
            // Find intersection of requested codes and supported codes
            $requestedCodes = [];
            foreach ($tok->getValues() as $v) {
                $code = $v->getCode();
                if (in_array($code, self::SUPPORTED_LOINC_CODES, true)) {
                    $requestedCodes[] = $code;
                }
            }
            
            unset($openEMRSearchParameters['code']);
            
            // If no valid codes requested, return empty
            if (empty($requestedCodes)) {
                return $result;
            }
        }

        // Build WHERE clause
        $where = [];
        $args  = [];

        // Patient filter
        if (isset($openEMRSearchParameters['patient'])) {
            $pidField = $openEMRSearchParameters['patient'];
            $pid = (int)($pidField instanceof ServiceField ? $pidField->getValue() : $pidField);
            if ($pid > 0) {
                $where[] = "patient_id = ?";
                $args[]  = $pid;
            }
            unset($openEMRSearchParameters['patient']);
        }

        // Status filter
        if (isset($openEMRSearchParameters['status'])) {
            $status = $openEMRSearchParameters['status'];
            if ($status instanceof TokenSearchField) {
                $vals = array_map(fn($v) => $v->getCode(), $status->getValues());
                if (!empty($vals)) {
                    $where[] = "status IN (" . implode(',', array_fill(0, count($vals), '?')) . ")";
                    array_push($args, ...$vals);
                }
            }
            unset($openEMRSearchParameters['status']);
        }

        // Date filter
        if (isset($openEMRSearchParameters['date'])) {
            $dateField = $openEMRSearchParameters['date'];
            foreach ($dateField->getValues() as $val) {
                $op = $val->getPrefix();
                $dt = $val->getValue();
                switch ($op) {
                    case 'ge':
                        $where[] = "effective_datetime >= ?";
                        $args[] = $dt;
                        break;
                    case 'le':
                        $where[] = "effective_datetime <= ?";
                        $args[] = $dt;
                        break;
                    case 'gt':
                        $where[] = "effective_datetime > ?";
                        $args[] = $dt;
                        break;
                    case 'lt':
                        $where[] = "effective_datetime < ?";
                        $args[] = $dt;
                        break;
                    default:
                        $where[] = "DATE(effective_datetime) = DATE(?)";
                        $args[] = $dt;
                        break;
                }
            }
            unset($openEMRSearchParameters['date']);
        }

        // ID filter
        if (isset($openEMRSearchParameters['_id'])) {
            $svc = $openEMRSearchParameters['_id'];
            $uuid = $svc instanceof ServiceField ? $svc->getValue() : null;
            if (!empty($uuid)) {
                $where[] = "uuid = ?";
                $args[]  = UuidRegistry::uuidToBytes($uuid);
            }
            unset($openEMRSearchParameters['_id']);
        }

        // FIXED: Filter by ALL supported LOINC codes or specific requested codes
        if ($requestedCodes !== null) {
            // User requested specific codes
            $placeholders = implode(',', array_fill(0, count($requestedCodes), '?'));
            $where[] = "observation_code IN ($placeholders)";
            array_push($args, ...$requestedCodes);
        } else {
            // No code filter, return all supported codes
            $placeholders = implode(',', array_fill(0, count(self::SUPPORTED_LOINC_CODES), '?'));
            $where[] = "observation_code IN ($placeholders)";
            array_push($args, ...self::SUPPORTED_LOINC_CODES);
        }

        // Build and execute query
        $sql = "SELECT id, uuid, patient_id, observation_code, observation_code_text,
                       value_type, value_code, value_code_system, value_display,
                       value_text, value_boolean, effective_datetime, status, note
                  FROM " . self::TABLE .
            (empty($where) ? "" : " WHERE " . implode(" AND ", $where)) .
            " ORDER BY effective_datetime DESC, id DESC";

        $rows = QueryUtils::fetchRecords($sql, $args) ?? [];
        $result->setData(array_map([$this, 'transformRow'], $rows));
        return $result;
    }

    /**
     * FIXED: Always use constants, handle observation_code from database
     */
    private function transformRow(array $r): array
    {
        return [
            'uuid' => !empty($r['uuid']) ? UuidRegistry::uuidToString($r['uuid']) : null,
            'pid'  => (int)$r['patient_id'],
            'date' => $r['effective_datetime'] ?? null,
            'status' => $r['status'] ?: self::DEFAULT_STATUS,
            'category' => self::CATEGORY_CODE,
            'code' => $r['observation_code'], // FIXED: Use actual code from database
            'code_text' => $r['observation_code_text'] ?: $this->getDisplayForCode($r['observation_code']),
            'value_type' => $r['value_type'],
            'value_code' => $r['value_code'],
            'value_code_system' => $r['value_code_system'],
            'value_display' => $r['value_display'],
            'value_text' => $r['value_text'],
            'value_boolean' => isset($r['value_boolean']) ? (bool)$r['value_boolean'] : null,
            'note' => $r['note'] ?? null
        ];
    }

    /**
     * Get display text for LOINC code
     */
    private function getDisplayForCode(string $code): string
    {
        $displays = [
            '75773-2' => 'Goals, preferences, and priorities for medical treatment [Reported]',
            '81329-5' => 'Thoughts on resuscitation (CPR)',
            '81330-3' => 'Thoughts on intubation',
            '81331-1' => 'Thoughts on tube feeding',
            '81332-9' => 'Thoughts on IV fluid and support',
            '81333-7' => 'Thoughts on antibiotics',
            '81336-0' => 'Patient\'s thoughts on cardiopulmonary bypass',
            '81337-8' => 'Patient\'s thoughts on mechanical ventilation',
            '81376-6' => 'Upon death organ donation consent',
            '81378-2' => 'Patient Healthcare goals',
        ];
        return $displays[$code] ?? 'Treatment intervention preference';
    }

    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRDomainResource|string
    {
        if (empty($dataRecord)) {
            throw new InvalidArgumentException("Data record cannot be empty");
        }

        $obs = new FHIRObservation();

        // Meta with profile
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->addProfile([new FHIRUri(self::PROFILE)]);
        $obs->setMeta($meta);

        // Resource ID
        if (!empty($dataRecord['uuid'])) {
            $id = new FHIRId();
            $id->setValue($dataRecord['uuid']);
            $obs->setId($id);
        }

        // Subject (Patient reference)
        if (!empty($dataRecord['pid'])) {
            $obs->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['pid']));
        }

        // Effective DateTime
        if (!empty($dataRecord['date'])) {
            $obs->setEffectiveDateTime(new FHIRDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date'])));
        }

        // Status
        $obs->setStatus($dataRecord['status'] ?: self::DEFAULT_STATUS);

        // Category
        $catCoding = new FHIRCoding();
        $catCoding->setSystem(self::CATEGORY_SYSTEM);
        $catCoding->setCode(self::CATEGORY_CODE);
        $cat = new FHIRCodeableConcept();
        $cat->addCoding($catCoding);
        $obs->addCategory($cat);

        // FIXED: Code - use actual code from database
        $codeCoding = new FHIRCoding();
        $codeCoding->setSystem(self::LOINC_SYSTEM);
        $codeCoding->setCode($dataRecord['code']);
        $codeCoding->setDisplay($dataRecord['code_text'] ?? $this->getDisplayForCode($dataRecord['code']));
        $code = new FHIRCodeableConcept();
        $code->addCoding($codeCoding);
        $obs->setCode($code);

        // Value[x]
        switch ($dataRecord['value_type']) {
            case 'boolean':
                if (isset($dataRecord['value_boolean']) && $dataRecord['value_boolean'] !== null) {
                    $obs->setValueBoolean((bool)$dataRecord['value_boolean']);
                }
                break;
                
            case 'text':
                if (!empty($dataRecord['value_text'])) {
                    $obs->setValueString($dataRecord['value_text']);
                }
                break;
                
            case 'coded':
            default:
                if (!empty($dataRecord['value_code']) || !empty($dataRecord['value_display'])) {
                    $cc = new FHIRCodeableConcept();
                    $coding = new FHIRCoding();
                    
                    $coding->setSystem($dataRecord['value_code_system'] ?: self::LOINC_SYSTEM);
                    
                    if (!empty($dataRecord['value_code'])) {
                        $coding->setCode($dataRecord['value_code']);
                    }
                    
                    if (!empty($dataRecord['value_display'])) {
                        $coding->setDisplay($dataRecord['value_display']);
                    }
                    
                    $cc->addCoding($coding);
                    $obs->setValueCodeableConcept($cc);
                }
                break;
        }

        // Note
        if (!empty($dataRecord['note'])) {
            $obs->addNote(UtilsService::createAnnotation($dataRecord['note']));
        }

        return $encode ? $obs->jsonSerialize() : $obs;
    }
}
