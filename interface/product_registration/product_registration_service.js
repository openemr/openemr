
/**
 * ProductRegistrationService (JavaScript)
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

"use strict";

function ProductRegistrationService() {
    top.restoreSession();
    const self = this;

    self.getProductStatus = function (callback) {
        $.ajax({
            url: registrationConstants.webroot + '/interface/product_registration/product_registration_controller.php',
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
            url: registrationConstants.webroot + '/interface/product_registration/product_registration_controller.php',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                _genericAjaxSuccessHandler(response, callback);
            },
            error: function (jqXHR) {
                alert(xl("Invalid Email Error. Please try again."));
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
