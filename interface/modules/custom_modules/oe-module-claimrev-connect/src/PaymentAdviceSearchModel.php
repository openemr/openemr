<?php

/**
 * Payment Advice search request model for ClaimRev API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

class PaymentAdviceSearchModel
{
    public ?string $receivedDateStart = null;
    public ?string $receivedDateEnd = null;
    public ?bool $isWorked = null;
    public ?string $patientFirstName = null;
    public ?string $patientLastName = null;
    public ?string $payerNumber = null;
    public ?string $patientControlNumber = null;
    public ?string $checkNumber = null;
    public ?string $serviceDateStart = null;
    public ?string $serviceDateEnd = null;
    public ?string $paymentAdviceId = null;
    /** @var list<int> */
    public array $queueIds = [];
    /** @var list<int> */
    public array $tagIds = [];
    public ?bool $hasFoundRcmClaim = null;
    public ?int $paymentStatusId = null;
    public ?bool $isProcessing = null;
    public PagingSearchModel $pagingSearch;

    public function __construct()
    {
        $this->pagingSearch = new PagingSearchModel();
    }
}
