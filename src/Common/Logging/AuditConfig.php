<?php

/**
 * Configuration for audit logging behavior
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

class AuditConfig
{
    /**
     * @param array<string, bool> $eventTypeFlags
     */
    public function __construct(
        public readonly bool $enabled,
        public readonly bool $forceBreakglass,
        public readonly bool $queryEvents,
        public readonly bool $httpRequestEvents,
        private readonly array $eventTypeFlags,
    ) {
    }

    public function isEventTypeEnabled(string $type): bool
    {
        return $this->eventTypeFlags[$type] ?? false;
    }
}
