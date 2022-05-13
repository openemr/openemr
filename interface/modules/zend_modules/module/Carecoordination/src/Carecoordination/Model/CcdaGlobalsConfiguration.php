<?php

/**
 * CcdaGlobalsConfiguration.php  is responsible for creating and retrieving the Carecoordination globals that are used
 * by the module.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;

class CcdaGlobalsConfiguration
{
    const GLOBAL_SECTION_NAME = 'Carecoordination';

    const GLOBAL_KEY_CCDA_MAX_SECTIONS = 'ccda_view_max_sections';
    const GLOBAL_KEY_CCDA_SORT_ORDER = 'ccda_section_sort_order';

    public function setupGlobalSections(GlobalsService $service)
    {
        $service->addUserSpecificTab(self::GLOBAL_SECTION_NAME);
        $setting = new GlobalSetting(
            xl('Max Sections To Display'),
            GlobalSetting::DATA_TYPE_NUMBER,
            0,
            xl('Total number of clinical sections to display when viewing a CCD-A document (0 for unlimited)'),
            true
        );
        $service->appendToSection(self::GLOBAL_SECTION_NAME, self::GLOBAL_KEY_CCDA_MAX_SECTIONS, $setting);


        $setting = new GlobalSetting(
            xl('Section Display Order'),
            GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR,
            '',
            xl('The order of clinical information sections to display when viewing a CCD-A document'),
            true
        );
        $service->appendToSection(self::GLOBAL_SECTION_NAME, self::GLOBAL_KEY_CCDA_SORT_ORDER, $setting);
    }

    public function getMaxSections(): int
    {
        return intval($GLOBALS[self::GLOBAL_KEY_CCDA_MAX_SECTIONS] ?? 0);
    }

    public function getSectionDisplayOrder(): array
    {
        $sortOrder = array();
        if (!empty($GLOBALS[self::GLOBAL_KEY_CCDA_SORT_ORDER])) {
            $sortString = $GLOBALS[self::GLOBAL_KEY_CCDA_SORT_ORDER] ?? "";
            $sortOrder = explode(";", $sortString);
        }
        return $sortOrder;
    }
}
