<?php

/**
 * CodeTypesService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Services\FHIR\FhirCodeSystemConstants;

class CodeTypesService
{
    private $snomedInstalled;

    const CODE_TYPE_SNOMED_CT = "SNOMED-CT";
    const CODE_TYPE_CPT4 = "CPT4";
    const CODE_TYPE_LOINC = "LOINC";
    const CODE_TYPE_NUCC = "NUCC";

    public function __construct()
    {
        // currently our installed code types are
        global $code_types;

        $this->snomedInstalled = isset($code_types[self::CODE_TYPE_SNOMED_CT]);
        $this->cpt4Installed = isset($code_types[self::CODE_TYPE_CPT4]);
    }

    /**
     * Lookup the description for a series of codes in the database.  Eventually we would want to
     * remove the global lookup_code_descriptions and use this so we can mock these codes and improve our unit test speed.
     * @see code_types.inc.php
     * @param  string $codes  Is of the form "type:code;type:code; etc.".
     * @param  string $desc_detail Can choose either the normal description('code_text') or the brief description('code_text_short').
     * @return string         Is of the form "description;description; etc.".
     */
    public function lookup_code_description($codes, $desc_detail = "code_text")
    {
        return lookup_code_descriptions($codes, $desc_detail);
    }

    /**
     * Returns true if the snomed-ct codes are installed
     * @return bool
     */
    public function isSnomedCodesInstalled()
    {
        return $this->snomedInstalled;
    }

    /**
     * Returns whether the system has the cpt4 codes installed
     * @return bool
     */
    public function isCPT4Installed()
    {
        return $this->cpt4Installed;
    }

    public function getSystemForCode($code, $useOid = false)
    {
        if (strpos($code, ":") !== false) {
            $parts = explode(":", $code);
            return $this->getSystemForCodeType($parts[0]);
        }
        return null;
    }

    public function getSystemForCodeType($codeType, $useOid = false)
    {
        $system = null;
        if ($useOid) {
            if (self::CODE_TYPE_SNOMED_CT == $codeType) {
                $system = '2.16.840.1.113883.6.96';
            } elseif (self::CODE_TYPE_CPT4 == $codeType) {
                $system = '2.16.840.1.113883.6.12';
            } elseif (self::CODE_TYPE_LOINC == $codeType) {
                $system = '2.16.840.1.113883.6.1';
            }
        } else {
            if (self::CODE_TYPE_SNOMED_CT == $codeType) {
                $system = FhirCodeSystemConstants::SNOMED_CT;
            } elseif (self::CODE_TYPE_NUCC == $codeType) {
                $system = FhirCodeSystemConstants::NUCC_PROVIDER;
            } else if (self::CODE_TYPE_LOINC == $codeType) {
                $system = FhirCodeSystemConstants::LOINC;
            }
        }
        return $system;
    }
}
