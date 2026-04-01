<?php

/**
 * XpathsConstantsCodesScreenTrait class
 *
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <https://medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstantsCodesScreenTrait
{
    public const CODE_SEARCH_BOX_TRAIT = '//input[@type="text" and @name="search"]';
    public const CODE_SEARCH_BUTTON_TRAIT = '//input[@type="submit" and @name="go"]';
    public const CODE_NAVIGATE_NEXT_BUTTON_TRAIT = '//a[@rel="next"]';
    public const CODE_NAVIGATE_PREV_BUTTON_TRAIT = '//a[@rel="prev"]';
    public const CODE_TYPE_SELECTION_MENU_TRAIT = '//select[@name="filter[]"]';
    public const CODE_TYPE_RESULT_LIST_TRAIT = '//table[@class="table table-striped table-bordered"]/tbody';
    public const CODE_TYPE_ICD10_TRAIT = '//select[@name="filter[]"]/option[text()="ICD10 Diagnosis"]';
    public const CODE_TYPE_CQM_TRAIT = '//select[@name="filter[]"]/option[text()="CQM Valueset"]';
    public const CODE_RESULT_FIRST_CODE_TRAIT = '//table[@class="table table-striped table-bordered"]/tbody/tr[1]/td[1]';
}

