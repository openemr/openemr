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
    'type' => 'allergy',
    'title' => 'Ampicillin',
    'pid' => 'test-fixture-789456',
    'reaction' => 'hives',
    'verification' => 'unconfirmed',
    'diagnosis' => 'RXCUI:7980',
    'occurrence' => 1,
    'begdate' => '1980-05-10',
    ],
    [
    'type' => 'allergy',
    'title' => 'Penicillin G',
    'pid' => 'test-fixture-8',
    'reaction' => 'hives',
    'verification' => 'confirmed',
    'diagnosis' => 'RXCUI:733',
    'occurrence' => 1,
    'begdate' => '1980-05-10',
    ],
];
