<?php

/**
 * This class represents the task that compiles claims into an X12 batch file
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
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
use OpenEMR\Billing\X125010837P;

class GeneratorX12 extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * If "Allow Encounter Claims" is enabled, this allows the claims to use
     * the alternate payor ID on the claim and sets the claims to report,
     * not chargeable. ie: RP = reporting, CH = chargeable
     *
     * @var bool|mixed
     */
    protected $encounter_claim = false;

    /**
     * @var BillingClaimBatch
     */
    protected $batch;

    public function __construct($action, $encounter_claim = false)
    {
        parent::__construct($action);
        $this->encounter_claim = $encounter_claim;
    }

    /**
     * This function is called for both validation and claim file generation.
     *
     * It calls the x-12 formatting function to format the individual claim
     * and appends the claim to the batch file.
     *
     * @param BillingClaim $claim
     */
    protected function updateBatchFile(BillingClaim $claim)
    {
        // Generate the file
        $log = '';
        $segs = explode(
            "~\n",
            X125010837P::genX12837P(
                $claim->getPid(),
                $claim->getEncounter(),
                $claim->getPartner(),
                $log,
                $this->encounter_claim,
                false,
                1 // HLCount
            )
        );
        $this->appendToLog($log);
        $this->batch->append_claim($segs);

        // Store the claims that are in this claims batch, because
        // if remote SFTP is enabled, we'll need the x12 partner ID to look up SFTP credentials
        $this->batch->addClaim($claim);
    }

    /**
     * This function is called before main claim loop to set up this
     * generator object.
     *
     * @param array $context
     */
    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.txt');
    }

    /**
     * In running the validate-only action, this method is called
     * by AbstractGenerator's execute() method.
     *
     * @param BillingClaim $claim
     */
    public function validateOnly(BillingClaim $claim)
    {
        $this->updateBatchFile($claim);
        $this->printToScreen(xl("Successfully validated claim") . ": " . $claim->getId());
    }

    /**
     * In running the validate-and-clear action, this method is called
     * by AbstractGenerator's execute() method.
     *
     * It marks the claim as billed, but doesn't write the final
     * batch claim file to the edi directory.
     *
     * @param BillingClaim $claim
     */
    public function validateAndClear(BillingClaim $claim)
    {
        $return = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS, // bill_process == 1 means??
            '', // process_file
            $claim->getTarget(),
            $claim->getPartner()
        );

        // Update the batch file content with this claim's data
        $this->updateBatchFile($claim);
        $this->printToScreen(xl("Successfully marked claim") . ": " . $claim->getId() .  " " . xl("as billed"));
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
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS, // bill_process == 1 means??
            '', // process_file
            $claim->getTarget(),
            $claim->getPartner()
        );

        // Update the batch file content with this claim's data
        $this->updateBatchFile($claim);

        // After we save the claim, update it with the filename (don't create a new revision)
        if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $this->batch->getBatFilename())) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    /**
     * Complete the file and write formatted content to screen.
     *
     * In running the validate-only, or validate-and-clear action, this method is called
     * by AbstractGenerator's complete() method.
     *
     * @param array $context
     */
    public function completeToScreen(array $context)
    {
        $this->batch->append_claim_close();
        // If we're validating only, or clearing and validating, don't write to our EDI directory
        // Just send to the browser in that case for the end-user to review.
        $format_bat = str_replace('~', PHP_EOL, $this->batch->getBatContent());
        $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
        echo $wrap;
    }

    /**
     * Complete the file and write formatted content to the edi directory.
     *
     * When running 'normal' action, this method is called
     * by AbstractGenerator's complete() method.
     *
     * @param array $context
     */
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
        // that's in the edi directory unless it's going to be sent via sftp
        if (!$GLOBALS['auto_sftp_claims_to_x12_partner']) {
            $this->logger->setLogCompleteCallback(function () {
                // This uses our parent's method to print the JS that automatically initiates
                // the download of this file, after the screen bill_log messages have printed
                $this->printDownloadClaimFileJS($this->batch->getBatFilename());
            });
        }
    }
}
