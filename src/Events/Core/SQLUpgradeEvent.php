<?php

/**
 * SQLUpgradeEvent class is fired the SQLUpgradeService when a SQL upgrade file has completed upgrading.  Note if there
 * are multiple upgrade files that must be processed this event will fire multiple times.  Consumers of this event
 * need to handle this use case.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

use OpenEMR\Services\Utils\Interfaces\ISQLUpgradeService;
use Symfony\Contracts\EventDispatcher\Event;

class SQLUpgradeEvent extends Event
{
    /**
     * This event is triggered just before the upgrade starts processing the upgrade file
     */
    const EVENT_UPGRADE_PRE = 'core.upgrade.sql.pre';

    /**
     * This event is triggered after the upgrade has finished completing processing the sql upgrade file.
     */
    const EVENT_UPGRADE_POST = 'core.upgrade.sql.post';

    /**
     * @param string $filename The filename that was executed to upgrade the database
     * @param string $path The path to the filename that was executed.
     * @param ISQLUpgradeService $sqlUpgradeService The sql upgrade service object
     */
    public function __construct(
        private $filename,
        private $path,
        private ISQLUpgradeService $sqlUpgradeService
    ) {
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return SQLUpgradeEvent
     */
    public function setFilename(string $filename): SQLUpgradeEvent
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return SQLUpgradeEvent
     */
    public function setPath(string $path): SQLUpgradeEvent
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return ISQLUpgradeService
     */
    public function getSqlUpgradeService(): ISQLUpgradeService
    {
        return $this->sqlUpgradeService;
    }

    /**
     * @param ISQLUpgradeService $sqlUpgradeService
     * @return SQLUpgradeEvent
     */
    public function setSqlUpgradeService(ISQLUpgradeService $sqlUpgradeService): SQLUpgradeEvent
    {
        $this->sqlUpgradeService = $sqlUpgradeService;
        return $this;
    }
}
