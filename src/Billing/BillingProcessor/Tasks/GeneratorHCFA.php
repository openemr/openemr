<?php

/**
 * This class represents the task that compiles claims into
 * a HCFA form batch. This prints the claim data only, in text form, with no
 * form fields (no background image) that are present on the HCFA 1500 paper form.
 *
 * The *other* HCFA generators will print PDFs of the data either over an image of
 * the paper form fields or not if enabled in globals.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\Hcfa1500;

class GeneratorHCFA extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * Our billing claim batch for tracking the filename and other
     * generic claim batch things
     *
     * @var BillingClaimBatch
     */
    protected $batch;


    /**
     * This function is called by the BillingProcessor before the main
     * claim loop starts.
     *
     * Here we set up our PDF canvas and our batch file.
     *
     * @param array $context
     */
    public function setup(array $context)
    {
        // This is to tell our execute method not to create a new page the first claim
        $this->createNewPage = false;

        // Instantiate mainly for the filename creation
        $this->batch = new BillingClaimBatch('.txt');
    }

    /**
     * Do the work to append the given claim to the document we're
     * working on generating
     *
     * @param BillingClaim $claim
     */
    protected function updateBatchFile(BillingClaim $claim)
    {
        // Do the actual claim processing
        $log = 'HCFA ' . $claim->action . ' ';
        $hcfa = new Hcfa1500();
        $lines = $hcfa->genHcfa1500($claim->getPid(), $claim->getEncounter(), $log);
        $this->appendToLog($log);
        $this->batch->append_claim($lines, true);
    }

    /**
     *
     * Do validation, mark claim as billed and tell the billing table
     * that the claim will be written to the given batch file name
     *
     * @param BillingClaim $claim
     */
    public function generate(BillingClaim $claim)
    {
        // Validate and mark as 'billed'
        $this->validateAndClear($claim);

        // Finalize the claim
        if (
            !BillingUtilities::updateClaim(
                false,
                $claim->getPid(),
                $claim->getEncounter(),
                -1,
                -1,
                2,
                2,
                $this->batch->getBatFilename()
            )
        ) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        } else {
            $this->printToScreen(xl("Successfully processed claim") . ": " . $claim->getId());
        }
    }

    /**
     * When user chooses "validate only" we just build the text
     *
     * @param BillingClaim $claim
     */
    public function validateOnly(BillingClaim $claim)
    {
        $this->updateBatchFile($claim);
        $this->printToScreen(xl("Successfully validated claim") . ": " . $claim->getId());
    }

    /**
     * When the user chooses "validate and clear" we build the text
     * and mark the claim as 'billed'
     *
     * @param BillingClaim $claim
     */
    public function validateAndClear(BillingClaim $claim)
    {
        $this->validateOnly($claim);

        // This is a validation pass
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED, // status == 2 means
            BillingClaim::BILL_PROCESS_IN_PROGRESS, // bill_process == 1 means??
            '', // process_file
            'hcfa'
        );

        $this->printToScreen(xl("Successfully marked claim") . ": " . $claim->getId() .  " " . xl("as billed"));
    }

    /**
     * This method is called when the user clicks "validate" or "validate and clear",
     * and writes the HCFA file to the temporary directory.
     *
     * @param array $context
     */
    public function completeToScreen(array $context)
    {
        // If we're validating only, or clearing and validating, don't write to our EDI directory
        // Just send to the browser in that case for the end-user to review.
        $format_bat = str_replace('~', PHP_EOL, $this->batch->getBatContent());
        $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
        echo $wrap;
    }

    /**
     * This method is called when the user clicks "continue" and initiates
     * 'normal' operation.
     *
     * Write the HCFA file to the edi directory.
     *
     * @param array $context
     */
    public function completeToFile(array $context)
    {
        $success = $this->batch->write_batch_file();
        if ($success) {
            $this->printToScreen(xl('HCFA Generated Successfully'));
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
