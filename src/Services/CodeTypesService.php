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
    const CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY = "2.16.840.1.114222.4.11.1066";
    const CODE_TYPE_OID = array(
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
        self::CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY => 'HealthCareProviderTaxonomy'
    );
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

    public function __construct()
    {
        // currently, our installed code types are
        global $code_types;
        $this->installedCodeTypes = $code_types;

        $this->snomedInstalled = isset($code_types[self::CODE_TYPE_SNOMED_CT]);
        $this->cpt4Installed = isset($code_types[self::CODE_TYPE_CPT4]);
        $this->rxnormInstalled = isset($code_types[self::CODE_TYPE_RXNORM]);
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
        if (is_string($code) && strpos($code, ":") !== false) {
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
        $tmp = explode(':', $code);
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
            if (self::CODE_TYPE_SNOMED_CT == $codeType) {
                $system = FhirCodeSystemConstants::SNOMED_CT;
            } elseif (self::CODE_TYPE_SNOMED == $codeType) {
                $system = FhirCodeSystemConstants::SNOMED_CT;
            } elseif (self::CODE_TYPE_NUCC == $codeType) {
                $system = FhirCodeSystemConstants::NUCC_PROVIDER;
            } elseif (self::CODE_TYPE_LOINC == $codeType) {
                $system = FhirCodeSystemConstants::LOINC;
            } elseif (self::CODE_TYPE_RXNORM == $codeType || self::CODE_TYPE_RXCUI == $codeType) {
                $system = FhirCodeSystemConstants::RXNORM;
            }
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
                if (strpos($type, '2.16.840.1.113883.') !== false) {
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
        $default = array(
            'code' => $code ?? '',
            'formatted_code' => $code . ':' . $codeType,
            'formatted_code_type' => $codeType ?? '',
            'code_text' => $currentCodeText,
            'system_oid' => '',
            'valueset' => '',
            'valueset_name' => ''
        );
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
            if (strpos($codeType, '2.16.840.1.113883.') !== false) {
                $oid = trim($codeType);
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

        return array(
            'code' => $code ?? "",
            'formatted_code' => $formatted_code ?: $code,
            'formatted_code_type' => $formatted_type ?: $codeType,
            'code_text' => trim($currentCodeText),
            'system_oid' => $oid ?? "",
            'valueset' => $valueset ?? "",
            'valueset_name' => $valueset_name ?? ""
        );
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
                array($code)
            );
        } else {
            $value = sqlQuery(
                "Select * From valueset Where code = ? And (code_type = ? Or code_type LIKE ? Or code_system = ?)",
                array($code, $codeType, "$codeType%", $codeSystem)
            );
        }
        return $value;
    }

    public function dischargeOptionIdFromCode($formatted_code)
    {
        $listService = new ListService();
        $ret = $listService->getOptionsByListName('discharge-disposition', ['codes' => $formatted_code]) ?? '';
        return $ret[0]['option_id'] ?? '';
    }

    public function dischargeCodeFromOptionId($option_id)
    {
        $listService = new ListService();
        return $listService->getListOption('discharge-disposition', $option_id)['codes'] ?? '';
    }

    public function parseCodesIntoCodeableConcepts($codes)
    {
        $codes = explode(";", $codes);
        $codeableConcepts = array();
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
}
