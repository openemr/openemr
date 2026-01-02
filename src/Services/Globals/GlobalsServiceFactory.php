<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

use Webmozart\Assert\Assert;

class GlobalsServiceFactory
{
    private static ?GlobalsService $instance = null;

    public static function build(
        array $globalsMetadata,
        array $userSpecificGlobals,
        array $userSpecificSections
    ): GlobalsService {
        Assert::null(self::$instance, 'Global settings is already initialized');

        self::$instance = new GlobalsService($globalsMetadata, $userSpecificGlobals, $userSpecificSections);

        return self::$instance;
    }

    public static function getInstance(): GlobalsService
    {
        Assert::notNull(self::$instance, 'GlobalsServiceFactory::getInstance called before ::build');

        return self::$instance;
    }
}
