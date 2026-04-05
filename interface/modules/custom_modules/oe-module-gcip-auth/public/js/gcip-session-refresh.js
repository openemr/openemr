/* jshint esversion: 6 */
/**
 * GCIP Session Refresh — silent client-side token refresh.
 *
 * Loads the Firebase compat SDK, initializes with the stored config,
 * and periodically refreshes the ID token before it expires. On success,
 * POSTs the fresh token to the core OIDC session refresh endpoint.
 *
 * Failure handling: retries silently up to MAX_RETRIES times. After all
 * retries are exhausted, shows a non-blocking banner with "Try again"
 * and "Go to login" buttons. Never auto-redirects — the user always
 * decides (OpenEMR has a beforeunload handler that would clash with
 * automatic navigation).
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
    const MAX_RETRIES = 3;
    const RETRY_DELAY_MS = 10 * 1000; // 10 seconds between retries
    let refreshInProgress = false;
    let bannerShown = false;
    let failureCount = 0;

    // SRI hashes for Firebase SDK scripts (defense-in-depth against CDN compromise)
    const SRI_HASHES = {
        'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js':
            'sha384-sEVIly94UBRLKWdkYoPpSG7GD/e79YHMrxVyZaOk712Ga7+EAw6w1EFi+xBzBdd+',
        'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js':
            'sha384-EkqK+ezBWJuvO3hfrSx2iVqr3YQbhmnzn8kPhOpBZ+0GMVU5oGSgptwIu8D84HjE'
    };

    /**
     * Load a script by URL with optional SRI verification, returns a Promise.
     */
    function loadScript(url) {
        return new Promise(function (resolve, reject) {
            const existing = document.querySelector(`script[src="${url}"]`);
            if (existing) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = url;
            if (SRI_HASHES[url]) {
                script.integrity = SRI_HASHES[url];
                script.crossOrigin = 'anonymous';
            }
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
     * Show a warning banner with action buttons. Never auto-redirects.
     *
     * @param {boolean} permanent - If true, the failure is unrecoverable
     *   (e.g. Firebase user gone) and "Try again" is not shown.
     */
    function showFailureBanner(permanent) {
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

        const message = document.createElement('span');
        message.textContent = permanent
            ? 'Your authentication session has expired. Please save your work and log in again. '
            : 'Session could not be refreshed. Please save your work. ';
        banner.appendChild(message);

        const btnStyle = 'margin:0 8px;padding:4px 16px;border:1px solid #fff;border-radius:4px;'
            + 'cursor:pointer;font-size:14px;';

        if (!permanent) {
            const retryBtn = document.createElement('button');
            retryBtn.textContent = 'Try again';
            retryBtn.style.cssText = `${btnStyle}background:transparent;color:#fff;`;
            retryBtn.addEventListener('click', function () {
                removeBanner();
                retryRefresh();
            });
            banner.appendChild(retryBtn);
        }

        const loginBtn = document.createElement('button');
        loginBtn.textContent = 'Go to login';
        loginBtn.style.cssText = `${btnStyle}background:#fff;color:#dc3545;`;
        loginBtn.addEventListener('click', function () {
            window.top.location.href = `${config.webRoot}/interface/login/login.php?site=${encodeURIComponent(config.siteId || 'default')}`;
        });
        banner.appendChild(loginBtn);

        document.body.appendChild(banner);
    }

    /**
     * Remove the banner and reset state so refresh can be retried.
     */
    function removeBanner() {
        const banner = document.getElementById('oidc-session-expiry-banner');
        if (banner) {
            banner.remove();
        }
        bannerShown = false;
        failureCount = 0;
        refreshInProgress = false;
    }

    /**
     * Retry refresh after user clicks "Try again".
     */
    function retryRefresh() {
        refreshToken();
    }

    /**
     * Handle a transient refresh failure — retry silently or show banner.
     */
    function onTransientFailure() {
        refreshInProgress = false;
        failureCount++;

        if (failureCount >= MAX_RETRIES) {
            showFailureBanner(false);
            return;
        }

        // Silent retry after delay
        setTimeout(function () {
            refreshToken();
        }, RETRY_DELAY_MS);
    }

    /**
     * Handle a permanent failure — no Firebase user or auth revoked.
     * "Try again" won't help; only "Go to login" is offered.
     */
    function onPermanentFailure() {
        refreshInProgress = false;
        showFailureBanner(true);
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
                // Wait for Firebase to restore auth state from IndexedDB.
                // currentUser is null until onAuthStateChanged fires.
                return new Promise(function (resolve, reject) {
                    const unsubscribe = firebase.auth().onAuthStateChanged(function (user) {
                        unsubscribe();
                        if (!user) {
                            reject({ permanent: true, reason: 'No Firebase user' });
                            return;
                        }
                        resolve(user);
                    });
                });
            })
            .then(function (user) {
                return user.getIdToken(true).catch(function (err) {
                    // Firebase auth errors (token revoked, user deleted) are permanent
                    throw { permanent: true, reason: err.message || 'Firebase auth error' };
                });
            })
            .then(function (idToken) {
                if (!idToken) {
                    throw { permanent: false, reason: 'Empty token from Firebase' };
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
                if (!response.ok) {
                    throw { permanent: false, reason: `Server returned ${response.status}` };
                }
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    throw { permanent: false, reason: 'Server returned failure' };
                }

                // Success — update local expiry, reset failure counter
                config.expiresAt = data.expires_at;
                refreshInProgress = false;
                failureCount = 0;
            })
            .catch(function (err) {
                if (err && err.permanent) {
                    onPermanentFailure();
                } else {
                    onTransientFailure();
                }
            });
    }

    /**
     * Periodic check — should we refresh now?
     */
    function checkAndRefresh() {
        if (bannerShown) {
            return; // Banner is showing, user decides next action
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
