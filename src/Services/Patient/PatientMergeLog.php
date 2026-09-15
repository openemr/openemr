<?php

/**
 * PatientMergeLog collects the human-readable steps a patient merge performed.
 *
 * The legacy merge_patients.php echoed each step to the browser as it ran. The merge is now
 * driven by {@see PatientMergeService}, which has no output channel, so the steps are collected
 * here and rendered once the merge finishes (or aborts).
 *
 * Steps are plain, unescaped strings. Whoever renders them is responsible for escaping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final class PatientMergeLog
{
    /** @var list<string> */
    private array $steps = [];

    public function add(string $step): void
    {
        $this->steps[] = $step;
    }

    /**
     * @return list<string>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function isEmpty(): bool
    {
        return $this->steps === [];
    }
}
