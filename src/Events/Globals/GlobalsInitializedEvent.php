<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Globals;

use OpenEMR\Services\Globals\GlobalsService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom global settings
 *
 * @package OpenEMR\Events
 * @subpackage Globals
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class GlobalsInitializedEvent extends Event
{
    /**
     * The global.init event occurs after globals have been set up in globals.inc.php
     */
    const EVENT_HANDLE = 'globals.initialized';

    /**
     * @var array
     *
     * The globals service for manipulating globals data structure
     */
    private $globalsService = null;

    /**
     * GlobalsInitializedEvent constructor.
     * @param array $globalsService
     */
    public function __construct(GlobalsService $globalsService)
    {
        $this->globalsService = $globalsService;
    }

    /**
     * @return GlobalsService
     *
     * Get the instance of globals service
     */
    public function getGlobalsService()
    {
        return $this->globalsService;
    }
}
