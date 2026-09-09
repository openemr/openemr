<?php

/**
 * PatientMergeAbortedException unwinds a merge that cannot continue.
 *
 * It replaces the die() calls the legacy merge_patients.php used. Unlike most exceptions in this
 * codebase its message IS intended for the user: every throw site passes a translated string that
 * the legacy page displayed verbatim. {@see PatientMergeService::merge()} catches it and turns it
 * into a {@see PatientMergeResult}, so it should not escape the service.
 *
 * It extends \RuntimeException rather than a broader type so callers can catch it without also
 * suppressing \Error subclasses, which the project forbids (see
 * tests/PHPStan/Rules/ForbiddenCatchTypeRule.php).
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

use RuntimeException;

class PatientMergeAbortedException extends RuntimeException
{
    /**
     * The translated, user-safe reason the merge stopped.
     */
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
