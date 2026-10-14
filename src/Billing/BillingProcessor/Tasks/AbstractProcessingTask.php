<?php

/**
 * This class represents the abstract implementation of ProcessingTaskInterface
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\FacilityZipDenial;

abstract class AbstractProcessingTask
{
    /**
     * Claim version this run inserted while the hold is on.
     */
    protected ?int $insertedClaimVersion = null;

    public function __construct(protected $action)
    {
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * Mark claim as 'billed' available to all children of
     * AbstractProcessingTask
     *
     * @param BillingClaim $claim
     * @return mixed
     */
    public function clearClaim(BillingClaim $claim)
    {
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            2
        ); // $sql .= " billed = 1, ";
        return $tmp;
    }

    /**
     * Insert or update the claims row for this run.
     *
     * An insert returns the version number that was stored. Pass that
     * version back in when a later update must change the same row.
     */
    protected function writeClaimRow(
        mixed $newversion,
        mixed $patientId,
        mixed $encounterId,
        mixed $payerId = -1,
        mixed $payerType = -1,
        mixed $status = -1,
        mixed $billProcess = -1,
        string $processFile = '',
        string $target = '',
        mixed $partnerId = -1,
        ?int $claimVersion = null
    ): mixed {
        return BillingUtilities::updateClaim(
            $newversion,
            $patientId,
            $encounterId,
            $payerId,
            $payerType,
            $status,
            $billProcess,
            $processFile,
            $target,
            $partnerId,
            0,
            '',
            $claimVersion
        );
    }

    /**
     * Positive int from a claim write that stored or updated a row.
     * Zero and any other result did not land.
     */
    protected function landedClaimWrite(mixed $result): ?int
    {
        if (!is_int($result) || $result <= 0) {
            return null;
        }

        return $result;
    }

    /**
     * Whether this claim's segments belong in the batch.
     *
     * With the hold on, a ZIP denial stays out, and an accepted claim
     * stays out unless its billed update landed. Hold off still sends.
     */
    protected function claimEntersBatch(
        bool $hold,
        FacilityZipDenial $denial,
        bool $billIfAccepted,
        bool $billedWriteLanded
    ): bool {
        if ($hold && $denial->willDeny()) {
            return false;
        }

        if ($hold && $billIfAccepted) {
            return $billedWriteLanded;
        }

        return true;
    }
}
