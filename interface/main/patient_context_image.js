/**
 * Patient Context Image Enhancement
 *
 * This module enhances OpenEMR's UI by displaying patient photos in the context box
 * throughout the application to help healthcare professionals quickly identify patients.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  [Your Name]
 * @copyright Copyright (c) 2025 [Your Name]
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function() {
    // Configuration
    const patientContextContainerId = 'attendantData';
    const patientPhotoCategory = window.globals?.patient_photo_category_name || 'Patient Photograph';
    
    // URLs for API endpoints
    const patientImageApiUrl = `${window.globals?.webroot}/api/patient/{pid}/document`;
    const documentApiUrl = `${window.globals?.webroot}/apis/api/document`;
    
    /**
     * Initialize the enhanced patient context
     */
    function initPatientContextWithImage() {
        const patientDataContainer = document.getElementById(patientContextContainerId);
        if (!patientDataContainer) return;
        
        // Get patient ID from the data attribute or global variable
        const patientId = patientDataContainer.dataset.pid || 
                         window.globals?.pid || 
                         window.pid;
        
        if (!patientId) return;
        
        // Create the image container if it doesn't exist
        let imageContainer = document.getElementById('patient_image_container');
        if (!imageContainer) {
            imageContainer = document.createElement('div');
            imageContainer.id = 'patient_image_container';
            imageContainer.className = 'patient-image-container';
            
            // Find the best place to insert the image (at the beginning of the patient data)
            const firstChild = patientDataContainer.firstChild;
            patientDataContainer.insertBefore(imageContainer, firstChild);
            
            // Add default placeholder
            const placeholderImg = document.createElement('div');
            placeholderImg.className = 'patient-image-placeholder';
            placeholderImg.innerHTML = '<i class="fa fa-user" aria-hidden="true"></i>';
            imageContainer.appendChild(placeholderImg);
        }
        
        // Load patient image
        loadPatientImage(patientId, imageContainer);
        
        // Set up event listeners for image changes
        setupImageChangeListeners(patientId, imageContainer);
    }
    
    /**
     * Load patient image from server
     *
     * @param {string} patientId - The patient's ID
     * @param {HTMLElement} container - The container element for the image
     */
    function loadPatientImage(patientId, container) {
        // First try to get from the documents API
        fetch(patientImageApiUrl.replace('{pid}', patientId))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Find the most recent photo in the specified category
                const photos = data.filter(doc => 
                    doc.categories && doc.categories.some(cat => 
                        cat.name === patientPhotoCategory
                    )
                ).sort((a, b) => new Date(b.date) - new Date(a.date));
                
                if (photos.length > 0) {
                    // Get the document URL for the most recent photo
                    const photoUrl = `${documentApiUrl}/${photos[0].id}/retrieve`;
                    updatePatientImage(container, photoUrl);
                }
            })
            .catch(error => {
                console.error('Error fetching patient image:', error);
            });
    }
    
    /**
     * Update the patient image in the container
     *
     * @param {HTMLElement} container - The container element for the image
     * @param {string} imageUrl - The URL of the patient image
     */
    function updatePatientImage(container, imageUrl) {
        // Clear existing content
        container.innerHTML = '';
        
        // Create new image element
        const img = document.createElement('img');
        img.className = 'patient-context-image';
        img.src = imageUrl;
        img.alt = 'Patient Photo';
        img.setAttribute('aria-label', 'Patient Photograph');
        
        // Handle errors by showing placeholder
        img.onerror = function() {
            container.innerHTML = '';
            const placeholder = document.createElement('div');
            placeholder.className = 'patient-image-placeholder';
            placeholder.innerHTML = '<i class="fa fa-user" aria-hidden="true"></i>';
            container.appendChild(placeholder);
        };
        
        container.appendChild(img);
    }
    
    /**
     * Set up event listeners for image changes
     *
     * @param {string} patientId - The patient's ID
     * @param {HTMLElement} container - The container element for the image
     */
    function setupImageChangeListeners(patientId, container) {
        // Listen for custom events that indicate patient image changes
        document.addEventListener('patientImageUpdated', function(e) {
            if (e.detail && e.detail.pid === patientId) {
                loadPatientImage(patientId, container);
            }
        });
        
        // Listen for changes in the patient context
        window.addEventListener('patientSelect', function(e) {
            if (e.detail && e.detail.pid) {
                loadPatientImage(e.detail.pid, container);
            }
        });
        
        // Poll for changes periodically as a fallback
        setInterval(function() {
            loadPatientImage(patientId, container);
        }, 300000); // 5 minutes
    }
    
    // Initialize when the DOM is ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initPatientContextWithImage();
    } else {
        document.addEventListener('DOMContentLoaded', initPatientContextWithImage);
    }
    
    // Expose a method to manually refresh the image
    window.refreshPatientContextImage = function(patientId) {
        const container = document.getElementById('patient_image_container');
        if (container && patientId) {
            loadPatientImage(patientId, container);
        }
    };
})();