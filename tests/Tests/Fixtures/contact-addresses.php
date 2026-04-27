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
    'address_id' =>
    [
      'table' => 'addresses',
      'columnSearch' => 'line1',
      'columnSearchValue' => '72 Rolling Green Ave',
      'columnReference' => 'id',
    ],
    'contact_id' =>
    [
      'table' => 'contacts',
      'columnSearch' => 'pid',
      'columnSearchValue' =>
      [
        'table' => 'patient_data',
        'columnSearch' => 'pubpid',
        'columnSearchValue' => 'test-fixture-789456',
        'columnReference' => 'pid',
      ],
      'columnReference' => 'id',
    ],
    'priority' => 0,
    'type' => 'physical',
    'use' => 'home',
    'status' => 'A',
    'is_primary' => 'Y',
    'period_start' => '2022-01-01 00:00:00',
    'period_end' => NULL,
    'inactivated_reason' => NULL,
    ],
    [
    'address_id' =>
    [
      'table' => 'addresses',
      'columnSearch' => 'line1',
      'columnSearchValue' => '72 Rolling Green Ave',
      'columnReference' => 'id',
    ],
    'contact_id' =>
    [
      'table' => 'contacts',
      'columnSearch' => 'pid',
      'columnSearchValue' =>
      [
        'table' => 'patient_data',
        'columnSearch' => 'pubpid',
        'columnSearchValue' => 'test-fixture-8',
        'columnReference' => 'pid',
      ],
      'columnReference' => 'id',
    ],
    'priority' => 0,
    'type' => 'physical',
    'use' => 'home',
    'status' => 'A',
    'is_primary' => 'Y',
    'period_start' => '2022-01-01 00:00:00',
    'period_end' => NULL,
    'inactivated_reason' => NULL,
    ],
];
