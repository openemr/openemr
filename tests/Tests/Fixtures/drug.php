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
    'name' => 'test-fixture-Ibuprofen',
    'ndc_number' => '12345678901',
    'form' => 'tablet',
    'size' => '200',
    'unit' => 'mg',
    'route' => 'oral',
    'related_code' => 'ibuprofen',
    'active' => '1',
    'drug_code' => NULL,
    'uuid' => 'uuid(\'drugs\')',
    ],
    [
    'name' => 'test-fixture-Acetaminophen',
    'ndc_number' => '12345678902',
    'form' => 'tablet',
    'size' => '500',
    'unit' => 'mg',
    'route' => 'oral',
    'related_code' => 'acetaminophen',
    'active' => '1',
    'drug_code' => NULL,
    'uuid' => 'uuid(\'drugs\')',
    ]
];