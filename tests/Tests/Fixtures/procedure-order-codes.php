<?php

/**
 * Test fixture data.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/** @return array<string, mixed>[] */
return [
    [
        'procedure_order_seq' => 1,
        'procedure_order_id' => null,
        'procedure_code' => '80053',
        'procedure_name' => 'Comprehensive Metabolic Panel',
        'procedure_source' => '1',
        'diagnoses' => 'ICD10:E78.5',
        'do_not_send' => 0,
        'procedure_order_title' => 'CMP',
    ],
    [
        'procedure_order_seq' => 2,
        'procedure_order_id' => null,
        'procedure_code' => '85025',
        'procedure_name' => 'Complete Blood Count',
        'procedure_source' => '1',
        'diagnoses' => '',
        'do_not_send' => 0,
        'procedure_order_title' => 'CBC',
    ],
];
