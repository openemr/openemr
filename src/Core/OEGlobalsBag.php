<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\HttpFoundation\ParameterBag;

class OEGlobalsBag extends ParameterBag
{
    private static ?OEGlobalsBag $instance = null;

    public static function getInstance(): OEGlobalsBag
    {
        if (null === self::$instance) {
            self::$instance = new OEGlobalsBag();
        }

        return self::$instance;
    }

    public function __construct(
        array $parameters = [],
        private readonly bool $compatabilityMode = false,
    ) {
        parent::__construct($parameters);
        $this->compatHolder = (object) ['enabled' => $this->compatabilityMode];
    }

    public function set(string $key, mixed $value): void
    {
        parent::set($key, $value);

        if ($this->compatabilityMode) {
            // In compatibility mode, also set the value in the global $_GLOBALS array
            $GLOBALS[$key] = $value;
        }
    }
}
