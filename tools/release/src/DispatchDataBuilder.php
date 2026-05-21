<?php

/**
 * Build the per-event `data` block for a DispatchRequest from CLI
 * options. Kept as a class (rather than a free function in dispatch.php)
 * so the script file declares no symbols beyond its side-effect entry,
 * per PSR-1.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class DispatchDataBuilder
{
    public function __construct(
        private OptionReader $opts,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $event): array
    {
        return match ($event) {
            DispatchRequest::EVENT_REL_CUT, DispatchRequest::EVENT_REL_UPDATE => [
                'branch' => $this->opts->string('branch'),
                'version' => $this->opts->string('release-version'),
                'prev_release' => $this->opts->string('prev-release'),
            ],
            DispatchRequest::EVENT_TAG => [
                'tag' => $this->opts->string('tag'),
                'branch' => $this->opts->string('branch'),
                'version' => $this->opts->string('release-version'),
            ],
            DispatchRequest::EVENT_PROBE => [
                'note' => 'release-permissions-probe; ignored by consumers',
            ],
            default => throw new \InvalidArgumentException('Unknown dispatch event: ' . $event),
        };
    }
}
