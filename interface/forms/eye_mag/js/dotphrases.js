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

    function loadUserPhrases(){
        var user = {};
        try {
            if (window.eyeMagUserPhrases && typeof window.eyeMagUserPhrases === 'object') {
                Object.assign(user, window.eyeMagUserPhrases);
            }
            var hidden = document.getElementById('DOTPHRASES_USER');
            if (hidden && hidden.value) {
                var parsed = JSON.parse(hidden.value);
                Object.assign(user, parsed);
            }
        } catch(e) {}
        return user;
    }

    function loadUserObjectPhrases(){
        var user = {};
        try {
            if (window.eyeMagObjectPhrases && typeof window.eyeMagObjectPhrases === 'object') {
                for (var key in window.eyeMagObjectPhrases) {
                    user[key.toLowerCase()] = window.eyeMagObjectPhrases[key];
                }
            }
        } catch(e) {}
        return user;
    }

    var ACTIVE_PHRASES = Object.assign({}, DEFAULT_PHRASES, loadUserPhrases());
    var ACTIVE_OBJECT_PHRASES = loadUserObjectPhrases(); // multi-field phrases

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
            console.log('Popup mousedown detected');
            POPUP_MOUSEDOWN = true;
        });
        
        popup.addEventListener('mouseup', function(e) {
            console.log('Popup mouseup detected');
            POPUP_MOUSEDOWN = false;
        });
        
        // Add event delegation to popup
        popup.addEventListener('click', function(e) {
            console.log('POPUP CLICK: e.target=' + e.target.tagName + ', e.target.className=' + e.target.className);
            var item = e.target.closest('.dotphrase-autocomplete-item');
            console.log('POPUP CLICK: closest item result=' + (item ? 'FOUND (' + item.getAttribute('data-phrase-key') + ')' : 'not found'));
            if (item) {
                var phraseKey = item.getAttribute('data-phrase-key');
                console.log('Popup click detected, phraseKey=' + phraseKey);
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
        console.log('selectAutocompletePhrase called with phraseKey=' + phraseKey);
        
        // Ensure element has focus before getting position
        el.focus();
        
        var pos = el.selectionStart;
        console.log('After focus: pos=' + pos + ', el.value="' + el.value + '"');
        
        var head = el.value.slice(0, pos);
        var prefix = getDotPhrasePrefix(el);
        
        console.log('prefix=' + prefix + ', phraseKey=' + phraseKey + ', head="' + head + '"');
        
        if (!prefix) {
            console.log('No prefix found, returning');
            hideAutocompletePopup();
            return;
        }
        
        var idx = head.lastIndexOf(prefix);
        console.log('idx=' + idx);
        
        if (idx < 0) {
            console.log('idx < 0, returning');
            hideAutocompletePopup();
            return;
        }
        
        // Check if it's an object phrase or text phrase
        var keyLower = phraseKey.toLowerCase();
        console.log('keyLower=' + keyLower + ', checking ACTIVE_PHRASES=' + (ACTIVE_PHRASES[keyLower] ? 'found' : 'NOT found') + ', ACTIVE_OBJECT_PHRASES=' + (ACTIVE_OBJECT_PHRASES[keyLower] ? 'found' : 'NOT found'));
        
        if (ACTIVE_OBJECT_PHRASES[keyLower]) {
            console.log('Applying object phrase');
            applyObjectPhrase(keyLower, el, prefix);
        } else if (ACTIVE_PHRASES[keyLower]) {
            console.log('Applying text phrase');
            var phrase = ACTIVE_PHRASES[keyLower];
            if (window.eyeMagSubst) {
                phrase = phrase.replace(/\{\{([A-Z_]+)\}\}/g, function(m,k){ return (k in window.eyeMagSubst) ? window.eyeMagSubst[k] : ''; });
            }
            // Replace the PREFIX (what was typed) with the selected PHRASEKEY content
            var before = head.slice(0, idx);
            var tail = el.value.slice(pos);
            el.value = before + phrase + tail;
            el.selectionStart = el.selectionEnd = before.length + phrase.length;
            console.log('After expansion: el.value="' + el.value + '"');
        } else {
            console.log('Phrase not found in either ACTIVE_PHRASES or ACTIVE_OBJECT_PHRASES');
        }
        
        // Trigger change event so the form knows the field was modified
        var event = new Event('change', { bubbles: true });
        el.dispatchEvent(event);
        console.log('Dispatched change event');
        
        hideAutocompletePopup();
    }

    function applyObjectPhrase(keyLower, triggerEl, token){
        var map = ACTIVE_OBJECT_PHRASES[keyLower];
        if (!map) return;
        Object.keys(map).forEach(function(fid){
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
            console.log('handleBlur: POPUP_MOUSEDOWN=true, skipping blur processing');
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

    function init(){
        document.addEventListener('keydown', handleKey, true);
        document.addEventListener('input', handleInput, true);
        // blur doesn't bubble, so we need to use focusout which does bubble
        document.addEventListener('focusout', function(e){
            handleBlur({target: e.target});
        }, true);
        
        // Handle autocomplete item clicks - MUST come before the hide-on-outside-click listener
        document.addEventListener('click', function(e) {
            console.log('Global document click (capture): e.target=' + e.target.tagName + ', AUTOCOMPLETE_POPUP exists=' + (AUTOCOMPLETE_POPUP ? 'yes' : 'no'));
            if (!AUTOCOMPLETE_POPUP) {
                console.log('No popup, skipping autocomplete check');
            } else {
                console.log('Popup exists, checking for item. popup children count=' + AUTOCOMPLETE_POPUP.children.length);
                var item = e.target.closest('.dotphrase-autocomplete-item');
                console.log('closest item result=' + (item ? 'FOUND' : 'not found'));
                if (item && AUTOCOMPLETE_ELEMENT) {
                    console.log('Document click: detected autocomplete item click');
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
                console.log('Document click: hiding popup (click outside)');
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

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
