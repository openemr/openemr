<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

interface WriterInterface
{
    /**
     * Returns true if the message was sent, false if not.
     */
    public function writeMessage(string $message): bool;
}
