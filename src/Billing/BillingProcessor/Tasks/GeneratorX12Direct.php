<?php

/**
 * This class represents the task that compiles claims into
 * x-12 batch files, one for each insurance/x-12 pair.
 *
 * This task will be run in favor of Task\GeneratorX12 if
 * the global is enabled "Generate X-12 Based On Insurance Company"
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
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
use OpenEMR\Billing\Claim;
use OpenEMR\Billing\FacilityZipDenial;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

class GeneratorX12Direct extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * An array of batches, one for each x-12 partner, indexed by partner id
     *
     * @var array
     */
    protected $x12_partner_batches = [];

    /**
     * An array of x-12 partners, indexed by partner id
     *
     * @var array
     */
    protected $x12_partners = [];

    /**
     * For each X12 partner, track edi counts
     * @var array
     */
    protected $edi_counts = [];

    /**
     * For each X12 partner, track patient segment counts
     * @var array
     */
    protected $pat_segment_counts = [];

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
     * In the direct-billing setup method, we need to make sure that
     * the directories are created for our x-12 partners because
     * we save one batch file for each z-12 partner.
     *
     * We also set up a BillingClaimBatch for each x-12 partner in case
     * we have any claims to write to them in this group of claims.
     *
     * @param $context
     */
    public function setup(array $context)
    {
        // We have to prepare our batches here
        // Get all of our x-12 partners and make sure we have
        // directories to write to for them
        $result = sqlStatement("SELECT * from x12_partners");
        while ($row = sqlFetchArray($result)) {
            $has_dir = true;
            if (!isset($row['x12_sftp_local_dir'])) {
                // Local Directory not set
                $has_dir = false;
                $this->printToScreen(xl("No directory for X12 partner " . $row['name']));
            } elseif (
                isset($row['x12_sftp_local_dir']) &&
                !is_dir($row['x12_sftp_local_dir'])
            ) {
                // If the local directory doesn't exist, attempt to create it
                $has_dir = mkdir($row['x12_sftp_local_dir'], '644', true);
                if (false === $has_dir) {
                    $this->printToScreen(xl("Could not create directory for X12 partner " . $row['name']));
                }
            }

            $batch = new BillingClaimBatch('.txt', $context);
            $filename = $batch->getBatFilename();
            $filename = str_replace('batch', 'batch-p' . $row['id'], $filename);
            $batch->setBatFilename($filename);

            // Only set the batch file directory if we have a valid directory
            if ($has_dir) {
                $batch->setBatFiledir($row['x12_sftp_local_dir']);
            }

            // Store the x-12 partner's data in case we need to reference it (like need the Name or something)
            $this->x12_partners[$row['id']] = $row;

            // We need to track the edi count for each x-12 partner, initialize them to zero here
            $this->edi_counts[$row['id']] = 0;

            // We need to track the patient segment count for each x-12 partner, initialize them to zero here
            $this->pat_segment_counts[$row['id']] = 0;

            // Store the directory in an associative array with the partner ID as the index
            $this->x12_partner_batches[$row['id']] = $batch;

            // Look through the claims and set is_last on each one that
            // is the last for this x-12 partner
            $lastClaim = null;
            foreach ($context['claims'] as $claim) {
                if ($claim->getPartner() === $row['id']) {
                    $lastClaim = $claim;
                }
            }
            if ($lastClaim !== null) {
                $lastClaim->setIsLast(true);
            }
        }
    }

    /**
     * In validate-only mode, we just build the batch and print to screen,
     * the claim remains unaltered in the database.
     *
     * @param BillingClaim $claim
     */
    public function validateOnly(BillingClaim $claim)
    {
        $batch = $this->updateBatchFile($claim);
        if (!$batch instanceof BillingClaimBatch) {
            return;
        }
        $this->printToScreen(xl("Successfully validated claim") . ": " . $claim->getId());
    }

    /**
     * In validate-and-clear mode, we mark the claim as 'billed'
     * and build the batch file.
     *
     * @param BillingClaim $claim
     * @return mixed
     */
    public function validateAndClear(BillingClaim $claim)
    {
        $this->insertedClaimVersion = null;
        $billIfAccepted = false;
        if ($this->holdClaimsThatWillDeny()) {
            if (!$this->rememberPayer($claim)) {
                $this->printToScreen(xl(FacilityZipDenial::LEFT_OUT_NOT_SAVED));
                return null;
            }
            $billIfAccepted = true;
        } else {
            $this->writeClaimRow(
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
        }

        // Return the batch we updated (depending on x-12 partner)
        return $this->updateBatchFile($claim, $billIfAccepted);
    }

    /**
     * This is the 'normal' mode, where we validate and clear each claim,
     * and also complete it and write the batch file to the database.
     *
     * @param BillingClaim $claim
     */
    public function generate(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Use the claim to update the appropriate batch file (depends on x-12 partner)
        // and return the batch we updated
        $batch = $this->validateAndClear($claim);
        if (!$batch instanceof BillingClaimBatch) {
            return;
        }

        $updated = $this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            -1,
            -1,
            2,
            2,
            $batch->getBatFilename(),
            '',
            -1,
            $this->insertedClaimVersion
        );
        if (!$updated) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    /**
     * This is where the batch formatting work happens on each claim. This generator
     * uses the TR3 format which has a different claim loop than the other
     * gen_x12 function.
     *
     * @param BillingClaim $claim
     * @return mixed
     */
    protected function updateBatchFile(BillingClaim $claim, bool $billIfAccepted = false)
    {
        // Get the correct batch file using the X-12 partner ID
        $batch = $this->x12_partner_batches[$claim->getPartner()];

        // Get the correct edi count for this x-12 partner using the partner ID
        $edicount = $this->edi_counts[$claim->getPartner()];

        // Get the correct patient segment count for this x-12 partner using the partner ID
        $patSegmentCount = $this->pat_segment_counts[$claim->getPartner()];
        $edicountBefore = $edicount;
        $patSegmentCountBefore = $patSegmentCount;

        // Off: add the claim before the 837 is built, which is the master order.
        // On: add it only after the ZIP is accepted, so a held claim is not in the batch.
        $hold = $this->holdClaimsThatWillDeny();
        if (!$hold) {
            $batch->addClaim($claim);
        }

        $log = 'X12Direct ' . $claim->action . ' ';
        $is_last_claim = $claim->getIsLast();
        $HLCount = count($batch->getClaims());
        if ($hold) {
            $HLCount++;
        }
        if ($HLCount > 1) {
            $idx = $HLCount - 2;
            $prior_claim = $batch->getClaims()[$idx];
            $priorX12ClaimSelfInsured = (
                new Claim(
                    $prior_claim->getPid(),
                    $prior_claim->getEncounter(),
                    $prior_claim->getPartner()
                )
                )->isSelfOfInsured($prior_claim->getPayorType() - 1);
            if (!$priorX12ClaimSelfInsured) {
                $patSegmentCount++;
            }
        }

        //$is_self_of_insured = $claim->isSelfOfInsured();
        $denial = new FacilityZipDenial();
        $segs = explode("~\n", (string) X125010837P::genX12837P(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPartner(),
            $log,
            $this->encounter_claim,
            $is_last_claim,
            $HLCount,
            $edicount,
            $patSegmentCount,
            $denial
        ));
        assert($denial instanceof FacilityZipDenial);
        $billedWriteLanded = true;
        if ($hold && $billIfAccepted && !$denial->willDeny()) {
            $billedWriteLanded = $this->markBilledExisting($claim);
        }
        $omit = !$this->claimEntersBatch($hold, $denial, $billIfAccepted, $billedWriteLanded);
        if ($omit) {
            $edicount = $edicountBefore;
            $patSegmentCount = $patSegmentCountBefore;
            if ($batch instanceof BillingClaimBatch && is_int($edicount) && $is_last_claim === true) {
                $edicount = $this->appendSeForHeldLastClaim($batch, $edicount);
            }
        }

        // edi count is passed by reference and incremented in the genX12837P function, and we need to set it back here
        $this->edi_counts[$claim->getPartner()] = $edicount;
        // also for the patient segment counts
        $this->pat_segment_counts[$claim->getPartner()] = $patSegmentCount;
        $this->appendToLog($log);
        if ($omit) {
            if ($denial->willDeny()) {
                $this->printDenialHold($denial);
            } else {
                $this->printToScreen(xl(FacilityZipDenial::LEFT_OUT_NOT_BILLED));
            }
            return null;
        }
        if ($hold && $batch instanceof BillingClaimBatch) {
            $batch->addClaim($claim);
        }
        $batch->append_claim($segs);

        return $batch;
    }

    /**
     * The held claim included the SE trailer, and those segments were discarded.
     * Claims already in the batch still need that trailer before GE and IEA.
     */
    protected function appendSeForHeldLastClaim(BillingClaimBatch $batch, int $segmentCount): int
    {
        if ($batch->getClaims() === [] || $batch->getBatContent() === '') {
            return $segmentCount;
        }

        $segmentCount++;
        $segments = ['SE*' . $segmentCount];
        $batch->append_claim($segments);

        return $segmentCount;
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
     * Store the payer before the 837 is built, and leave the claim unbilled.
     *
     * False means the insert did not land, so this run has no version to bill.
     */
    protected function rememberPayer(BillingClaim $claim): bool
    {
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
     * Complete the file and write formatted content to the edi directory.
     *
     * When running 'normal' action, this method is called
     * by AbstractGenerator's complete() method.
     *
     * We call finish with a closure that
     *
     * @param array $context
     */
    public function completeToFile(array $context)
    {
        $this->finish($context, function ($context): void {

            // Get the created_batches from the finish method
            $created_batches = $context['created_batches'];
            if (is_array($created_batches) && $created_batches === []) {
                $this->printToScreen(xl('No claim file was written.'));
                return;
            }

            // In the "normal" operation, we have written the batch files to disk above, and
            // need to build a presentation for the user to download them.
            $html = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'>";

            // If the global is enabled to SFTP claim files, tell the user
            if (OEGlobalsBag::getInstance()->getBoolean('auto_sftp_claims_to_x12_partner')) {
                $html .= "<div class='alert alert-primary' role='alert'>" . xlt("Sending Claims via STFP. Check status on the `Claim File Tracker`") . "</div>";
            }

            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            // Build the download URLs for our claim files so we can present them to the
            // user for download.
            $html .= "<ul class='list-group'>";
            foreach ($created_batches as $x12_partner_id => $created_batch) {
                // This is the final, validated claim, write to the edi location for this x12 partner
                $created_batch->write_batch_file($x12_partner_id);
                $x12_partner_name = text($this->x12_partners[$x12_partner_id]['name']);
                // For the modal, build a list of downloads
                $file = $created_batch->getBatFilename();
                $url = OEGlobalsBag::getInstance()->getKernel()->getWebRoot() . '/interface/billing/get_claim_file.php?' .
                    'key=' . urlencode($file) .
                    '&partner=' . urlencode($x12_partner_id) .
                    '&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken(session: $session));
                $html .=
                    "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <a href='" . attr($url) . "'>" . text($file) . "</a>
                        <span class='badge badge-primary badge-pill'>" . text($x12_partner_name) . "</span>
                    </li>";
            }
            $html .= "</ul>";
            $html .= "</div></body></html>";

            echo $html;
        });
    }

    /**
     * Validation writes the batch text to the screen.
     *
     * @param array $context
     */
    public function completeToScreen(array $context)
    {
        $this->finish($context, function ($context): void {

            // Get the format_bat string from the finish method
            $format_bat = $context['format_bat'];
            if (is_string($format_bat) && $format_bat === '') {
                return;
            }

            // if validating (sending to screen for user)
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
        });
    }

    /**
     * This is the common finish function to both completeToFile (normal)
     * and completeToScreen (validation). We pass the callback to let the
     * caller specify what we do after we finish up.
     *
     * This uses the generator's 'action' attribute to decide whether
     * to generate the edi file or not. If we're in NORMAL mode, generate the
     * file.
     *
     * @param array $context
     * @param callable $callback
     */
    protected function finish(array $context, callable $callback)
    {
        $format_bat = "";
        $created_batches = [];
        // Loop through all of the X12 batch files we've created, one per x-12 partner,
        // and depending on the action we're running, either write the final claim
        // to disk, or format the content for printing to the screen.
        foreach ($this->x12_partner_batches as $x12_partner_id => $x12_partner_batch) {
            // If we didn't write any claims for this X12 partner
            // don't append the closing lines or write the claim file or do anything else
            if (empty($x12_partner_batch->getBatContent())) {
                continue;
            }

            $x12_partner_batch->append_claim_close();

            // Write the batch content to formatted string for presenting to user
            $format_bat .= str_replace('~', PHP_EOL, $x12_partner_batch->getBatContent()) . "\n";

            // Store all the batches we create with the x12-partner ID as index
            // so we can pass them to the callback
            $created_batches[$x12_partner_id] = $x12_partner_batch;
        }

        // Call the callback with new context
        $callback([
            'created_batches' => $created_batches,
            'format_bat' => $format_bat
        ]);
    }
}
