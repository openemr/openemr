<?php

/**
 * Decides whether a patient is "best served in a language other than English"
 * for UDS Table 3B, Line 12.
 *
 * OpenEMR has no single field for this, so the rule combines the two signals it
 * does have. `patient_data.interpreter_needed` = `yes` is the primary indicator
 * of limited English proficiency and is decisive. Otherwise the patient counts
 * when their recorded `patient_data.language` is a real, non-English preference
 * (not English, not blank, not "declined to specify"). This is a documented
 * heuristic; a center that captures LEP explicitly should drive it from the
 * interpreter-needed flag.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class LanguageBarrierRule
{
    private const NON_LANGUAGE_OPTIONS = ['', 'english', 'decline_to_specify'];

    public function bestServedInNonEnglishLanguage(
        ?string $languageOptionId,
        ?string $interpreterNeeded,
    ): bool {
        if ($interpreterNeeded === 'yes') {
            return true;
        }

        if ($languageOptionId === null) {
            return false;
        }

        return !in_array($languageOptionId, self::NON_LANGUAGE_OPTIONS, true);
    }
}
