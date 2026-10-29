<?php

/**
 * In-memory audit events waiting for one database transaction.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class AuditEventBuffer
{
    /** @var list<Event> */
    private array $events = [];

    public string $lastLogId = '0';

    public function add(Event $event): void
    {
        $this->events[] = $event;
    }

    public function isEmpty(): bool
    {
        return $this->events === [];
    }

    /**
     * @return list<Event>
     */
    public function pending(): array
    {
        return $this->events;
    }

    /**
     * @return list<Event>
     */
    public function drain(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
