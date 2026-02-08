
/**
 * ProductRegistrationService (JavaScript)
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";

function ProductRegistrationService() {
    top.restoreSession();
    const self = this;

    self.getProductStatus = function (callback) {
        $.ajax({
            url: top.webroot_url + '/interface/product_registration/product_registration_controller.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                _genericAjaxSuccessHandler(response, callback);
            },
            error: function (jqXHR) {
                _genericAjaxFailureHandler(jqXHR, callback);
            }
        });
    };

    self.submitRegistration = function (data, callback) {
        top.restoreSession();
        $.ajax({
            url:top.webroot_url + '/interface/product_registration/product_registration_controller.php',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                _genericAjaxSuccessHandler(response, callback);
            },
            error: function (jqXHR) {
                _genericAjaxFailureHandler(jqXHR, callback);
            }
        });
    };

    const _genericAjaxSuccessHandler = function (response, callback) {
        if (response) {
            return callback(null, response);
        }
        return callback(registrationTranslations.genericError, null);
    };

    const _genericAjaxFailureHandler = function (jqXHR, callback) {
        if (jqXHR && Object.prototype.hasOwnProperty.call(jqXHR, 'responseText')) {
            try {
                let rawErrorObject = jqXHR.responseText;
                let parsedErrorObject = JSON.parse(rawErrorObject);
                if (parsedErrorObject && Object.prototype.hasOwnProperty.call(parsedErrorObject, 'message')) {
                    callback(parsedErrorObject.message, null);
                }
            } catch (jsonParseException) {
                callback(registrationTranslations.genericError, null);
            }
        } else {
            callback(registrationTranslations.genericError, null);
        }
    };
}
