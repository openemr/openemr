<?php

/**
 * ServiceSaveEvent is intended to be used and dispatched whenever an OpenEMR Service saves a record.  Listeners
 * can filter data before the record is saved and respond to whatever data was saved by the service.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use OpenEMR\Services\BaseService;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceSaveEvent extends Event
{
    /**
     * This event is triggered before a record has been created, and an assoc
     * array containing the POST of the record data is passed to the event object
     */
    const EVENT_PRE_SAVE = 'service.save.pre';

    /**
     * This event is triggered after a record has been created, and an assoc
     * array containing the POST of new record data is passed to the event object
     */
    const EVENT_POST_SAVE = 'service.save.post';

    /**
     * @var BaseService
     */
    private $service;

    /**
     * @var array
     */
    private $saveData;

    /**
     * UserCreatedEvent constructor.
     * @param $saveData
     */
    public function __construct(BaseService $service, array $saveData)
    {
        $this->service = $service;
        $this->saveData = $saveData;
    }

    public function getService(): BaseService
    {
        return $this->service;
    }

    /**
     * @return array
     */
    public function getSaveData()
    {
        return $this->saveData;
    }

    /**
     * @param array $saveData
     * @return ServiceSaveEvent
     */
    public function setSaveData($saveData)
    {
        $this->saveData = $saveData;
        return $this;
    }
}
