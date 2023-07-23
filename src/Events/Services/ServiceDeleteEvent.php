<?php

/**
 * ServiceDeleteEvent is intended to be used and dispatched whenever an OpenEMR Service deletes a record.  Listeners
 * can key off the pre delete event or the post delete event to handle data cleanup or other tasks.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use OpenEMR\Services\BaseService;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceDeleteEvent extends Event
{
    /**
     * This event is triggered before a record has been deleted, and the recordId of the record being deleted is passed
     * to the event object
     */
    const EVENT_PRE_DELETE = 'service.delete.pre';

    const EVENT_POST_DELETE = 'service.delete.post';

    /**
     * @var BaseService
     */
    private $service;

    /**
     * @var string|int|array $recordId The id of the record being deleted.  If the record id is a composite key it will contain a hashmap of the keys
     */
    private $recordId;

    public function __construct(BaseService $service, int|string|array $recordId)
    {
        $this->service = $service;
        $this->recordId = $recordId;
    }

    public function getService(): BaseService
    {
        return $this->service;
    }

    /**
     * @return int|string|array The key or composite key (as a hashmap of column_name => value) of the record being deleted
     */
    public function getRecordId()
    {
        return $this->recordId;
    }
}
