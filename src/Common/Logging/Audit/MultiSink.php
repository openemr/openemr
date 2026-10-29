<?php

/**
 * Writes the audit event to multiple sinks
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

readonly class MultiSink implements SinkInterface
{
    /**
     * @param SinkInterface[] $sinks
     */
    public function __construct(
        private array $sinks,
    ) {
    }

    public function record(Event $event): ?int
    {
        $apiLogId = null;
        foreach ($this->sinks as $sink) {
            $sinkApiLogId = $sink->record($event);
            if ($sinkApiLogId !== null) {
                $apiLogId = $sinkApiLogId;
            }
        }
        return $apiLogId;
    }
}
