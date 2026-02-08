<?php

namespace OpenEMR\Services\Traits;

use OpenEMR\Events\Services\ServiceSaveEvent;

trait ServiceEventTrait
{
    /**
     *
     * @param string $type The type of save event to dispatch
     * @param array $saveData The history data to send in the event
     * @return array
     */
    private function dispatchSaveEvent(string $type, $saveData)
    {
        $saveEvent = new ServiceSaveEvent($this, $saveData);
        $filteredData = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($saveEvent, $type);
        if ($filteredData instanceof ServiceSaveEvent) { // make sure whoever responds back gives us the right data.
            $saveData = $filteredData->getSaveData();
        }
        return $saveData;
    }
}
