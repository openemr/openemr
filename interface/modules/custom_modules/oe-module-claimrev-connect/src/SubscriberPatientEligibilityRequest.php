<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    namespace OpenEMR\Modules\ClaimRevConnector;

class SubscriberPatientEligibilityRequest
{
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $middleName = null;
    public ?string $suffix = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;
    public ?string $dateOfBirth = null;
    public ?string $gender = null;
    public ?string $memberId = null;

    public function __construct()
    {
    }
}
