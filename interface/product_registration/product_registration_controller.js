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
        $('.product-registration-modal .message').text(error);
    };
    const _registrationCreatedHandler = function (data) {
        $('.product-registration-modal .context').remove();
        $('.product-registration-modal .email').remove();
        $('.product-registration-modal .message').text(registrationTranslations.registeredSuccess);
        _closeModal(1000);
        self.displayRegistrationInformationIfDivExists(data);
    };
    const _formCancellationHandler = function () {
        _closeModal();
        // Ask later by user. Handle by ignoring the registration.
        top.restoreSession();
    };
    const _formSubmissionHandler = function () {
        let email = $('.product-registration-modal .email').val() || '';
        if (email > '' && email.indexOf('@') < 0) {
            $('.product-registration-modal .message').text(registrationTranslations.pleaseProvideValidEmail);
            $('.product-registration-modal .email').focus();
            return false;
        }
        if (email === '') {
            $('.product-registration-modal .message').text(registrationTranslations.pleaseProvideValidEmail);
            let returned = confirm(xl('Continue without registering an email address?'));
            if (returned === false) {
                $('.product-registration-modal .email').focus();
                return false;
            }
        }

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

        $('.product-registration-modal .email').val(registrationConstants.email);

        // Handle enter key on the email field
        $('.product-registration-modal .email').on('keypress', function (event) {
            if (event.which === 13) {
                _formSubmissionHandler();
                return false;
            }
        });
    };

    // If we are on the about_page, show the registration data.
    self.displayRegistrationInformationIfDivExists = function (data) {
        if ($('.product-registration').length > 0) {
            $('.product-registration .email').text(registrationTranslations.registeredEmail + ' ' + data.email);
        }
    };
}
