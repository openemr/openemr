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
    'foreign_table' => 'patient_data',
    'foreign_id' =>
    [
      'table' => 'patient_data',
      'columnSearch' => 'pubpid',
      'columnSearchValue' => 'test-fixture-789456',
      'columnReference' => 'pid',
    ],
    ],
    [
    'foreign_table' => 'patient_data',
    'foreign_id' =>
    [
      'table' => 'patient_data',
      'columnSearch' => 'pubpid',
      'columnSearchValue' => 'test-fixture-8',
      'columnReference' => 'pid',
    ],
    ],
];
