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

class RevenueToolsRequest
{
    // Required for this module, does not go to claimrev
    public ?string $payerResponsibility = null;

    // Defaults that may be set as global vars
    public ?string $accountNumber = null;
    /** @var list<string>|null */
    public ?array $serviceTypeCodes = null;
    public ?bool $includeCredit = null;
    /** @var list<int>|null */
    public ?array $productsToRun = null;

    public ?string $practiceName = null;
    public ?string $requestingSoftware = null;

    public ?string $originatingSystemId = null;

    public ?string $npi = null;
    public ?string $pinCode = null;
    public ?string $practiceState = null;
    public ?string $subscriberId = null;
    public ?string $patientFirstName = null;
    public ?string $patientLastName = null;
    public ?string $patientGender = null;
    public ?string $patientDob = null;
    public ?string $patientSsn = null;
    public ?string $patientAddress1 = null;
    public ?string $patientAddress2 = null;
    public ?string $patientCity = null;
    public ?string $patientState = null;
    public ?string $patientZip = null;
    public ?string $patientEmailAddress = null;
    /** @var list<RevenueToolsPayer>|null */
    public ?array $payers = null;

    public ?string $subscriberFirstName = null;
    public ?string $subscriberLastName = null;
    public ?string $subscriberDob = null;

    public ?string $serviceBeginDate = null;
    public ?string $serviceEndDate = null;
    /** @var list<string>|null */
    public ?array $procedureCodes = null;
    /** @var list<string>|null */
    public ?array $diagnosisCodes = null;
    public ?string $clientId = null;
    public ?string $createDate = null;
    public ?string $uniqueTransactionNumber = null;
    public ?string $patientEmployer = null;
    public ?string $patientEmployerState = null;

    public function __construct()
    {
    }
}
