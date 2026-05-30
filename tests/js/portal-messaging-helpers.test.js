/**
 * @jest-environment jsdom
 */

/**
 * Tests for the pure helper functions in portal/messaging/js/messages.js and
 * portal/messaging/js/secure_chat.js. These helpers are the security-sensitive
 * pieces of the messaging UI (escaping, sanitizing, URL allowlisting); a
 * regression here would re-introduce XSS or shortcode-based injection.
 *
 * The two modules are IIFEs that don't export. They expose a small test seam
 * at window.__OE_MESSAGES_TEST__ and window.__OE_SECURE_CHAT_TEST__ specifically
 * for unit tests; production code never reads from those.
 *
 * Run with: npm run test:js -- tests/js/portal-messaging-helpers.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

// jsdom doesn't load external scripts referenced in HTML, so we inject the
// libraries the messaging code needs (DOMPurify) and the module source itself
// before reading off the test seam.

const DOMPurify = require('dompurify');
global.window.DOMPurify = DOMPurify(global.window);
global.DOMPurify = global.window.DOMPurify;

// Load the two module sources. Both IIFE on load and attach to window.
function loadModule(relativePath) {
    const src = fs.readFileSync(path.resolve(__dirname, '../../', relativePath), 'utf8');
    new Function('window', 'document', 'DOMPurify', src)(
        global.window,
        global.window.document,
        global.window.DOMPurify
    );
}

// messages.js requires window.OE_MESSAGES_CONFIG and a few DOM elements at
// init time. Stub the config; the DOM elements just need to exist.
global.window.OE_MESSAGES_CONFIG = {
    inboxItems: [],
    userproper: 'Test User',
    isPortal: 1,
    isDashboard: false,
    authrecips: [],
    csrf: 'test-csrf-token',
    strings: {
        forwardedRe: 'Fwd: ',
        confirmOne: 'Archive?',
        confirmAll: 'Archive selected?',
        sendingToSelf: 'Sending to yourself!',
        conversationFrom: 'Conversation from',
        regarding: 'regarding',
        onPrep: 'on',
        archiveTitle: 'Archive this',
        composeReplyTitle: 'Reply',
        composeNewTitle: 'Compose'
    }
};
global.window.OE_SECURE_CHAT_CONFIG = {
    user: 'admin',
    userid: '1',
    isPortal: false,
    noRecipError: 'Pick a recipient',
    clickTitle: 'Click'
};

// Stub jQuery and Audio constructor; the IIFEs don't actually use them at
// load-time but the parser still needs them defined.
global.window.$ = () => ({ on: () => {}, modal: () => {}, summernote: () => {} });
global.$ = global.window.$;
global.window.Audio = function () { return {}; };

loadModule('portal/messaging/js/messages.js');
loadModule('portal/messaging/js/secure_chat.js');

const messagesHelpers = global.window.__OE_MESSAGES_TEST__;
const secureChatHelpers = global.window.__OE_SECURE_CHAT_TEST__;

// ---------------------------------------------------------------------------
// messages.js: escapeHtml — the load-bearing XSS guard for every ${} in
// template literals fed into innerHTML.
// ---------------------------------------------------------------------------
describe('messages.js escapeHtml', () => {
    const { escapeHtml } = messagesHelpers;

    test('escapes script tags', () => {
        expect(escapeHtml('<script>alert(1)</script>')).toBe('&lt;script&gt;alert(1)&lt;/script&gt;');
    });

    test('escapes double quotes so values can\'t break out of double-quoted attributes (CWE-79)', () => {
        // Regression: an earlier textContent-based implementation left " untouched,
        // letting a stored message title like `" autofocus onfocus="alert(1)` break
        // out of data-mtitle="${escapeHtml(sel.title)}" and inject event handlers.
        expect(escapeHtml('" autofocus onfocus="alert(1)'))
            .toBe('&quot; autofocus onfocus=&quot;alert(1)');
    });

    test('escapes single quotes so values can\'t break out of single-quoted attributes', () => {
        expect(escapeHtml("' onclick='alert(1)"))
            .toBe('&#39; onclick=&#39;alert(1)');
    });

    test('escapes ampersands', () => {
        expect(escapeHtml('A & B')).toBe('A &amp; B');
    });

    test('returns empty string for null/undefined', () => {
        expect(escapeHtml(null)).toBe('');
        expect(escapeHtml(undefined)).toBe('');
    });

    test('coerces non-strings via String()', () => {
        expect(escapeHtml(42)).toBe('42');
    });
});

// ---------------------------------------------------------------------------
// messages.js: escapeHtml in an actual attribute-context flow. Regression
// guard for the Aisle finding (CWE-79, vuln_c9c47f38) — verifies that a
// stored message title with attribute-breaking quotes cannot inject
// attributes when interpolated into a double-quoted attribute.
// ---------------------------------------------------------------------------
describe('messages.js escapeHtml in attribute context', () => {
    const { escapeHtml } = messagesHelpers;

    test('payload that broke out before now stays inside the attribute', () => {
        const malicious = '" autofocus onfocus="alert(1)';
        const html = `<button data-title="${escapeHtml(malicious)}"></button>`;

        // Parse the rendered HTML and inspect the actual DOM. No matter what
        // the source text looks like, the parser must see a single button with
        // exactly one attribute and no autofocus/onfocus injected.
        const div = document.createElement('div');
        div.innerHTML = html;
        const btn = div.querySelector('button');

        expect(btn).not.toBeNull();
        expect(btn.hasAttribute('autofocus')).toBe(false);
        expect(btn.hasAttribute('onfocus')).toBe(false);
        // The malicious string must survive as the literal attribute value.
        expect(btn.getAttribute('data-title')).toBe(malicious);
    });
});

// ---------------------------------------------------------------------------
// messages.js: htmlToText — used to produce the inbox-row body preview.
// Must strip HTML cleanly and not double-escape (regression: an earlier
// implementation returned jsText() which entity-escaped, then the caller
// escapeHtml'd again, producing &amp;lt; in previews).
// ---------------------------------------------------------------------------
describe('messages.js htmlToText', () => {
    const { htmlToText } = messagesHelpers;

    test('strips tags and returns plain text', () => {
        expect(htmlToText('<p>Hello <b>world</b></p>')).toBe('Hello world');
    });

    test('strips anchor and image tags (FORBID_TAGS)', () => {
        expect(htmlToText('See <a href="http://x">link</a> and <img alt="x">')).toBe('See link and ');
    });

    test('removes script payloads entirely', () => {
        // DOMPurify drops <script> elements; the textContent of the surviving
        // tree contains no executable trace.
        const result = htmlToText('<p>Safe</p><script>alert(1)</script>');
        expect(result).toBe('Safe');
        expect(result).not.toContain('alert');
    });

    test('returns plain text characters not entity-escaped', () => {
        // Regression: prior version returned jsText() which converted < & > to
        // entities; the calling code already escapeHtml'd before innerHTML.
        expect(htmlToText('A &amp; B')).toBe('A & B');
    });
});

// ---------------------------------------------------------------------------
// messages.js: limitTo — body-preview truncation.
// ---------------------------------------------------------------------------
describe('messages.js limitTo', () => {
    const { limitTo } = messagesHelpers;

    test('truncates strings longer than limit', () => {
        expect(limitTo('abcdefghij', 5)).toBe('abcde');
    });

    test('returns string unchanged when shorter than limit', () => {
        expect(limitTo('abc', 5)).toBe('abc');
    });

    test('handles empty / null inputs', () => {
        expect(limitTo('', 5)).toBe('');
        expect(limitTo(null, 5)).toBe('');
        expect(limitTo(undefined, 5)).toBe('');
    });
});

// ---------------------------------------------------------------------------
// messages.js: renderMessageBody — the only sink that passes user-supplied
// HTML straight into innerHTML. Must be DOMPurify-sanitized and must forbid
// <a> and <img> per OpenEMR policy.
// ---------------------------------------------------------------------------
describe('messages.js renderMessageBody', () => {
    const { renderMessageBody } = messagesHelpers;

    test('removes script tags entirely', () => {
        expect(renderMessageBody('<p>ok</p><script>alert(1)</script>')).not.toContain('<script');
        expect(renderMessageBody('<p>ok</p><script>alert(1)</script>')).not.toContain('alert');
    });

    test('strips on* event handlers', () => {
        expect(renderMessageBody('<div onclick="hack()">x</div>')).not.toContain('onclick');
    });

    test('forbids <a> and <img> tags', () => {
        expect(renderMessageBody('<a href="x">link</a>')).not.toContain('<a ');
        expect(renderMessageBody('<img src="x">')).not.toContain('<img');
    });

    test('preserves safe formatting tags', () => {
        const out = renderMessageBody('<p><b>bold</b> and <i>italic</i></p>');
        expect(out).toContain('<b>');
        expect(out).toContain('<i>');
        expect(out).toContain('<p>');
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: escapeHtml — same purpose as messages.js's version, but
// they're independent copies in different IIFEs.
// ---------------------------------------------------------------------------
describe('secure_chat.js escapeHtml', () => {
    const { escapeHtml } = secureChatHelpers;

    test('escapes script tags', () => {
        expect(escapeHtml('<script>alert(1)</script>')).toBe('&lt;script&gt;alert(1)&lt;/script&gt;');
    });

    test('escapes double quotes so values can\'t break out of double-quoted attributes', () => {
        expect(escapeHtml('" autofocus onfocus="alert(1)'))
            .toBe('&quot; autofocus onfocus=&quot;alert(1)');
    });

    test('escapes single quotes so values can\'t break out of single-quoted attributes', () => {
        expect(escapeHtml("' onclick='alert(1)"))
            .toBe('&#39; onclick=&#39;alert(1)');
    });

    test('escapes ampersands', () => {
        expect(escapeHtml('A & B')).toBe('A &amp; B');
    });

    test('null/undefined become empty string', () => {
        expect(escapeHtml(null)).toBe('');
        expect(escapeHtml(undefined)).toBe('');
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: safeUrl — defense-in-depth before [img]/[url] shortcode
// expansion. Must reject every non-http(s) scheme so `[url]javascript:...`
// can never resolve to a working javascript: link.
// ---------------------------------------------------------------------------
describe('secure_chat.js safeUrl', () => {
    const { safeUrl } = secureChatHelpers;

    test('allows http: URLs', () => {
        expect(safeUrl('http://example.com/x')).toMatch(/^http:/);
    });

    test('allows https: URLs', () => {
        expect(safeUrl('https://example.com/x')).toMatch(/^https:/);
    });

    test('blocks javascript: URLs', () => {
        expect(safeUrl('javascript:alert(1)')).toBe('#');
    });

    test('blocks data: URLs', () => {
        expect(safeUrl('data:text/html,<script>alert(1)</script>')).toBe('#');
    });

    test('blocks file: URLs', () => {
        expect(safeUrl('file:///etc/passwd')).toBe('#');
    });

    test('blocks vbscript: URLs', () => {
        expect(safeUrl('vbscript:msgbox(1)')).toBe('#');
    });

    test('blocks malformed input', () => {
        expect(safeUrl('not a url at all')).toMatch(/^(#|http)/);
        // Whitespace / undefined / null all get handled without throwing.
        expect(() => safeUrl(undefined)).not.toThrow();
        expect(() => safeUrl(null)).not.toThrow();
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: replaceShortcodes — expands [img]/[url] shortcodes that
// chat messages contain. The expansion writes the URL into a single-quoted
// HTML attribute, so anything that survives safeUrl() is a candidate XSS.
// ---------------------------------------------------------------------------
describe('secure_chat.js replaceShortcodes', () => {
    const { replaceShortcodes } = secureChatHelpers;

    test('expands [url] with a safe http URL', () => {
        const out = replaceShortcodes('See [url]https://example.com[/url]');
        expect(out).toContain('<a href=');
        expect(out).toContain('https://example.com');
    });

    test('expands [img] with a safe http URL', () => {
        const out = replaceShortcodes('Pic [img]https://example.com/x.png[/img]');
        expect(out).toContain('<img');
        expect(out).toContain('https://example.com/x.png');
    });

    test('neutralises [url]javascript:...[/url]', () => {
        const out = replaceShortcodes('Click [url]javascript:alert(1)[/url]');
        // safeUrl turns the dangerous href into "#"; the visible link text
        // still shows the original URL (entity-escaped) so users can see what
        // was intended without it being executable.
        expect(out).toContain("href='#'");
        // The text appears, but in the visible-text position only — never as
        // an actual href that would execute.
        expect(out).not.toMatch(/href=['"]?javascript:/);
    });

    test('neutralises [img] with a javascript: src', () => {
        const out = replaceShortcodes('[img]javascript:alert(1)[/img]');
        expect(out).toContain("src='#'");
        expect(out).not.toContain('javascript:alert');
    });

    test('expands multiple shortcodes in one message', () => {
        const out = replaceShortcodes('[url]http://a/[/url] and [url]http://b/[/url]');
        const matches = out.match(/<a /g);
        expect(matches && matches.length).toBe(2);
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: sanitizeHtml — DOMPurify wrapper used at the message-render
// sink. Should strip every active-content vector even when given malicious
// input that already slipped past replaceShortcodes.
// ---------------------------------------------------------------------------
describe('secure_chat.js sanitizeHtml', () => {
    const { sanitizeHtml } = secureChatHelpers;

    test('removes <script> elements', () => {
        expect(sanitizeHtml('<p>ok</p><script>alert(1)</script>')).not.toContain('<script');
    });

    test('strips on* event handlers', () => {
        expect(sanitizeHtml('<div onclick="hack()">x</div>')).not.toContain('onclick');
    });

    test('strips javascript: hrefs', () => {
        const out = sanitizeHtml('<a href="javascript:alert(1)">x</a>');
        expect(out).not.toContain('javascript:');
    });

    test('preserves safe formatting tags', () => {
        const out = sanitizeHtml('<p><b>bold</b></p>');
        expect(out).toContain('<b>');
        expect(out).toContain('<p>');
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: unique — collection dedup used for the recipient and
// onlines lists.
// ---------------------------------------------------------------------------
describe('secure_chat.js unique', () => {
    const { unique } = secureChatHelpers;

    test('dedups by the given key, preserving first-seen order', () => {
        const input = [
            { username: 'a', extra: 1 },
            { username: 'b', extra: 2 },
            { username: 'a', extra: 3 }
        ];
        expect(unique(input, 'username')).toEqual([
            { username: 'a', extra: 1 },
            { username: 'b', extra: 2 }
        ]);
    });

    test('returns empty array for empty input', () => {
        expect(unique([], 'username')).toEqual([]);
    });
});

// ---------------------------------------------------------------------------
// messages.js: paginate — chunks the filtered list into fixed-size pages.
// Off-by-one errors here would drop or duplicate inbox rows at page edges.
// ---------------------------------------------------------------------------
describe('messages.js paginate', () => {
    const { paginate } = messagesHelpers;
    const seq = n => Array.from({ length: n }, (_, i) => ({ id: i + 1 }));

    test('returns no pages for an empty list', () => {
        expect(paginate([], 20)).toEqual([]);
    });

    test('keeps a partial final page intact', () => {
        const pages = paginate(seq(25), 20);
        expect(pages).toHaveLength(2);
        expect(pages[0]).toHaveLength(20);
        expect(pages[1]).toHaveLength(5);
    });

    test('does not create an empty trailing page on an exact multiple', () => {
        const pages = paginate(seq(40), 20);
        expect(pages).toHaveLength(2);
        expect(pages[1]).toHaveLength(20);
    });

    test('fits a list smaller than one page on a single page', () => {
        const pages = paginate(seq(3), 20);
        expect(pages).toHaveLength(1);
        expect(pages[0].map(i => i.id)).toEqual([1, 2, 3]);
    });

    test('preserves item order across the page boundary', () => {
        const pages = paginate(seq(21), 20);
        expect(pages[0][19].id).toBe(20);
        expect(pages[1][0].id).toBe(21);
    });
});

// ---------------------------------------------------------------------------
// messages.js: groupChain — gathers a conversation thread by mail_chain.
// Threading bugs would either merge unrelated conversations or hide replies.
// ---------------------------------------------------------------------------
describe('messages.js groupChain', () => {
    const { groupChain } = messagesHelpers;
    const items = [
        { id: 1, mail_chain: 10 },
        { id: 2, mail_chain: 11 },
        { id: 3, mail_chain: 10 }
    ];

    test('returns only the messages sharing the chain id', () => {
        expect(groupChain(items, 10).map(i => i.id)).toEqual([1, 3]);
    });

    test('matches a numeric chain id stored as a string (loose equality)', () => {
        const stringChain = [{ id: 1, mail_chain: '10' }, { id: 2, mail_chain: '11' }];
        expect(groupChain(stringChain, 10).map(i => i.id)).toEqual([1]);
    });

    test('falls back to the full list for a non-numeric chain id', () => {
        const all = groupChain(items, NaN);
        expect(all.map(i => i.id)).toEqual([1, 2, 3]);
    });

    test('does not return the source array reference on the fallback path', () => {
        expect(groupChain(items, NaN)).not.toBe(items);
    });
});

// ---------------------------------------------------------------------------
// secure_chat.js: lastIdOf — used to decide whether a poll brought a new
// message. A wrong result here re-triggers (or silences) the new-message beep.
// ---------------------------------------------------------------------------
describe('secure_chat.js lastIdOf', () => {
    const { lastIdOf } = secureChatHelpers;

    test('returns the id of the last message', () => {
        expect(lastIdOf([{ id: 1 }, { id: 2 }, { id: 7 }])).toBe(7);
    });

    test('returns null for an empty list', () => {
        expect(lastIdOf([])).toBeNull();
    });
});
