<?php

declare(strict_types=1);

use Doctrine\DBAL\{
    Connection,
    DriverManager,
};
use OpenEMR\BC\DatabaseConnectionOptions;
use Firehed\Container\TypedContainerInterface as TC;

return [
    Connection::class => function (TC $c) {
        $opts = $c->get(DatabaseConnectionOptions::class);
        return DriverManager::getConnection($opts->toDbalParams());
    },

    DatabaseConnectionOptions::class => function (TC $c) {
        $site = $c->get('OPENEMR_SITE');
        assert(is_string($site));
        return DatabaseConnectionOptions::forSite("sites/site");
    }
];
