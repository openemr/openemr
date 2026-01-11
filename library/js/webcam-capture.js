/**
 * Webcam Capture Module for OpenEMR
 *
 * Provides webcam capture functionality with base64 output for patient photo capture.
 * Uses modern browser APIs (navigator.mediaDevices.getUserMedia).
 *
 * Supports two modes:
 * 1. Form mode: Stores captured photo in a hidden form field
 * 2. AJAX mode: Uploads photo directly via AJAX endpoint
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI-Generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, $) {
    'use strict';

    /**
     * Create a new WebcamCapture instance
     * @param {Object} options - Configuration options
     */
    function WebcamCapture(options) {
        this.config = $.extend({
            // Video constraints
            videoConstraints: {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            },
            // Output settings
            outputFormat: 'image/jpeg',
            outputQuality: 0.9,

            // Element selectors
            modalSelector: '#webcamModal',
            videoSelector: '#webcamVideo',
            canvasSelector: '#webcamCanvas',
            capturedImageSelector: '#capturedImage',
            errorContainerSelector: '#webcamError',
            webcamContainerSelector: '#webcamContainer',
            capturedImageContainerSelector: '#capturedImageContainer',
            btnCaptureSelector: '#btnCapture',
            btnRetakeSelector: '#btnRetake',
            btnUsePhotoSelector: '#btnUsePhoto',

            // Form mode elements (optional)
            triggerSelector: '#btnTakePicture',
            targetInputSelector: '#patient_photo_base64',
            previewContainerSelector: '#photoPreviewContainer',
            previewImageSelector: '#photoPreview',
            btnRemoveSelector: '#btnRemovePhoto',

            // Callbacks
            onPhotoUsed: null,      // function(base64Data) - called when photo is used
            onPhotoCaptured: null,  // function(base64Data) - called when photo is captured
            onError: null,          // function(error) - called on error
            onModalOpen: null,      // function() - called when modal opens
            onModalClose: null      // function() - called when modal closes
        }, options);

        this.stream = null;
        this.capturedData = null;
        this.elements = {};

        this._cacheElements();
        this._bindEvents();
    }

    WebcamCapture.prototype = {
        /**
         * Cache jQuery references to DOM elements
         */
        _cacheElements: function() {
            this.elements.modal = $(this.config.modalSelector);
            this.elements.video = $(this.config.videoSelector)[0];
            this.elements.canvas = $(this.config.canvasSelector)[0];
            this.elements.capturedImage = $(this.config.capturedImageSelector);
            this.elements.errorContainer = $(this.config.errorContainerSelector);
            this.elements.webcamContainer = $(this.config.webcamContainerSelector);
            this.elements.capturedImageContainer = $(this.config.capturedImageContainerSelector);
            this.elements.btnCapture = $(this.config.btnCaptureSelector);
            this.elements.btnRetake = $(this.config.btnRetakeSelector);
            this.elements.btnUsePhoto = $(this.config.btnUsePhotoSelector);
            this.elements.trigger = $(this.config.triggerSelector);
            this.elements.targetInput = $(this.config.targetInputSelector);
            this.elements.previewContainer = $(this.config.previewContainerSelector);
            this.elements.previewImage = $(this.config.previewImageSelector);
            this.elements.btnRemove = $(this.config.btnRemoveSelector);
        },

        /**
         * Bind event handlers
         */
        _bindEvents: function() {
            var self = this;

            // Trigger button opens modal
            if (this.elements.trigger.length) {
                this.elements.trigger.on('click', function() {
                    self.openModal();
                });
            }

            // Capture button
            this.elements.btnCapture.on('click', function() {
                self.capturePhoto();
            });

            // Retake button
            this.elements.btnRetake.on('click', function() {
                self.retakePhoto();
            });

            // Use photo button
            this.elements.btnUsePhoto.on('click', function() {
                self.usePhoto();
            });

            // Remove button (form mode)
            if (this.elements.btnRemove.length) {
                this.elements.btnRemove.on('click', function() {
                    self.removePhoto();
                });
            }

            // Stop camera when modal closes
            this.elements.modal.on('hidden.bs.modal', function() {
                self.stopCamera();
                self._resetModalState();
                if (typeof self.config.onModalClose === 'function') {
                    self.config.onModalClose();
                }
            });

            this.elements.modal.on('shown.bs.modal', function() {
                if (typeof self.config.onModalOpen === 'function') {
                    self.config.onModalOpen();
                }
            });
        },

        /**
         * Open modal and start webcam
         */
        openModal: function() {
            this.elements.modal.modal('show');
            this.startCamera();
        },

        /**
         * Close the modal
         */
        closeModal: function() {
            this.elements.modal.modal('hide');
        },

        /**
         * Start the webcam stream
         */
        startCamera: function() {
            var self = this;

            // Check for browser support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this._showError(window.xljs_webcam_not_supported || 'Your browser does not support webcam access. Please use a modern browser like Chrome, Firefox, or Edge.');
                return;
            }

            // Check for HTTPS (required for getUserMedia except on localhost)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                this._showError(window.xljs_webcam_https_required || 'Webcam access requires a secure connection (HTTPS). Please contact your administrator.');
                return;
            }

            navigator.mediaDevices.getUserMedia(this.config.videoConstraints)
                .then(function(stream) {
                    self.stream = stream;
                    self.elements.video.srcObject = stream;
                    self._hideError();
                })
                .catch(function(err) {
                    console.error('Webcam error:', err);
                    var message;
                    if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                        message = window.xljs_webcam_permission_denied || 'Camera access was denied. Please allow camera access in your browser settings and try again.';
                    } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                        message = window.xljs_webcam_not_found || 'No camera device found. Please connect a camera and try again.';
                    } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                        message = window.xljs_webcam_in_use || 'Camera is already in use by another application. Please close other applications using the camera.';
                    } else {
                        message = (window.xljs_webcam_error || 'Unable to access webcam: ') + err.message;
                    }
                    self._showError(message);
                    if (typeof self.config.onError === 'function') {
                        self.config.onError(err);
                    }
                });
        },

        /**
         * Stop the webcam stream
         */
        stopCamera: function() {
            if (this.stream) {
                this.stream.getTracks().forEach(function(track) {
                    track.stop();
                });
                this.stream = null;
            }
            if (this.elements.video) {
                this.elements.video.srcObject = null;
            }
        },

        /**
         * Capture photo from video stream
         */
        capturePhoto: function() {
            var video = this.elements.video;
            var canvas = this.elements.canvas;

            if (!video || !canvas) {
                return;
            }

            var ctx = canvas.getContext('2d');

            // Set canvas size to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw video frame to canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to base64
            this.capturedData = canvas.toDataURL(
                this.config.outputFormat,
                this.config.outputQuality
            );

            // Show captured image
            this.elements.capturedImage.attr('src', this.capturedData);
            this.elements.webcamContainer.addClass('d-none');
            this.elements.capturedImageContainer.removeClass('d-none');

            // Toggle buttons
            this.elements.btnCapture.addClass('d-none');
            this.elements.btnRetake.removeClass('d-none');
            this.elements.btnUsePhoto.removeClass('d-none');

            if (typeof this.config.onPhotoCaptured === 'function') {
                this.config.onPhotoCaptured(this.capturedData);
            }
        },

        /**
         * Retake photo - return to live preview
         */
        retakePhoto: function() {
            this.capturedData = null;
            this.elements.webcamContainer.removeClass('d-none');
            this.elements.capturedImageContainer.addClass('d-none');
            this.elements.btnCapture.removeClass('d-none');
            this.elements.btnRetake.addClass('d-none');
            this.elements.btnUsePhoto.addClass('d-none');
        },

        /**
         * Use the captured photo
         */
        usePhoto: function() {
            if (!this.capturedData) {
                return;
            }

            // If callback is provided, use it (AJAX mode)
            if (typeof this.config.onPhotoUsed === 'function') {
                this.config.onPhotoUsed(this.capturedData);
            } else {
                // Default form mode behavior
                this._usePhotoFormMode();
            }

            // Close modal
            this.closeModal();
        },

        /**
         * Default form mode: store in hidden field and show preview
         */
        _usePhotoFormMode: function() {
            if (this.elements.targetInput.length) {
                this.elements.targetInput.val(this.capturedData);
            }
            if (this.elements.previewImage.length) {
                this.elements.previewImage.attr('src', this.capturedData);
            }
            if (this.elements.previewContainer.length) {
                this.elements.previewContainer.show();
            }
        },

        /**
         * Remove the captured photo (form mode)
         */
        removePhoto: function() {
            if (this.elements.targetInput.length) {
                this.elements.targetInput.val('');
            }
            if (this.elements.previewImage.length) {
                this.elements.previewImage.attr('src', '');
            }
            if (this.elements.previewContainer.length) {
                this.elements.previewContainer.hide();
            }
            this.capturedData = null;
        },

        /**
         * Get the captured photo data
         * @returns {string|null} Base64 encoded image data
         */
        getCapturedData: function() {
            return this.capturedData;
        },

        /**
         * Reset modal to initial state
         */
        _resetModalState: function() {
            this.capturedData = null;
            this.elements.webcamContainer.removeClass('d-none');
            this.elements.capturedImageContainer.addClass('d-none');
            this.elements.btnCapture.removeClass('d-none');
            this.elements.btnRetake.addClass('d-none');
            this.elements.btnUsePhoto.addClass('d-none');
            this._hideError();
        },

        /**
         * Show error message
         * @param {string} message - Error message to display
         */
        _showError: function(message) {
            this.elements.errorContainer
                .text(message)
                .removeClass('d-none');
            this.elements.webcamContainer.addClass('d-none');
        },

        /**
         * Hide error message
         */
        _hideError: function() {
            this.elements.errorContainer.addClass('d-none');
            this.elements.webcamContainer.removeClass('d-none');
        }
    };

    // Expose to global scope
    window.WebcamCapture = WebcamCapture;

})(window, jQuery);
