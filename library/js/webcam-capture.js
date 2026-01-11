/**
 * Webcam Capture Module for OpenEMR
 *
 * Provides webcam capture functionality with base64 output for patient photo capture.
 * Uses modern browser APIs (navigator.mediaDevices.getUserMedia).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI-Generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, $) {
    'use strict';

    var WebcamCapture = {
        // Configuration
        config: {
            videoConstraints: {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            },
            outputFormat: 'image/jpeg',
            outputQuality: 0.9
        },

        // State
        stream: null,
        capturedData: null,

        // DOM Elements (set during init)
        elements: {
            video: null,
            canvas: null,
            capturedImage: null,
            errorContainer: null,
            webcamContainer: null,
            capturedImageContainer: null,
            btnCapture: null,
            btnRetake: null,
            btnUsePhoto: null,
            modal: null,
            targetInput: null,
            previewContainer: null,
            previewImage: null
        },

        /**
         * Initialize the webcam capture module
         * @param {Object} options - Configuration options
         */
        init: function(options) {
            // Merge options with defaults
            if (options) {
                $.extend(this.config, options);
            }

            // Cache DOM elements
            this.cacheElements();

            // Bind events
            this.bindEvents();
        },

        /**
         * Cache jQuery references to DOM elements
         */
        cacheElements: function() {
            this.elements.video = document.getElementById('webcamVideo');
            this.elements.canvas = document.getElementById('webcamCanvas');
            this.elements.capturedImage = $('#capturedImage');
            this.elements.errorContainer = $('#webcamError');
            this.elements.webcamContainer = $('#webcamContainer');
            this.elements.capturedImageContainer = $('#capturedImageContainer');
            this.elements.btnCapture = $('#btnCapture');
            this.elements.btnRetake = $('#btnRetake');
            this.elements.btnUsePhoto = $('#btnUsePhoto');
            this.elements.modal = $('#webcamModal');
            this.elements.targetInput = $('#patient_photo_base64');
            this.elements.previewContainer = $('#photoPreviewContainer');
            this.elements.previewImage = $('#photoPreview');
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // Open modal and start camera
            $('#btnTakePicture').on('click', function() {
                self.openModal();
            });

            // Capture photo
            this.elements.btnCapture.on('click', function() {
                self.capturePhoto();
            });

            // Retake photo
            this.elements.btnRetake.on('click', function() {
                self.retakePhoto();
            });

            // Use captured photo
            this.elements.btnUsePhoto.on('click', function() {
                self.usePhoto();
            });

            // Remove photo
            $('#btnRemovePhoto').on('click', function() {
                self.removePhoto();
            });

            // Stop camera when modal closes
            this.elements.modal.on('hidden.bs.modal', function() {
                self.stopCamera();
                self.resetModalState();
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
         * Start the webcam stream
         */
        startCamera: function() {
            var self = this;

            // Check for browser support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.showError(window.xljs_webcam_not_supported || 'Your browser does not support webcam access. Please use a modern browser like Chrome, Firefox, or Edge.');
                return;
            }

            // Check for HTTPS (required for getUserMedia except on localhost)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                this.showError(window.xljs_webcam_https_required || 'Webcam access requires a secure connection (HTTPS). Please contact your administrator.');
                return;
            }

            navigator.mediaDevices.getUserMedia(this.config.videoConstraints)
                .then(function(stream) {
                    self.stream = stream;
                    self.elements.video.srcObject = stream;
                    self.hideError();
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
                    self.showError(message);
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

            // Store base64 data in hidden field
            this.elements.targetInput.val(this.capturedData);

            // Show preview thumbnail
            this.elements.previewImage.attr('src', this.capturedData);
            this.elements.previewContainer.show();

            // Close modal
            this.elements.modal.modal('hide');
        },

        /**
         * Remove the captured photo
         */
        removePhoto: function() {
            this.elements.targetInput.val('');
            this.elements.previewImage.attr('src', '');
            this.elements.previewContainer.hide();
            this.capturedData = null;
        },

        /**
         * Reset modal to initial state
         */
        resetModalState: function() {
            this.capturedData = null;
            this.elements.webcamContainer.removeClass('d-none');
            this.elements.capturedImageContainer.addClass('d-none');
            this.elements.btnCapture.removeClass('d-none');
            this.elements.btnRetake.addClass('d-none');
            this.elements.btnUsePhoto.addClass('d-none');
            this.hideError();
        },

        /**
         * Show error message
         * @param {string} message - Error message to display
         */
        showError: function(message) {
            this.elements.errorContainer
                .text(message)
                .removeClass('d-none');
            this.elements.webcamContainer.addClass('d-none');
        },

        /**
         * Hide error message
         */
        hideError: function() {
            this.elements.errorContainer.addClass('d-none');
            this.elements.webcamContainer.removeClass('d-none');
        }
    };

    // Expose to global scope
    window.WebcamCapture = WebcamCapture;

})(window, jQuery);
