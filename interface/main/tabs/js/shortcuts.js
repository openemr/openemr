/**
 * Keyboard shortcuts for the tabbed main screen.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

hotkeys('alt+1', (e, h) => {
    e.preventDefault();
    navigateTab(webroot_url + '/interface/main/main_info.php', 'cal', function () {
        activateTabByName('cal', true);
    });
    return false;
});

hotkeys('alt+2', function (e, handler) {
    e.preventDefault();
    navigateTab(webroot_url + '/interface/main/finder/dynamic_finder.php', 'fin', function () {
        activateTabByName('fin', true);
    });
    return false;
});

// Eventually allow refreshing of a tab with alt+r, but not working just yet
hotkeys('alt+r', function (e, h) {
    e.preventDefault();
    tabRefresh();
    return false;
});

hotkeys('alt+shift+w', function (e, h) {
    e.preventDefault();
    closePatientChart();
    return false;
});
