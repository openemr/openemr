<?php

/**
 * Outcome of a single mutator invocation: which files changed and any
 * informational messages the conductor PR body should surface.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep;

final readonly class MutatorResult
{
    /**
     * @param list<string> $changedFiles
     * @param list<string> $messages
     */
    public function __construct(
        public array $changedFiles,
        public array $messages = [],
    ) {
    }

    public static function noop(): self
    {
        return new self([], []);
    }

    public function changed(): bool
    {
        return $this->changedFiles !== [];
    }
}
