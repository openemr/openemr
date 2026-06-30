<?php

/**
 * Assembles a UDS Patient Snapshot from resolved demographics.
 *
 * Pure presentation logic — no database, no globals — so the field set, order,
 * and labels are fully unit-testable. The new (not-yet-captured) UDS sections
 * are emitted as pending placeholders pointing at their pathway steps.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

final class UdsSnapshotAssembler
{
    public function assemble(PatientDemographics $demographics): UdsSnapshot
    {
        return new UdsSnapshot(
            $demographics->fullName,
            $this->demographicsFields($demographics),
            $this->pendingSections(),
        );
    }

    /**
     * Reused demographics that UDS Tables 3A/3B/4 draw on, in display order.
     *
     * @return list<UdsField>
     */
    public function demographicsFields(PatientDemographics $demographics): array
    {
        return [
            new UdsField('Age / sex', $this->joinNonEmpty([$demographics->ageDisplay, $demographics->sex])),
            new UdsField('Race', $demographics->race),
            new UdsField('Ethnicity', $demographics->ethnicity),
            new UdsField('Preferred language', $demographics->language),
            new UdsField('ZIP code', $demographics->zip),
        ];
    }

    /**
     * UDS sections whose capture is implemented in later pathway steps.
     *
     * @return list<PendingSection>
     */
    public function pendingSections(): array
    {
        // Every essential UDS section now has its own dedicated card on the
        // Snapshot, so there are no generic pending placeholders left.
        return [];
    }

    /**
     * @param list<?string> $parts
     */
    private function joinNonEmpty(array $parts): ?string
    {
        $kept = array_values(array_filter(
            $parts,
            static fn(?string $part): bool => $part !== null && trim($part) !== '',
        ));

        return $kept === [] ? null : implode(' · ', $kept);
    }
}
