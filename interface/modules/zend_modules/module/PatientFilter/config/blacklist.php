<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * This is the data structure for creating a blacklist that restricts certain users' access to certain patients
 *
 * @package PatientFilter
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
return [
    [
        'username' => 'admin', // The username of user to restrict
        'blacklist' => [1, 3] // The list of pids that this user should not have access to
    ],
    [
        'username' => 'doctor99',
        'blacklist' => [2, 3]
    ],
];
