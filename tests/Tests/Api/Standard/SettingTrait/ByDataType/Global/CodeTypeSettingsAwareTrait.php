<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global;

trait CodeTypeSettingsAwareTrait
{
    protected static function getCodeTypeDataProviderChunks(): iterable
    {
        yield [
            'billing',
            'default_search_code_type',
            [
                'setting_key' => 'default_search_code_type',
                'setting_name' => 'Default Search Code Type',
                'setting_description' => 'The default code type to search for in the Fee Sheet.',
                'setting_default_value' => 'ICD10',
                'setting_is_default_value' => true,
                'setting_value' => 'ICD10',
                'setting_value_options' => [
                    [
                        'option_value' => 'ICD9',
                        'option_label' => 'ICD9 Diagnosis'
                    ],
                    [
                        'option_value' => 'CPT4',
                        'option_label' => 'CPT4 Procedure/Service'
                    ],
                    [
                        'option_value' => 'HCPCS',
                        'option_label' => 'HCPCS Procedure/Service'
                    ],
                    [
                        'option_value' => 'CVX',
                        'option_label' => 'CVX Immunization'
                    ],
                    [
                        'option_value' => 'DSMIV',
                        'option_label' => 'DSMIV Diagnosis'
                    ],
                    [
                        'option_value' => 'ICD10',
                        'option_label' => 'ICD10 Diagnosis'
                    ],
                    [
                        'option_value' => 'SNOMED',
                        'option_label' => 'SNOMED Diagnosis'
                    ],
                    [
                        'option_value' => 'CPTII',
                        'option_label' => 'CPTII Performance Measures'
                    ],
                    [
                        'option_value' => 'ICD9-SG',
                        'option_label' => 'ICD9 Procedure/Service'
                    ],
                    [
                        'option_value' => 'ICD10-PCS',
                        'option_label' => 'ICD10 Procedure/Service'
                    ],
                    [
                        'option_value' => 'SNOMED-CT',
                        'option_label' => 'SNOMED Clinical Term'
                    ],
                    [
                        'option_value' => 'SNOMED-PR',
                        'option_label' => 'SNOMED Procedure'
                    ],
                    [
                        'option_value' => 'RXCUI',
                        'option_label' => 'RXCUI Medication'
                    ],
                    [
                        'option_value' => 'LOINC',
                        'option_label' => 'LOINC'
                    ],
                    [
                        'option_value' => 'PHIN Questions',
                        'option_label' => 'PHIN Questions'
                    ],
                    [
                        'option_value' => 'NCI-CONCEPT-ID',
                        'option_label' => 'NCI CONCEPT ID'
                    ],
                    [
                        'option_value' => 'VALUESET',
                        'option_label' => 'CQM Valueset'
                    ],
                    [
                        'option_value' => 'OID',
                        'option_label' => 'OID Valueset'
                    ]
                ],
            ],
        ];
    }
}
