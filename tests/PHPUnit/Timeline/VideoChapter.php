<?php

/**
 * One test's span within the E2e recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

final readonly class VideoChapter
{
    /**
     * @param float $startOffset seconds from the start of the recording
     * @param float $endOffset   seconds from the start of the recording
     */
    public function __construct(
        public string $label,
        public float $startOffset,
        public float $endOffset,
        public TestOutcome $outcome,
    ) {
    }

    public function title(): string
    {
        return $this->outcome->value . ' ' . $this->label;
    }
}
