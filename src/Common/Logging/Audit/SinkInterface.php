<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

interface SinkInterface
{
    public function record(Event $event): void;

    /**
     * Writes events buffered by record(). Sinks that write immediately
     * can leave this empty.
     */
    public function flush(): void;
}
