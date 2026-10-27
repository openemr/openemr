<?php

/**
 * Fired for each encounter a patient statement run is about to put on a statement, so a
 * module can leave the encounter off: its lines, its balance and its statement count.
 *
 * The module decides by its own rules. A Title X family planning site, for example, keeps
 * the visits it provides confidentially off a statement addressed to someone other than
 * the patient. What was left off stays on the statement data (StatementLines::excluded()),
 * and its balance is still owed, so a module can bill it another way.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Billing;

use Symfony\Contracts\EventDispatcher\Event;

final class StatementEncounterEvent extends Event
{
    public const EVENT_NAME = 'billing.statement.encounter';

    private bool $excluded = false;

    /**
     * @param string $sensitivity form_encounter.sensitivity as stored ('' when none), for information
     * @param int $notice the statement number this would be for the encounter (1 for the first)
     * @param list<array<string, mixed>> $lines the lines the encounter would add, as StatementLines builds them
     * @param string $balance what the encounter would add to the amount due, "%.2f"
     * @param list<string> $recipientLines the statement's "to" block (StatementRecipient::lines())
     * @param bool $addressedToPatient false when the statement goes to the guardian (guarantor) on the patient's demographics
     * @param array<string, mixed> $statement the statement being built, as it stands before this encounter
     */
    public function __construct(
        private readonly int $pid,
        private readonly int $encounter,
        private readonly string $serviceDate,
        private readonly string $sensitivity,
        private readonly int $notice,
        private readonly array $lines,
        private readonly string $balance,
        private readonly array $recipientLines,
        private readonly bool $addressedToPatient,
        private readonly array $statement,
    ) {
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getEncounter(): int
    {
        return $this->encounter;
    }

    /** The date of service, "yyyy-mm-dd". */
    public function getServiceDate(): string
    {
        return $this->serviceDate;
    }

    /** form_encounter.sensitivity as stored, '' when none: information only. */
    public function getSensitivity(): string
    {
        return $this->sensitivity;
    }

    public function getNotice(): int
    {
        return $this->notice;
    }

    /** @return list<array<string, mixed>> */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    /** @return list<string> */
    public function getRecipientLines(): array
    {
        return $this->recipientLines;
    }

    public function isAddressedToPatient(): bool
    {
        return $this->addressedToPatient;
    }

    /**
     * The statement being built, as it stands before this encounter (a copy).
     *
     * @return array<string, mixed>
     */
    public function getStatement(): array
    {
        return $this->statement;
    }

    /** Leave this encounter off the statement. */
    public function exclude(): void
    {
        $this->excluded = true;
    }

    public function isExcluded(): bool
    {
        return $this->excluded;
    }
}
