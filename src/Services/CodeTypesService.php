<?php

/**
 * CodeTypesService.php
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use InvalidArgumentException;

/**
 * Service for code type
 */
class CodeTypesService
{
    /**
     * @const string
     */
    const CODE_TYPE_SNOMED_CT = "SNOMED-CT";
    const CODE_TYPE_SNOMED = "SNOMED";
    const CODE_TYPE_CPT4 = "CPT4";
    const CODE_TYPE_LOINC = "LOINC";
    const CODE_TYPE_NUCC = "NUCC";
    const CODE_TYPE_RXNORM = "RXNORM";
    const CODE_TYPE_RXCUI = "RXCUI";
    const CODE_TYPE_ICD10 = 'ICD10';
    const CODE_TYPE_ICD10PCS = 'ICD10PCS';
    const CODE_TYPE_CPT = 'CPT';
    const CODE_TYPE_CVX = 'CVX';

    const CODE_TYPE_NDC = 'NDC';

    const CODE_TYPE_NCI = 'NCI-CONCEPT-ID';

    const CODE_TYPE_DATE_ABSENT_REASON = 'DataAbsentReason';
    const CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY = "2.16.840.1.114222.4.11.1066";

    const CODE_TYPE_HL7_ROLE_CODE = 'RoleCode';

    const CODE_TYPE_HL7_PARTICIPATION_FUNCTION = 'ParticipationFunction';

    const CODE_TYPE_HSOC = 'HSOC';

    const CODE_TYPE_OID = [
        '2.16.840.1.113883.6.96' => self::CODE_TYPE_SNOMED_CT,
        '2.16.840.1.113883.6.12' => self::CODE_TYPE_CPT4,
        '2.16.840.1.113883.6.1' => self::CODE_TYPE_LOINC,
        '2.16.840.1.113883.6.101' => self::CODE_TYPE_NUCC,
        '2.16.840.1.113883.6.88' => self::CODE_TYPE_RXNORM,
        '2.16.840.1.113883.6.90' => self::CODE_TYPE_ICD10,
        '2.16.840.1.113883.6.103' => 'ICD9-CM',
        '2.16.840.1.113883.6.104' => 'ICD9-PCS',
        '2.16.840.1.113883.6.4' => 'ICD10-PCS',
        '2.16.840.1.113883.6.14' => 'HCP',
        '2.16.840.1.113883.6.285' => 'HCPCS',
        '2.16.840.1.113883.5.2' => "HL7 Marital Status",
        '2.16.840.1.113883.12.292' => 'CVX',
        '2.16.840.1.113883.5.83' => 'HITSP C80 Observation Status',
        '2.16.840.1.113883.3.26.1.1' => 'NCI Thesaurus',
        '2.16.840.1.113883.3.88.12.80.20' => 'FDA',
        "2.16.840.1.113883.4.9" => "UNII",
        "2.16.840.1.113883.6.69" => "NDC",
        '2.16.840.1.113883.5.14' => 'HL7 ActStatus',
        '2.16.840.1.113883.6.259' => 'HL7 Healthcare Service Location',
        '2.16.840.1.113883.12.112' => 'DischargeDisposition',
        '2.16.840.1.113883.5.4' => 'HL7 Act Code',
        '2.16.840.1.113883.1.11.18877' => 'HL7 Relationship Code',
        '2.16.840.1.113883.6.238' => 'CDC Race',
        '2.16.840.1.113883.6.177' => 'NLM MeSH',
        '2.16.840.1.113883.5.1076' => "Religious Affiliation",
        '2.16.840.1.113883.1.11.19717' => "HL7 ActNoImmunicationReason",
        '2.16.840.1.113883.3.88.12.80.33' => "NUBC",
        '2.16.840.1.113883.1.11.78' => "HL7 Observation Interpretation",
        '2.16.840.1.113883.3.221.5' => "Source of Payment Typology",
        '2.16.840.1.113883.6.13' => 'CDT',
        '2.16.840.1.113883.18.2' => 'AdministrativeSex',
        '2.16.840.1.113883.5.1' => 'AdministrativeGender',
        '2.16.840.1.114222.4.11.7900' => 'IndustryODH',
        '2.16.840.1.114222.4.11.7901' => 'OccupationODH',
        self::CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY => 'HealthCareProviderTaxonomy'
    ];
    /**
     * @var array
     */
    public $installedCodeTypes;
    /**
     * @var bool
     */
    private $snomedInstalled;
    private $cpt4Installed;
    private $rxnormInstalled;

    protected ListService $listService;

    public function __construct()
    {
        // currently, our installed code types are
        global $code_types;
        $this->installedCodeTypes = $code_types;

        $this->snomedInstalled = isset($code_types[self::CODE_TYPE_SNOMED_CT]);
        $this->cpt4Installed = isset($code_types[self::CODE_TYPE_CPT4]);
        $this->rxnormInstalled = isset($code_types[self::CODE_TYPE_RXNORM]);
    }

    public function setListService(ListService $listService): void
    {
        $this->listService = $listService;
    }

    public function getListService(): ListService
    {
        if (!isset($this->listService)) {
            $this->listService = new ListService();
        }
        return $this->listService;
    }

    /**
     * Lookup the description for a series of codes in the database.  Eventually we would want to
     * remove the global lookup_code_descriptions and use this so we can mock these codes and improve our unit test speed.
     *
     * @param string $codes       - Is of the form "type:code;type:code; etc.".
     * @param string $desc_detail - Can choose either the normal description('code_text') or the brief description('code_text_short').
     * @return string               - Is of the form "description;description; etc.".
     * @see code_types.inc.php
     */
    public function lookup_code_description($codes, $desc_detail = "code_text"): string
    {
        if (empty($codes)) {
            return "";
        }
        $code_text = lookup_code_descriptions($codes, $desc_detail);
        if (empty($code_text)) {
            // let's return a description of code if available.
            // sometimes depending on code, long and/or short description may not be available.
            $desc_detail = $desc_detail == "code_text" ? "code_text_short" : "code_text";
            $code_text = lookup_code_descriptions($codes, $desc_detail);
        }
        return $code_text;
    }

    /**
     *
     * @return bool - Returns true if the snomed-ct codes are installed
     */
    public function isSnomedCodesInstalled(): bool
    {
        return $this->snomedInstalled;
    }

    /**
     *
     * @return bool - Returns whether the system has the cpt4 codes installed
     */
    public function isCPT4Installed(): bool
    {
        return $this->cpt4Installed;
    }

    /**
     *
     * @return bool - Returns whether the system has the rxnorm codes installed
     */
    public function isRXNORMInstalled(): bool
    {
        return $this->rxnormInstalled;
    }

    public function isInstalledCodeType($codeType): bool
    {
        return isset($this->installedCodeTypes[$codeType]);
    }

    /**
     * @param       $code
     * @param false $useOid
     * @return string|null
     */
    public function getSystemForCode($code, $useOid = false)
    {
        $codeType = $this->getCodeTypeForCode($code);
        if (!empty($codeType)) {
            return $this->getSystemForCodeType($codeType);
        }
        return null;
    }

    /**
     * @param $code
     * @return array
     */
    public function parseCode($code)
    {
        $parsedCode = $code;
        $parsedType = null;
        if (is_string($code) && str_contains($code, ":")) {
            $parts = explode(":", $code);
            $parsedCode = $parts[1];
            $parsedType = $parts[0];
        }
        return ['code' => $parsedCode, 'code_type' => $parsedType];
    }

    /**
     * Returns a code with the code type prefixed
     *
     * @param $code string The value for the code that exists in the given code_type datadatabse
     * @param $type string The code_type that the code belongs to (SNOMED, RXCUI, ICD10, etc).
     * @return string  The fully typed code (TYPE:CODE)
     */
    public function getCodeWithType($code, $type, $oe_format = false)
    {
        if (empty($type) || empty($code)) {
            return "";
        }
        $tmp = explode(':', (string) $code);
        if (is_array($tmp) && count($tmp ?? []) === 2) {
            if (!$oe_format) {
                return $code;
            }
            // rebuild when code type format flag is set
            $type = $tmp[0];
            $code = $tmp[1];
        }
        if ($oe_format) {
            $type = $this->formatCodeType($type ?? "");
        }
        return ($type ?? "") . ":" . ($code ?? "");
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getCodeTypeForCode($code)
    {
        $parsedCode = $this->parseCode($code);
        return $parsedCode['code_type'];
    }

    public function getCodeTypeForSystemUrl(string $system): ?string
    {
        $codeType = match ($system) {
            FhirCodeSystemConstants::SNOMED_CT => self::CODE_TYPE_SNOMED_CT,
            FhirCodeSystemConstants::NUCC_PROVIDER => self::CODE_TYPE_NUCC,
            FhirCodeSystemConstants::LOINC => self::CODE_TYPE_LOINC,
            FhirCodeSystemConstants::RXNORM => self::CODE_TYPE_RXCUI,
            FhirCodeSystemConstants::NDC => self::CODE_TYPE_NDC,
            FhirCodeSystemConstants::NCI_THESAURUS => self::CODE_TYPE_NCI,
            FhirCodeSystemConstants::AMA_CPT => self::CODE_TYPE_CPT4,
            FhirCodeSystemConstants::HL7_ICD10 => self::CODE_TYPE_ICD10,
            FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM => self::CODE_TYPE_DATE_ABSENT_REASON,
            FHIRCodeSystemConstants::HL7_ROLE_CODE => self::CODE_TYPE_HL7_ROLE_CODE,
            FHIRCodeSystemConstants::HL7_PARTICIPATION_TYPE => self::CODE_TYPE_HL7_PARTICIPATION_FUNCTION,
            FHIRCodeSystemConstants::HSOC => self::CODE_TYPE_HSOC,
            default => null,
        };
        return $codeType;
    }

    /**
     * @param string $codeType
     * @param false  $useOid
     * @return string|null
     */
    public function getSystemForCodeType($codeType, $useOid = false)
    {
        $system = null;

        if ($useOid) {
            if (self::CODE_TYPE_SNOMED_CT == $codeType) {
                $system = '2.16.840.1.113883.6.96';
            } elseif (self::CODE_TYPE_SNOMED == $codeType) {
                $system = '2.16.840.1.113883.6.96';
            } elseif (self::CODE_TYPE_CPT4 == $codeType) {
                $system = '2.16.840.1.113883.6.12';
            } elseif (self::CODE_TYPE_LOINC == $codeType) {
                $system = '2.16.840.1.113883.6.1';
            } elseif (self::CODE_TYPE_ICD10 == $codeType) {
                $system = '2.16.840.1.113883.6.90';
            } elseif (self::CODE_TYPE_RXCUI == $codeType || self::CODE_TYPE_RXNORM == $codeType) {
                $system = '2.16.840.1.113883.6.88';
            } elseif (self::CODE_TYPE_CPT == $codeType) {
                $system = '2.16.840.1.113883.6.12';
            } elseif (self::CODE_TYPE_CVX == $codeType) {
                $system = '2.16.840.1.113883.12.292';
            } elseif (self::CODE_TYPE_ICD10PCS == $codeType) {
                $system = '2.16.840.1.113883.6.4';
            }
        } else {
            $system = match ($codeType) {
                self::CODE_TYPE_SNOMED_CT => FhirCodeSystemConstants::SNOMED_CT,
                self::CODE_TYPE_SNOMED => FhirCodeSystemConstants::SNOMED_CT,
                self::CODE_TYPE_NUCC => FhirCodeSystemConstants::NUCC_PROVIDER,
                self::CODE_TYPE_LOINC => FhirCodeSystemConstants::LOINC,
                self::CODE_TYPE_RXNORM, self::CODE_TYPE_RXCUI => FhirCodeSystemConstants::RXNORM,
                self::CODE_TYPE_NDC => FhirCodeSystemConstants::NDC,
                self::CODE_TYPE_NCI => FhirCodeSystemConstants::NCI_THESAURUS,
                self::CODE_TYPE_CPT4, self::CODE_TYPE_CPT => FhirCodeSystemConstants::AMA_CPT,
                self::CODE_TYPE_ICD10 => FhirCodeSystemConstants::HL7_ICD10,
                self::CODE_TYPE_DATE_ABSENT_REASON => FhirCodeSystemConstants::DATA_ABSENT_REASON_CODE_SYSTEM,
                self::CODE_TYPE_HL7_ROLE_CODE => FHIRCodeSystemConstants::HL7_ROLE_CODE,
                self::CODE_TYPE_HL7_PARTICIPATION_FUNCTION => FHIRCodeSystemConstants::HL7_PARTICIPATION_TYPE,
                self::CODE_TYPE_HSOC => FHIRCodeSystemConstants::HSOC,
                default => null,
            };
        }
        if (empty($system)) {
            foreach (self::CODE_TYPE_OID as $oid => $system_code) {
                if ($system_code == $codeType) {
                    return $oid;
                }
            }
        }
        return $system;
    }

    /**
     * @param string $type
     * @return string
     */
    public function formatCodeType($type)
    {
        switch (strtoupper(trim($type))) {
            case 'ICD10':
            case 'ICD10-CM':
            case 'ICD-10-CM':
            case 'ICD10CM':
                $type = 'ICD10';
                break;
            case 'SNOMED CT':
            case 'SNOMED-CT':
            case 'SNOMEDCT':
                $type = 'SNOMED-CT';
                if (!$this->isSnomedCodesInstalled()) {
                    $type = 'SNOMED CT'; // for valueset table lookups
                    break;
                }
                break;
            case 'RXCUI':
            case 'RXNORM':
                if (!$this->isRXNORMInstalled() && $this->isInstalledCodeType('RXCUI')) {
                    $type = 'RXCUI'; // then let's use RxCUI for lookups
                } else {
                    $type = 'RXNORM';
                }
                break;
            case 'CPT':
            case 'CPT4':
                $type = 'CPT4';
                break;
            default:
                if (str_contains($type, '2.16.840.1.113883.')) {
                    $type = $this->getCodeSystemNameFromSystem($type);
                }
        }
        return $type ?? "";
    }

    public function getCodeSystemNameFromSystem($oid): string
    {
        return self::CODE_TYPE_OID[$oid] ?? '';
    }

    /**
     * Returns a resolved code set including using valueset table to try and get a code set.
     *
     * @param        $code
     * @param        $codeType
     * @param string $currentCodeText
     * @param string $codeDescriptionType
     * @return array
     */
    public function resolveCode($code, $codeType, $currentCodeText = '', $codeDescriptionType = 'code_text')
    {
        $valueset = '';
        $valueset_name = '';
        $default = [
            'code' => $code ?? '',
            'formatted_code' => $code . ':' . $codeType,
            'formatted_code_type' => $codeType ?? '',
            'code_text' => $currentCodeText,
            'system_oid' => '',
            'valueset' => '',
            'valueset_name' => ''
        ];
        if (empty($code)) {
            $default['formatted_code'] = '';
            return $default;
        }

        $formatted_type = $this->formatCodeType($codeType ?: '');
        $oid = $this->getSystemForCodeType($formatted_type, true);
        $formatted_code = $this->getCodeWithType($code ?? '', $formatted_type);
        if (empty($currentCodeText) && $this->isInstalledCodeType($formatted_type)) {
            $currentCodeText = $this->lookup_code_description($formatted_code, $codeDescriptionType);
        }

        // use valueset table if code description not found.
        if (empty($currentCodeText)) {
            if (str_contains((string) $codeType, '2.16.840.1.113883.')) {
                $oid = trim((string) $codeType);
                $codeType = "";
            }
            $value = $this->lookupFromValueset($code, $formatted_type, $oid);
            $formatted_type = ($value['code_type'] ?? null) ?: $formatted_type;
            if (!empty($code) && !empty($formatted_type)) {
                $formatted_code = $formatted_type . ':' . $code;
            }
            $oid = $value['code_system'] ?? '';
            $currentCodeText = $value['description'] ?? '';
            $valueset_name = $value['valueset_name'] ?? '';
            $valueset = $value['valueset'] ?? '';
        }

        return [
            'code' => $code ?? "",
            'formatted_code' => $formatted_code ?: $code,
            'formatted_code_type' => $formatted_type ?: $codeType,
            'code_text' => trim($currentCodeText),
            'system_oid' => $oid ?? "",
            'valueset' => $valueset ?? "",
            'valueset_name' => $valueset_name ?? ""
        ];
    }

    public function getInstalledCodeTypes()
    {
        return $this->installedCodeTypes ?? null;
    }

    public function lookupFromValueset($code, $codeType, $codeSystem)
    {
        if (empty($codeSystem) && empty($codeType)) {
            $value = sqlQuery(
                "Select * From valueset Where code = ? LIMIT 1",
                [$code]
            );
        } else {
            $value = sqlQuery(
                "Select * From valueset Where code = ? And (code_type = ? Or code_type LIKE ? Or code_system = ?)",
                [$code, $codeType, "$codeType%", $codeSystem]
            );
        }
        return $value;
    }

    public function dischargeOptionIdFromCode($formatted_code)
    {
        $listService = $this->getListService();
        $ret = $listService->getOptionsByListName('discharge-disposition', ['codes' => $formatted_code]) ?? '';
        return $ret[0]['option_id'] ?? '';
    }

    public function dischargeCodeFromOptionId($option_id)
    {
        $listService = $this->getListService();
        return $listService->getListOption('discharge-disposition', $option_id)['codes'] ?? '';
    }

    public function parseCodesIntoCodeableConcepts($codes)
    {
        if (!is_string($codes) || empty(trim($codes))) {
            return [];
        }
        $codes = explode(";", $codes);

        $codeableConcepts = [];
        foreach ($codes as $codeItem) {
            $parsedCode = $this->parseCode($codeItem);
            $codeType = $parsedCode['code_type'];
            $code = $parsedCode['code'];
            $system = $this->getSystemForCodeType($codeType);
            $codedesc = $this->lookup_code_description($codeItem);
            $codeableConcepts[$code] = [
                'code' => $code
                , 'description' => $codedesc
                , 'code_type' => $codeType
                , 'system' => $system
            ];
        }
        return $codeableConcepts;
    }

    /**
     * Return listing of pertinent and active code types.
     *
     * Function will return listing (ct_key) of pertinent
     * active code types, such as diagnosis codes or procedure
     * codes in a chosen format. Supported returned formats include
     * as 1) an array and as 2) a comma-separated lists that has been
     * process by urlencode() in order to place into URL  address safely.
     *
     * @param  string       $category       category of code types('diagnosis', 'procedure', 'clinical_term', 'active' or 'medical_problem')
     * @param  string       $return_format  format or returned code types ('array' or 'csv')
     * @return string|array
     */
    public function collectCodeTypes($category, $return_format = "array"): string|array
    {
        global $code_types;

        // could turn this into an enum later if desired
        if (!in_array($return_format, ['array','csv'])) {
            throw new InvalidArgumentException("Unsupported return format: $return_format");
        }

        $code_remap = [
            'active' => 'active',
            'clinical_term' => 'term',
            'diagnosis' => 'diag',
            'drug' => 'drug',
            'medical_problem' => 'problem',
            'procedure' => 'proc',
        ];
        $cat_code = $code_remap[$category] ?? null;
        if ($cat_code === null) {
            throw new InvalidArgumentException("Unsupported code category: $category");
        }

        $return = array_keys(array_filter($code_types, fn($ct_arr): bool => ($ct_arr['active'] ?? false) && ($ct_arr[$cat_code] ?? false)));

        return $return_format === 'csv' ? csv_like_join($return) : $return;
    }

    /**
     * Given a system URL and code, return the OpenEMR formatted code with type prefix if applicable.
     * @param string|null $system
     * @param float|bool|int|string $code
     * @return string
     */
    public function getOpenEMRCodeForSystemAndCode(?string $system, float|bool|int|string $code): string
    {
        if (!empty($system)) {
            $codeType = $this->getCodeTypeForSystemUrl($system);
            if (!empty($codeType)) {
                return $this->getCodeWithType($code, $codeType);
            }
        }
        return $code;
    }
}
