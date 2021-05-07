<?php

/**
 * This implementation of processing task re-opens all claims
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\ProcessingTaskInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;

class TaskReopen extends AbstractProcessingTask implements ProcessingTaskInterface, LoggerInterface
{
    use WritesToBillingLog;

    public function setup(array $context)
    {
        // nothing to do
    }

    public function execute(BillingClaim $claim)
    {
        $this->printToScreen("Opening claim");
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            1,
            0 // Set 'billed' flag to '0' to re-open claim
        );
        return $tmp;
    }

    public function complete(array $context)
    {
        // nothing to do
    }
}
