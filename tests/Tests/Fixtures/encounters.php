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
    'facility_id' =>
    [
      'table' => 'facility',
      'columnSearch' => 'name',
      'columnSearchValue' => 'test-fixture-Your Clinic Name Here',
      'columnReference' => 'id',
    ],
    'facility' => 'test-fixture-Your Clinic Name Here',
    'billing_facility' => '',
    'pc_catid' =>
    [
      'table' => 'openemr_postcalendar_categories',
      'columnSearch' => 'pc_constant_id',
      'columnSearchValue' => 'office_visit',
      'columnReference' => 'pc_catid',
    ],
    'uuid' => 'uuid(\'form_encounter\')',
    'encounter' => 'generateId()',
    'sensitivity' => 'normal',
    'reason' => 'test-fixture-Complains of nausea, loose stools and weakness.',
    'last_level_billed' => 0,
    'last_level_closed' => 0,
    'stmt_count' => 0,
    'invoice_refno' => '',
    'referral_source' => '',
    'class_code' => 'AMB',
    'shift' => '',
    'voucher_number' => '',
    ],
];
