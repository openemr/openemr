/**
 * Handles the checking of a provider's telehealth registration when they login.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function(window, comlink) {
    let telehealth = comlink.telehealth || {};

    if (telehealth && telehealth.launchRegistrationChecker)
    {
        window.addEventListener('load', function() {
            telehealth.launchRegistrationChecker(false);
        });
    }
})(window, window.comlink || {});