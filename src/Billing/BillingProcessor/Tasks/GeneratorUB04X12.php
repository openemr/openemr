<?php

/**
 * This class represents the task that compiles claims into a UB04 X12 batch file
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\X125010837I;

require_once __DIR__ . '/../../../../interface/billing/ub04_dispose.php';

class GeneratorUB04X12 extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    // These two are specific to UB04
    protected $template = array();
    protected $ub04id = array();

    protected $batch;

    protected function updateBatch(BillingClaim $claim)
    {
        // Do the UB04 processing
        $log = '';
        $segs = explode(
            "~\n",
            X125010837I::generateX12837I(
                $claim->getPid(),
                $claim->getEncounter(),
                $claim->getPartner(),
                $log,
                $this->ub04id
            )
        );
        $this->appendToLog($log);
        $this->batch->append_claim($segs);

        // Store the claims that are in this claims batch, because
        // if remote SFTP is enabled, we'll need the x12 partner ID to look up SFTP credentials
        $this->batch->addClaim($claim);
    }

    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.txt');

        // This was called at top of old billing_process.php so call in setup()
        ub04_dispose();
    }

    public function validateOnly(BillingClaim $claim)
    {
        $this->printToScreen(xl("Successfully validated claim") . ": " . $claim->getId());
        $this->ub04id = get_ub04_array($claim->getPid(), $claim->getEncounter());
        return $this->updateBatch($claim);
    }

    public function validateAndClear(BillingClaim $claim)
    {
        $this->ub04id = get_ub04_array($claim->getPid(), $claim->getEncounter());
        $ub_save = json_encode($this->ub04id);
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            '',
            $claim->getTarget(),
            $claim->getPartner() . '-837I',
            0,
            $ub_save
        );

        return $this->updateBatch($claim);
    }

    /**
     * In running the 'normal' action, this method is called
     * by AbstractGenerator's execute() method.
     *
     * It marks the claim as billed and writes batch filename to
     * the billing table.
     *
     * @param BillingClaim $claim
     */
    public function generate(BillingClaim $claim)
    {
        $this->validateAndClear($claim);

        $tmp = BillingUtilities::updateClaim(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            -1,
            -1,
            2,
            2,
            $this->batch->getBatFilename(),
            'X12-837I',
            -1,
            0,
            json_encode($this->ub04id)
        );

        // If we had an error, print to screen
        if (!$tmp) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    public function completeToScreen(array $context)
    {
        $this->batch->append_claim_close();

        $format_bat = str_replace('~', PHP_EOL, $this->batch->getBatContent());
        $wrap = "<!DOCTYPE html><html><head></head><body class='bg-light text-dark'><div class='bg-light text-dark' style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
        echo $wrap;
    }

    public function completeToFile(array $context)
    {
        $this->batch->append_claim_close();
        $success = $this->batch->write_batch_file();
        if ($success) {
            $this->printToScreen(xl('X-12 Generated Successfully'));
        } else {
            $this->printToScreen(xl('Error Generating Batch File'));
        }

        // Tell the billing_process.php script to initiate a download of this file
        // that's in the edi directory
        $this->logger->setLogCompleteCallback(function () {
            // This uses our parent's method to print the JS that automatically initiates
            // the download of this file, after the screen bill_log messages have printed
            $this->printDownloadClaimFileJS($this->batch->getBatFilename());
        });
    }
}
