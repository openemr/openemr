<?php

namespace OpenEMR\Services\FHIR\Observation;

use InvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
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
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class FhirObservationCareExperiencePreferenceService extends FhirServiceBase implements IResourceSearchableService, IResourceUSCIGProfileService
{
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    private const PROFILE = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-care-experience-preference';
    private const CATEGORY_SYSTEM = 'http://hl7.org/fhir/us/core/CodeSystem/us-core-category';
    private const CATEGORY_CODE = 'care-experience-preference';
    private const LOINC_SYSTEM = FhirCodeSystemConstants::LOINC ?? 'http://loinc.org';

    // Support ALL care experience preference LOINC codes from your schema
    private const SUPPORTED_LOINC_CODES = [
        '95541-9',  // Care experience preference
        '81364-2',  // Religious or cultural beliefs (reported)
        '81365-9',  // Religious/cultural affiliation contact to notify (reported)
        '103980-9', // Preferred pharmacy
        '81338-6',  // Patient goals, preferences & priorities for care experience
        '81342-8',  // Care experience preference under certain health conditions
        '81343-6',  // Care experience preference at end of life
        '81362-6',  // Preferred location for healthcare
        '81363-4',  // Preferred healthcare professional
    ];

    private const TABLE = 'patient_care_experience_preferences';
    private const DEFAULT_STATUS = 'final';

    public function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['p.observation_code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['effective_datetime']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['p.status']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('p.uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('pd.uuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_modified']);
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
     * Check if code is one of our supported LOINC codes
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

        // Handle code filter - check against ALL supported codes
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
            // If no valid codes requested, return empty
            if (empty($requestedCodes)) {
                return $result;
            }
        }

        $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters, true);
        $sql_frag = $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        // Build and execute query
        $sql = "SELECT p.id, p.uuid, p.patient_id, p.observation_code, p.observation_code_text,
                       p.value_type, p.value_code, p.value_code_system, p.value_display,
                       p.value_text, p.value_boolean, p.effective_datetime, p.status, p.note,
                       pd.uuid as patient_uuid
                  FROM " . self::TABLE . " p
                  LEFT JOIN patient_data pd ON pd.pid = p.patient_id" .
            (empty($sql_frag) ? "" : $sql_frag) .
            " ORDER BY p.effective_datetime DESC, p.id DESC";

        $rows = QueryUtils::fetchRecords($sql, $sqlBindArray) ?? [];
        $result->setData(array_map([$this, 'transformRow'], $rows));
        return $result;
    }

    /**
     * Always use constants, handle observation_code from database
     */
    private function transformRow(array $r): array
    {
        return [
            'uuid' => !empty($r['uuid']) ? UuidRegistry::uuidToString($r['uuid']) : null,
            'pid' => (int)$r['patient_id'],
            'puuid' => !empty($r['patient_uuid']) ? UuidRegistry::uuidToString($r['patient_uuid']) : null,
            'date' => $r['effective_datetime'] ?? null,
            'status' => $r['status'] ?: self::DEFAULT_STATUS,
            'category' => self::CATEGORY_CODE,
            'code' => $r['observation_code'], // Use actual code from database
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
            '95541-9' => 'Care experience preference',
            '81364-2' => 'Religious or cultural beliefs [Reported]',
            '81365-9' => 'Religious/cultural affiliation contact to notify [Reported]',
            '103980-9' => 'Preferred pharmacy',
            '81338-6' => 'Patient goals, preferences & priorities for care experience',
            '81342-8' => 'Care experience preference under certain health conditions',
            '81343-6' => 'Care experience preference at end of life',
            '81362-6' => 'Preferred location for healthcare',
            '81363-4' => 'Preferred healthcare professional',
        ];
        return $displays[$code] ?? 'Care experience preference';
    }

    /**
     * Get patient UUID from patient ID
     */
    private function getPatientUuidFromPid(int $pid): ?string
    {
        $sql = "SELECT uuid FROM patient_data WHERE pid = ?";
        $result = QueryUtils::fetchRecords($sql, [$pid]);

        if (!empty($result[0]['uuid'])) {
            return UuidRegistry::uuidToString($result[0]['uuid']);
        }

        return null;
    }

    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRDomainResource|string
    {
        if (empty($dataRecord)) {
            throw new InvalidArgumentException("Data record cannot be empty");
        }

        $obs = new FHIRObservation();

        // Meta with profile - Don't wrap in array, addProfile expects a FHIRUri
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $profileUri = new FHIRUri();
        $profileUri->setValue(self::PROFILE);
        $meta->addProfile($profileUri);
        $obs->setMeta($meta);

        // Resource ID
        if (!empty($dataRecord['uuid'])) {
            $id = new FHIRId();
            $id->setValue($dataRecord['uuid']);
            $obs->setId($id);
        }

        // Subject (Patient reference) - Use patient UUID not PID
        if (!empty($dataRecord['puuid'])) {
            // Use patient UUID from the query join
            $obs->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } elseif (!empty($dataRecord['pid'])) {
            // Fallback: fetch patient UUID from pid
            $patientUuid = $this->getPatientUuidFromPid($dataRecord['pid']);
            if ($patientUuid) {
                $obs->setSubject(UtilsService::createRelativeReference('Patient', $patientUuid));
            } else {
                // Last resort fallback to PID
                $obs->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['pid']));
            }
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

        // Code - use actual code from database
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
            $annotation = new FHIRAnnotation();
            $annotation->setText($dataRecord['note']);
            $obs->addNote($annotation);
        }

        return $encode ? $obs->jsonSerialize() : $obs;
    }
}
