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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\SqlConfigEvent;
use OpenEMR\Common\System\MissingSiteException;

$siteDir = $GLOBALS['OE_SITE_DIR'] ?? '';
if (empty($siteDir)) {
    if (!defined('OPENEMR_STATIC_ANALYSIS') || !OPENEMR_STATIC_ANALYSIS) {
        throw new MissingSiteException();
    }
    // GLOBALS may not be defined consistently during static analysis.
    $siteDir = __DIR__ . '/../sites/default';
}

require_once $siteDir . "/sqlconf.php";

if (OEGlobalsBag::getInstance()->hasKernel()) {
    $eventDispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
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
        $config = $configEntity->getConfig();
    }
}
