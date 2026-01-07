<?php

/**
 * OidRegistry.php - OID Constants and Lookups for CCDA
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Utils;

class OidRegistry
{
    // Code Systems
    public const SNOMED_CT = '2.16.840.1.113883.6.96';
    public const LOINC = '2.16.840.1.113883.6.1';
    public const RXNORM = '2.16.840.1.113883.6.88';
    public const ICD10_CM = '2.16.840.1.113883.6.90';
    public const ICD9_CM = '2.16.840.1.113883.6.103';
    public const CPT = '2.16.840.1.113883.6.12';
    public const CVX = '2.16.840.1.113883.12.292';
    public const NDC = '2.16.840.1.113883.6.69';
    public const NPI = '2.16.840.1.113883.4.6';
    public const HCPCS = '2.16.840.1.113883.6.285';
    public const NCI_THESAURUS = '2.16.840.1.113883.3.26.1.1';
    
    // HL7
    public const HL7_ACT_CODE = '2.16.840.1.113883.5.4';
    public const HL7_ROLE_CODE = '2.16.840.1.113883.5.111';
    public const HL7_CONFIDENTIALITY = '2.16.840.1.113883.5.25';
    public const HL7_GENDER = '2.16.840.1.113883.5.1';
    
    // NUCC
    public const NUCC_PROVIDER_TAXONOMY = '2.16.840.1.113883.6.101';

    private const CODE_SYSTEM_MAP = [
        'SNOMED CT' => self::SNOMED_CT,
        'LOINC' => self::LOINC,
        'RXNORM' => self::RXNORM,
        'ICD-10-CM' => self::ICD10_CM,
        'ICD-9-CM' => self::ICD9_CM,
        'CPT' => self::CPT,
        'CVX' => self::CVX,
        'NDC' => self::NDC,
        'HCPCS' => self::HCPCS,
    ];

    public static function getOid(string $codeSystemName): string
    {
        return self::CODE_SYSTEM_MAP[strtoupper($codeSystemName)] ?? '';
    }
}
