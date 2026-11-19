<?php

/**
 * What a failed Composer check reports: log text, annotations, and a job summary
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\ComposerAnnotations;

final readonly class Report
{
    /**
     * @param string $log text for the job log, readable without annotations
     * @param non-empty-list<Annotation> $annotations
     * @param string $summary Markdown for the job summary, empty when there is nothing to add to it
     */
    public function __construct(
        public string $log,
        public array $annotations,
        public string $summary,
    ) {
    }
}
