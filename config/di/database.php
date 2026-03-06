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
        $site = $c->get('OPENEMR_SITE');
        assert(is_string($site));
        $opts = DatabaseConnectionOptions::forSite("sites/$site");
        return DriverManager::getConnection($opts->toDbalParams());
    },
];
