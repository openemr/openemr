<?php

/**
 * Inputs to Dispatcher::dispatch(). Carries the event name and the
 * per-event data fields plus the App-token credential. Constructor
 * validates that the event name is one the cross-repo schema knows
 * about (or that probe-mode is set, which bypasses schema validation
 * for the permissions-check workflow's no-op event).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class DispatchRequest
{
    public const EVENT_REL_CUT = 'openemr-rel-cut';
    public const EVENT_REL_UPDATE = 'openemr-rel-update';
    public const EVENT_TAG = 'openemr-tag';
    public const EVENT_DOCS_BINARIES = 'openemr-docs-binaries';
    public const EVENT_PROBE = 'release-permissions-probe';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $event,
        public string $repo,
        public string $sha,
        public string $actor,
        public string $dispatchedAt,
        public string $appToken,
        public array $data,
        public bool $probe = false,
    ) {
        if ($appToken === '') {
            throw new \InvalidArgumentException('appToken is required');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toEnvelope(): array
    {
        return [
            'event' => $this->event,
            'repo' => $this->repo,
            'sha' => $this->sha,
            'actor' => $this->actor,
            'dispatched_at' => $this->dispatchedAt,
            'data' => $this->data,
        ];
    }
}
