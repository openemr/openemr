/**
 * Appointment TeleHealth javascript library for interacting with the appointment dialog window.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function(window, comlink) {

    /**
     * @type {string} The path of where the module is installed at.  In a multisite we pull this from the server configuration, otherwise we default here
     */
    let moduleLocation = comlink.settings.modulePath || '/interface/modules/custom_modules/oe-module-comlink-telehealth/';

    let defaultTranslations = {
    };

    function getProviderSelectNode() {
        let node = window.document.querySelector("#provd");
        if (!node) {
            console.error("Failed to find node with selector #provd");
            return;
        }
        return node;
    }

    function hideInvalidTelehealthProviders(telehealthProviders) {
        telehealthProviders = telehealthProviders || [];
        let ids = telehealthProviders.map(p => +p).filter(p => !isNaN(p));
        ids.sort();

       let providerSelector = getProviderSelectNode();
        if (!providerSelector) {
            console.error("Failed to find provider select node");
            return;
        }

        let options = providerSelector.options;
        let selectedValues = [];
        for (var index = 0; index < options.length; index++) {
            let value = +(options[index].value || 0);
            console.log("options are ", options[index]);
            // we could do a binary search here if we need to for the amount of data... should be fairly small though for 3-4000 providers.
            if (ids.indexOf(value) === -1) {
                if (options[index].selected) {
                    selectedValues.push(options[index]);
                }
                options[index].selected = false;
                options[index].classList.add("d-none");
            } else {
                console.log("options are ", options[index]);
                options[index].classList.remove("d-none");
            }
        }
        if (selectedValues.length > 0) {
            providerSelector.selectedIndex = -1; // if nothing is visible is selected we should remove all selected items
        }
    }

    function displayAllProviders() {
        let providerSelector = getProviderSelectNode();
        if (!providerSelector) {
            console.error("Failed to find provider select node");
            return;
        }

        let options = providerSelector.options;
        for (var index = 0; index < options.length; index++) {
            options[index].classList.remove("d-none");
        }
    }

    function updateAppointmentScreenForCategory(category, telehealthProviders, telehealthCategories) {

        let node = window.document.querySelector("#form_category");
        if (!node) {
            console.error("Failed to find node with selector #form_category");
            return;
        }

        // setup the change order
        // if the current category has an id of one of the telehealth categories
        // grab the select options and set their display option to be hidden if the provider is not in the
        // telehealth provider lists
        // Note this will probably max out at a few thousand providers... so if an OpenEMR install is larger than that
        // this will need to be adjusted.
        let value = +(node.value || 0);
        if (value > 0 && telehealthCategories.indexOf(value) !== -1) {
            // now let's hide our providers
            hideInvalidTelehealthProviders(telehealthProviders);
        } else {
            displayAllProviders();
        }
    }

    function initAppointmentWithTelehealth(telehealthProviders, telehealthCategories) {
        let node = window.document.querySelector("#form_category");
        if (!node) {
            console.error("Failed to find node with selector #form_category");
            return;
        }

        node.addEventListener('change', function(evt) {
            let select = evt.currentTarget;
            updateAppointmentScreenForCategory(select.value, telehealthProviders, telehealthCategories);
        });

        // go with the initial value
        updateAppointmentScreenForCategory(node.value, telehealthProviders, telehealthCategories);
    }

    comlink.initAppointmentWithTelehealth = initAppointmentWithTelehealth;

    let translations = comlink.translations || defaultTranslations;
    window.comlink = comlink;
})(window, window.comlink || {});