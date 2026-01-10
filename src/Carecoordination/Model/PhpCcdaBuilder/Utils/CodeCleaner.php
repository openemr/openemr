<?php

/**
 * CodeCleaner.php - Code Cleaning Utilities for CCDA
 *
 * PHP equivalent of utils/clean-code/clean-code.js from serveccda.js
 * Cleans and normalizes medical codes for CCDA output.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Utils;

class CodeCleaner
{
    /**
     * Clean a code value for CCDA output
     *
     * Removes common prefixes, whitespace, and invalid characters.
     *
     * @param string|null $code The code to clean
     * @return string The cleaned code or empty string
     */
    public static function clean(?string $code): string
    {
        if ($code === null || $code === '') {
            return '';
        }

        $code = trim($code);

        // Remove common code system prefixes
        $prefixes = [
            'SNOMED-CT:',
            'SNOMED CT:',
            'SNOMED:',
            'ICD10:',
            'ICD-10:',
            'ICD10CM:',
            'ICD-10-CM:',
            'ICD9:',
            'ICD-9:',
            'RXNORM:',
            'RxNorm:',
            'LOINC:',
            'CVX:',
            'CPT:',
            'CPT4:',
            'HCPCS:',
            'NDC:',
        ];

        foreach ($prefixes as $prefix) {
            if (stripos($code, $prefix) === 0) {
                $code = substr($code, strlen($prefix));
                break;
            }
        }

        // Trim again after removing prefix
        $code = trim($code);

        // Remove any surrounding quotes
        $code = trim($code, '"\'');

        return $code;
    }

    /**
     * Determine the code system name from a code value
     *
     * @param string|null $code The code value (may include prefix)
     * @param string $default Default code system if not determinable
     * @return string The code system name
     */
    public static function getCodeSystem(?string $code, string $default = 'SNOMED CT'): string
    {
        if ($code === null || $code === '') {
            return $default;
        }

        $code = trim($code);
        $codeUpper = strtoupper($code);

        // Check for prefixes
        $systemMap = [
            'SNOMED-CT:' => 'SNOMED CT',
            'SNOMED CT:' => 'SNOMED CT',
            'SNOMED:' => 'SNOMED CT',
            'ICD10:' => 'ICD-10-CM',
            'ICD-10:' => 'ICD-10-CM',
            'ICD10CM:' => 'ICD-10-CM',
            'ICD-10-CM:' => 'ICD-10-CM',
            'ICD9:' => 'ICD-9-CM',
            'ICD-9:' => 'ICD-9-CM',
            'RXNORM:' => 'RXNORM',
            'LOINC:' => 'LOINC',
            'CVX:' => 'CVX',
            'CPT:' => 'CPT',
            'CPT4:' => 'CPT',
            'HCPCS:' => 'HCPCS',
            'NDC:' => 'NDC',
        ];

        foreach ($systemMap as $prefix => $system) {
            if (stripos($code, $prefix) === 0) {
                return $system;
            }
        }

        // Try to infer from code pattern
        return self::inferCodeSystem($code, $default);
    }

    /**
     * Infer code system from code pattern
     */
    private static function inferCodeSystem(string $code, string $default): string
    {
        $code = self::clean($code);

        if (empty($code)) {
            return $default;
        }

        // SNOMED CT: Numeric, typically 6-18 digits
        if (preg_match('/^\d{6,18}$/', $code)) {
            return 'SNOMED CT';
        }

        // ICD-10-CM: Letter followed by digits, optional decimal
        if (preg_match('/^[A-TV-Z]\d{2}(\.\d{1,4})?$/i', $code)) {
            return 'ICD-10-CM';
        }

        // ICD-9-CM: 3-5 digits, optional decimal
        if (preg_match('/^\d{3}(\.\d{1,2})?$/', $code)) {
            return 'ICD-9-CM';
        }

        // RxNorm: Numeric, typically 5-7 digits
        if (preg_match('/^\d{5,7}$/', $code)) {
            return 'RXNORM';
        }

        // LOINC: Digits followed by hyphen and check digit
        if (preg_match('/^\d{1,5}-\d$/', $code)) {
            return 'LOINC';
        }

        // CVX: 2-3 digit numeric codes
        if (preg_match('/^\d{2,3}$/', $code)) {
            // Could be CVX, but also could be other things
            // Return default since we can't be sure
            return $default;
        }

        // CPT: 5-digit numeric
        if (preg_match('/^\d{5}$/', $code)) {
            return 'CPT';
        }

        // HCPCS: Letter followed by 4 digits
        if (preg_match('/^[A-Z]\d{4}$/i', $code)) {
            return 'HCPCS';
        }

        // NDC: Various formats with dashes
        if (preg_match('/^\d{4,5}-\d{3,4}-\d{1,2}$/', $code)) {
            return 'NDC';
        }

        return $default;
    }

    /**
     * Get the OID for a code system name
     *
     * @param string $codeSystemName The code system name
     * @return string The OID or empty string if unknown
     */
    public static function getCodeSystemOid(string $codeSystemName): string
    {
        $oidMap = [
            'SNOMED CT' => '2.16.840.1.113883.6.96',
            'SNOMED-CT' => '2.16.840.1.113883.6.96',
            'ICD-10-CM' => '2.16.840.1.113883.6.90',
            'ICD-10' => '2.16.840.1.113883.6.90',
            'ICD-9-CM' => '2.16.840.1.113883.6.103',
            'ICD-9' => '2.16.840.1.113883.6.103',
            'RXNORM' => '2.16.840.1.113883.6.88',
            'RxNorm' => '2.16.840.1.113883.6.88',
            'LOINC' => '2.16.840.1.113883.6.1',
            'CVX' => '2.16.840.1.113883.12.292',
            'CPT' => '2.16.840.1.113883.6.12',
            'CPT-4' => '2.16.840.1.113883.6.12',
            'HCPCS' => '2.16.840.1.113883.6.285',
            'NDC' => '2.16.840.1.113883.6.69',
            'NCI Thesaurus' => '2.16.840.1.113883.3.26.1.1',
            'Medication Route FDA' => '2.16.840.1.113883.3.26.1.1',
            'HL7 ActCode' => '2.16.840.1.113883.5.4',
            'HL7 RoleCode' => '2.16.840.1.113883.5.111',
            'NUCC Health Care Provider Taxonomy' => '2.16.840.1.113883.6.101',
        ];

        return $oidMap[$codeSystemName] ?? '';
    }

    /**
     * Normalize a code system name to standard form
     *
     * @param string|null $codeSystemName The code system name to normalize
     * @return string The normalized code system name
     */
    public static function normalizeCodeSystemName(?string $codeSystemName): string
    {
        if ($codeSystemName === null || $codeSystemName === '') {
            return '';
        }

        $normalized = strtoupper(trim($codeSystemName));

        $normMap = [
            'SNOMED' => 'SNOMED CT',
            'SNOMED-CT' => 'SNOMED CT',
            'SNOMEDCT' => 'SNOMED CT',
            'ICD10' => 'ICD-10-CM',
            'ICD-10' => 'ICD-10-CM',
            'ICD10CM' => 'ICD-10-CM',
            'ICD9' => 'ICD-9-CM',
            'ICD-9' => 'ICD-9-CM',
            'ICD9CM' => 'ICD-9-CM',
            'RXNORM' => 'RXNORM',
            'CPT4' => 'CPT',
            'CPT-4' => 'CPT',
        ];

        return $normMap[$normalized] ?? $codeSystemName;
    }
}
