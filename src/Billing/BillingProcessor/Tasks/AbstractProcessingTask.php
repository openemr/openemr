<?php

/**
 * This class represents the abstract implementation of ProcessingTaskInterface
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingUtilities;

abstract class AbstractProcessingTask
{
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
}
