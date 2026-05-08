<?php

/**
 * Hacks for backwards compatability. Please pretend these don't exist :(
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\BC\Crypto;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Encryption;

return [
    CryptoInterface::class => Crypto\Crypto::class,
    Crypto\Crypto::class => function (TC $c) {
        // TODO: these need to be sourced from app config
        $shouldEncryptForDatabase = true;
        $shouldEncryptForFilesystem = true;
        return new Crypto\Crypto(
            $c->get(Encryption\Keys\KeychainInterface::class),
            $c->get(LoggerInterface::class),
            shouldEncryptForDatabase: $shouldEncryptForDatabase,
            shouldEncryptForFilesystem: $shouldEncryptForFilesystem,
        );
    },

    Encryption\Keys\KeychainInterface::class => function (TC $c) {
        $keyDirectory = sprintf(
            '%s/documents/logs_and_misc/methods',
            $c->getString('legacySiteDirectory'),
        );
        $fs = new Encryption\Storage\PlaintextKeyOnDisk($keyDirectory);
        $db = new Encryption\Storage\PlaintextKeyInDbKeysTable($c->get(Connection::class));
        return Crypto\LegacyKeychainLoader::loadWithEngines(
            filesystemStorage: $fs,
            databaseStorage: $db,
            storageDir: $keyDirectory,
        );
    },
];
