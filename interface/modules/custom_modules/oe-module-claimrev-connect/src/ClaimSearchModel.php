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

class ClaimSearchModel
{
    public string $objectId = "";
    public string $patientFirstName = "";
    public string $patientLastName = "";
    public string $patientGender = "";
    public ?string $patientBirthDate = null;
    public ?string $receivedDateStart = null;
    public ?string $receivedDateEnd = null;
    public ?string $serviceDateStart = null;
    public ?string $serviceDateEnd = null;
    public string $payerName = "";
    public string $payerNumber = "";
    public ?float $payerPaidAmtStart = null;
    public ?float $payerPaidAmtEnd = null;
    public string $traceNumber = "";
    /** @var list<string> */
    public array $traceNumbers = [];
    public string $patientControlNumber = "";
    /** @var list<string> */
    public array $patientControlNumbers = [];
    public string $payerControlNumber = "";
    /** @var list<string> */
    public array $payerControlNumbers = [];
    public string $billingProviderNpi = "";
    public string $errorMessage = "";
    /** @var list<int> */
    public array $statusIds = [];
    /** @var list<string> */
    public array $accountNumbers = [];
    /** @var list<int> */
    public array $claimTypeIds = [];
    /** @var list<int> */
    public array $excludeStatusIds = [];
    /** @var list<int> */
    public array $paymentAdviceStatusIds = [];
    /** @var list<array{fieldName: string, sortDirection: int, priority: int}> */
    public array $sorting = [];
    /** @var list<int> */
    public array $tagIds = [];
    /** @var list<int> */
    public array $excludeTagIds = [];
    /** @var list<string> */
    public array $eraClassifications = [];
    public PagingSearchModel $pagingSearch;

    public function __construct()
    {
        $this->pagingSearch = new PagingSearchModel();
    }
}
