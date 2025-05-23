<?php

/**
 * Handles the retrieval of calendar categories that are specific to TeleHealth
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Repository;

use OpenEMR\Services\AppointmentService;

class CalendarEventCategoryRepository
{
    const TELEHEALTH_EVENT_CATEGORY_CONSTANT_IDS = ['comlink_telehealth_new_patient', 'comlink_telehealth_established_patient'];

    private $categoryEvents = [];

    public function getEventCategoryForId($id)
    {
        $categoryEvents = $this->getEventCategories();
        if (isset($categoryEvents[$id])) {
            return $categoryEvents[$id];
        }
        return null;
    }

    public function getEventCategories($skipCache = false)
    {
        if (!$skipCache && !empty($this->categoryEvents)) {
            return $this->categoryEvents;
        }

        $apptRepo = new AppointmentService();
        $categories = $apptRepo->getCalendarCategories();
        $filteredCategories = [];
        foreach ($categories as $category) {
            if (array_search($category['pc_constant_id'], self::TELEHEALTH_EVENT_CATEGORY_CONSTANT_IDS) !== false) {
                $filteredCategories[$category['pc_catid']] = $category;
            }
        }
        $this->categoryEvents = $filteredCategories;
        return $this->categoryEvents;
    }
}
