/**
 * Search highlight utility — DOM-walking implementation.
 *
 * Replaces the vendored SearchHighlight.js jQuery plugin and the hand-rolled
 * mark_hilight() regex approach previously used in the patient custom-report
 * search. Uses TreeWalker + Range; no jQuery dependency.
 *
 * Highlights wrap each match in `<mark class="hilite">...</mark>` (or any
 * configured tag/class), matching the existing CSS selectors.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 Andrew Alanis
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* This module is pure vanilla JavaScript — no jQuery dependency. */

(function (window) {
    'use strict';

    // Tags whose text content should never be highlighted.
    const SKIP_TAGS = new Set(['SCRIPT', 'STYLE', 'TEXTAREA', 'NOSCRIPT']);

    /**
     * Escape a string for safe embedding in a RegExp.
     *
     * @param {string} s
     * @returns {string}
     */
    function escapeRegExp(s) {
        return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Build a search regex from a free-form query string.
     *
     * Whitespace-separated tokens are joined with `|` so any single token
     * matches. `exact` controls word-boundary behavior:
     *   - "exact":   match the token as a whole word
     *   - "whole":   match a word that contains the token
     *   - "partial": match the token anywhere (default)
     *
     * @param {string} query
     * @param {{exact?: string, caseSensitive?: boolean|string}} [opts]
     * @returns {RegExp|null}
     */
    function buildRegex(query, opts) {
        const options = opts || {};
        const trimmed = String(query == null ? '' : query).trim();
        if (!trimmed) {
            return null;
        }

        const tokens = trimmed.split(/[\s,]+/).map(escapeRegExp).filter(Boolean);
        if (tokens.length === 0) {
            return null;
        }
        let pattern = tokens.join('|');

        switch (options.exact) {
            case 'exact':
                pattern = '\\b(?:' + pattern + ')\\b';
                break;
            case 'whole':
                pattern = '\\b\\w*(?:' + pattern + ')\\w*\\b';
                break;
            default:
                // "partial" or unset: leave pattern as a plain alternation
                break;
        }

        // Accept boolean or stringy "true"/"false" for case sensitivity, since
        // existing callers pass it as a string from PHP.
        const caseSensitive = options.caseSensitive === true
            || options.caseSensitive === 'true';
        return new RegExp(pattern, caseSensitive ? 'g' : 'gi');
    }

    /**
     * Collect text nodes under `root` that are eligible for highlighting.
     * Skips text inside SKIP_TAGS and inside existing highlight wrappers.
     *
     * @param {Element} root
     * @param {string} skipClassName  — class name of existing highlights to skip
     * @returns {Text[]}
     */
    function collectTextNodes(root, skipClassName) {
        const walker = document.createTreeWalker(
            root,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    if (!node.data) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    let parent = node.parentNode;
                    while (parent && parent !== root) {
                        if (parent.nodeType === 1) {
                            if (SKIP_TAGS.has(parent.tagName)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            if (skipClassName && parent.classList
                                && parent.classList.contains(skipClassName)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                        }
                        parent = parent.parentNode;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        const nodes = [];
        let n;
        while ((n = walker.nextNode())) {
            nodes.push(n);
        }
        return nodes;
    }

    /**
     * Find all regex matches in `root`'s text content and wrap each in a new
     * element. Matches that span text-node boundaries are not joined; each
     * text node is searched independently.
     *
     * @param {Element} root
     * @param {RegExp}  regex
     * @param {{className?: string, tagName?: string}} [opts]
     * @returns {Element[]}  the inserted wrapper elements, in document order
     */
    function highlight(root, regex, opts) {
        const options = opts || {};
        const className = options.className || 'hilite';
        const tagName = options.tagName || 'mark';
        if (!root || !regex) {
            return [];
        }

        const textNodes = collectTextNodes(root, className);
        const inserted = [];

        for (let i = 0; i < textNodes.length; i++) {
            const node = textNodes[i];
            const text = node.data;
            const ranges = [];
            regex.lastIndex = 0;
            let m;
            while ((m = regex.exec(text)) !== null) {
                if (m[0].length === 0) {
                    regex.lastIndex++;
                    continue;
                }
                ranges.push({ start: m.index, end: m.index + m[0].length });
            }
            if (ranges.length === 0) {
                continue;
            }

            const parent = node.parentNode;
            if (!parent) {
                continue;
            }

            // Walk the ranges left-to-right, splitting the text node each
            // time and wrapping the middle piece. After each iteration
            // `cursor` points to the remainder following the most recent
            // match.
            let cursor = node;
            let cursorStart = 0;
            for (let j = 0; j < ranges.length; j++) {
                const start = ranges[j].start;
                const end = ranges[j].end;
                const matchNode = cursor.splitText(start - cursorStart);
                const afterNode = matchNode.splitText(end - start);
                const wrapper = document.createElement(tagName);
                wrapper.className = className;
                parent.insertBefore(wrapper, matchNode);
                wrapper.appendChild(matchNode);
                inserted.push(wrapper);
                cursor = afterNode;
                cursorStart = end;
            }
        }

        return inserted;
    }

    /**
     * Remove highlight wrappers inside `root`, restoring the original text.
     *
     * @param {Element} root
     * @param {string}  [className='hilite']
     */
    function unhighlight(root, className) {
        if (!root) {
            return;
        }
        const cls = className || 'hilite';
        const wrappers = root.querySelectorAll('.' + cls);
        for (let i = 0; i < wrappers.length; i++) {
            const wrapper = wrappers[i];
            const parent = wrapper.parentNode;
            if (!parent) {
                continue;
            }
            while (wrapper.firstChild) {
                parent.insertBefore(wrapper.firstChild, wrapper);
            }
            parent.removeChild(wrapper);
        }
        // Coalesce adjacent text nodes left over from splitText() calls.
        if (typeof root.normalize === 'function') {
            root.normalize();
        }
    }

    /**
     * Convenience wrapper that mirrors the legacy doSearch() signature: takes
     * a selector or element plus a free-form query, builds the regex, and
     * applies highlights.
     *
     * @param {string|Element} target
     * @param {string} query
     * @param {{exact?: string, caseSensitive?: boolean|string, className?: string, tagName?: string}} [opts]
     * @returns {Element[]}
     */
    function search(target, query, opts) {
        const options = opts || {};
        const root = typeof target === 'string'
            ? document.querySelector(target)
            : target;
        if (!root) {
            return [];
        }
        const regex = buildRegex(query, options);
        if (!regex) {
            return [];
        }
        return highlight(root, regex, options);
    }

    // Expose as a global namespace, matching the project's existing pattern
    // for library/js/*.js utility modules.
    window.OpenEMRSearchHighlight = {
        search: search,
        highlight: highlight,
        unhighlight: unhighlight,
        buildRegex: buildRegex,
        escapeRegExp: escapeRegExp
    };
})(window);
