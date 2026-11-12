<?php

/**
 * A message line in the ERA posting report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class ReportMessage
{
    /**
     * @param bool $multiline show the text's line breaks
     */
    public function __construct(
        public ReportStyle $style,
        public string $text,
        public bool $multiline = false,
    ) {
    }
}
