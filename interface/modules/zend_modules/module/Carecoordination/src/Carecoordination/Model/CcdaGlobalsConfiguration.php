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

    // these are based on the four document types we allow
    const GLOBAL_KEY_CCDA_CCD_SORT_ORDER = 'ccda_ccd_section_sort_order';
    const GLOBAL_KEY_CCDA_REFERRAL_SORT_ORDER = 'ccda_referral_section_sort_order';
    const GLOBAL_KEY_CCDA_TOC_SORT_ORDER = 'ccda_toc_section_sort_order';
    const GLOBAL_KEY_CCDA_CAREPLAN_SORT_ORDER = 'ccda_careplan_section_sort_order';
    const GLOBAL_KEY_CCDA_DEFAULT_SORT_ORDER = "ccda_default_section_sort_order";

    /**
     * @var array in memory cache of the ccda list options in the database
     */
    private $ccdaSections;

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


        $docTypeSortOrderSections = [
            xl("CCD Document Section Display Order") => self::GLOBAL_KEY_CCDA_CCD_SORT_ORDER
            ,xl("Referral Document Section Display Order") => self::GLOBAL_KEY_CCDA_REFERRAL_SORT_ORDER
            ,xl("Transition of Care Document Section Display Order") => self::GLOBAL_KEY_CCDA_TOC_SORT_ORDER
            ,xl("Careplan Document Section Display Order") => self::GLOBAL_KEY_CCDA_CAREPLAN_SORT_ORDER
            ,xl("Referral Document Section Display Order") => self::GLOBAL_KEY_CCDA_REFERRAL_SORT_ORDER
        ];
        foreach ($docTypeSortOrderSections as $name => $globalKey) {
            $setting = new GlobalSetting(
                $name,
                GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR,
                '',
                xl('The order of clinical information sections to display when viewing a CCD-A document'),
                true
            );
            $setting->addFieldOption(GlobalSetting::DATA_TYPE_OPTION_LIST_ID, 'ccda-sections');
            $service->appendToSection(self::GLOBAL_SECTION_NAME, $globalKey, $setting);
        }

        $setting = new GlobalSetting(
            xl("Default Document Section Display Order"),
            GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR,
            '',
            xl('The order of clinical information sections to display when viewing a CCD-A document when the document type is not supported or is unknown'),
            true
        );
        $setting->addFieldOption(GlobalSetting::DATA_TYPE_OPTION_LIST_ID, 'ccda-sections');
        $service->appendToSection(self::GLOBAL_SECTION_NAME, self::GLOBAL_KEY_CCDA_DEFAULT_SORT_ORDER, $setting);
    }

    public function getMaxSections(): int
    {
        return intval($GLOBALS[self::GLOBAL_KEY_CCDA_MAX_SECTIONS] ?? 0);
    }

    /**
     * Retrieves an mapped array of sorted section oids where each key in the map is the oid of a document template in CCD-A
     * that we support inside of OpenEMR.  This will retrieve the global settings that users have configured for their
     * carecoordination document types.
     * @return array
     */
    public function getSectionDisplayOrder(): array
    {
        return [
            CcdaDocumentTemplateOids::CCD => $this->getSectionDisplayOrderForType(self::GLOBAL_KEY_CCDA_CCD_SORT_ORDER)
            ,CcdaDocumentTemplateOids::CAREPLAN => $this->getSectionDisplayOrderForType(self::GLOBAL_KEY_CCDA_CAREPLAN_SORT_ORDER)
            ,CcdaDocumentTemplateOids::TRANSFER_SUMMARY => $this->getSectionDisplayOrderForType(self::GLOBAL_KEY_CCDA_TOC_SORT_ORDER)
            ,CcdaDocumentTemplateOids::REFERRAL => $this->getSectionDisplayOrderForType(self::GLOBAL_KEY_CCDA_REFERRAL_SORT_ORDER)
            ,'default' => $this->getSectionDisplayOrderForType(self::GLOBAL_KEY_CCDA_DEFAULT_SORT_ORDER)
        ];
    }

    /**
     * Retrieves an array of sorted section oids for the given global key we want to retrieve.
     * @param string $key
     * @return array
     */
    private function getSectionDisplayOrderForType($key = self::GLOBAL_KEY_CCDA_CCD_SORT_ORDER)
    {
        $codeService = new CodeTypesService();
        $sortOrder = array();
        $sortOrderIndexesByKeys = [];
        if (!empty($GLOBALS[$key])) {
            $sortString = $GLOBALS[$key] ?? "";
            $sortOrder = explode(";", $sortString);
            $sortOrderIndexesByKeys = array_combine($sortOrder, array_keys($sortOrder));
        }
        if (!empty($sortOrder)) {
            // now we are going to grab our keys from the list service
            // should be less than 50 items, better to just use memory than try to hit the db off a search
            $sections = $this->getCcdaSections();
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

    private function getCcdaSections()
    {
        if (empty($this->ccdaSections)) {
            $listService = new ListService();
            $this->ccdaSections = $listService->getOptionsByListName('ccda-sections');
        }
        return $this->ccdaSections;
    }
}
