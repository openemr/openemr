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

use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\ListService;

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
        $setting->addFieldOption(GlobalSetting::DATA_TYPE_OPTION_LIST_ID, 'ccda-sections');
        $service->appendToSection(self::GLOBAL_SECTION_NAME, self::GLOBAL_KEY_CCDA_SORT_ORDER, $setting);
    }

    public function getMaxSections(): int
    {
        return intval($GLOBALS[self::GLOBAL_KEY_CCDA_MAX_SECTIONS] ?? 0);
    }

    public function getSectionDisplayOrder(): array
    {
        $codeService = new CodeTypesService();
        $sortOrder = array();
        $sortOrderIndexesByKeys = [];
        if (!empty($GLOBALS[self::GLOBAL_KEY_CCDA_SORT_ORDER])) {
            $sortString = $GLOBALS[self::GLOBAL_KEY_CCDA_SORT_ORDER] ?? "";
            $sortOrder = explode(";", $sortString);
            $sortOrderIndexesByKeys = array_combine($sortOrder, array_keys($sortOrder));
        }
        if (!empty($sortOrder)) {
            // now we are going to grab our keys from the list service
            $listService = new ListService();
            // should be less than 50 items, better to just use memory than try to hit the db off a search
            $sections = $listService->getOptionsByListName('ccda-sections');
            foreach ($sections as $section) {
                $option_id = $section['option_id'] ?? 'undefined';
                if (isset($sortOrderIndexesByKeys[$option_id])) {
                    $sortOrderIndex = $sortOrderIndexesByKeys[$option_id];
                    $oid = $section['codes'];
                    $parsedCode = $codeService->parseCode($oid);
                    $sortOrder[$sortOrderIndex] = $parsedCode['code'];
                }
            }
        }
        return $sortOrder;
    }
}
