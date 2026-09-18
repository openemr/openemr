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
     * @param list<EventCategory> $enabledEventTypes
     */
    public function __construct(
        public readonly bool $enabled,
        public readonly bool $forceBreakglass,
        public readonly bool $queryEvents,
        public readonly bool $httpRequestEvents,
        private readonly array $enabledEventTypes,
    ) {
    }

    public function isEventCategoryEnabled(EventCategory $category): bool
    {
        return in_array($category, $this->enabledEventTypes, true);
    }
}
