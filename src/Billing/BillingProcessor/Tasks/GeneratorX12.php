<?php

/**
 * This class represents the task that compiles claims into an X12 batch file
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\FacilityZipDenial;
use OpenEMR\Billing\UnbilledFileDecision;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Core\OEGlobalsBag;

class GeneratorX12 extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * @var BillingClaimBatch
     */
    protected $batch;

    /**
     * True when the global held this claim out of the batch.
     */
    private bool $claimHeld = false;

    /**
     * @param mixed $action
     * @param bool $encounter_claim If "Allow Encounter Claims" is enabled, this allows the claims to use the alternate payor ID on the claim and sets the claims to report, not chargeable. ie: RP = reporting, CH = chargeable
     */
    public function __construct(
        $action,
        protected $encounter_claim = false
    ) {
        parent::__construct($action);
    }

    /**
     * This function is called for both validation and claim file generation.
     *
     * It calls the x-12 formatting function to format the individual claim
     * and appends the claim to the batch file.
     *
     * @param BillingClaim $claim
     */
    protected function updateBatchFile(BillingClaim $claim, bool $billIfAccepted = false)
    {
        $this->claimHeld = false;
        $hold = $this->holdClaimsThatWillDeny();
        if ($hold && $billIfAccepted) {
            $this->bindSelectedPayer($claim);
        }
        [$log, $segs, $denial] = $this->renderedClaim($claim);
        $this->appendToLog($log);
        if ($hold && $billIfAccepted && !$denial->willDeny() && $this->billWhenTheFileLands) {
            if (!$this->holdGeneration($claim)) {
                $this->printToScreen(xl(UnbilledFileDecision::STILL_BEING_WRITTEN));
                $this->claimHeld = true;

                return;
            }
            $previous = $this->previousFileDecision($claim, $this->batch);
            if ($previous === UnbilledFileDecision::Present) {
                $sentence = $this->markStoredFileBilled($claim)
                    ? UnbilledFileDecision::ALREADY_WRITTEN
                    : FacilityZipDenial::LEFT_OUT_NOT_BILLED;
                $this->printToScreen(xl($sentence));
                $this->claimHeld = true;

                return;
            }
            if ($previous === UnbilledFileDecision::Busy) {
                $this->printToScreen(xl(UnbilledFileDecision::STILL_BEING_WRITTEN));
                $this->claimHeld = true;

                return;
            }
            if ($previous === UnbilledFileDecision::Missing) {
                $this->printToScreen(xl(UnbilledFileDecision::FILE_WAS_MISSING));
            }
        }
        // A denial stores no version. The next run stores one only if the ZIP is accepted.
        $insertLanded = true;
        if ($hold && $billIfAccepted && !$denial->willDeny()) {
            $insertLanded = $this->rememberPayer($claim);
        }
        $billedWriteLanded = true;
        if ($insertLanded && $billIfAccepted && !$denial->willDeny()) {
            // The file name is stored while the row stays unbilled. The billed
            // update runs after that file is written.
            $billedWriteLanded = $this->billWhenTheFileLands
                ? $this->noteClaimFile($claim, $this->batch)
                : $this->markBilledExisting($claim);
        }
        if (!$insertLanded || !$this->claimEntersBatch($hold, $denial, $billIfAccepted, $billedWriteLanded)) {
            if ($hold && $denial->willDeny()) {
                $this->printDenialHold($denial);
            } elseif (!$insertLanded) {
                $this->printToScreen(xl(FacilityZipDenial::LEFT_OUT_NOT_SAVED));
            } else {
                $this->printToScreen(xl(FacilityZipDenial::LEFT_OUT_NOT_BILLED));
            }
            $this->claimHeld = true;
            return;
        }

        $this->batch->append_claim($segs);

        // Store the claims that are in this claims batch, because
        // if remote SFTP is enabled, we'll need the x12 partner ID to look up SFTP credentials
        $this->batch->addClaim($claim);
    }

    /**
     * X12 segments for one claim, plus the ZIP flags the hold reads.
     * The log is the same string genX12837P appends to.
     *
     * @return array{string, non-empty-list<string>, FacilityZipDenial}
     */
    protected function renderedClaim(BillingClaim $claim): array
    {
        $log = 'X12 ' . $claim->action . ' ';
        $hlCount = 1;
        $edicount = 0;
        $patSegmentCount = 0;
        $denial = new FacilityZipDenial();
        $text = (string) X125010837P::genX12837P(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPartner(),
            $log,
            $this->encounter_claim,
            false,
            $hlCount,
            $edicount,
            $patSegmentCount,
            $denial
        );
        assert($denial instanceof FacilityZipDenial);
        if (!is_string($log)) {
            $log = 'X12 ';
        }

        return [$log, explode("~\n", $text), $denial];
    }

    /**
     * The opt-in hold for a claim whose facility ZIP will deny. Off still sends the claim and writes the log.
     */
    protected function holdClaimsThatWillDeny(): bool
    {
        return OEGlobalsBag::getInstance()->getBoolean('gbl_hold_claims_that_will_deny', false);
    }

    /**
     * Screen text for a claim left out of the batch. Same sentences as the log.
     */
    protected function printDenialHold(FacilityZipDenial $denial): void
    {
        if ($denial->billing) {
            $this->printToScreen(xl(X125010837P::BILLING_ZIP_LOG));
        }
        if ($denial->service) {
            $this->printToScreen(xl(X125010837P::SERVICE_ZIP_LOG));
        }
    }

    /**
     * Store an accepted claim and leave that row unbilled.
     *
     * Called after the ZIP check. A denial does not store a version. An
     * unbilled version from a failed billed update is kept and billed on
     * the next run, instead of inserting another. False means this run
     * has no version to bill.
     */
    protected function rememberPayer(BillingClaim $claim): bool
    {
        $existing = $this->openUnbilledVersion($claim);
        if ($existing !== null) {
            $this->insertedClaimVersion = $existing;

            return true;
        }

        $version = $this->landedClaimWrite($this->writeClaimRow(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_LEAVE_UNBILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            '',
            $claim->getTarget(),
            $claim->getPartner()
        ));
        $this->insertedClaimVersion = $version;

        return $version !== null;
    }

    /**
     * Master's first updateClaim: a new claim row, marked billed.
     */
    protected function markBilledNew(BillingClaim $claim): void
    {
        $this->writeClaimRow(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            '',
            $claim->getTarget(),
            $claim->getPartner()
        );
    }

    /**
     * Mark the unbilled row from rememberPayer() as billed. Not a second claim version.
     */
    protected function markBilledExisting(BillingClaim $claim): bool
    {
        return $this->landedClaimWrite($this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            '',
            $claim->getTarget(),
            $claim->getPartner(),
            $this->insertedClaimVersion
        )) !== null;
    }

    /**
     * This function is called before main claim loop to set up this
     * generator object.
     *
     * @param array $context
     */
    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.txt', $context);
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
        if ($this->claimHeld) {
            return;
        }
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
        $this->billWhenTheFileLands = false;
        $this->insertedClaimVersion = null;
        $billIfAccepted = false;
        if ($this->holdClaimsThatWillDeny()) {
            $billIfAccepted = true;
        } else {
            $this->markBilledNew($claim);
        }

        // Update the batch file content with this claim's data
        $this->updateBatchFile($claim, $billIfAccepted);
        if ($this->claimHeld) {
            return;
        }
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
        $this->insertedClaimVersion = null;
        $this->billWhenTheFileLands = $this->holdClaimsThatWillDeny();
        $billIfAccepted = false;
        if ($this->holdClaimsThatWillDeny()) {
            $billIfAccepted = true;
        } else {
            $this->markBilledNew($claim);
        }

        // Update the batch file content with this claim's data
        $this->updateBatchFile($claim, $billIfAccepted);
        if ($this->claimHeld || $this->billWhenTheFileLands) {
            return;
        }

        // After we save the claim, update it with the filename (don't create a new revision)
        $updated = $this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            -1,
            -1,
            2,
            2,
            $this->batch->getBatFilename(),
            '',
            -1,
            $this->insertedClaimVersion
        );
        if (!$updated) {
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
        if ($this->batch->getClaims() === []) {
            $this->printToScreen(xl('No claims were added to the batch.'));
            return;
        }

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
        try {
            $this->completeHeldBatchToFile();
        } finally {
            $this->releaseGenerationFences();
        }
    }

    /**
     * Write the batch after the claims still name its file.
     */
    private function completeHeldBatchToFile(): void
    {
        if ($this->batch->getClaims() === []) {
            $this->printToScreen(xl('No claim file was written.'));
            return;
        }

        $this->batch->append_claim_close();
        $filename = $this->batch->getBatFilename();
        $this->batch->requireGenerationOwner(
            fn (string $phase): bool => $phase !== '' && $this->awaitingFileStillOwned($filename)
        );
        $success = $this->storeBatchFile($this->batch);
        if ($this->awaitingFile !== []) {
            if ($success && $this->claimFileLanded($this->batch, $filename)) {
                $this->billAwaitingFile($filename);
            } else {
                $this->releaseAwaitingFile($filename);
                $success = false;
            }
        }
        if ($success) {
            $this->printToScreen(xl('X-12 Generated Successfully'));
        } else {
            $this->printToScreen(xl('Error Generating Batch File'));
            return;
        }

        // Tell the billing_process.php script to initiate a download of this file
        // that's in the edi directory unless it's going to be sent via sftp
        if ($this->logger !== null && !OEGlobalsBag::getInstance()->getBoolean('auto_sftp_claims_to_x12_partner')) {
            $this->logger->setLogCompleteCallback(function (): void {
                // This uses our parent's method to print the JS that automatically initiates
                // the download of this file, after the screen bill_log messages have printed
                $this->printDownloadClaimFileJS($this->batch->getBatFilename());
            });
        }
    }

    /**
     * Write the batch. Tests replace this so they do not create an edi file.
     */
    protected function storeBatchFile(BillingClaimBatch $batch): bool
    {
        return (bool) $batch->write_batch_file();
    }
}
