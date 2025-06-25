/**
 * OpenEMR Modal Accessibility Fix
 * 
 * This script fixes accessibility issues with Bootstrap modals by replacing
 * the problematic aria-hidden attribute with the more appropriate inert attribute.
 * 
 * This addresses the warning:
 * "Blocked aria-hidden on an element because its descendant retained focus. The focus must not be 
 * hidden from assistive technology users. Avoid using aria-hidden on a focused element or its ancestor."
 * 
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function() {
    'use strict';

    // Execute when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all Bootstrap modals (including ones from third-party plugins)
        setupModalAccessibility();
    });

    /**
     * Apply accessibility fixes to all Bootstrap modals
     */
    function setupModalAccessibility() {
        // Function to apply inert attribute to non-modal elements
        function applyInertToBackground(modalElement) {
            // Remove any aria-hidden that Bootstrap might add
            modalElement.removeAttribute('aria-hidden');

            // Find all direct children of body except the current modal and modal backdrop
            var nonModalElements = document.querySelectorAll('body > *:not(.modal-backdrop)');
            
            // Apply inert to all non-modal elements
            for (var i = 0; i < nonModalElements.length; i++) {
                var element = nonModalElements[i];
                // Skip the modal's parent if it's a direct child of body
                if (!element.contains(modalElement) && !modalElement.contains(element) && 
                    !element.classList.contains('modal') && 
                    !element.classList.contains('modal-backdrop')) {
                    element.setAttribute('inert', '');
                }
            }
        }

        // Function to remove inert attribute from all elements
        function removeInertFromAll() {
            var elements = document.querySelectorAll('[inert]');
            for (var i = 0; i < elements.length; i++) {
                elements[i].removeAttribute('inert');
            }
        }

        // Setup jQuery event listeners if jQuery and Bootstrap are loaded
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('show.bs.modal', '.modal', function() {
                applyInertToBackground(this);
            }).on('hidden.bs.modal', '.modal', function() {
                removeInertFromAll();
            });
        }

        // Fallback using mutation observer for non-jQuery implementations or future compatibility
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class' || mutation.attributeName === 'style') {
                    var target = mutation.target;
                    
                    // Check if this is a modal being shown
                    if (target.classList && 
                        target.classList.contains('modal') && 
                        (target.style.display === 'block' || 
                         window.getComputedStyle(target).display === 'block')) {
                        
                        // Apply our fix
                        applyInertToBackground(target);
                    }
                    
                    // Check if modal is being hidden
                    if (target.classList && 
                        target.classList.contains('modal') && 
                        target.style.display === 'none') {
                        
                        // Remove our fix
                        removeInertFromAll();
                    }
                }
            });
        });

        // Watch for changes to class and style attributes on .modal elements
        var modals = document.querySelectorAll('.modal');
        for (var i = 0; i < modals.length; i++) {
            observer.observe(modals[i], { attributes: true });
        }

        // Also watch for dynamically added modals
        observer.observe(document.body, { 
            childList: true, 
            subtree: true,
            attributes: false
        });
    }

})();
