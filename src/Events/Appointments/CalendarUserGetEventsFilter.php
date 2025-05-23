<?php

/**
 * CalendarUserGetEventsFilter class is fired from pnuserapi::postcalendar_userapi_pcGetEvents and can be used to filter
 * the array of event data that is used by the calendar.  Additional properties can be added that are used by the template
 * engine, events can be removed, rearranged, etc.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

class CalendarUserGetEventsFilter
{
    /**
     * @var array
     */
    private $eventsByDays;

    /**
     * @var string
     */
    private $viewType;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $endDate;

    /**
     * @var string
     */
    private $providerID;

    public const EVENT_NAME = "postcalendar_userapi_pcGetEvents.filter";

    /**
     * @return array
     */
    public function getEventsByDays(): array
    {
        return $this->eventsByDays;
    }

    /**
     * @param array $eventsByDays
     * @return CalendarUserGetEventsFilter
     */
    public function setEventsByDays(array $eventsByDays): CalendarUserGetEventsFilter
    {
        $this->eventsByDays = $eventsByDays;
        return $this;
    }

    /**
     * @return string
     */
    public function getViewType(): ?string
    {
        return $this->viewType;
    }

    /**
     * @param string $viewType
     * @return CalendarUserGetEventsFilter
     */
    public function setViewType(?string $viewType): CalendarUserGetEventsFilter
    {
        $this->viewType = $viewType;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return CalendarUserGetEventsFilter
     */
    public function setKeywords(?string $keywords): CalendarUserGetEventsFilter
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return CalendarUserGetEventsFilter
     */
    public function setCategory(?string $category): CalendarUserGetEventsFilter
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     * @return CalendarUserGetEventsFilter
     */
    public function setStartDate(?string $startDate): CalendarUserGetEventsFilter
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     * @return CalendarUserGetEventsFilter
     */
    public function setEndDate(?string $endDate): CalendarUserGetEventsFilter
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getProviderID(): ?string
    {
        return $this->providerID;
    }

    /**
     * @param string $providerID
     * @return CalendarUserGetEventsFilter
     */
    public function setProviderID(?string $providerID): CalendarUserGetEventsFilter
    {
        $this->providerID = $providerID;
        return $this;
    }
}
