<?php

/**
 * This class represents the task that compiles claims into a UB04 PDF
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;

require_once __DIR__ . '/../../../../interface/billing/ub04_dispose.php';

class GeneratorUB04Form_PDF extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    // These two are specific to UB04
    protected $template = array();
    protected $ub04id = array();
    protected $batch;

    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.pdf');

        // This was called at top of old billing_process.php so call in setup()
        ub04_dispose();
    }

    /**
     * Called on each claim in the claim file loop
     *
     * @param BillingClaim $claim
     */
    public function generate(BillingClaim $claim)
    {
        $this->ub04id = get_ub04_array($claim->getPid(), $claim->getEncounter());
        $ub_save = json_encode($this->ub04id);
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            2,
            1,
            '',
            'ub04',
            -1,
            0,
            $ub_save
        );

        $log = "";
        $this->template[] = buildTemplate($claim->getPid(), $claim->getEncounter(), "", "", $log);
        $this->appendToLog($log);

        if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $this->batch->getBatFilename(), 'ub04', -1, 0, json_encode($this->ub04id))) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    /**
     * Called after claim file loop
     *
     * @param array $context
     */
    public function completeToFile(array $context)
    {
        ub04Dispose('download', $this->template, $this->batch->getBatFilename(), 'form');
        exit();
    }
}
