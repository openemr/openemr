<?php

/**
 * XpathsConstants class
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstants
{
    public const COLLAPSED_MENU_BUTTON = '//div[@id="mainBox"]/nav/button[@data-target="#mainMenu"]';

    public const ADMINISTRATION_MENU = '//div[@id="mainMenu"]//div[text()="Admin"]';

    public const USERS_SUBMENU = '//div[@id="mainMenu"]//div[text()="Users"]';

    public const ACTIVE_TAB = "//div[@id='tabs_div']/div/div[not(contains(concat(' ',normalize-space(@class),' '),' tabsNoHover '))]";

    public const ADMIN_IFRAME = "//*[@id='framesDisplay']//iframe[@name='adm']";
}
