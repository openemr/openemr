/**
 * GCIP Authentication Module JavaScript
 * 
 * AI-Generated Content Start
 * This JavaScript file provides client-side functionality for GCIP
 * authentication including login button interactions, OAuth2 flow
 * initiation, and user interface enhancements.
 * AI-Generated Content End
 */

(function() {
    'use strict';
    
    // GCIP Authentication Manager - AI-Generated
    window.GcipAuth = {
        
        /**
         * Initialize GCIP authentication functionality - AI-Generated
         */
        init: function() {
            this.setupLoginButton();
            this.handleCallbackErrors();
            this.setupUserInterface();
        },
        
        /**
         * Setup GCIP login button functionality - AI-Generated
         */
        setupLoginButton: function() {
            const gcipButton = document.getElementById('gcip-login-btn');
            if (gcipButton) {
                gcipButton.addEventListener('click', this.initiateLogin.bind(this));
            }
            
            // Add GCIP button to existing login forms - AI-Generated
            this.addGcipButtonToLoginForms();
        },
        
        /**
         * Add GCIP login button to existing login forms - AI-Generated
         */
        addGcipButtonToLoginForms: function() {
            const loginForms = document.querySelectorAll('form[name="login"], form#login_form');
            
            loginForms.forEach(form => {
                // Check if GCIP button already exists - AI-Generated
                if (form.querySelector('.gcip-login-button')) {
                    return;
                }
                
                // Create GCIP login button - AI-Generated
                const gcipContainer = document.createElement('div');
                gcipContainer.className = 'gcip-auth-container';
                gcipContainer.innerHTML = `
                    <div class="gcip-separator">
                        <span>Or sign in with</span>
                    </div>
                    <button type="button" class="btn gcip-login-button" id="gcip-login-btn">
                        <img src="${this.getModulePath()}/public/images/google-icon.svg" alt="Google" class="gcip-icon">
                        Sign in with Google
                    </button>
                `;
                
                // Insert after login button - AI-Generated
                const loginButton = form.querySelector('input[type="submit"], button[type="submit"]');
                if (loginButton && loginButton.parentNode) {
                    loginButton.parentNode.insertBefore(gcipContainer, loginButton.nextSibling);
                }
                
                // Setup click handler - AI-Generated
                const newButton = gcipContainer.querySelector('#gcip-login-btn');
                if (newButton) {
                    newButton.addEventListener('click', this.initiateLogin.bind(this));
                }
            });
        },
        
        /**
         * Initiate GCIP OAuth2 login flow - AI-Generated
         */
        initiateLogin: function(event) {
            event.preventDefault();
            
            // Show loading state - AI-Generated
            const button = event.target.closest('.gcip-login-button');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Connecting...';
            }
            
            // Generate state parameter for CSRF protection - AI-Generated
            const state = this.generateState();
            
            // Store state in session - AI-Generated
            this.storeState(state).then(() => {
                // Build authorization URL - AI-Generated
                const authUrl = this.buildAuthorizationUrl(state);
                
                if (authUrl) {
                    // Store current page as return URL - AI-Generated
                    this.storeReturnUrl();
                    
                    // Redirect to Google OAuth2 - AI-Generated
                    window.location.href = authUrl;
                } else {
                    this.showError('GCIP authentication is not properly configured.');
                    this.resetButton(button);
                }
            }).catch(error => {
                console.error('Failed to initiate GCIP login:', error);
                this.showError('Failed to initiate authentication. Please try again.');
                this.resetButton(button);
            });
        },
        
        /**
         * Generate random state parameter - AI-Generated
         */
        generateState: function() {
            const array = new Uint8Array(32);
            crypto.getRandomValues(array);
            return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
        },
        
        /**
         * Store state parameter in session - AI-Generated
         */
        storeState: function(state) {
            return fetch(this.getModulePath() + '/public/ajax/store_state.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ state: state }),
                credentials: 'same-origin'
            });
        },
        
        /**
         * Store return URL for post-authentication redirect - AI-Generated
         */
        storeReturnUrl: function() {
            const returnUrl = window.location.href;
            fetch(this.getModulePath() + '/public/ajax/store_return_url.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ return_url: returnUrl }),
                credentials: 'same-origin'
            }).catch(error => {
                console.warn('Failed to store return URL:', error);
            });
        },
        
        /**
         * Build OAuth2 authorization URL - AI-Generated
         */
        buildAuthorizationUrl: function(state) {
            // This would typically be generated server-side for security
            // For demo purposes, we'll redirect to a server endpoint that builds the URL
            return this.getModulePath() + '/public/auth/login.php?state=' + encodeURIComponent(state);
        },
        
        /**
         * Handle OAuth2 callback errors - AI-Generated
         */
        handleCallbackErrors: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const gcipError = urlParams.get('gcip_error');
            
            if (gcipError) {
                this.showError(decodeURIComponent(gcipError));
                
                // Clean up URL - AI-Generated
                const url = new URL(window.location);
                url.searchParams.delete('gcip_error');
                window.history.replaceState({}, '', url);
            }
        },
        
        /**
         * Setup user interface enhancements - AI-Generated
         */
        setupUserInterface: function() {
            // Add GCIP status indicator if user is GCIP authenticated - AI-Generated
            if (this.isGcipAuthenticated()) {
                this.addGcipStatusIndicator();
            }
            
            // Setup logout handler for GCIP users - AI-Generated
            this.setupLogoutHandler();
        },
        
        /**
         * Check if current user is GCIP authenticated - AI-Generated
         */
        isGcipAuthenticated: function() {
            // This would be determined by checking session or user data
            return document.body.getAttribute('data-gcip-authenticated') === 'true';
        },
        
        /**
         * Add GCIP status indicator to UI - AI-Generated
         */
        addGcipStatusIndicator: function() {
            const userInfo = document.querySelector('.user-info, .navbar-nav');
            if (userInfo) {
                const indicator = document.createElement('span');
                indicator.className = 'gcip-status-indicator';
                indicator.innerHTML = '<i class="fa fa-google"></i> GCIP';
                indicator.title = 'Authenticated via Google Cloud Identity Platform';
                userInfo.appendChild(indicator);
            }
        },
        
        /**
         * Setup logout handler for GCIP users - AI-Generated
         */
        setupLogoutHandler: function() {
            const logoutLinks = document.querySelectorAll('a[href*="logout"], input[value*="Logout"]');
            
            logoutLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    if (this.isGcipAuthenticated()) {
                        // Add GCIP-specific logout handling if needed
                        this.handleGcipLogout();
                    }
                });
            });
        },
        
        /**
         * Handle GCIP-specific logout operations - AI-Generated
         */
        handleGcipLogout: function() {
            // Clean up GCIP session data
            fetch(this.getModulePath() + '/public/ajax/logout.php', {
                method: 'POST',
                credentials: 'same-origin'
            }).catch(error => {
                console.warn('Failed to clean up GCIP session:', error);
            });
        },
        
        /**
         * Show error message to user - AI-Generated
         */
        showError: function(message) {
            // Create error alert - AI-Generated
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger gcip-error-alert';
            alertDiv.innerHTML = `
                <strong>Authentication Error:</strong> ${this.escapeHtml(message)}
                <button type="button" class="close" onclick="this.parentElement.remove()">
                    <span>&times;</span>
                </button>
            `;
            
            // Insert error message - AI-Generated
            const container = document.querySelector('.container, .login-container, body');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
                
                // Auto-remove after 10 seconds - AI-Generated
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 10000);
            }
        },
        
        /**
         * Reset button to original state - AI-Generated
         */
        resetButton: function(button) {
            if (button) {
                button.disabled = false;
                button.innerHTML = `
                    <img src="${this.getModulePath()}/public/images/google-icon.svg" alt="Google" class="gcip-icon">
                    Sign in with Google
                `;
            }
        },
        
        /**
         * Get module path for asset URLs - AI-Generated
         */
        getModulePath: function() {
            return window.webroot + '/interface/modules/custom_modules/oe-module-gcip-auth';
        },
        
        /**
         * Escape HTML to prevent XSS - AI-Generated
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when DOM is ready - AI-Generated
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            GcipAuth.init();
        });
    } else {
        GcipAuth.init();
    }
    
})();