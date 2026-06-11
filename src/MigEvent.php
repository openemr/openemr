<?php

declare(strict_types=1);

namespace OpenEMR;

use Doctrine\Common\EventSubscriber;
// use Doctrine\Migrations\Event\;
use Doctrine\Migrations\Events;

class MigEvent implements EventSubscriber
{
    public function __construct(
    ) {
        var_dump(__METHOD__);
    }
    public function getSubscribedEvents(): array
    {
        return [
            Events::onMigrationsMigrated,
            Events::onMigrationsMigrating,
            Events::onMigrationsVersionSkipped,
        ];
    }

    public function onMigrationsMigrating(): void
    {
        var_dump(__METHOD__);
    }

    public function onMigrationsMigrated(): void
    {
        var_dump(__METHOD__);
    }
}
