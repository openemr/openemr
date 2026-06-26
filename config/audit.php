<?php

use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\Common\Database\{
    ConnectionManager,
    ConnectionType,
};
use OpenEMR\Common\Logging\{
    BreakglassChecker,
    BreakglassCheckerInterface,
};

return [
    BreakglassCheckerInterface::class => BreakglassChecker::class,
    // See notes in BreakglassChecker's constructor: it must use the
    // non-audited connection in order to avoid an infinite loop w/ SQL logging
    BreakglassChecker::class => fn (TC $c) => new BreakglassChecker(
        $c->get(ConnectionManager::class)->get(ConnectionType::NonAudited),
    ),
];
