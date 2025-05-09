/**
 * ProductRegistrationController (JavaScript)
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";

function ProductRegistrationController() {
    const _closeModal = function (closeWaitTimeMilliseconds) {
        setTimeout(function () {
            $('.product-registration-modal').modal('toggle');
        }, closeWaitTimeMilliseconds || 0);
    };
    const _registrationFailedHandler = function (error) {
        $('#submit_registration_loader').hide();
        $('.product-registration-modal .message').text(error);
    };
    const _registrationCreatedHandler = function (data) {
        // If telemetry is enabled, set the telemetryEnabled flag
        if (data && typeof data === 'object' && Object.prototype.hasOwnProperty.call(data, 'telemetry_enabled') && data.telemetry_enabled) {
            top.telemetryEnabled = 1;
        }
        _closeModal();
    };
    const _formCancellationHandler = function () {
        _closeModal();
        // Ask later by user. Handle by ignoring the registration.
        top.restoreSession();
    };
    const _formSubmissionHandler = function () {
        let email = $('.product-registration-modal .email').val() || null;
        if (email > '' && email.indexOf('@') < 0) {
            $('.product-registration-modal .message').text(registrationTranslations.pleaseProvideValidEmail);
            $('.product-registration-modal .email').focus();
            return false;
        }

        $('#submit_registration_loader').show();

        $('.product-registration-modal .message').text('');
        // Read the checkbox values; use 1 for checked, 0 otherwise.
        let allowTelemetry = $('.product-registration-modal #allowTelemetry').is(':checked') ? 1 : 0;
        // Build the data object to send to the service
        const formData = {
            email: email,
            allow_telemetry: allowTelemetry,
        };

        _productRegistrationService.submitRegistration(formData, function (err, data) {
            if (err) {
                return _registrationFailedHandler(err);
            }
            _registrationCreatedHandler(data);
        });
    };

    const self = this;

    const _productRegistrationService = new ProductRegistrationService();

    self.getProductRegistrationStatus = function (callback) {
        _productRegistrationService.getProductStatus(function (err, data) {
            if (err) {
                return callback(err, null);
            }
            callback(null, data);
        });
    };

    self.showProductRegistrationModal = function () {
        _displayFormView();
    };

    const _displayFormView = function () {
        // Update modal header with title
        $('.product-registration-modal .modal-header').text(registrationTranslations.title);

        // Wire up button handlers
        $('.product-registration-modal .submit').on('click', function (e) {
            _formSubmissionHandler();
            return false;
        });

        $('.product-registration-modal .nothanks').on('click', function (e) {
            _formCancellationHandler();
            return false;
        });

        // Toggle the modal display
        $('.product-registration-modal').modal('toggle');

        // Handle enter key on the email field
        $('.product-registration-modal .email').on('keypress', function (event) {
            if (event.which === 13) {
                _formSubmissionHandler();
                return false;
            }
        });
    };
}
