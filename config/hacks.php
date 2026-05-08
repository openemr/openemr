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

use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\Common\Crypto;

return [
    Crypto\CryptoInterface::class => Crypto\CryptoGen::class,
    Crypto\CryptoGen::class => function (TC $c) {
        $GLOBALS['OE_SITE_DIR'] = 'sites/' . $c->getString('OPENEMR_SITE');
        require_once 'library/sql.inc.php';
        return new Crypto\CryptoGen();
    },
];
