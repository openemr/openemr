<?php

/**
 * A received 835 that is left for a person to post in the posting window.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;

final readonly class ReceivedFile
{
    /**
     * @param int                 $id             x12_remote_received.id
     * @param string              $partnerName    the X12 partner it came from; '' when the partner is gone
     * @param string              $storedName     the name it is stored under in documents/era
     * @param ?AutoPostFileStatus $autoPostStatus null when the file was not for unattended posting
     * @param list<HeldCheck>     $heldChecks     its checks that unattended posting held
     * @param list<int>           $payerIds       the insurance companies unattended posting posted or held its
     *                                            checks for, lowest id first; none when it recorded none
     */
    public function __construct(
        public int $id,
        public string $partnerName,
        public string $remoteName,
        public string $storedName,
        public \DateTimeImmutable $receivedAt,
        public ?AutoPostFileStatus $autoPostStatus,
        public array $heldChecks,
        public ?WorkedMark $worked,
        public array $payerIds = [],
    ) {
    }

    /**
     * The insurance company the file is posted for by hand unless the person says otherwise:
     * the one its checks were recorded for. 0 when there is none, or more than one.
     */
    public function payerId(): int
    {
        return count($this->payerIds) === 1 ? $this->payerIds[0] : 0;
    }

    /**
     * Whether a posting run is still to take the file, or took it and has not finished it.
     * Such a file is not posted by hand, or marked worked, until it is taken from the run.
     */
    public function awaitsRun(): bool
    {
        return $this->autoPostStatus === AutoPostFileStatus::Pending || $this->autoPostStatus === AutoPostFileStatus::Running;
    }

    /**
     * The name the posting window opens the file by: the stored name without its extension.
     */
    public function eraName(): string
    {
        return str_ends_with($this->storedName, '.edi') ? substr($this->storedName, 0, -4) : $this->storedName;
    }
}
