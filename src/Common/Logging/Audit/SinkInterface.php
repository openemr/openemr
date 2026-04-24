<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

interface SinkInterface
{
    public function record(Event $event): bool;
}
