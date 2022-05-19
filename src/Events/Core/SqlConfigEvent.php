<?php

/**
 * SqlConfigEvent class is fired when attemtping to set the SQL credentials and is used to manage how we set credentials
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

use OpenEMR\Events\Core\Interfaces\SqlConfigInterface;

class SqlConfigEvent
{
    public const EVENT_NAME = "sql.config";

    /**
     * @var SqlConfigInterface
     */
    private $config;

    /**
     * Get the configuration details
     *
     * @return SqlConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the configuration options
     *
     * @param SqlConfigInterface $config
     * @return SqlConfigEvent
     */
    public function setConfig(SqlConfigInterface $config): SqlConfigEvent
    {
        $this->config = $config;
        return $this;
    }
}
