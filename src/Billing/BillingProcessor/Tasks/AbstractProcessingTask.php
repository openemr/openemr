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

use OpenEMR\Billing\BatchFilePublisher;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\FacilityZipDenial;
use OpenEMR\Billing\UnbilledFileDecision;

abstract class AbstractProcessingTask
{
    /**
     * Claim version this run inserted while the hold is on.
     */
    protected ?int $insertedClaimVersion = null;

    /**
     * Normal generation waits to mark the claim billed until the file is written.
     */
    protected bool $billWhenTheFileLands = false;

    /**
     * True while generate() is inside validate-and-clear, so that call does not clear the wait.
     */
    protected bool $insideGenerate = false;

    /**
     * File name read from the unbilled row for this claim.
     */
    protected string $settledFileName = '';

    /**
     * Version on the same unbilled row as the file name.
     */
    protected ?int $settledVersion = null;

    /**
     * Accepted claims whose file name is stored and whose billed update waits for that file.
     *
     * @var list<array{claim: BillingClaim, version: int, filename: string}>
     */
    protected array $awaitingFile = [];

    /**
     * Generation fences this run holds, one name per claim.
     *
     * @var list<string>
     */
    protected array $generationFences = [];

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
     * Unbilled version left when a billed update did not land.
     *
     * The next accepted run bills this row instead of inserting another.
     */
    protected function openUnbilledVersion(BillingClaim $claim): ?int
    {
        return BillingUtilities::newestUnbilledClaimVersion(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId()
        );
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

    /**
     * File name already stored on the unbilled row. Empty when this run should assign one.
     */
    protected function openUnbilledFile(BillingClaim $claim): string
    {
        return BillingUtilities::newestUnbilledClaimFile(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId()
        );
    }

    /**
     * The named file is in this batch's edi directory.
     *
     * A name with a directory segment is not a file this run wrote.
     */
    protected function claimFileLanded(BillingClaimBatch $batch, string $filename): bool
    {
        $dir = $batch->getBatFiledir();
        if ($dir === '') {
            return false;
        }

        return BatchFilePublisher::downloadAllowed($dir, $filename)
            && BatchFilePublisher::isPublished($dir, $filename);
    }

    /**
     * Drop the file name on an unbilled row so the next run can write a new file.
     */
    protected function clearClaimFile(BillingClaim $claim, int $version, string $filename = ''): void
    {
        BillingUtilities::clearUnbilledClaimFile(
            $claim->getPid(),
            $claim->getEncounter(),
            $version,
            $filename
        );
    }

    /**
     * Store the selected payer before the 837 is rendered.
     */
    protected function bindSelectedPayer(BillingClaim $claim): void
    {
        BillingUtilities::selectBillingPayer(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId()
        );
    }

    /**
     * Claim this run's file name. False means another run already named the row.
     */
    protected function stampClaimFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return BillingUtilities::assignUnbilledClaimFile(
            $claim->getPid(),
            $claim->getEncounter(),
            $version,
            $filename
        );
    }

    /**
     * Bill the row only while it still names this file.
     */
    protected function settleClaimFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return BillingUtilities::billUnbilledClaimFile(
            $claim->getPid(),
            $claim->getEncounter(),
            $version,
            $filename
        );
    }

    /**
     * Read the stored file name. Clear it only while this run holds the claim.
     *
     * A missing file is not abandoned while another run may still be writing it.
     */
    protected function previousFileDecision(BillingClaim $claim, BillingClaimBatch $batch): UnbilledFileDecision
    {
        $assignment = $this->openUnbilledAssignment($claim);
        $this->settledVersion = $assignment['version'] ?? null;
        $this->settledFileName = $assignment['process_file'] ?? '';
        $landed = $this->settledVersion !== null
            && $this->settledFileName !== ''
            && $this->claimFileLanded($batch, $this->settledFileName);
        $decision = UnbilledFileDecision::fromStoredFile($this->settledFileName, $landed);
        if ($decision === UnbilledFileDecision::Missing && $this->settledVersion !== null) {
            if (!$this->generationFenceHeld($claim)) {
                return UnbilledFileDecision::Busy;
            }
            $dir = $batch->getBatFiledir();
            if ($dir !== '' && BatchFilePublisher::hasPublishingMarker($dir, $this->settledFileName)) {
                // A matching note is a finished file whose marker is still present.
                // Leave the assignment until that marker can be removed.
                if (BatchFilePublisher::isPublished($dir, $this->settledFileName)) {
                    return UnbilledFileDecision::Busy;
                }
                if (!BatchFilePublisher::quarantineInterrupted($dir, $this->settledFileName)) {
                    return UnbilledFileDecision::Busy;
                }
            }
            $this->clearClaimFile($claim, $this->settledVersion, $this->settledFileName);
        }

        return $decision;
    }

    /**
     * Hold this claim's generation fence. False means another run still owns it.
     */
    protected function holdGeneration(BillingClaim $claim): bool
    {
        $name = $this->generationFenceName($claim);
        if ($name === null) {
            return false;
        }
        if (in_array($name, $this->generationFences, true)) {
            return true;
        }
        if (!$this->acquireGenerationFence($name)) {
            return false;
        }
        $this->generationFences[] = $name;

        return true;
    }

    /**
     * This run holds the fence for the claim.
     */
    protected function generationFenceHeld(BillingClaim $claim): bool
    {
        $name = $this->generationFenceName($claim);

        return is_string($name) && in_array($name, $this->generationFences, true);
    }

    /**
     * Lock name for one claim. Null when the ids are not a safe lock name.
     */
    protected function generationFenceName(BillingClaim $claim): ?string
    {
        $patient = $claim->getPid();
        $encounter = $claim->getEncounter();
        $payer = $claim->getPayorId();
        if (!is_string($payer)) {
            return null;
        }

        return BillingUtilities::generationFenceName(
            $patient,
            $encounter,
            $payer,
            $this->generationFenceSite()
        );
    }

    /**
     * Database name that keeps this claim's fence off other sites on the server.
     */
    protected function generationFenceSite(): string
    {
        return BillingUtilities::generationFenceSite();
    }

    /**
     * Take the database fence. Tests replace this so they do not lock a claim.
     */
    protected function acquireGenerationFence(string $name): bool
    {
        return BillingUtilities::acquireGenerationFence($name);
    }

    /**
     * Release every fence this run took.
     */
    protected function releaseGenerationFences(): void
    {
        foreach ($this->generationFences as $name) {
            $this->releaseGenerationFence($name);
        }
        $this->generationFences = [];
    }

    /**
     * Release one fence. Tests replace this so they do not touch the database.
     */
    protected function releaseGenerationFence(string $name): void
    {
        BillingUtilities::releaseGenerationFence($name);
    }

    /**
     * Every claim waiting on this file still names it.
     *
     * No waiting claim means this batch is not holding a deferred file.
     */
    protected function awaitingFileStillOwned(string $filename): bool
    {
        if ($filename === '') {
            return false;
        }

        foreach ($this->awaitingFile as $pending) {
            if ($pending['filename'] !== $filename) {
                continue;
            }
            if (!$this->unbilledRowNamesFile($pending['claim'], $pending['version'], $filename)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The unbilled row still names this file. Tests replace the database read.
     */
    protected function unbilledRowNamesFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return BillingUtilities::unbilledRowNamesFile(
            $claim->getPid(),
            $claim->getEncounter(),
            $version,
            $filename
        );
    }

    /**
     * Newest unbilled version and its file name, from one row.
     *
     * @return array{version: int, process_file: string}|null
     */
    protected function openUnbilledAssignment(BillingClaim $claim): ?array
    {
        return BillingUtilities::newestUnbilledClaimAssignment(
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId()
        );
    }

    /**
     * Bill a published file when the hold is off.
     *
     * True means the file was billed. False means it is on disk and the
     * billed update missed. Null means this run should write the claim.
     */
    protected function recoverPublishedFile(BillingClaim $claim, BillingClaimBatch $batch): ?bool
    {
        $assignment = $this->openUnbilledAssignment($claim);
        if ($assignment === null) {
            return null;
        }

        $version = $assignment['version'];
        $filename = $assignment['process_file'];
        if ($version <= 0 || $filename === '' || !$this->claimFileLanded($batch, $filename)) {
            return null;
        }

        $this->settledVersion = $version;
        $this->settledFileName = $filename;

        return $this->markStoredFileBilled($claim);
    }

    /**
     * Mark the unbilled row billed now that its file is already on disk.
     */
    protected function markStoredFileBilled(BillingClaim $claim): bool
    {
        $version = $this->settledVersion;
        if ($version === null) {
            return false;
        }

        $this->insertedClaimVersion = $version;

        return $this->settleClaimFile($claim, $version, $this->settledFileName);
    }

    /**
     * Store the file name on the unbilled row. The billed update waits until that file exists.
     */
    protected function noteClaimFile(BillingClaim $claim, BillingClaimBatch $batch): bool
    {
        $version = $this->insertedClaimVersion;
        $filename = $batch->getBatFilename();
        if ($version === null || $version <= 0 || $filename === '') {
            return false;
        }

        if (!$this->stampClaimFile($claim, $version, $filename)) {
            return false;
        }

        $this->awaitingFile[] = [
            'claim' => $claim,
            'version' => $version,
            'filename' => $filename,
        ];

        return true;
    }

    /**
     * The file is on disk. Mark each claim that was waiting on this name.
     *
     * A claim whose billed update does not land stays in the waiting list.
     *
     * @return list<BillingClaim>
     */
    protected function billAwaitingFile(string $filename): array
    {
        $stillWaiting = [];
        $unbilled = [];
        foreach ($this->awaitingFile as $pending) {
            if ($pending['filename'] !== $filename) {
                $stillWaiting[] = $pending;
                continue;
            }

            $claim = $pending['claim'];
            if (!$this->settleClaimFile($claim, $pending['version'], $filename)) {
                $stillWaiting[] = $pending;
                $unbilled[] = $claim;
            }
        }

        $this->awaitingFile = $stillWaiting;

        return $unbilled;
    }

    /**
     * The file was not written. Clear the name so the next run does not treat it as sent.
     */
    protected function releaseAwaitingFile(string $filename): void
    {
        $stillWaiting = [];
        foreach ($this->awaitingFile as $pending) {
            if ($pending['filename'] !== $filename) {
                $stillWaiting[] = $pending;
                continue;
            }

            $this->clearClaimFile($pending['claim'], $pending['version'], $filename);
        }

        $this->awaitingFile = $stillWaiting;
    }
}
