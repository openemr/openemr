<?php

declare(strict_types=1);

namespace OpenEMR\PHPUnit;

use PHPUnit\Event\Application\{
    Finished,
    FinishedSubscriber,
};

class ShutdownTracker implements FinishedSubscriber
{
    private bool $gotFinishedEvent = false;

    public function notify(Finished $event): void
    {
        $this->gotFinishedEvent = true;
    }

    public function install(): void
    {
        register_shutdown_function(function () {
            if (!$this->gotFinishedEvent) {
                error_log("CRITICAL ERROR: Exiting without having recevied PHPUnit shutdown event");
                exit(70); // "Internal software error"
            }
        });
    }
}
