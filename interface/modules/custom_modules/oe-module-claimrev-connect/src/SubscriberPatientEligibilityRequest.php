<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

class SubscriberPatientEligibilityRequest
{
    public $firstName;//
    public $lastName;//
    public $middleName;//
    public $suffix;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $dateOfBirth;//
    public $gender;//
    public $memberId;//

    public function __construct()
    {
    }
}
