<?php

/**
 * XpathsConstants class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstants
{
    public const USER_MENU_ICON = '//i[@id="user_icon"]';

    public const MODAL_TITLE = '//h5[@class="modal-title"]';

    public const ACTIVE_TAB = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

    public const ADMIN_IFRAME = "//*[@id='framesDisplay']//iframe[@name='adm']";

    public const PATIENT_IFRAME = "//*[@id='framesDisplay']//iframe[@name='pat']";

    public const PATIENT_FINDER_IFRAME = "//*[@id='framesDisplay']//iframe[@name='fin']";

    public const ENCOUNTER_IFRAME = "//*[@id='framesDisplay']//iframe[@name='enc']";

    public const ENCOUNTER_FORMS_IFRAME = "//iframe[@src='forms.php']";
}
