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
    'pid' =>
    [
      'table' => 'patient_data',
      'columnSearch' => 'pubpid',
      'columnSearchValue' => 'test-fixture-789456',
      'columnReference' => 'pid',
    ],
    'encounter' =>
    [
      'table' => 'form_encounter',
      'columnSearch' => 'reason',
      'columnSearchValue' => 'test-fixture-Complains of nausea, loose stools and weakness.',
      'columnReference' => 'encounter',
    ],
    'user' =>
    [
      'table' => 'users',
      'columnSearch' => 'id',
      'columnSearchValue' => '1',
      'columnReference' => 'username',
    ],
    'id' => 'generateId()',
    'groupname' => 'Default',
    'authorized' => 1,
    'activity' => 1,
    'code' => 'SNOMED-CT:168731009',
    'codetext' => 'Standard chest x-ray',
    'description' => 'test-fixture-xray',
    'care_plan_type' => 'plan_of_care',
    ],
];
