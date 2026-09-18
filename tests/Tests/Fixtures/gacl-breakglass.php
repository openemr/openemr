<?php

/**
 * Test fixture data.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/** @return array<string, array<string, mixed>[]> */
return [
  'gacl_aro' =>
  [
        [
      'id' => 9001,
      'value' => 'testbreakglassuser',
      'name' => 'Test Breakglass User',
        ],
  ],
  'gacl_groups_aro_map' =>
  [
        [
      'group_id' => 16,
      'aro_id' => 9001,
        ],
  ],
];
