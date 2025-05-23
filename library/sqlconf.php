<?php

/**
 * The sqlconf.php file is the central place to load the SITE_ID SQL credentials. It allows allows modules to manage the
 * credential variables
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\SqlConfigEvent;

require_once $GLOBALS['OE_SITE_DIR'] . "/sqlconf.php";

if (array_key_exists('kernel', $GLOBALS) && $GLOBALS['kernel'] instanceof Kernel) {
    $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
    $sqlConfigEvent = new SqlConfigEvent();

    if ($eventDispatcher->hasListeners(SqlConfigEvent::EVENT_NAME)) {
        /**
         * @var SqlConfigEvent
         */
        $configEvent = $eventDispatcher->dispatch(new SqlConfigEvent(), SqlConfigEvent::EVENT_NAME);
        $configEntity = $configEvent->getConfig();

        // Override the variables set in sites/<site_id>/sqlconf.php file that was required above.
        $host = $configEntity->getHost();
        $port = $configEntity->getPort();
        $login = $configEntity->getUser();
        $pass = $configEntity->getPass();
        $dbase = $configEntity->getDatabaseName();
        $db_encoding = $configEntity->getEncoding();
        $disable_utf8_flag = $configEntity->getDisableUTF8();
        $config = $configEntity->getConfig();
    }
}
