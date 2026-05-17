/**
 * @jest-environment jsdom
 */

/**
 * Tests for library/js/searchHighlight.js
 *
 * Run with: npm test -- tests/js/searchHighlight.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

// Load the module once — it self-executes and attaches to window.
const src = fs.readFileSync(
    path.resolve(__dirname, '../../library/js/searchHighlight.js'),
    'utf8'
);
// eslint-disable-next-line no-new-func
new Function('window', src)(global.window);

const { escapeRegExp, buildRegex, highlight, unhighlight, search } =
    global.window.OpenEMRSearchHighlight;

// ---------------------------------------------------------------------------
// escapeRegExp
// ---------------------------------------------------------------------------
describe('escapeRegExp', () => {

    test('escapes regex metacharacters', () => {
        expect(escapeRegExp('a.b*c?')).toBe('a\\.b\\*c\\?');
    });

    test('returns plain alphanumeric strings unchanged', () => {
        expect(escapeRegExp('glucose')).toBe('glucose');
    });
});

// ---------------------------------------------------------------------------
// buildRegex — token splitting
// ---------------------------------------------------------------------------
describe('buildRegex — token splitting', () => {

    test('returns null for empty query', () => {
        expect(buildRegex('')).toBeNull();
        expect(buildRegex('   ')).toBeNull();
    });

    test('builds a case-insensitive regex by default', () => {
        const re = buildRegex('glucose');
        expect(re.flags).toContain('i');
    });

    test('builds a case-sensitive regex when requested', () => {
        const re = buildRegex('Glucose', { caseSensitive: true });
        expect(re.flags).not.toContain('i');
    });

    test('accepts string "true" for caseSensitive (legacy PHP callers)', () => {
        const re = buildRegex('Glucose', { caseSensitive: 'true' });
        expect(re.flags).not.toContain('i');
    });

    test('splits whitespace-separated tokens into an alternation', () => {
        const re = buildRegex('glucose cholesterol');
        expect(re.source).toBe('glucose|cholesterol');
    });

    test('splits comma-separated tokens (matches legacy plugin behaviour)', () => {
        const re = buildRegex('glucose,cholesterol');
        expect(re.source).toBe('glucose|cholesterol');
    });

    test('splits mixed whitespace-and-comma tokens', () => {
        const re = buildRegex('glucose, cholesterol');
        expect(re.source).toBe('glucose|cholesterol');
    });

    test('ignores leading/trailing commas', () => {
        const re = buildRegex(',glucose,');
        expect(re.source).toBe('glucose');
    });

    test('applies word-boundary anchors for exact mode', () => {
        const re = buildRegex('glucose', { exact: 'exact' });
        expect(re.source).toBe('\\b(?:glucose)\\b');
    });

    test('applies whole-word pattern for whole mode', () => {
        const re = buildRegex('glu', { exact: 'whole' });
        expect(re.source).toBe('\\b\\w*(?:glu)\\w*\\b');
    });
});

// ---------------------------------------------------------------------------
// highlight / unhighlight
// ---------------------------------------------------------------------------
describe('highlight and unhighlight', () => {

    function makeRoot(html) {
        const div = document.createElement('div');
        div.innerHTML = html;
        document.body.appendChild(div);
        return div;
    }

    afterEach(() => {
        document.body.innerHTML = '';
    });

    test('wraps a single match in a <mark class="hilite">', () => {
        const root = makeRoot('<p>The glucose level is normal.</p>');
        const regex = buildRegex('glucose');
        const inserted = highlight(root, regex);

        expect(inserted).toHaveLength(1);
        expect(inserted[0].tagName).toBe('MARK');
        expect(inserted[0].className).toBe('hilite');
        expect(inserted[0].textContent).toBe('glucose');
    });

    test('wraps multiple matches', () => {
        const root = makeRoot('<p>glucose and more glucose here</p>');
        const regex = buildRegex('glucose');
        const inserted = highlight(root, regex);

        expect(inserted).toHaveLength(2);
    });

    test('multi-token query wraps each token separately', () => {
        const root = makeRoot('<p>glucose and cholesterol levels</p>');
        const regex = buildRegex('glucose cholesterol');
        const inserted = highlight(root, regex);

        expect(inserted).toHaveLength(2);
        const texts = inserted.map((el) => el.textContent).sort();
        expect(texts).toEqual(['cholesterol', 'glucose']);
    });

    test('comma-separated query wraps each token separately', () => {
        const root = makeRoot('<p>glucose and cholesterol levels</p>');
        const regex = buildRegex('glucose,cholesterol');
        const inserted = highlight(root, regex);

        expect(inserted).toHaveLength(2);
    });

    test('supports custom tagName and className options', () => {
        const root = makeRoot('<p>hello world</p>');
        const regex = buildRegex('hello');
        const inserted = highlight(root, regex, { tagName: 'span', className: 'found' });

        expect(inserted[0].tagName).toBe('SPAN');
        expect(inserted[0].className).toBe('found');
    });

    test('does not highlight text inside SKIP_TAGS (SCRIPT, STYLE, TEXTAREA, NOSCRIPT)', () => {
        const root = makeRoot(
            '<script>var glucose = 1;</script>' +
            '<style>.glucose {}</style>' +
            '<textarea>glucose</textarea>' +
            '<noscript>glucose</noscript>' +
            '<p>glucose</p>'
        );
        const regex = buildRegex('glucose');
        const inserted = highlight(root, regex);

        // Only the <p> text should be highlighted.
        expect(inserted).toHaveLength(1);
        expect(inserted[0].closest('p')).not.toBeNull();
    });

    test('does not re-highlight already-highlighted text', () => {
        const root = makeRoot('<p>glucose level</p>');
        const regex = buildRegex('glucose');
        highlight(root, regex);
        const second = highlight(root, regex);

        // Second call should find nothing new because the text node is
        // already inside a .hilite wrapper, which is skipped.
        expect(second).toHaveLength(0);
    });

    test('unhighlight removes wrappers and restores text', () => {
        const root = makeRoot('<p>glucose level</p>');
        const regex = buildRegex('glucose');
        highlight(root, regex);

        expect(root.querySelectorAll('.hilite')).toHaveLength(1);

        unhighlight(root, 'hilite');

        expect(root.querySelectorAll('.hilite')).toHaveLength(0);
        expect(root.textContent).toBe('glucose level');
    });

    test('unhighlight coalesces split text nodes via normalize()', () => {
        const root = makeRoot('<p>glucose level</p>');
        const regex = buildRegex('glucose');
        highlight(root, regex);
        unhighlight(root, 'hilite');

        const p = root.querySelector('p');
        expect(p.childNodes).toHaveLength(1);
        expect(p.childNodes[0].nodeType).toBe(Node.TEXT_NODE);
    });

    test('returns empty array when root is null', () => {
        const regex = buildRegex('glucose');
        expect(highlight(null, regex)).toEqual([]);
    });

    test('returns empty array when regex is null', () => {
        const root = makeRoot('<p>glucose</p>');
        expect(highlight(root, null)).toEqual([]);
    });

    test('zero-length match guard prevents infinite loop', () => {
        // The guard increments lastIndex when a zero-length match is found,
        // preventing an infinite loop on patterns like /a*/g.
        const root = makeRoot('<p>abc</p>');
        const zeroLengthRegex = /a*/g;
        expect(() => highlight(root, zeroLengthRegex)).not.toThrow();
    });
});

// ---------------------------------------------------------------------------
// search (convenience wrapper)
// ---------------------------------------------------------------------------
describe('search', () => {

    afterEach(() => {
        document.body.innerHTML = '';
    });

    test('accepts a CSS selector string as target', () => {
        const div = document.createElement('div');
        div.id = 'test-container';
        div.innerHTML = '<p>glucose level</p>';
        document.body.appendChild(div);

        const inserted = search('#test-container', 'glucose');
        expect(inserted).toHaveLength(1);
    });

    test('accepts a DOM element as target', () => {
        const div = document.createElement('div');
        div.innerHTML = '<p>glucose level</p>';
        document.body.appendChild(div);

        const inserted = search(div, 'glucose');
        expect(inserted).toHaveLength(1);
    });

    test('returns empty array for non-existent selector', () => {
        expect(search('#does-not-exist', 'glucose')).toEqual([]);
    });

    test('returns empty array for empty query', () => {
        const div = document.createElement('div');
        div.innerHTML = '<p>glucose</p>';
        document.body.appendChild(div);

        expect(search(div, '')).toEqual([]);
    });

    test('passes options through to buildRegex and highlight', () => {
        const div = document.createElement('div');
        div.innerHTML = '<p>Glucose level</p>';
        document.body.appendChild(div);

        // Case-sensitive: 'glucose' should NOT match 'Glucose'
        expect(search(div, 'glucose', { caseSensitive: true })).toHaveLength(0);
        // Case-insensitive (default): should match
        expect(search(div, 'glucose')).toHaveLength(1);
    });
});
