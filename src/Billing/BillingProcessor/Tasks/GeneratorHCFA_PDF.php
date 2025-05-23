<?php

/**
 * This class represents the task that compiles claims into
 * a HCFA form batch. This prints the claim data only, with no
 * form fields (no background image) that are present on the HCFA 1500 paper form.
 *
 * The *other* HCFA generator will print the data over an image of
 * the paper form fields if enabled in globals.
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

class GeneratorHCFA_PDF extends AbstractGenerator implements
    GeneratorInterface,
    GeneratorCanValidateInterface,
    LoggerInterface
{
    use WritesToBillingLog;

    /**
     * Instance of the Cezpdf object for writing
     * @Cezpdf
     */
    protected $pdf;

    /**
     * Our billing claim batch for tracking the filename and other
     * generic claim batch things
     *
     * @BillingClaimBatch
     */
    protected $batch;

    /**
     * When we run the execute function on each claim, we don't want
     * to create a new page the first time. The instantiation of the PDF
     * object "comes with" a canvas to write to, so the first claim, we
     * don't need to create one. On subsequent claims, we do so we initialize
     * this to false, and then set to true after the first claim.
     *
     * @bool
     */
    protected $createNewPage;

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
        $post = $context['post'];
        $this->pdf = new \Cezpdf('LETTER');
        $this->pdf->ezSetMargins(trim($post['top_margin']) + 0, 0, trim($post['left_margin']) + 0, 0);
        $this->pdf->selectFont('Courier');

        // This is to tell our execute method not to create a new page the first claim
        $this->createNewPage = false;

        // Instantiate mainly for the filename creation, we're not tracking text segments
        // since we're generating a PDF, which is managed in this object
        $this->batch = new BillingClaimBatch('.pdf');
    }

    /**
     * Do the work to append the given claim to the PDF document we're
     * working on generating
     *
     * @param BillingClaim $claim
     */
    protected function updateBatch(BillingClaim $claim)
    {
        // Do the actual claim processing
        $log = 'HCFA PDF ' . $claim->action . ' ';
        $hcfa = new Hcfa1500();
        $lines = $hcfa->genHcfa1500($claim->getPid(), $claim->getEncounter(), $log);
        $this->appendToLog($log);
        $alines = explode("\014", $lines); // form feeds may separate pages
        foreach ($alines as $tmplines) {
            // The first claim we don't create a new page.
            if ($this->createNewPage) {
                $this->pdf->ezNewPage();
            } else {
                $this->createNewPage = true;
            }
            $this->pdf->ezSetY($this->pdf->ez['pageHeight'] - $this->pdf->ez['topMargin']);
            $this->pdf->ezText($tmplines, 12, array(
                'justification' => 'left',
                'leading' => 12
            ));
        }
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
     * When user chooses "validate only" we just build the PDF
     *
     * @param BillingClaim $claim
     */
    public function validateOnly(BillingClaim $claim)
    {
        $this->updateBatch($claim);
        $this->printToScreen(xl("Successfully validated claim") . ": " . $claim->getId());
    }

    /**
     * When the user chooses "validate and clear" we build the PDF
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
        // If we are just validating, make a temp file
        $tmp_claim_file = $GLOBALS['temporary_files_dir'] .
            DIRECTORY_SEPARATOR .
            $this->batch->getBatFilename();
        file_put_contents($tmp_claim_file, $this->pdf->ezOutput());

        // If we are just validating, the output should be a PDF presented
        // to the user, but we don't save to the edi/ directory.
        // This just writes to a tmp file, serves to user and then removes tmp file
        $this->logger->setLogCompleteCallback(function () {
            // This is the callback function passed to the logger, called when the
            // result screen is finished rendering. This prints some JS that will
            // start the download of the 'temporary' HCFA pdf after messages have been printed to the
            // screen. The delete flag tells get_claim_file.php endpoint to delete the file after
            // download. The location string tells get_claim_file.php that the file is in
            // the globally-configured tmp directory.
            $this->printDownloadClaimFileJS($this->batch->getBatFilename(), 'tmp', true);
        });
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
        // If a writable edi directory exists (and it should), write the pdf to it.
        $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/{$this->batch->getBatFilename()}", 'a');
        if ($fh) {
            fwrite($fh, $this->pdf->ezOutput());
            fclose($fh);
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
