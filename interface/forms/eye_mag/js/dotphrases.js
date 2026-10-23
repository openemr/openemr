/*
 * dotphrases.js - Dot phrase system with single and multi-field support
 */
(function(){
    var DEFAULT_PHRASES = {
        ".hpi": "Patient presents for follow-up of chronic conditions. Denies acute changes today.",
        ".ros": "ROS: No fever, chills, weight loss. No new ENT, cardiopulmonary, GI, GU, derm, neuro complaints.",
        ".plan": "Plan: Continue current management. Reinforce compliance. Return precautions reviewed.",
        ".normext": "External: Lids/Lashes normal. Conjunctiva clear. Sclera white.",
        ".normant": "Anterior Segment: Cornea clear. AC deep & quiet. Iris round/reactive. Lens clear.",
        ".normret": "Posterior Segment: Vitreous clear. Optic nerve sharp with healthy rim. Macula flat. Vessels normal. Periphery intact.",
        ".exam": "Comprehensive ophthalmic examination performed. Findings documented above.",
        ".imp": "Impression: Stable exam without new pathology.",
        ".rtc": "RTC: Return in 1 year or sooner if symptoms develop.",
        ".dilate": "Patient advised about dilation effects; discussed driving safety and provided sunglasses.",
        ".consent": "Informed consent obtained after risks, benefits, alternatives discussed."
    };

    var AUTOCOMPLETE_POPUP = null;
    var AUTOCOMPLETE_ELEMENT = null;
    var POPUP_MOUSEDOWN = false; // Track if user is currently clicking popup
    var DEBUG_DOTPHRASES = false;
    function debugLog() {
        if (DEBUG_DOTPHRASES && window.console && typeof console.log === "function") {
            console.log.apply(console, arguments);
        }
    }

    // The builder renders in the TOP OpenEMR document when the eye form is
    // framed, so the modal covers the whole application window instead of
    // just this frame.  Everything is same-origin, so the sheet is
    // injected up there and the modal is re-parented on open.
    function dpTopDoc() {
        try {
            if (window.top && window.top.document && window.top !== window) return window.top.document;
        } catch (e) {}
        return document;
    }
    function dpEnsureStyles() {
        var d = dpTopDoc();
        if (d === document) return;               // frameless: view.php loads it
        // Cache-busted like the frame's own <link> (the bare URL is edge-cached).
        var wanted = new URL('../../forms/eye_mag/css/dotphrases.css?v=' + encodeURIComponent(window.eyeMagDpCssV || '1'), window.location.href).href;
        var link = d.getElementById('dotphrases_builder_css');
        if (link) {
            // The top document outlives form reloads: re-target the link
            // whenever the frame's sheet version changes, so builder style
            // updates always land without a full app refresh.
            if (link.href !== wanted) link.href = wanted;
            return;
        }
        link = d.createElement('link');
        link.id = 'dotphrases_builder_css';
        link.rel = 'stylesheet';
        link.href = wanted;
        d.head.appendChild(link);
    }

    // Translated string lookup: view.php renders window.eyeMagDpT via xlj()
    // (one key per builder string); English fallbacks keep the builder
    // usable if the dictionary has not loaded.
    function dpT(key, fallback) {
        var d = window.eyeMagDpT || {};
        return (d[key] !== undefined && d[key] !== '') ? d[key] : (fallback || key);
    }

    function loadUserPhrases(){
        var user = {};
        try {
            if (window.eyeMagUserPhrases && typeof window.eyeMagUserPhrases === 'object') {
                Object.keys(window.eyeMagUserPhrases).forEach(function(key){
                    if (typeof window.eyeMagUserPhrases[key] !== 'object') {
                        user[key.toLowerCase()] = window.eyeMagUserPhrases[key];
                    }
                });
            }
            var hidden = document.getElementById('DOTPHRASES_USER');
            if (hidden && hidden.value) {
                var parsed = JSON.parse(hidden.value);
                Object.keys(parsed).forEach(function(key){
                    if (typeof parsed[key] !== 'object') user[key.toLowerCase()] = parsed[key];
                });
            }
        } catch(e) {}
        return user;
    }

    function loadUserObjectPhrases(){
        var user = {};
        try {
            if (window.eyeMagUserPhrases && typeof window.eyeMagUserPhrases === 'object') {
                for (var storedKey in window.eyeMagUserPhrases) {
                    if (window.eyeMagUserPhrases[storedKey] && typeof window.eyeMagUserPhrases[storedKey] === 'object') {
                        user[storedKey.toLowerCase()] = window.eyeMagUserPhrases[storedKey];
                    }
                }
            }
            if (window.eyeMagObjectPhrases && typeof window.eyeMagObjectPhrases === 'object') {
                for (var key in window.eyeMagObjectPhrases) {
                    user[key.toLowerCase()] = window.eyeMagObjectPhrases[key];
                }
            }
            var hidden = document.getElementById('DOTPHRASES_USER');
            if (hidden && hidden.value) {
                var parsed = JSON.parse(hidden.value);
                Object.keys(parsed).forEach(function(key){
                    if (parsed[key] && typeof parsed[key] === 'object') user[key.toLowerCase()] = parsed[key];
                });
            }
        } catch(e) {}
        return user;
    }

    var ACTIVE_PHRASES = Object.assign({}, DEFAULT_PHRASES, loadUserPhrases());
    var ACTIVE_OBJECT_PHRASES = loadUserObjectPhrases(); // multi-field phrases

    // Server-backed access metadata, keyed by lowercase phrase key:
    //   {id, name, owner_uid, visibility, zones, improvement_of}
    // Populated from the hidden DOTPHRASES_META input rendered by view.php.
    var PHRASE_META = {};
    var SERVER_PHRASES = [];
    try {
        var metaEl = document.getElementById('DOTPHRASES_META');
        if (metaEl && metaEl.value) {
            PHRASE_META = JSON.parse(metaEl.value) || {};
        }
    } catch(e) {}

    // Patient/visit values used by variable tokens in single-field phrases.
    // view.php supplies these as JSON so the same substitutions work in every
    // textarea where dot phrases are enabled.
    try {
        var substEl = document.getElementById('DOTPHRASES_SUBST');
        if (substEl && substEl.value) window.eyeMagSubst = JSON.parse(substEl.value) || {};
    } catch(e) {}

    // Field id → zone classification, derived from the zone containers in view.php.
    var ZONE_CONTAINERS = ['HPI_1','PMH_1','EXT_1','ANTSEG_1','RETINA_1','SDRETINA_1','NEURO_1','IMPPLAN_1'];
    var FIELD_ZONE = {};
    (function(){
        ZONE_CONTAINERS.forEach(function(cid){
            var zone = cid.replace('_1','');
            var cont = document.getElementById(cid);
            if (!cont) return;
            var nodes = cont.querySelectorAll('textarea[id], input[id], select[id]');
            Array.prototype.forEach.call(nodes, function(n){
                if (!n.id) return;
                var type = (n.tagName === 'INPUT') ? (n.getAttribute('type') || 'text') : n.tagName.toLowerCase();
                if (type === 'hidden' || type === 'button' || type === 'submit' || type === 'checkbox' || type === 'radio') return;
                if (!(n.id in FIELD_ZONE)) FIELD_ZONE[n.id] = zone;
            });
        });
    })();

    // Zone membership of a phrase: explicit from meta.zones, else inferred from
    // the field ids in an object phrase.  Returns an array like ['EXT','RETINA'].
    // The exam zones with dot-phrase panels.  SDRETINA shares RETINA and has
    // no standalone phrase section.
    var DF_ZONES = ['HPI','PMH','EXT','ANTSEG','RETINA','NEURO','IMPPLAN'];
    function phraseZones(phraseKey) {
        var k = (phraseKey || '').toLowerCase();
        var meta = PHRASE_META[k];
        if (meta && meta.zones) {
            return String(meta.zones).split(',').map(function(z){ return z.trim().toUpperCase(); })
                .filter(function(z){ return DF_ZONES.indexOf(z) !== -1; });
        }
        var map = ACTIVE_OBJECT_PHRASES[k];
        if (!map || typeof map !== 'object') return [];
        var zones = [];
        Object.keys(map).forEach(function(fid){
            var z = phraseFieldZone(fid);
            if (z && zones.indexOf(z) === -1) zones.push(z);
        });
        return zones;
    }

    function phraseFieldZone(fieldId) {
        if (FIELD_ZONE[fieldId]) return FIELD_ZONE[fieldId];
        var wanted = String(fieldId || '').toLowerCase();
        var known = Object.keys(FIELD_ZONE).filter(function(id) {
            return id.toLowerCase() === wanted;
        });
        if (known.length) return FIELD_ZONE[known[0]];
        var upper = String(fieldId || '').toUpperCase();
        for (var i = 0; i < DF_ZONES.length; i++) {
            if (upper.indexOf(DF_ZONES[i] + '_') === 0) return DF_ZONES[i];
        }
        return null;
    }

    function phraseFieldsForZone(phraseKey, zone) {
        var map = ACTIVE_OBJECT_PHRASES[(phraseKey || '').toLowerCase()];
        if (!map || typeof map !== 'object') return [];
        return Object.keys(map).filter(function(fid) {
            return phraseFieldZone(fid) === String(zone || '').toUpperCase();
        });
    }

    var TOKEN_PATTERN = /(\.[A-Za-z0-9_]{2,})$/;

    function getDotPhrasePrefix(el) {
        var pos = el.selectionStart;
        if (pos == null) return null;
        var head = el.value.slice(0,pos);

        // Look for a dot phrase pattern at the end of head (. followed by word chars)
        var match = head.match(/(\.[A-Za-z0-9_]*)$/);
        return match ? match[1] : null;
    }

    function getMatchingPhrases(prefix) {
        if (!prefix || prefix.length < 1) return [];
        var lowerPrefix = prefix.toLowerCase();
        var matches = [];

        Object.keys(ACTIVE_PHRASES).forEach(function(key) {
            if (key.startsWith(lowerPrefix)) {
                matches.push({key: key, text: ACTIVE_PHRASES[key], type: 'text'});
            }
        });
        Object.keys(ACTIVE_OBJECT_PHRASES).forEach(function(key) {
            if (key.startsWith(lowerPrefix)) {
                matches.push({key: key, text: '[Multi-field phrase]', type: 'object'});
            }
        });

        // Sort by key name, limit to 5
        matches.sort(function(a,b) { return a.key.localeCompare(b.key); });
        return matches.slice(0, 5);
    }

    function showAutocompletePopup(el, matches, prefix) {
        hideAutocompletePopup();

        if (!matches || matches.length === 0) return;

        var popup = document.createElement('div');
        popup.style.position = 'fixed';
        popup.style.backgroundColor = '#fff';
        popup.style.border = '1px solid #999';
        popup.style.borderRadius = '4px';
        popup.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        popup.style.zIndex = '10000';
        popup.style.maxWidth = '300px';
        popup.style.fontSize = '13px';
        popup.style.maxHeight = '200px';
        popup.style.overflowY = 'auto';

        var rect = el.getBoundingClientRect();
        popup.style.left = rect.left + 'px';
        popup.style.top = (rect.bottom + 5) + 'px';

        matches.forEach(function(match, idx) {
            var item = document.createElement('div');
            item.style.padding = '8px 12px';
            item.style.cursor = 'pointer';
            item.style.borderBottom = idx < matches.length - 1 ? '1px solid #eee' : 'none';
            item.style.backgroundColor = '#fff';
            item.className = 'dotphrase-autocomplete-item';
            item.setAttribute('data-phrase-key', match.key);
            item.onmouseover = function() { item.style.backgroundColor = '#f0f0f0'; };
            item.onmouseout = function() { item.style.backgroundColor = '#fff'; };

            item.innerHTML = '<strong>' + match.key + '</strong><br/><span style="color:#666;font-size:11px">' +
                             (match.text.length > 40 ? match.text.substring(0, 40) + '...' : match.text) + '</span>';

            popup.appendChild(item);
        });

        // Detect mousedown on popup to prevent blur from hiding it
        popup.addEventListener('mousedown', function(e) {
            debugLog('Popup mousedown detected');
            POPUP_MOUSEDOWN = true;
        });

        popup.addEventListener('mouseup', function(e) {
            debugLog('Popup mouseup detected');
            POPUP_MOUSEDOWN = false;
        });

        // Add event delegation to popup
        popup.addEventListener('click', function(e) {
            debugLog('POPUP CLICK: e.target=' + e.target.tagName + ', e.target.className=' + e.target.className);
            var item = e.target.closest('.dotphrase-autocomplete-item');
            debugLog('POPUP CLICK: closest item result=' + (item ? 'FOUND (' + item.getAttribute('data-phrase-key') + ')' : 'not found'));
            if (item) {
                var phraseKey = item.getAttribute('data-phrase-key');
                debugLog('Popup click detected, phraseKey=' + phraseKey);
                selectAutocompletePhrase(el, phraseKey);
            }
        });

        document.body.appendChild(popup);
        AUTOCOMPLETE_POPUP = popup;
        AUTOCOMPLETE_ELEMENT = el;
    }

    function hideAutocompletePopup() {
        if (AUTOCOMPLETE_POPUP) {
            AUTOCOMPLETE_POPUP.remove();
            AUTOCOMPLETE_POPUP = null;
            AUTOCOMPLETE_ELEMENT = null;
        }
    }

    function selectAutocompletePhrase(el, phraseKey) {
        debugLog('selectAutocompletePhrase called with phraseKey=' + phraseKey);

        // Ensure element has focus before getting position
        el.focus();

        var pos = el.selectionStart;
        debugLog('After focus: pos=' + pos + ', el.value="' + el.value + '"');

        var head = el.value.slice(0, pos);
        var prefix = getDotPhrasePrefix(el);

        debugLog('prefix=' + prefix + ', phraseKey=' + phraseKey + ', head="' + head + '"');

        if (!prefix) {
            debugLog('No prefix found, returning');
            hideAutocompletePopup();
            return;
        }

        var idx = head.lastIndexOf(prefix);
        debugLog('idx=' + idx);

        if (idx < 0) {
            debugLog('idx < 0, returning');
            hideAutocompletePopup();
            return;
        }

        // Check if it's an object phrase or text phrase
        var keyLower = phraseKey.toLowerCase();
        debugLog('keyLower=' + keyLower + ', checking ACTIVE_PHRASES=' + (ACTIVE_PHRASES[keyLower] ? 'found' : 'NOT found') + ', ACTIVE_OBJECT_PHRASES=' + (ACTIVE_OBJECT_PHRASES[keyLower] ? 'found' : 'NOT found'));

        if (ACTIVE_OBJECT_PHRASES[keyLower]) {
            debugLog('Applying object phrase');
            applyObjectPhrase(keyLower, el, prefix);
        } else if (ACTIVE_PHRASES[keyLower]) {
            debugLog('Applying text phrase');
            var phrase = ACTIVE_PHRASES[keyLower];
            if (window.eyeMagSubst) {
                phrase = phrase.replace(/\{\{([A-Z_]+)\}\}/g, function(m,k){ return (k in window.eyeMagSubst) ? window.eyeMagSubst[k] : ''; });
            }
            // Replace the PREFIX (what was typed) with the selected PHRASEKEY content
            var before = head.slice(0, idx);
            var tail = el.value.slice(pos);
            el.value = before + phrase + tail;
            el.selectionStart = el.selectionEnd = before.length + phrase.length;
            debugLog('After expansion: el.value="' + el.value + '"');
        } else {
            debugLog('Phrase not found in either ACTIVE_PHRASES or ACTIVE_OBJECT_PHRASES');
        }

        // Trigger change event so the form knows the field was modified
        var event = new Event('change', { bubbles: true });
        el.dispatchEvent(event);
        debugLog('Dispatched change event');

        hideAutocompletePopup();
    }

    // IMP/PLAN is a structured one-to-many area, not a pair of ordinary
    // form controls.  The Eye Form's native #IMP blur handler parses the
    // first line as the impression, the remaining text as its plan, adds an
    // obj.IMPPLAN_items record, and persists it.  Route phrase data through
    // that workflow so PLAN_0 cannot overwrite an unrelated existing item.
    function applyImpPlanPhrase(map) {
        var handled = {};
        var imp = document.getElementById('IMP');
        var title = (map && map.IMP !== null && map.IMP !== undefined) ? String(map.IMP).trim() : '';
        if (!imp || !title) return handled;

        var planKey = Object.keys(map).filter(function(fid){ return /^PLAN_\d+$/.test(fid); }).sort()[0] || '';
        var plan = planKey ? String(map[planKey] || '').trim() : '';
        imp.value = title + (plan ? '\n' + plan : '');
        if (window.jQuery) window.jQuery(imp).trigger('blur');
        else imp.dispatchEvent(new Event('blur'));
        imp.classList.add('df-live-populated');
        handled.IMP = true;
        if (planKey) handled[planKey] = true;
        return handled;
    }

    function applyObjectPhrase(keyLower, triggerEl, token){
        var map = ACTIVE_OBJECT_PHRASES[keyLower];
        if (!map) return;
        var handled = applyImpPlanPhrase(map);
        Object.keys(map).forEach(function(fid){
            if (handled[fid]) return;
            var target = document.getElementById(fid);
            if (!target) return;
            var val = map[fid];
            if (window.eyeMagSubst) {
                val = val.replace(/\{\{([A-Z_]+)\}\}/g, function(x,k){ return (k in window.eyeMagSubst)? window.eyeMagSubst[k] : ''; });
            }
            target.value = val;
            target.dispatchEvent(new Event('change', {bubbles:true}));
        });
        var pos = triggerEl.selectionStart;
        var head = triggerEl.value.slice(0,pos);
        var idx = head.lastIndexOf(token);
        if (idx >= 0) {
            triggerEl.value = head.slice(0,idx) + head.slice(idx + token.length) + triggerEl.value.slice(pos);
            triggerEl.selectionStart = triggerEl.selectionEnd = idx;
        }
    }

    function expandIfDotPhrase(el){
        var prefix = getDotPhrasePrefix(el);
        if (!prefix || prefix.length < 2) return; // Need at least . + 2 chars

        var keyLower = prefix.toLowerCase();
        if (ACTIVE_OBJECT_PHRASES[keyLower]) {
            applyObjectPhrase(keyLower, el, prefix);
            hideAutocompletePopup();
            return;
        }
        var phrase = ACTIVE_PHRASES[keyLower];
        if (!phrase) return;

        var pos = el.selectionStart;
        var head = el.value.slice(0,pos);
        var idx = head.lastIndexOf(prefix);
        if (idx < 0) return;
        var before = head.slice(0,idx);
        var afterHead = head.slice(idx + prefix.length);
        if (window.eyeMagSubst) {
            phrase = phrase.replace(/\{\{([A-Z_]+)\}\}/g, function(m,k){ return (k in window.eyeMagSubst) ? window.eyeMagSubst[k] : ''; });
        }
        var tail = el.value.slice(pos);
        el.value = before + phrase + afterHead + tail;
        var newPos = (before + phrase + afterHead).length;
        el.selectionStart = el.selectionEnd = newPos;
        hideAutocompletePopup();
    }

    function handleKey(e){
        var el = e.target;
        if (el.tagName !== 'TEXTAREA' && !(el.tagName === 'INPUT' && el.type === 'text')) return;
        if (!el.closest('.eye_mag')) return;

        if (e.key === 'Escape') {
            hideAutocompletePopup();
            return;
        }

        if (/^( |Enter|Tab|,|\.|;)$/.test(e.key)) {
            if (AUTOCOMPLETE_POPUP && AUTOCOMPLETE_ELEMENT === el) {
                // If popup is showing and user presses a trigger key, don't expand yet - let them select
                if (e.key !== ' ' && e.key !== 'Enter' && e.key !== 'Tab') return;
            }
            Promise.resolve().then(function(){ expandIfDotPhrase(el); });
        }
    }

    window.updateDotPhrases = function() {
        ACTIVE_PHRASES = Object.assign({}, DEFAULT_PHRASES, loadUserPhrases());
        ACTIVE_OBJECT_PHRASES = loadUserObjectPhrases();
    };

    function handleInput(e){
        var el = e.target;
        if (el.tagName !== 'TEXTAREA' && !(el.tagName === 'INPUT' && el.type === 'text')) return;
        if (!el.closest('.eye_mag')) return;

        // Show autocomplete popup for dot phrases - but only after 3 letters typed
        var prefix = getDotPhrasePrefix(el);
        if (prefix && prefix.length >= 4) { // . + 3 letters minimum
            var matches = getMatchingPhrases(prefix);
            showAutocompletePopup(el, matches, prefix);
        } else {
            hideAutocompletePopup();
        }
    }

    function handleBlur(e){
        var el = e.target;
        if (el.tagName !== 'TEXTAREA' && !(el.tagName === 'INPUT' && el.type === 'text')) return;
        if (!el.closest('.eye_mag')) return;

        // If user is currently clicking the popup, don't process blur yet
        if (POPUP_MOUSEDOWN) {
            debugLog('handleBlur: POPUP_MOUSEDOWN=true, skipping blur processing');
            return;
        }

        // Refresh cache from localStorage in case phrases were recently added
        var stored = localStorage.getItem('eyeMagUserPhrases');
        if (stored) {
            try {
                var data = JSON.parse(stored);
                Object.keys(data).forEach(function(k){
                    var lowerKey = k.toLowerCase();
                    if (typeof data[k] === 'object') ACTIVE_OBJECT_PHRASES[lowerKey]=data[k]; else ACTIVE_PHRASES[lowerKey]=data[k];
                });
            } catch(e) {}
        }
        expandIfDotPhrase(el);
        hideAutocompletePopup();
    }

    function scanAll(el){
        var originalPos = el.selectionStart;
        var beforeLength = el.value.length;
        var replaced = false;
        el.value = el.value.replace(/(\.[A-Za-z0-9_]{2,})(?=[\s,.;\)])/g, function(m){
            var key = m.toLowerCase();
            if (ACTIVE_OBJECT_PHRASES[key]) { applyObjectPhrase(key, el, m); replaced = true; return ''; }
            if (ACTIVE_PHRASES[key]) {
                var phrase = ACTIVE_PHRASES[key];
                if (window.eyeMagSubst) {
                    phrase = phrase.replace(/\{\{([A-Z_]+)\}\}/g, function(x,k){ return (k in window.eyeMagSubst)? window.eyeMagSubst[k] : ''; });
                }
                replaced = true; return phrase;
            }
            return m;
        });
        if (replaced) {
            var afterLength = el.value.length;
            var delta = afterLength - beforeLength;
            el.selectionStart = el.selectionEnd = originalPos + delta;
        }
    }

    function initHandlers(){
        document.addEventListener('keydown', handleKey, true);
        document.addEventListener('input', handleInput, true);
        // blur doesn't bubble, so we need to use focusout which does bubble
        document.addEventListener('focusout', function(e){
            handleBlur({target: e.target});
        }, true);

        // Handle autocomplete item clicks - MUST come before the hide-on-outside-click listener
        document.addEventListener('click', function(e) {
            debugLog('Global document click (capture): e.target=' + e.target.tagName + ', AUTOCOMPLETE_POPUP exists=' + (AUTOCOMPLETE_POPUP ? 'yes' : 'no'));
            if (!AUTOCOMPLETE_POPUP) {
                debugLog('No popup, skipping autocomplete check');
            } else {
                debugLog('Popup exists, checking for item. popup children count=' + AUTOCOMPLETE_POPUP.children.length);
                var item = e.target.closest('.dotphrase-autocomplete-item');
                debugLog('closest item result=' + (item ? 'FOUND' : 'not found'));
                if (item && AUTOCOMPLETE_ELEMENT) {
                    debugLog('Document click: detected autocomplete item click');
                    var phraseKey = item.getAttribute('data-phrase-key');
                    selectAutocompletePhrase(AUTOCOMPLETE_ELEMENT, phraseKey);
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
            }
        }, true); // Use capture phase to intercept before bubbling

        // Hide popup on click outside
        document.addEventListener('click', function(e) {
            if (AUTOCOMPLETE_POPUP && e.target !== AUTOCOMPLETE_ELEMENT && !AUTOCOMPLETE_POPUP.contains(e.target)) {
                debugLog('Document click: hiding popup (click outside)');
                hideAutocompletePopup();
            }
        });

        window.eyeMagAddDotPhrase = function(key, text){ if (!key.startsWith('.')) key='.'+key; var k=key.toLowerCase(); ACTIVE_PHRASES[k] = text; persist(k, text); };
        window.eyeMagListDotPhrases = function(){ return Object.assign({}, ACTIVE_PHRASES); };
        window.eyeMagAddDotPhraseObject = function(key, obj){ if (!key.startsWith('.')) key='.'+key; var k=key.toLowerCase(); ACTIVE_OBJECT_PHRASES[k] = obj; persist(k, obj); };
        window.eyeMagListDotPhraseObjects = function(){ return Object.assign({}, ACTIVE_OBJECT_PHRASES); };
        window.eyeMagDeleteDotPhrase = function(key){ var k=(key||'').toLowerCase(); delete ACTIVE_PHRASES[k]; removePersist(k); };
        window.eyeMagDeleteDotPhraseObject = function(key){ var k=(key||'').toLowerCase(); delete ACTIVE_OBJECT_PHRASES[k]; removePersist(k); };
        window.eyeMagUpdateDotPhrase = function(key, text){ if (!key.startsWith('.')) key='.'+key; var k=key.toLowerCase(); ACTIVE_PHRASES[k]=text; persist(k,text); };
        window.eyeMagUpdateDotPhraseObject = function(key, obj){ if (!key.startsWith('.')) key='.'+key; var k=key.toLowerCase(); ACTIVE_OBJECT_PHRASES[k]=obj; persist(k,obj); };
        window.eyeMagExportPhrases = function(){
            try { var stored = localStorage.getItem('eyeMagUserPhrases'); return stored || '{}'; } catch(e){ return '{}'; }
        };
        window.eyeMagImportPhrases = function(json, merge){
            try {
                var data = JSON.parse(json||'{}');
                var existing = {};
                try { var s=localStorage.getItem('eyeMagUserPhrases'); if (s) existing = JSON.parse(s); } catch(e){}
                var combined = merge === false ? data : Object.assign(existing, data);
                localStorage.setItem('eyeMagUserPhrases', JSON.stringify(combined));
                // Reload runtime caches
                Object.keys(combined).forEach(function(k){
                    var lowerKey = k.toLowerCase();
                    if (typeof combined[k] === 'object') { ACTIVE_OBJECT_PHRASES[lowerKey]=combined[k]; delete ACTIVE_PHRASES[lowerKey]; }
                    else { ACTIVE_PHRASES[lowerKey]=combined[k]; delete ACTIVE_OBJECT_PHRASES[lowerKey]; }
                });
                window.eyeMagUserPhrases = combined;
                return true;
            } catch(e){ return false; }
        };
        // Load persisted and sync with server data
        try {
            var serverData = {};
            var hidden = document.getElementById('DOTPHRASES_USER');
            if (hidden && hidden.value) {
                try { serverData = JSON.parse(hidden.value); } catch(e){}
            }

            var localData = {};
            var stored = localStorage.getItem('eyeMagUserPhrases');
            if (stored) {
                try { localData = JSON.parse(stored); } catch(e){}
            }

            // Merge Server data into Local data (Server takes precedence for consistency)
            var merged = Object.assign({}, localData, serverData);

            // Update LocalStorage and Runtime
            localStorage.setItem('eyeMagUserPhrases', JSON.stringify(merged));
            window.eyeMagUserPhrases = merged;

            Object.keys(merged).forEach(function(k){
                var lowerKey = k.toLowerCase();
                if (typeof merged[k] === 'object') ACTIVE_OBJECT_PHRASES[lowerKey]=merged[k]; else ACTIVE_PHRASES[lowerKey]=merged[k];
            });
        } catch(e) {}
    }

    function persist(key,val){
        try {
            var stored = localStorage.getItem('eyeMagUserPhrases');
            var data = stored ? JSON.parse(stored) : {};
            data[key] = val;
            localStorage.setItem('eyeMagUserPhrases', JSON.stringify(data));
            window.eyeMagUserPhrases = data;
        } catch(e) {}
    }

    function removePersist(key){
        try {
            var stored = localStorage.getItem('eyeMagUserPhrases');
            var data = stored ? JSON.parse(stored) : {};
            delete data[key];
            localStorage.setItem('eyeMagUserPhrases', JSON.stringify(data));
            window.eyeMagUserPhrases = data;
        } catch(e) {}
    }

    // ── Server sync (form_eye_dotphrases via save.php?action=dotphrase) ──
    function serverRequest(sub, payload) {
        var form = new FormData();
        var token = d.getElementById('csrf_token_form');
        form.append('csrf_token_form', token ? token.value : '');
        Object.keys(payload || {}).forEach(function(k){ form.append(k, payload[k]); });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        return fetch('../../forms/eye_mag/save.php?action=dotphrase&sub=' + encodeURIComponent(sub), {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(res){ return res.json(); });
    }

    // Rebuild runtime caches + PHRASE_META from the server's authoritative list,
    // keeping DEFAULT_PHRASES and any local-only phrases intact.
    function serverRefreshCache(phrases) {
        if (!phrases || !Array.isArray(phrases)) return;
        SERVER_PHRASES = phrases.map(function(source){
            var row = Object.assign({}, source);
            row.id = parseInt(row.id, 10) || 0;
            row.owner_uid = parseInt(row.owner_uid, 10) || 0;
            row.phrase_key = String(row.phrase_key || '').toLowerCase();
            if (row.content_type === 'object' && typeof row.content === 'string') {
                try { row.content = JSON.parse(row.content); } catch (e) { row.content = {}; }
            }
            return row;
        });
        PHRASE_META = {};
        ACTIVE_PHRASES = Object.assign({}, DEFAULT_PHRASES);
        ACTIVE_OBJECT_PHRASES = {};
        // Shared rows load first and this user's rows load last. If both use
        // the same trigger, autocomplete/deploy therefore selects My Phrase.
        SERVER_PHRASES.slice().sort(function(a, b){
            return (a.owner_uid === MANAGER_AUTH_UID ? 1 : 0) - (b.owner_uid === MANAGER_AUTH_UID ? 1 : 0);
        }).forEach(function(row){
            var k = String(row.phrase_key || '').toLowerCase();
            PHRASE_META[k] = {
                id: row.id, name: row.name, owner_uid: row.owner_uid,
                visibility: row.visibility, zones: row.zones, improvement_of: row.improvement_of,
                creator_name: row.creator_name, username: row.username
            };
            if (row.content_type === 'object') {
                var obj = row.content;
                ACTIVE_OBJECT_PHRASES[k] = (obj && typeof obj === 'object') ? obj : {};
                delete ACTIVE_PHRASES[k];
            } else {
                ACTIVE_PHRASES[k] = row.content || '';
                delete ACTIVE_OBJECT_PHRASES[k];
            }
        });
    }

    function serverActionById(sub, id) {
        return serverRequest(sub, {id: id}).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    }

    // Create or update a phrase on the server, then refresh the cache.
    function serverSavePhrase(payload) {
        var sub = payload.id ? 'update' : 'create';
        return serverRequest(sub, payload).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    }

    window.eyeMagServerList = function(){ return serverRequest('list', {}).then(function(r){ if (r && r.ok) serverRefreshCache(r.phrases); return r; }); };
    window.eyeMagServerCreate = function(payload){ return serverSavePhrase(payload); };
    window.eyeMagServerUpdate = function(payload){ return serverSavePhrase(payload); };
    window.eyeMagServerDelete = function(phraseKey){
        var meta = PHRASE_META[(phraseKey || '').toLowerCase()];
        if (!meta || !meta.id) return Promise.resolve({ok:false, msg:'not on server'});
        return serverRequest('delete', {id: meta.id}).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    };
    window.eyeMagServerShare = function(phraseKey){
        var meta = PHRASE_META[(phraseKey || '').toLowerCase()];
        if (!meta || !meta.id) return Promise.resolve({ok:false, msg:'not on server'});
        return serverRequest('share', {id: meta.id}).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    };
    window.eyeMagServerUnshare = function(phraseKey){
        var meta = PHRASE_META[(phraseKey || '').toLowerCase()];
        if (!meta || !meta.id) return Promise.resolve({ok:false, msg:'not on server'});
        return serverRequest('unshare', {id: meta.id}).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    };
    window.eyeMagServerImport = function(phraseKey){
        var meta = PHRASE_META[(phraseKey || '').toLowerCase()];
        if (!meta || !meta.id) return Promise.resolve({ok:false, msg:'not on server'});
        return serverRequest('import', {id: meta.id}).then(function(r){
            if (r && r.ok) return serverRequest('list', {}).then(function(list){
                if (list && list.ok) serverRefreshCache(list.phrases);
                return r;
            });
            return r;
        });
    };
    window.eyeMagPhraseMeta = function(phraseKey){ return PHRASE_META[(phraseKey || '').toLowerCase()] || null; };
    window.eyeMagPhraseZones = function(phraseKey){ return phraseZones(phraseKey); };
    window.eyeMagBuilderOpen = function(){ managerShow(); };
    window.eyeMagBuilderToggle = function(){ managerToggle(); };
    window.eyeMagPhraseMatches = function(q, zone){ return dfMatches(q, zone || ''); };

    // ── Dot Phrase Builder ────────────────────────────────────────────
    // Phrase manager window.  The vitals-row red dot opens it straight
    // into the multi-field Create view with every exam zone enabled; the
    // header list view is reachable via window.eyeMagBuilderOpen().  Lists
    // the phrases the current user may read (their own + active shared
    // rows), backed by form_eye_dotphrases via save.php?action=dotphrase.
    // Offers Create (zone-toggle capture form) and per-row Import (adopt a
    // colleague's shared phrase), Deploy / Edit / Share /
    // Unshare / Delete gated by ownership + admin.
    var MANAGER_EL = null;
    var MANAGER_VIEW = 'list';        // 'list' | 'create'
    var MANAGER_EDIT_KEY = null;      // phrase key being edited (null = new)
    var MANAGER_EDIT_ROW = null;      // exact server row (duplicate keys stay distinct)
    var MANAGER_ORIGIN_ZONE = null;   // zone that opened Create, seeded on
    var MANAGER_CREATE_MULTI = false; // open Create in Multiple mode with all zones on

    // Current user identity for owner/admin gating, rendered by view.php.
    var MANAGER_AUTH_UID = 0;
    var MANAGER_IS_ADMIN = false;
    (function(){
        var a = document.getElementById('DOTPHRASES_AUTH');
        if (a) MANAGER_AUTH_UID = parseInt(a.value, 10) || 0;
        var ad = document.getElementById('DOTPHRASES_ADMIN');
        if (ad) MANAGER_IS_ADMIN = ad.value === '1';
    })();

    function managerField(tag, text) {
        if (tag === 'label') {
            var l = document.createElement('label');
            l.style.cssText = 'font-weight:bold; display:block; margin-top:6px;';
            l.textContent = text;
            return l;
        }
        var el = document.createElement(tag);
        if (tag === 'input') {
            el.style.cssText = 'margin-bottom:2px;';
        }
        if (text) el.textContent = text;
        return el;
    }

    function managerShow(zone) {
        if (!MANAGER_EL) managerBuild();
        MANAGER_ORIGIN_ZONE = zone || null;
        MANAGER_VIEW = 'list';
        MANAGER_EDIT_KEY = null;
        MANAGER_EDIT_ROW = null;
        dpEnsureStyles();
        dpTopDoc().body.appendChild(MANAGER_EL);
        MANAGER_EL.style.display = 'flex';
        if (window.eyeMagServerList) {
            window.eyeMagServerList().then(function(r){ if (!(r && r.ok)) managerRenderView(); else managerRenderView(); });
        } else {
            managerRenderView();
        }
    }

    function managerHide() {
        if (MANAGER_EL) MANAGER_EL.style.display = 'none';
    }

    function managerToggle(zone) {
        if (MANAGER_EL && MANAGER_EL.style.display === 'flex') { managerHide(); return; }
        managerShow(zone);
    }

    // Retained for callers that explicitly want a full multi-field capture.
    function managerShowCreateFull() {
        if (!MANAGER_EL) managerBuild();
        MANAGER_ORIGIN_ZONE = null;
        MANAGER_EDIT_KEY = null;
        MANAGER_EDIT_ROW = null;
        MANAGER_CREATE_MULTI = true;
        MANAGER_VIEW = 'create';
        dpEnsureStyles();
        dpTopDoc().body.appendChild(MANAGER_EL);
        MANAGER_EL.style.display = 'flex';
        if (window.eyeMagServerList) {
            window.eyeMagServerList().then(function(){ managerRenderView(); });
        } else {
            managerRenderView();
        }
    }

    // Themed confirmation dialog (no native confirm()).
    function managerConfirm(message, confirmLabel, onConfirm) {
        var overlay = document.createElement('div');
        overlay.className = 'dpm-confirm-overlay';
        var box = document.createElement('div');
        box.className = 'dpm-confirm-box';
        var msg = document.createElement('div');
        msg.className = 'dpm-confirm-msg';
        msg.textContent = message;
        box.appendChild(msg);
        var actions = document.createElement('div');
        actions.className = 'dpm-confirm-actions';
        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn btn-secondary btn-sm';
        cancel.textContent = dpT('cancel','Cancel');
        var ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'btn btn-danger btn-sm';
        ok.textContent = confirmLabel || dpT('confirm','Confirm');
        function close(){ if (overlay.parentNode) overlay.parentNode.removeChild(overlay); }
        cancel.addEventListener('click', close);
        ok.addEventListener('click', function(){ close(); onConfirm(); });
        overlay.addEventListener('click', function(e){ if (e.target === overlay) close(); });
        actions.appendChild(cancel);
        actions.appendChild(ok);
        box.appendChild(actions);
        overlay.appendChild(box);
        dpTopDoc().body.appendChild(overlay);
    }

    // Themed toast notification (no native alert()).
    function managerToast(msg, type) {
        var t = document.createElement('div');
        t.className = 'dpm-toast';
        t.setAttribute('data-type', type || 'info');
        t.textContent = msg;
        dpTopDoc().body.appendChild(t);
        setTimeout(function(){ t.style.opacity = '0'; }, 2600);
        setTimeout(function(){ if (t.parentNode) t.parentNode.removeChild(t); }, 3200);
    }

    function managerBuild() {
        MANAGER_EL = document.createElement('div');
        MANAGER_EL.id = 'DOTPHRASE_MANAGER';
        MANAGER_EL.style.cssText = 'position:fixed; top:24px; left:50%; transform:translateX(-50%); display:flex; flex-direction:column; overflow:hidden; z-index:999999;';

        var head = document.createElement('div');
        head.className = 'dpm-head';
        head.innerHTML = '<div class="dpm-title">' + dpT('builder_title','My Phrases Builder') + '</div>';
        var close = document.createElement('span');
        close.className = 'fa fa-times dpm-close';
        close.title = dpT('close','Close');
        close.addEventListener('click', managerHide);
        head.appendChild(close);
        MANAGER_EL.appendChild(head);

        var body = document.createElement('div');
        body.className = 'dpm-body';
        body.id = 'DOTPHRASE_MANAGER_VIEW';
        body.style.cssText = 'flex:1 1 auto; min-height:0; display:flex; flex-direction:column; overflow:hidden;';
        MANAGER_EL.appendChild(body);

        MANAGER_EL.addEventListener('mousedown', function(e){ e.stopPropagation(); });
    }

    // Dispatch view rendering based on MANAGER_VIEW state.
    function managerRenderView() {
        var body = MANAGER_EL && MANAGER_EL.querySelector('#DOTPHRASE_MANAGER_VIEW');
        if (!body) return;
        MANAGER_EL.classList.toggle('dpm-create-mode', MANAGER_VIEW === 'create');
        MANAGER_EL.classList.toggle('dpm-list-mode', MANAGER_VIEW === 'list');
        if (MANAGER_VIEW === 'create') managerRenderCreate(body);
        else managerRenderList(body);
    }

    function managerRenderList(body) {
        body.innerHTML = '';

        function mkBtn(text, cls) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = cls;
            b.textContent = text;
            return b;
        }

        function mkIconBtn(icon, title, cls) {
            var b = document.createElement('button');
            var i = document.createElement('i');
            b.type = 'button';
            b.className = cls + ' dpm-action-icon';
            b.title = title;
            b.setAttribute('aria-label', title);
            b.setAttribute('data-toggle', 'tooltip');
            i.className = 'fa ' + icon;
            i.setAttribute('aria-hidden', 'true');
            b.appendChild(i);
            return b;
        }

        // Server rows stay distinct even when they share a trigger (the
        // common shared-source + imported-private-copy case).
        var entries = SERVER_PHRASES.slice();
        var serverKeys = {};
        entries.forEach(function(row){ serverKeys[row.phrase_key] = true; });
        Object.keys(ACTIVE_PHRASES).concat(Object.keys(ACTIVE_OBJECT_PHRASES)).forEach(function(key){
            key = key.toLowerCase();
            if (serverKeys[key]) return;
            entries.push({
                id: 0, phrase_key: key, name: key, owner_uid: 0,
                visibility: 'local', content_type: ACTIVE_OBJECT_PHRASES[key] ? 'object' : 'text',
                content: ACTIVE_OBJECT_PHRASES[key] || ACTIVE_PHRASES[key], zones: '', creator_name: dpT('built_in','Built-in')
            });
        });

        var sort = {field: 'name', direction: 1};

        // Import is intentionally a row action beside shared phrases. The
        // toolbar contains only the top-level Create function and search.
        var toolbar = managerField('div', '');
        toolbar.className = 'dpm-toolbar';
        var createBtn = mkBtn('+ ' + dpT('create_new','Create new'), 'btn btn-primary btn-sm');
        createBtn.addEventListener('click', function(){ MANAGER_VIEW = 'create'; MANAGER_EDIT_KEY = null; MANAGER_EDIT_ROW = null; managerRenderView(); });
        toolbar.appendChild(createBtn);

        var search = document.createElement('input');
        search.type = 'text';
        search.placeholder = dpT('search_placeholder','Search name, phrase, field, or creator…');
        search.className = 'form-control form-control-sm dpm-search';
        toolbar.appendChild(search);
        body.appendChild(toolbar);

        // A table with a sticky header; it fills the remaining modal height.
        var listWrap = managerField('div', '');
        listWrap.className = 'dpm-list-wrap';
        var table = document.createElement('table');
        table.className = 'dpm-table';
        var thead = document.createElement('thead');
        var headerRow = document.createElement('tr');
        [['name', dpT('col_name','Name')], ['phrase_key', dpT('col_my_phrase','My Phrase')],
         ['contents', dpT('col_contents','Contents')], ['creator', dpT('col_creator','Creator')]].forEach(function(col){
            var th = document.createElement('th');
            th.className = 'dpm-sortable';
            th.dataset.sort = col[0];
            th.textContent = col[1];
            th.addEventListener('click', function(){
                if (sort.field === col[0]) sort.direction *= -1;
                else { sort.field = col[0]; sort.direction = 1; }
                renderRows();
            });
            headerRow.appendChild(th);
        });
        var actionHead = document.createElement('th');
        actionHead.className = 'dpm-actions-col';
        actionHead.textContent = dpT('col_actions','Actions');
        headerRow.appendChild(actionHead);
        thead.appendChild(headerRow);
        table.appendChild(thead);
        var tbody = document.createElement('tbody');
        table.appendChild(tbody);
        listWrap.appendChild(table);
        body.appendChild(listWrap);

        function rowContent(row) {
            if (row.content_type !== 'object') return {};
            if (row.content && typeof row.content === 'object') return row.content;
            try { return JSON.parse(row.content || '{}'); } catch (e) { return {}; }
        }

        function creator(row) {
            var name = String(row.creator_name || '').trim();
            return name || (row.owner_uid ? dpT('user_number','User #%ID%').replace('%ID%', row.owner_uid) : dpT('built_in','Built-in'));
        }

        function rowZones(row) {
            var zones = String(row.zones || '').split(',').map(function(zone){ return zone.trim().toUpperCase(); }).filter(Boolean);
            if (!zones.length && row.content_type === 'object') {
                Object.keys(rowContent(row)).forEach(function(fieldId){
                    var zone = phraseFieldZone(fieldId);
                    if (zone && zones.indexOf(zone) === -1) zones.push(zone);
                });
            }
            return zones.sort();
        }

        function textContent(row) {
            return String(row.content || '').replace(/\s+/g, ' ').trim();
        }

        function contentSortValue(row) {
            return row.content_type === 'object' ? rowZones(row).join(' ') : textContent(row);
        }

        function textPreview(row) {
            var content = textContent(row);
            return content.length > 135 ? content.substring(0, 135).replace(/\s+$/, '') + '\u2026' : content;
        }

        function matches(row, q) {
            return [row.name, row.phrase_key, creator(row), contentSortValue(row)].join(' ').toLowerCase().indexOf(q) !== -1;
        }

        function renderRows() {
            var q = (search.value || '').toLowerCase().trim();
            tbody.innerHTML = '';
            var shown = 0;
            Array.prototype.forEach.call(thead.querySelectorAll('.dpm-sortable'), function(th){
                var label = th.textContent.replace(/ [\u2191\u2193]$/, '');
                th.textContent = label + (th.dataset.sort === sort.field ? (sort.direction > 0 ? ' \u2191' : ' \u2193') : '');
            });
            var rows = entries.filter(function(row){ return !q || matches(row, q); });
            rows.sort(function(a, b){
                function value(row) {
                    if (sort.field === 'contents') return contentSortValue(row).toLowerCase();
                    if (sort.field === 'creator') return creator(row).toLowerCase();
                    return String(row[sort.field] || '').toLowerCase();
                }
                return value(a).localeCompare(value(b)) * sort.direction;
            });
            rows.forEach(function(row){
                shown++;
                var key = row.phrase_key;
                var isObject = row.content_type === 'object';
                var mine = row.owner_uid === MANAGER_AUTH_UID;

                var tr = document.createElement('tr');
                tr.className = 'dpm-tr';

                var tdName = document.createElement('td');
                tdName.className = 'dpm-td-name';
                tdName.textContent = row.name || key;
                tr.appendChild(tdName);

                var tdKey = document.createElement('td');
                tdKey.className = 'dpm-td-key';
                tdKey.textContent = key;
                tr.appendChild(tdKey);

                var tdContents = document.createElement('td');
                tdContents.className = 'dpm-td-contents';
                if (isObject) {
                    var zones = rowZones(row);
                    if (zones.length) {
                        zones.forEach(function(zone){
                            var badge = document.createElement('span');
                            badge.className = 'dpm-zone';
                            badge.textContent = zone;
                            tdContents.appendChild(badge);
                        });
                    } else {
                        tdContents.textContent = dpT('empty_fields','No fields');
                    }
                } else {
                    tdContents.classList.add('dpm-text-preview');
                    tdContents.textContent = textPreview(row) || '\u2014';
                    tdContents.title = textContent(row);
                }
                tr.appendChild(tdContents);

                var tdCreator = document.createElement('td');
                tdCreator.className = 'dpm-td-creator';
                tdCreator.appendChild(document.createTextNode(creator(row)));
                if (row.visibility === 'shared') {
                    var sv = document.createElement('span');
                    sv.className = 'dpm-vis';
                    sv.textContent = dpT('shared','Shared');
                    tdCreator.appendChild(sv);
                }
                tr.appendChild(tdCreator);

                var tdAct = document.createElement('td');
                tdAct.className = 'dpm-td-actions';

                if (isObject) {
                    var depBtn = mkIconBtn('fa-arrow-circle-right', dpT('deploy_title',"Copy this phrase's values into the chart"), 'btn btn-primary btn-sm');
                    depBtn.addEventListener('click', function(){
                        var priorMap = ACTIVE_OBJECT_PHRASES[key];
                        var priorMeta = PHRASE_META[key];
                        ACTIVE_OBJECT_PHRASES[key] = rowContent(row);
                        PHRASE_META[key] = row;
                        dfDeploy(key);
                        if (priorMap === undefined) delete ACTIVE_OBJECT_PHRASES[key];
                        else ACTIVE_OBJECT_PHRASES[key] = priorMap;
                        if (priorMeta === undefined) delete PHRASE_META[key];
                        else PHRASE_META[key] = priorMeta;
                    });
                    tdAct.appendChild(depBtn);
                }

                if (row.id && !mine && row.visibility === 'shared') {
                    var impBtn = mkIconBtn('fa-download', dpT('import_title','Copy as a private phrase'), 'btn btn-info btn-sm');
                    impBtn.addEventListener('click', function(){
                        serverActionById('import', row.id).then(function(r){
                            if (r && r.ok) managerRenderView();
                            else managerToast(r.msg || dpT('import_failed','Import failed.'), 'error');
                        });
                    });
                    tdAct.appendChild(impBtn);
                }

                if (!row.id || mine) {
                    var editBtn = mkIconBtn('fa-edit', dpT('edit_title','Edit this phrase'), 'btn btn-primary btn-sm');
                    editBtn.addEventListener('click', function(){
                        MANAGER_VIEW = 'create';
                        MANAGER_EDIT_KEY = key;
                        MANAGER_EDIT_ROW = row.id ? row : null;
                        managerRenderView();
                    });
                    tdAct.appendChild(editBtn);
                }

                if (row.id && mine) {
                    var shareTitle = row.visibility === 'shared' ? dpT('unshare_title','Make this copy private again') : dpT('share_title','Make this phrase available to all users');
                    var shBtn = mkIconBtn(row.visibility === 'shared' ? 'fa-lock' : 'fa-share-alt', shareTitle, 'btn btn-secondary btn-sm');
                    shBtn.addEventListener('click', function(){
                        var pr = serverActionById(row.visibility === 'shared' ? 'unshare' : 'share', row.id);
                        pr.then(function(r){ if (r && r.ok) managerRenderView(); else managerToast(r.msg || dpT('action_failed','Action failed.'), 'error'); });
                    });
                    tdAct.appendChild(shBtn);
                }

                if (!row.id || mine || MANAGER_IS_ADMIN) {
                    var delBtn = mkIconBtn('fa-trash', dpT('delete_title','Delete this phrase'), 'btn btn-danger btn-sm');
                    delBtn.addEventListener('click', function(){
                        managerConfirm(dpT('delete_confirm','Delete phrase %KEY%? This cannot be undone.').replace('%KEY%', key), dpT('delete','Delete'), function(){
                            if (!row.id) {
                                if (ACTIVE_OBJECT_PHRASES[key]) delete ACTIVE_OBJECT_PHRASES[key];
                                else delete ACTIVE_PHRASES[key];
                                if (window.removePersist) removePersist(key);
                                managerRenderView();
                            } else {
                                serverActionById('delete', row.id).then(function(r){
                                    if (r && r.ok) managerRenderView();
                                    else managerToast(r.msg || dpT('delete_failed','Delete failed.'), 'error');
                                });
                            }
                        });
                    });
                    tdAct.appendChild(delBtn);
                }

                tr.appendChild(tdAct);
                tbody.appendChild(tr);
            });

            if (shown === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="dpm-empty">' + dpT('no_matches','No phrases match your search.') + '</td></tr>';
            }
        }

        search.addEventListener('input', renderRows);
        renderRows();
    }

    // Create / Edit form.  For a field-capture phrase the form is a
    // two-column layout: left vertical zone list with on/off toggles
    // (the originating zone is on by default), right the fields of the
    // chosen sections pre-seeded with their current values so the phrase
    // records exactly what the user wants re-applied.  Plain-text phrases
    // get a simple expansion-text box instead.
    function managerRenderCreate(body) {
        body.innerHTML = '';

        var editKey = (MANAGER_EDIT_KEY || '').toLowerCase();
        var meta = MANAGER_EDIT_ROW || PHRASE_META[editKey] || null;
        // Consumed once: the vitals-row red dot requests the multi-field
        // Create view with all zones pre-enabled; manual entry keeps the
        // Single/Single-with-origin defaults.
        var forceMulti = MANAGER_CREATE_MULTI;
        MANAGER_CREATE_MULTI = false;
        var isObject = forceMulti || (meta && meta.content_type
            ? meta.content_type === 'object'
            : !!ACTIVE_OBJECT_PHRASES[editKey]);
        function editObjectContent() {
            if (meta && meta.content_type === 'object') {
                if (meta.content && typeof meta.content === 'object') return meta.content;
                try { return JSON.parse(meta.content || '{}'); } catch (e) { return {}; }
            }
            return ACTIVE_OBJECT_PHRASES[editKey] || {};
        }
        function editTextContent() {
            if (meta && meta.content_type === 'text') return meta.content || '';
            return ACTIVE_PHRASES[editKey] || '';
        }

        // Single header row: back, Name, My Phrase (trigger key), and the
        // Single/Multiple toggle — all on one line to save vertical space.
        var headRow = document.createElement('div');
        headRow.style.cssText = 'display:flex; align-items:center; gap:12px; flex-wrap:nowrap; margin-bottom:12px; white-space:nowrap;';

        var back = managerField('button', '\u2190');
        back.type = 'button';
        back.title = dpT('back_title','Back to all phrases');
        back.className = 'btn btn-secondary btn-sm';
        back.addEventListener('click', function(){ MANAGER_VIEW = 'list'; MANAGER_EDIT_KEY = null; MANAGER_EDIT_ROW = null; managerRenderView(); });
        headRow.appendChild(back);

        function labeled(label, input, chw) {
            var box = document.createElement('div');
            box.style.cssText = 'display:flex; align-items:center; gap:6px; white-space:nowrap;';
            var lbl = document.createElement('span');
            lbl.textContent = label;
            lbl.style.cssText = 'font-weight:bold; font-size:.82em; white-space:nowrap;';
            box.appendChild(lbl);
            input.style.width = chw + 'ch';
            box.appendChild(input);
            return box;
        }

        var nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.id = 'DOTPHRASE_NEW_NAME';
        nameInput.className = 'form-control form-control-sm';
        nameInput.placeholder = dpT('col_name','Name');
        nameInput.value = (meta && meta.name) || '';
        headRow.appendChild(labeled(dpT('name','Name:'), nameInput, '20'));

        var keyInput = document.createElement('input');
        keyInput.type = 'text';
        keyInput.id = 'DOTPHRASE_NEW_KEY';
        keyInput.className = 'form-control form-control-sm';
        keyInput.placeholder = '.phrase';
        keyInput.value = editKey;
        if (editKey) keyInput.readOnly = true;   // server owns the key on update
        headRow.appendChild(labeled(dpT('my_phrase','My Phrase:'), keyInput, '16'));

        var toggleBox = document.createElement('div');
        toggleBox.style.cssText = 'display:flex; align-items:center; gap:6px; white-space:nowrap;';
        var tglLbl = document.createElement('span');
        tglLbl.textContent = dpT('toggle_fields','Toggle Fields:');
        tglLbl.style.cssText = 'font-weight:bold; font-size:.82em;';
        toggleBox.appendChild(tglLbl);
        var singleBtn = document.createElement('button');
        singleBtn.type = 'button';
        singleBtn.textContent = dpT('single','Single');
        singleBtn.className = 'btn btn-sm ' + (!isObject ? 'btn-primary' : 'btn-secondary');
        var multiBtn = document.createElement('button');
        multiBtn.type = 'button';
        multiBtn.textContent = dpT('multiple','Multiple');
        multiBtn.className = 'btn btn-sm ' + (isObject ? 'btn-primary' : 'btn-secondary');
        toggleBox.appendChild(singleBtn);
        toggleBox.appendChild(multiBtn);
        headRow.appendChild(toggleBox);

        body.appendChild(headRow);

        var contentBox = managerField('div', '');
        contentBox.id = 'DOTPHRASE_NEW_CONTENT';
        contentBox.style.cssText = 'flex:1 1 auto; min-height:0; display:flex; flex-direction:column; margin-top:8px;';
        body.appendChild(contentBox);

        function renderContent() {
            var ctype = isObject ? 'object' : 'text';
            MANAGER_EL.classList.toggle('dpm-single-mode', ctype === 'text');
            MANAGER_EL.classList.toggle('dpm-multi-mode', ctype === 'object');
            contentBox.innerHTML = '';
            if (ctype === 'text') {
                var area = managerField('textarea', '');
                area.id = 'DOTPHRASE_NEW_TEXT';
                area.className = 'form-control dpm-single-text';
                area.value = editTextContent();
                contentBox.appendChild(area);

                var variableTitle = document.createElement('div');
                variableTitle.className = 'dpm-variable-title';
                variableTitle.textContent = dpT('variables_help','Variables — drag or double-click to insert');
                contentBox.appendChild(variableTitle);
                var tray = document.createElement('div');
                tray.className = 'dpm-variable-tray';
                var variables = [
                    ['PATIENT_NAME', dpT('var_patient_name','Patient name')],
                    ['FIRST_NAME', dpT('var_first_name','First name')],
                    ['LAST_NAME', dpT('var_last_name','Last name')],
                    ['DOB', dpT('var_dob','Date of birth')],
                    ['AGE', dpT('var_age','Age')],
                    ['PRONOUN', dpT('var_pronoun','Pronoun')],
                    ['PROVIDER', dpT('var_provider','Provider')],
                    ['VISIT_DATE', dpT('var_visit_date','Visit date')]
                ];
                function insertToken(token) {
                    var start = area.selectionStart || 0;
                    var end = area.selectionEnd || start;
                    area.value = area.value.substring(0, start) + token + area.value.substring(end);
                    area.focus();
                    area.setSelectionRange(start + token.length, start + token.length);
                }
                variables.forEach(function(variable){
                    var chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'dpm-variable';
                    chip.draggable = true;
                    chip.dataset.token = '{{' + variable[0] + '}}';
                    chip.textContent = variable[1];
                    chip.title = chip.dataset.token;
                    chip.addEventListener('dblclick', function(){ insertToken(chip.dataset.token); });
                    chip.addEventListener('dragstart', function(e){ e.dataTransfer.setData('text/plain', chip.dataset.token); });
                    tray.appendChild(chip);
                });
                area.addEventListener('dragover', function(e){ e.preventDefault(); });
                area.addEventListener('drop', function(e){
                    e.preventDefault();
                    var token = e.dataTransfer.getData('text/plain');
                    if (token) insertToken(token);
                });
                contentBox.appendChild(tray);
                return;
            }

            var layout = managerField('div', '');
            layout.id = 'DOTPHRASE_NEW_LAYOUT';
            layout.style.cssText = 'display:flex; gap:10px; flex:1 1 auto; min-height:0;';
            layout.innerHTML = '';  // built below

            var zones = document.createElement('div');
            zones.className = 'dpm-zones';
            zones.style.cssText = 'flex:0 0 140px; min-height:0; overflow-y:auto;';
            var zHead = managerField('div', dpT('sections','Sections'));
            zHead.style.cssText = 'font-weight:bold; margin-bottom:4px;';
            zones.appendChild(zHead);

            var fields = document.createElement('div');
            fields.className = 'dpm-fields';
            fields.style.cssText = 'flex:1 1 auto; min-width:0;';
            var initial = editObjectContent();
            // PMH is the PMSFH history drawer and NEURO's motility/ACT
            // layout does not yet mirror cleanly — neither is offered as
            // a capture section.  (NEURO phrases can still be created
            // elsewhere and deployed from the list.)
            var origins = (MANAGER_ORIGIN_ZONE && MANAGER_ORIGIN_ZONE !== 'PMH' && MANAGER_ORIGIN_ZONE !== 'NEURO') ? MANAGER_ORIGIN_ZONE : null;
            var zona = (DF_ZONES || ['HPI','PMH','EXT','ANTSEG','RETINA','NEURO','IMPPLAN']).filter(function(z){ return z !== 'PMH' && z !== 'NEURO'; });
            var existing = phraseZones(editKey);

            zona.forEach(function(zone){
                var on = !!editKey
                    ? (existing.indexOf(zone) !== -1)
                    : (forceMulti || zone === origins || (!origins && zone === 'EXT'));
                var row = managerField('label', '');
                row.className = 'dpm-zone-row';
                row.style.cssText = 'display:flex; align-items:center; gap:6px; margin:2px 0;';
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.checked = on;
                cb.dataset.zone = zone;
                row.appendChild(cb);
                row.appendChild(document.createTextNode(zone));
                zones.appendChild(row);
            });

            // The right pane shows a bordered clone of every enabled zone's
            // REAL panel (same shape as PRIORS: labels, layout, borders), not
            // a flat list of DB field ids.  Each clone's controls keep the
            // live form's current values; editing an existing phrase overlays
            // the stored phrase values on top.  data-df-id maps them back.
            function renderFields() {
                var selected = {};
                Array.prototype.forEach.call(zones.querySelectorAll('input[type=checkbox]'), function(c){
                    if (c.checked) selected[c.dataset.zone] = true;
                });
                fields.innerHTML = '';
                var any = false;
                zona.forEach(function(zone){
                    if (!selected[zone]) return;
                    any = true;
                    var block = managerField('div', '');
                    // DF_class puts the capture section in the SAME CSS
                    // context as the pt-phrase right panels, so the mirror
                    // inherits the full fixed layout contract (float
                    // geometry, container resets, provenance colors) instead
                    // of the partial #DOTPHRASE_MANAGER overrides alone.
                    block.className = 'DF_class dpm-create-section';
                    // Flex items form their own BFC, so the floated
                    // measurements grid is contained without clipping; the
                    // absolute R/L box is allowed to reach its full height.
                    block.style.cssText = 'border:1px solid #aeb9c2; border-radius:6px; margin:0 0 14px; overflow:visible;';
                    var cap = managerField('div', zone);
                    cap.className = 'dpm-create-caption';
                    cap.style.cssText = 'background:#eef3f7; border-bottom:1px solid #cbd5dc; font-weight:bold; padding:6px 10px;';
                    block.appendChild(cap);
                    var mirror = managerField('div', '');
                    mirror.className = 'DF_mirror_wrapper';
                    dfMirrorBuild(zone, mirror);
                    // Capture mode: the mirrored fields are fillable so the
                    // user can complete only the values they want saved.
                    Array.prototype.forEach.call(mirror.querySelectorAll('input, textarea, select'), function(n){
                        n.disabled = false;
                        n.readOnly = false;
                    });

                    // Pre-fill the clone with the current exam / stored values.
                    Array.prototype.forEach.call(mirror.querySelectorAll('input, textarea, select'), function(n){
                        var origId = n.getAttribute('data-df-id');
                        if (!origId) return;
                        var live = document.getElementById(origId);
                        if (live && (typeof live.value === 'string')) n.value = live.value;
                        if (editKey && (origId in initial)) {
                            n.value = initial[origId];
                            // Same provenance cue the pt panels use (green);
                            // an inline color would lose to the !important
                            // neutral-empty mirror rule.
                            n.classList.add('df-phrase-populated');
                        }
                    });
                    if (zone === 'RETINA') {
                        // The Eye Form carries the dilation-consent
                        // checkbox beside the zone title; the capture
                        // section does the same in its caption bar.  The
                        // leftover zone heading ("Retina:") leaves the
                        // content area — the caption already names it.
                        var dilInput = mirror.querySelector('[data-df-id="DIL_RISKS"]');
                        var dilWrap = dilInput ? dilInput.parentElement : null;
                        if (dilWrap && dilWrap.tagName === 'SPAN') {
                            // Drop TEXT_class: the capture-section rule
                            // gives .TEXT_class a 415px floor, which would
                            // blow up the caption bar it moves into.
                            dilWrap.classList.remove('TEXT_class');
                            dilWrap.classList.add('dpm-dil-title');
                            cap.appendChild(dilWrap);
                        }
                        Array.prototype.forEach.call(mirror.querySelectorAll('span.bold'), function(b){
                            var t = (b.textContent || '').replace(/\s+/g, ' ').trim();
                            if (/^Retina/i.test(t) && !b.querySelector('input, textarea, select')) b.remove();
                        });
                    }
                    block.appendChild(mirror);
                    fields.appendChild(block);
                });
                if (!any) {
                    var none = managerField('div', dpT('no_sections','Turn on a section to capture its fields.'));
                    none.style.cssText = 'color:#888; font-style:italic;';
                    fields.appendChild(none);
                }
            }

            zones.addEventListener('change', renderFields);
            layout.appendChild(zones);
            layout.appendChild(fields);
            renderFields();
            contentBox.appendChild(layout);
        }

        function setType(obj) {
            isObject = obj;
            singleBtn.className = 'btn btn-sm ' + (!isObject ? 'btn-primary' : 'btn-secondary');
            multiBtn.className = 'btn btn-sm ' + (isObject ? 'btn-primary' : 'btn-secondary');
            renderContent();
        }
        singleBtn.addEventListener('click', function(){ setType(false); });
        multiBtn.addEventListener('click', function(){ setType(true); });

        var saveRow = managerField('div', '');
        saveRow.className = 'btn-row';
        saveRow.style.marginTop = '10px';
        var saveBtn = managerField('button', editKey ? dpT('save_changes','Save changes') : dpT('create_phrase','Create phrase'));
        saveBtn.type = 'button';
        saveBtn.className = 'btn btn-primary btn-sm';
        saveBtn.addEventListener('click', function(){
            var name = (nameInput.value || '').trim();
            var key = (keyInput.value || '').trim();
            if (!key) { managerToast(dpT('enter_key','Enter a phrase key.'), 'warn'); return; }
            if (key.charAt(0) !== '.') key = '.' + key;
            var k = key.toLowerCase();

            var payload = { name: name || k, phrase_key: key };
            if (meta && meta.id) payload.id = meta.id;

            if (isObject) {
                var layout = dpTopDoc().getElementById('DOTPHRASE_NEW_LAYOUT');
                var map = {};
                if (layout) {
                    Array.prototype.forEach.call(layout.querySelectorAll('.dpm-create-section .DF_mirror_wrapper input, .dpm-create-section .DF_mirror_wrapper textarea, .dpm-create-section .DF_mirror_wrapper select'), function(n){
                        var origId = n.getAttribute('data-df-id');
                        if (!origId) return;
                        var type = (n.tagName === 'INPUT') ? (n.getAttribute('type') || 'text') : n.tagName.toLowerCase();
                        if (type === 'hidden' || type === 'button' || type === 'submit' || type === 'checkbox' || type === 'radio') return;
                        var v = String(n.value || '').trim();
                        if (v !== '') map[origId] = v;
                    });
                }
                if (Object.keys(map).length === 0) { managerToast(dpT('need_value','Include at least one field with a value.'), 'warn'); return; }
                var zonesSel = {};
                Array.prototype.forEach.call(dpTopDoc().querySelectorAll('#DOTPHRASE_NEW_LAYOUT input[type=checkbox]'), function(c){
                    if (c.dataset.zone && c.checked) zonesSel[c.dataset.zone] = true;
                });
                payload.content_type = 'object';
                payload.content = JSON.stringify(map);
                payload.zones = Object.keys(zonesSel).join(',');
            } else {
                var area = dpTopDoc().getElementById('DOTPHRASE_NEW_TEXT');
                var text = area ? area.value : '';
                if (!text) { managerToast(dpT('enter_text','Enter expansion text.'), 'warn'); return; }
                payload.content_type = 'text';
                payload.content = text;
                payload.zones = '';
            }

            var req = payload.id ? window.eyeMagServerUpdate(payload) : window.eyeMagServerCreate(payload);
            req.then(function(r){
                if (r && r.ok) {
                    MANAGER_VIEW = 'list';
                    MANAGER_EDIT_KEY = null;
                    MANAGER_EDIT_ROW = null;
                    managerHide();
                } else if (r && !r.ok) {
                    managerToast(r.msg || dpT('save_failed','Save failed.'), 'error');
                }
            });
        });
        saveRow.appendChild(saveBtn);
        body.appendChild(saveRow);

        renderContent();
    }

    // Import view: every shared phrase owned by someone else, adopt as a
    // private copy (server sets improvement_of -> source).
    function managerRenderImport(body) {
        body.innerHTML = '';

        var back = managerField('button', '\u2190 ' + dpT('all_phrases','All phrases'));
        back.type = 'button';
        back.className = 'btn btn-secondary btn-sm';
        back.style.marginBottom = '8px';
        back.addEventListener('click', function(){ MANAGER_VIEW = 'list'; managerRenderView(); });
        body.appendChild(back);

        var intro = managerField('div', dpT('import_intro','Shared phrases from other users — Import copies them as your own private phrase you can then edit and improve.'));
        intro.style.cssText = 'color:#666; margin-bottom:8px;';
        body.appendChild(intro);

        var list = managerField('div', '');
        list.className = 'dpm-list';
        body.appendChild(list);

        var shared = [];
        Object.keys(PHRASE_META).forEach(function(key){
            var m = PHRASE_META[key];
            if (m.visibility === 'shared' && m.owner_uid !== MANAGER_AUTH_UID) shared.push({key: key, meta: m});
        });
        if (shared.length === 0) {
            list.innerHTML = '<div class="dpm-empty">' + dpT('no_shared','No shared phrases from other users.') + '</div>';
            return;
        }
        shared.forEach(function(row){
            var item = managerField('div', '');
            item.className = 'dpm-row';
            var z = phraseZones(row.key);
            var zonesHtml = z.length ? z.map(function(zn){ return '<span class="dpm-zone">' + zn + '</span>'; }).join(' ') : '';
            var t = document.createElement('span');
            t.className = 'dpm-row-body';
            t.innerHTML = '<strong>' + row.key.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</strong> ' +
                (row.meta.name ? '<span style="color:#666;">' + row.meta.name.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</span>' : '') +
                ' ' + zonesHtml +
                ' <span class="dpm-vis">' + dpT('by_user','by user') + ' ' + row.meta.owner_uid + '</span>';
            item.appendChild(t);
            var btns = managerField('div', '');
            btns.className = 'dpm-row-actions';
            var imp = managerField('button', dpT('import','Import'));
            imp.className = 'btn btn-info btn-xs';
            imp.addEventListener('click', function(){
                window.eyeMagServerImport(row.key).then(function(r){
                    if (r && r.ok) managerRenderView();
                    else if (r && !r.ok) managerToast(r.msg || 'Import failed.', 'error');
                });
            });
            btns.appendChild(imp);
            item.appendChild(btns);
            list.appendChild(item);
        });
    }

    // Mirror the live left panel's real structure (same tables, classes and
    // containing blocks as the PRIORS panel), not a flattened table or a
    // dimension-copied reconstruction. Only interactive chrome is removed;
    // every source geometry class (.narrow_textarea/.wide_textarea/
    // .borderShadow/.QP_lengthen, and each field's own class) is kept so the
    // SAME CSS that lays out the left panel and PRIORS lays out this mirror.
    // ids are deduped into data-df-id; every control is disabled read-only.
    function dfBuildDisplayContent(zone) {
        var src = document.getElementById(zone + '_left');
        if (!src) return null;
        var clone = src.cloneNode(true);
        var serial = 0;

        // Interactive chrome that must not appear in a read-only mirror.
        // Note: data icons (e.g. the fa-minus separators in measurement rows)
        // and data images/wrappers are intentionally left in place, so the
        // mirror keeps the same cell geometry as the live panel and PRIORS.
        Array.prototype.forEach.call(clone.querySelectorAll('button, a, .kb, .kb_off, .kb_left, .kb_right, .kb_center, .copier, .overlay, [class*="closeButton"], [class*="DotPhraseIcon"], [name*="Shorthand"], [name*="DotPhrases"]'), function(node){
            node.remove();
        });

        // The expand/collapse control belongs to the live left-panel R/L
        // subsection. It is not part of the read-only DF mirror.
        Array.prototype.forEach.call(clone.querySelectorAll('.top_right'), function(node){
            node.remove();
        });

        // Stripping the tab <a> anchors above leaves the HPI CC-tab strip as
        // a <ul> of empty <li>s that render as stray bullet dots.  Drop any
        // list left with no text content at all.
        Array.prototype.forEach.call(clone.querySelectorAll('ul'), function(ul){
            var hasText = Array.prototype.some.call(ul.children, function(li){
                return (li.textContent || '').replace(/\s+/g, '') !== '';
            });
            if (!hasText) ul.remove();
        });

        // HPI capture is CC1/HPI1 only: the inactive CC panes (CC2/CC3),
        // the Chronic Problems reference cell and the "Detailed HPI" coding
        // footer are not capturable fields, so they never enter the
        // capture sections.
        Array.prototype.forEach.call(clone.querySelectorAll('#tab2_CC_text, #tab3_CC_text'), function(pane){
            if (pane.parentNode) pane.parentNode.removeChild(pane);
        });
        var chronicHelp = clone.querySelector('#CHRONIC_HELP');
        if (chronicHelp) {
            var cell = chronicHelp;
            while (cell && cell.tagName !== 'TD') cell = cell.parentElement;
            if (cell && cell.parentNode) cell.parentNode.removeChild(cell);
        }
        var detailedHelp = clone.querySelector('#CODE_HIGH_HELP');
        if (detailedHelp) {
            var footRow = detailedHelp;
            while (footRow && footRow.tagName !== 'TR') footRow = footRow.parentElement;
            if (footRow && footRow.parentNode) footRow.parentNode.removeChild(footRow);
        }

        // RETINA carries the document-link rows (OCT / FA-ICG / Optic
        // Disc / Fundus / Radiology / VF uploads) and the Scleral
        // Depression tool button.  After the chrome strip these are
        // fieldless label tables with nothing capturable — drop them so
        // only real exam fields remain.
        if (zone === 'RETINA') {
            Array.prototype.forEach.call(clone.querySelectorAll('table'), function(tbl){
                if (!tbl.querySelector('input, textarea, select, table')) tbl.remove();
            });
        }

        // Dilation box: the live form floats the Time input beside the
        // title; the capture view shows it as a full-width centered row
        // UNDER the two drug columns instead.  (This runs before the id
        // dedupe, so the clone still carries its live-form ids.)
        var dilInput = clone.querySelector('#DIL_MEDS');
        var dilTable = clone.querySelector('#dil_listbox');
        if (dilInput && dilTable && dilTable.parentNode === dilInput.parentNode) {
            dilTable.parentNode.insertBefore(dilInput, dilTable.nextSibling);
        }

        // Drop the zone heading label and its separators so the mirror starts
        // at the real field content (no extra heading row / stray colon).
        Array.prototype.forEach.call(clone.querySelectorAll('.TEXT_class, [id$="_left_text"]'), function(tc){
            Array.prototype.forEach.call(tc.querySelectorAll(':scope > b, :scope > span.bold, :scope > i'), function(n){ n.remove(); });
            var kids = tc.childNodes, i = 0;
            while (i < kids.length) {
                var n = kids[i];
                if (n.nodeType === 3 && /^[:,\s\u00a0]*$/.test(n.nodeValue)) { tc.removeChild(n); continue; }
                if (n.nodeType === 1 && n.tagName === 'BR') { tc.removeChild(n); continue; }
                break;
            }
        });

        // Deduplicate every id so the mirror cannot clash with the live form.
        Array.prototype.forEach.call(clone.querySelectorAll('[id]'), function(node){
            node.setAttribute('data-df-id', node.id);
            node.removeAttribute('id');
            node.id = 'DF_' + zone + '_' + (++serial);
        });

        // The outer left wrapper is a floated 550px card; the DF panel must
        // not float or card itself (the *_right panel owns that chrome).
        clone.removeAttribute('id');
        clone.removeAttribute('name');
        clone.classList.remove('exam_section_left', 'borderShadow');

        // Read-only mirror.
        Array.prototype.forEach.call(clone.querySelectorAll('input, textarea, select'), function(node){
            node.disabled = true;
            node.readOnly = true;
        });

        return clone;
    }

    function dfMirrorBuild(zone, mirrorEl) {
        if (!mirrorEl) return;
        var content = dfBuildDisplayContent(zone);
        if (!content) return;

        // Demote every control to disabled read-only.
        Array.prototype.forEach.call(content.querySelectorAll('input, textarea, select'), function(n){
            n.disabled = true;
            n.readOnly = true;
        });

        mirrorEl.innerHTML = '';
        mirrorEl.appendChild(content);
    }

    function dfAuthorized(key) {
        var meta = PHRASE_META[key.toLowerCase()];
        if (!meta) return true;          // defaults / local-only phrases stay visible
        if (meta.visibility === 'shared') return true;
        return true;                      // server list already restricted to what the user may read
    }

    function dfMatches(query, zone) {
        var q = (query || '').toLowerCase().trim();
        if (q.length < 3) return [];
        var out = [];
        // A DF zone mirror is filled from an object phrase's field->value
        // map.  Plain text phrases have no field mapping, so offering them
        // here made Apply write nothing (and the mirror got cleared).  Zone
        // searches therefore list object phrases only; the global mid-menu
        // search (zone === '') still returns everything.
        Object.keys(ACTIVE_OBJECT_PHRASES).forEach(function(key){
            if (key.indexOf(q) === -1) return;
            if (!dfAuthorized(key)) return;
            if (zone && phraseFieldsForZone(key, zone).length === 0) return;
            out.push(key);
        });
        if (!zone) {
            Object.keys(ACTIVE_PHRASES).forEach(function(key){
                if (key.indexOf(q) === -1) return;
                if (!dfAuthorized(key)) return;
                var z = phraseZones(key);
                if (zone && z.length && z.indexOf(zone) === -1) return;
                out.push(key);
            });
        }
        return out.sort();
    }

    // Deploy a phrase's stored values straight into the live chart (no
    // panel preview).  Non-empty values are written across every zone the
    // phrase belongs to, and marked with the dot-phrase provenance color.
    function dfDeploy(key) {
        var k = (key || '').toLowerCase();
        var map = ACTIVE_OBJECT_PHRASES[k];
        if (!map || typeof map !== 'object') {
            managerToast(dpT('no_fields','The phrase %KEY% has no fields to deploy.').replace('%KEY%', key), 'warn');
            return 0;
        }
        var applied = 0;
        var handled = applyImpPlanPhrase(map);
        applied += Object.keys(handled).length;
        Object.keys(map).forEach(function(fid){
            if (handled[fid]) return;
            var live = document.getElementById(fid);
            var val = map[fid];
            if (!live || val === null || val === undefined || String(val) === '') return;
            if (typeof live.value !== 'string') return;
            var v = String(val);
            if (live.value !== v) { live.value = v; $(live).trigger('change'); applied++; }
            live.classList.add('df-live-populated');
        });
        managerToast(dpT('deployed','Deployed %KEY% - %N% field(s) written to chart.').replace('%KEY%', key).replace('%N%', String(applied)), 'ok');
        return applied;
    }

    function init() {
        initHandlers();
        initMidMenu();
    }

    function initMidMenu() {
        var icon = document.getElementById('BUTTON_mid_dp_builder');
        if (icon) icon.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); managerShow(); });

        var ac = document.getElementById('mid_dp_search');
        if (!ac) return;
        ac.addEventListener('input', function(){
            var items = dfMatches(ac.value, '');
            if (!ac.dataset.popover) {
                var pop = document.createElement('div');
                pop.style.cssText = 'background:#fff; border:1px solid #cbd5dc; border-radius:4px; box-shadow:2px 2px 6px rgba(0,0,0,.18); max-height:160px; overflow:auto; position:absolute; z-index:9000;';
                pop.className = 'DF_popover';
                ac.style.position = 'relative';
                var wrap = ac.parentNode;
                wrap.style.position = 'relative';
                wrap.appendChild(pop);
                ac.dataset.popover = '1';
            }
            var pop = ac.parentNode.querySelector('.DF_popover');
            if (!pop) return;
            pop.innerHTML = '';
            pop.style.display = items.length ? 'block' : 'none';
            Array.prototype.forEach.call(items.slice(0, 25), function(key){
                var item = document.createElement('div');
                item.textContent = key + '  —  ' + (PHRASE_META[key.toLowerCase()] ? PHRASE_META[key.toLowerCase()].name : '');
                item.className = 'df_popover-item';
                item.style.cssText = 'cursor:pointer; padding:4px 8px;';
                item.addEventListener('mousedown', function(e){
                    e.preventDefault();
                    ac.value = key;
                    pop.style.display = 'none';
                    if (ACTIVE_OBJECT_PHRASES[key.toLowerCase()]) dfDeploy(key);
                });
                pop.appendChild(item);
            });
        });
        ac.addEventListener('keydown', function(e){
            if (e.key !== 'Enter') return;
            var items = dfMatches(ac.value, '');
            var key = items.length ? items[0] : (ac.value || '').toLowerCase();
            if (!ACTIVE_OBJECT_PHRASES[key]) return;
            e.preventDefault();
            var pop = ac.parentNode.querySelector('.DF_popover');
            if (pop) pop.style.display = 'none';
            dfDeploy(key);
        });
        ac.addEventListener('blur', function(){
            var pop = ac.parentNode.querySelector('.DF_popover');
            if (pop) setTimeout(function(){ pop.style.display = 'none'; }, 150);
        });
    }

    // If the eye form frame goes away, take the builder with it.
    window.addEventListener('pagehide', function(){
        var d = dpTopDoc();
        var m = d.getElementById('DOTPHRASE_MANAGER');
        if (m && m.parentNode) m.parentNode.removeChild(m);
        Array.prototype.forEach.call(d.querySelectorAll('.dpm-confirm-overlay, .dpm-toast'), function(n){
            if (n.parentNode) n.parentNode.removeChild(n);
        });
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
