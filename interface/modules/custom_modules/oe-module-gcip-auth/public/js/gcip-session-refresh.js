/* jshint esversion: 6 */
/**
 * GCIP Session Refresh — silent client-side token refresh.
 *
 * Loads the Firebase compat SDK, initializes with the stored config,
 * and periodically refreshes the ID token before it expires. On success,
 * POSTs the fresh token to the core OIDC session refresh endpoint.
 *
 * Configuration is read from window.__gcipSessionRefresh (set by the
 * module's RenderEvent listener in Bootstrap.php).
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */
(function () {
    'use strict';

    const config = window.__gcipSessionRefresh;
    if (!config || !config.firebaseApiKey || !config.expiresAt) {
        return;
    }

    const REFRESH_ENDPOINT = `${config.webRoot}/library/ajax/oidc_session_refresh.php`;
    const MARGIN_MS = (config.refreshMarginMinutes || 5) * 60 * 1000;
    const CHECK_INTERVAL_MS = 30 * 1000; // check every 30 seconds
    const REDIRECT_DELAY_MS = 30 * 1000; // 30 seconds warning before redirect
    let refreshInProgress = false;
    let bannerShown = false;

    /**
     * Load a script by URL, returns a Promise.
     */
    function loadScript(url) {
        return new Promise(function (resolve, reject) {
            // Check if already loaded
            const existing = document.querySelector(`script[src="${url}"]`);
            if (existing) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = function () {
                reject(new Error(`Failed to load: ${url}`));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Initialize Firebase (compat SDK) if not already initialized.
     */
    function initFirebase() {
        if (typeof firebase !== 'undefined' && firebase.apps && firebase.apps.length > 0) {
            return Promise.resolve();
        }

        return loadScript('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js')
            .then(function () {
                return loadScript('https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js');
            })
            .then(function () {
                firebase.initializeApp({
                    apiKey: config.firebaseApiKey,
                    authDomain: config.firebaseAuthDomain,
                    projectId: config.firebaseProjectId
                });

                if (config.tenantId) {
                    firebase.auth().tenantId = config.tenantId;
                }
            });
    }

    /**
     * Show a warning banner before redirect.
     */
    function showExpiryBanner() {
        if (bannerShown) {
            return;
        }
        bannerShown = true;

        const banner = document.createElement('div');
        banner.id = 'oidc-session-expiry-banner';
        banner.style.cssText = `position:fixed;top:0;left:0;right:0;z-index:99999;
            background:#dc3545;color:#fff;padding:12px 20px;text-align:center;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
            font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,0.3);`;

        let remaining = Math.ceil(REDIRECT_DELAY_MS / 1000);
        banner.textContent = `Your session is expiring. Please save your work. Redirecting to login in ${remaining} seconds...`;
        document.body.appendChild(banner);

        const countdown = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdown);
                redirectToLogin();
                return;
            }
            banner.textContent = `Your session is expiring. Please save your work. Redirecting to login in ${remaining} seconds...`;
        }, 1000);
    }

    /**
     * Redirect to login page.
     */
    function redirectToLogin() {
        window.top.location.href = `${config.webRoot}/interface/login/login.php?site=${encodeURIComponent(config.siteId || 'default')}`;
    }

    /**
     * Attempt to refresh the token silently.
     */
    function refreshToken() {
        if (refreshInProgress) {
            return;
        }
        refreshInProgress = true;

        initFirebase()
            .then(function () {
                const user = firebase.auth().currentUser;
                if (!user) {
                    // User not signed into Firebase — session can't be refreshed
                    showExpiryBanner();
                    return;
                }

                return user.getIdToken(true);
            })
            .then(function (idToken) {
                if (!idToken) {
                    return;
                }

                const formData = new FormData();
                formData.append('oidc_id_token', idToken);
                formData.append('csrf_token_form', config.csrfToken);

                return fetch(REFRESH_ENDPOINT, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            })
            .then(function (response) {
                if (!response) {
                    return;
                }

                if (!response.ok) {
                    // Server rejected the refresh
                    showExpiryBanner();
                    return;
                }

                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    return;
                }

                // Update local expiry for next check
                config.expiresAt = data.expires_at;
                refreshInProgress = false;
            })
            .catch(function () {
                // Network error or Firebase refresh failed
                showExpiryBanner();
            });
    }

    /**
     * Periodic check — should we refresh now?
     */
    function checkAndRefresh() {
        if (bannerShown) {
            return; // Already failing, don't retry
        }

        const nowMs = Date.now();
        const expiresAtMs = config.expiresAt * 1000;
        const refreshAtMs = expiresAtMs - MARGIN_MS;

        if (nowMs >= refreshAtMs) {
            refreshToken();
        }
    }

    // Start the periodic check
    setInterval(checkAndRefresh, CHECK_INTERVAL_MS);

    // Also do an immediate check (in case page loaded close to expiry)
    checkAndRefresh();
})();
