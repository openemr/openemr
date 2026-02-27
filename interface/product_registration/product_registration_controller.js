/**
 * ProductRegistrationController (JavaScript)
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

"use strict";

function ProductRegistrationController() {
    // Helper to get or create Bootstrap modal instance (compatible with BS5 versions)
    const _getModalInstance = function (modalEl) {
        if (!modalEl) {
            console.error('ProductRegistration: Modal element not found');
            return null;
        }
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            console.error('ProductRegistration: Bootstrap Modal not available');
            return null;
        }
        // Bootstrap 5.1+ has getOrCreateInstance
        if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            return bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        // Bootstrap 5.0 has getInstance
        if (typeof bootstrap.Modal.getInstance === 'function') {
            return bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        }
        // Direct instantiation fallback
        return new bootstrap.Modal(modalEl);
    };

    const _closeModal = function (closeWaitTimeMilliseconds) {
        setTimeout(function () {
            var modalEl = document.querySelector('.product-registration-modal');
            var modalInstance = _getModalInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
                // BS5: Ensure backdrop is removed after hide
                setTimeout(function() {
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) { backdrop.remove(); }
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    document.body.style.overflow = '';
                }, 300);
            }
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

        // Wire up button handlers (use off().on() to prevent duplicates)
        $('.product-registration-modal .submit').off('click').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            _formSubmissionHandler();
        });

        $('.product-registration-modal .nothanks').off('click').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            _formCancellationHandler();
        });

        // Handle enter key on the email field
        $('.product-registration-modal .email').off('keypress').on('keypress', function (event) {
            if (event.which === 13) {
                event.preventDefault();
                _formSubmissionHandler();
            }
        });

        // Show the modal
        var modalEl = document.querySelector('.product-registration-modal');
        var modalInstance = _getModalInstance(modalEl);
        if (modalInstance) {
            modalInstance.show();
        }
    };
}
