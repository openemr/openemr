<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin;

/**
 * Sample holder of all events related to Admin actions.
 *
 * Central holder for all Admin events, a convenience location for users to
 * subscribe. For instance, when registering, always subscribe to
 * `AdminEvents::BUILD_MAIN_MENU` instead of `admin.menu.build`
 *
 * @package OpenEMR\Admin
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class AdminEvents
{
    const BUILD_MAIN_MENU = "admin.menu.build";
}
