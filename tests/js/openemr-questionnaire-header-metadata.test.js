/**
 * @jest-environment jsdom
 */

/**
 * Form header (title with copyright popup, description as GitHub Flavored
 * Markdown), help / legal popup buttons and flyover text per the FHIR
 * questionnaire-item-control codes, and QuestionnaireResponse metadata (questionnaire
 * canonical, meta.profile, OpenEMR version tag) for the native runtime
 * (issue #14736).
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-header-metadata.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const runtimeSource = fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js'),
    'utf8'
);

function loadRuntime() {
    new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', runtimeSource)(
        global.window,
        global.document,
        global.window.HTMLElement,
        global.window.HTMLInputElement,
        global.window.CustomEvent
    );
    return global.window.OpenEMRQuestionnaire;
}

let runtimeApi = loadRuntime();

const itemControl = (code) => ({
    url: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl',
    valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code }] },
});
const HELP = itemControl('help');
const LEGAL = itemControl('legal');
const FLYOVER = itemControl('flyover');

function mount(questionnaire, { questionnaireResponse = null, options = {} } = {}) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: [], ...questionnaire },
        questionnaireResponse,
        container,
        options,
    });
    return { container, runtime };
}

function markdown(source) {
    const host = document.createElement('div');
    host.appendChild(runtimeApi.renderMarkdown(source, document));
    return host;
}

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('form header', () => {
    test('copyright is a button on the title that opens a markdown popup; description is inline', () => {
        const { container } = mount({
            title: 'PHQ-9',
            copyright: 'Copyright **Pfizer**. See [terms](https://example.org/terms).',
            description: 'Patient *health* questionnaire.',
            item: [{ linkId: 'q1', type: 'string', text: 'Q1' }],
        });
        const header = container.querySelector('.oe-questionnaire-header');
        expect(container.firstElementChild).toBe(header);
        const title = header.querySelector('h4.oe-questionnaire-title');
        expect(title.firstChild.textContent).toBe('PHQ-9');
        const button = title.querySelector('.oe-questionnaire-copyright-button');
        expect(button.textContent).toBe('\u00a9');
        expect(button.getAttribute('aria-haspopup')).toBe('dialog');
        expect(button.getAttribute('aria-expanded')).toBe('false');
        // copyright text is not rendered inline
        expect(header.textContent).not.toContain('Pfizer');
        expect(header.querySelector('.oe-questionnaire-description em').textContent).toBe('health');

        button.click();
        const popup = container.querySelector('.oe-questionnaire-popup.oe-questionnaire-copyright');
        expect(popup.getAttribute('role')).toBe('dialog');
        expect(popup.getAttribute('aria-label')).toBe('Copyright');
        expect(button.getAttribute('aria-expanded')).toBe('true');
        expect(button.getAttribute('aria-controls')).toBe(popup.id);
        expect(popup.querySelector('strong').textContent).toBe('Pfizer');
        const link = popup.querySelector('a');
        expect(link.getAttribute('href')).toBe('https://example.org/terms');
        expect(link.getAttribute('rel')).toBe('noopener noreferrer');
        expect(document.activeElement).toBe(popup);
    });

    test('title falls back to name; options can hide each part', () => {
        let { container } = mount({ name: 'phq9', copyright: 'c' });
        expect(container.querySelector('.oe-questionnaire-title').firstChild.textContent).toBe('phq9');

        // title hidden: the copyright button still renders on its own row
        ({ container } = mount({ title: 'T', copyright: 'c', description: 'd' }, { options: { showTitle: false } }));
        expect(container.querySelector('h4.oe-questionnaire-title')).toBeNull();
        expect(container.querySelector('.oe-questionnaire-copyright-button')).not.toBeNull();

        ({ container } = mount({ title: 'T', copyright: 'c', description: 'd' }, { options: { showCopyright: false, showDescription: false } }));
        expect(container.querySelector('.oe-questionnaire-copyright-button')).toBeNull();
        expect(container.querySelector('.oe-questionnaire-description')).toBeNull();
    });

    test('top-level help and legal display items become title buttons', () => {
        const { container } = mount({
            title: 'Form',
            item: [
                { linkId: 'root-help', type: 'display', text: 'Form help', extension: [HELP] },
                { linkId: 'root-legal', type: 'display', text: 'Form legal', extension: [LEGAL] },
                { linkId: 'q', type: 'string', text: 'Q' },
            ],
        });
        const title = container.querySelector('.oe-questionnaire-title');
        expect(title.querySelector('.oe-questionnaire-help-button')).not.toBeNull();
        expect(title.querySelector('.oe-questionnaire-legal-button')).not.toBeNull();
        expect(container.querySelector('[data-link-id="root-help"]').classList.contains('d-none')).toBe(true);
        expect(container.querySelector('[data-link-id="root-legal"]').classList.contains('d-none')).toBe(true);
    });

    test('no header is rendered when there is nothing to show', () => {
        const { container } = mount({ item: [{ linkId: 'q1', type: 'string', text: 'Q1' }] });
        expect(container.querySelector('.oe-questionnaire-header')).toBeNull();
    });
});

describe('markdown rendering is safe', () => {
    test('raw HTML is shown as text, never as elements', () => {
        const host = markdown('<script>alert(1)</script> and <img src=x onerror=alert(2)> <b>bold</b>');
        expect(host.querySelector('script, img, b')).toBeNull();
        expect(host.textContent).toContain('<script>alert(1)</script>');
        expect(host.textContent).toContain('<img src=x onerror=alert(2)>');
    });

    test('unsafe link schemes do not produce anchors', () => {
        for (const destination of ['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'java&#9;script:alert(1)', 'data:text/html,x', 'vbscript:x', '/relative/path']) {
            const host = markdown('[click](' + destination + ')');
            expect(host.querySelector('a')).toBeNull();
            expect(host.textContent).toBe('click');
        }
        // a raw tab breaks the destination, so it is not a link at all
        expect(markdown('[click](java\tscript:alert(1))').querySelector('a')).toBeNull();
        const autolink = markdown('<javascript:alert(1)>');
        expect(autolink.querySelector('a')).toBeNull();
    });

    test('images become links to their source and never load', () => {
        const host = markdown('![LOINC logo](https://loinc.org/logo.png "Logo")');
        expect(host.querySelector('img')).toBeNull();
        const anchor = host.querySelector('a');
        expect(anchor.getAttribute('href')).toBe('https://loinc.org/logo.png');
        expect(anchor.textContent).toBe('LOINC logo');
    });

    test('entities decode and backslash escapes are literal', () => {
        const host = markdown('&copy; 2024 &amp; &#169; \\*not emphasis\\*');
        expect(host.textContent).toBe('© 2024 & © *not emphasis*');
        expect(host.querySelector('em')).toBeNull();
    });
});

describe('GitHub Flavored Markdown coverage', () => {
    test('inline formatting', () => {
        const host = markdown('**b** *i* ***bi*** ~~gone~~ `x < y` __u__ snake_case_name');
        expect(host.querySelector('p > strong').textContent).toBe('b');
        expect(host.querySelector('p > em').textContent).toBe('i');
        expect(host.querySelector('strong > em').textContent).toBe('bi');
        expect(host.querySelector('del').textContent).toBe('gone');
        expect(host.querySelector('code').textContent).toBe('x < y');
        expect([...host.querySelectorAll('strong')].map((node) => node.textContent)).toContain('u');
        // intraword underscores are not emphasis
        expect(host.textContent).toContain('snake_case_name');
    });

    test('bare URL, www and email autolinks', () => {
        const host = markdown('Terms at https://loinc.org/terms-of-use. Also www.example.org, or mail help@example.org.');
        const hrefs = [...host.querySelectorAll('a')].map((anchor) => anchor.getAttribute('href'));
        expect(hrefs).toEqual(['https://loinc.org/terms-of-use', 'http://www.example.org', 'mailto:help@example.org']);
    });

    test('block structure: headings, lists, tasks, quote, rule, code, breaks', () => {
        const host = markdown([
            '# Title',
            'Setext',
            '---',
            '',
            '- one',
            '- two',
            '  - nested',
            '',
            '3. three',
            '4. four',
            '',
            '- [x] done',
            '- [ ] todo',
            '',
            '> quoted *text*',
            '',
            '***',
            '',
            '```js',
            'const a = "<b>";',
            '```',
            '',
            'line one  ',
            'line two',
        ].join('\n'));
        expect(host.querySelector('h1').textContent).toBe('Title');
        expect(host.querySelector('h2').textContent).toBe('Setext');
        const bullet = host.querySelector('ul');
        expect(bullet.children).toHaveLength(2);
        expect(bullet.querySelector('li ul > li').textContent).toBe('nested');
        expect(bullet.children[0].firstChild.textContent).toBe('one');
        const ordered = host.querySelector('ol');
        expect(ordered.getAttribute('start')).toBe('3');
        const boxes = host.querySelectorAll('li.oe-questionnaire-task-item input[type="checkbox"]');
        expect(boxes).toHaveLength(2);
        expect(boxes[0].checked).toBe(true);
        expect(boxes[0].disabled).toBe(true);
        expect(boxes[1].checked).toBe(false);
        expect(host.querySelector('blockquote em').textContent).toBe('text');
        expect(host.querySelector('hr')).not.toBeNull();
        const code = host.querySelector('pre > code');
        expect(code.textContent).toBe('const a = "<b>";');
        expect(code.className).toBe('language-js');
        expect(host.querySelector('p br')).not.toBeNull();
    });

    test('tables with alignment', () => {
        const host = markdown(['| Score | Meaning |', '|:-----:|--------:|', '| 0 | None |', '| 1 \\| 2 | Some |'].join('\n'));
        const table = host.querySelector('table');
        expect(table).not.toBeNull();
        expect([...table.querySelectorAll('th')].map((cell) => cell.textContent)).toEqual(['Score', 'Meaning']);
        expect(table.querySelector('th').className).toBe('text-center');
        expect(table.querySelectorAll('th')[1].className).toBe('text-right');
        const rows = table.querySelectorAll('tbody tr');
        expect(rows).toHaveLength(2);
        expect(rows[1].querySelector('td').textContent).toBe('1 | 2');
    });
});

describe('group headers', () => {
    // bban160's TestQuestionnaire3 on openemr/openemr#14744: "text": "" is a group with no heading.
    test('a group whose text is explicitly empty has no header, only its body', () => {
        const { container } = mount({
            item: [{ linkId: 'temporalQuestions', type: 'group', text: '', item: [{ linkId: 'dateQuestion', type: 'date', text: 'Date' }] }],
        });
        const group = container.querySelector('[data-link-id="temporalQuestions"]');
        expect(group.querySelector(':scope > .card-header')).toBeNull();
        expect(group.querySelector(':scope > .card-body [data-link-id="dateQuestion"]')).not.toBeNull();
        expect(group.textContent).not.toContain('temporalQuestions');
    });

    test('a group with no text at all still shows its linkId as the header', () => {
        const { container } = mount({ item: [{ linkId: 'g', type: 'group', item: [{ linkId: 'q', type: 'string', text: 'Q' }] }] });
        expect(container.querySelector('[data-link-id="g"] > .card-header h5').textContent).toBe('g');
    });

    test('an untitled group keeps its header when it carries help, with no title text', () => {
        const { container } = mount({
            item: [{ linkId: 'g', type: 'group', text: '', item: [
                { linkId: 'g-help', type: 'display', text: 'Group help', extension: [HELP] },
                { linkId: 'q', type: 'string', text: 'Q' },
            ] }],
        });
        const heading = container.querySelector('[data-link-id="g"] > .card-header h5');
        expect(heading.querySelector('.oe-questionnaire-help-button')).not.toBeNull();
        expect(heading.textContent).not.toContain('g');
    });
});

describe('help, legal and flyover display controls (questionnaire-item-control)', () => {
    test('group help button sits inside the group heading after its text', () => {
        const { container } = mount({
            item: [{
                linkId: 'g', type: 'group', text: 'Group heading',
                item: [
                    { linkId: 'g-help', type: 'display', text: 'Group help', extension: [HELP] },
                    { linkId: 'q', type: 'string', text: 'Q' },
                ],
            }],
        });
        const heading = container.querySelector('[data-link-id="g"] > .card-header h5');
        const button = heading.querySelector('.oe-questionnaire-help-button');
        expect(heading.firstChild.nodeType).toBe(Node.TEXT_NODE);
        expect(heading.firstChild.textContent).toBe('Group heading');
        expect(heading.lastChild).toBe(button);
        expect(button.getAttribute('aria-label')).toBe('Help');
        // help text is not in the page until the button is pushed
        expect(container.textContent).not.toContain('Group help');
        button.click();
        expect(container.querySelector('.oe-questionnaire-popup.oe-questionnaire-help').textContent).toContain('Group help');
    });

    test('repeating group help button sits inside its heading and is not repeated per instance', () => {
        const { container } = mount({
            item: [{
                linkId: 'r', type: 'group', text: 'Repeat', repeats: true,
                item: [
                    { linkId: 'r-help', type: 'display', text: 'Repeat help', extension: [HELP] },
                    { linkId: 'v', type: 'string', text: 'V' },
                ],
            }],
        });
        expect(container.querySelector('[data-link-id="r"] > .card-header h5 .oe-questionnaire-help-button')).not.toBeNull();
        expect(container.querySelector('.oe-questionnaire-group-instance [data-link-id="r-help"]')).toBeNull();
    });

    test('question help and legal buttons follow the label text and open popups', () => {
        const { container } = mount({
            item: [{
                linkId: 'q', type: 'string', text: 'Question text', required: true,
                item: [
                    { linkId: 'q-help', type: 'display', text: 'Question help', extension: [HELP] },
                    { linkId: 'q-legal', type: 'display', text: 'Question legal', extension: [LEGAL] },
                ],
            }],
        });
        const row = container.querySelector('[data-link-id="q"] .oe-questionnaire-label-row');
        const label = row.querySelector('label');
        expect(label.classList.contains('d-inline')).toBe(true);
        const help = row.querySelector('.oe-questionnaire-help-button');
        const legal = row.querySelector('.oe-questionnaire-legal-button');
        expect(label.nextElementSibling).toBe(help);
        expect(help.nextElementSibling).toBe(legal);
        expect(legal.getAttribute('aria-label')).toBe('Legal notice');
        // neither display child renders as its own item
        expect(container.querySelector('[data-link-id="q-help"]')).toBeNull();
        expect(container.querySelector('[data-link-id="q-legal"]')).toBeNull();

        help.click();
        expect(container.querySelectorAll('.oe-questionnaire-popup')).toHaveLength(1);
        expect(container.querySelector('.oe-questionnaire-popup').textContent).toContain('Question help');
        // opening another closes the first
        legal.click();
        expect(container.querySelectorAll('.oe-questionnaire-popup')).toHaveLength(1);
        expect(container.querySelector('.oe-questionnaire-popup.oe-questionnaire-legal').textContent).toContain('Question legal');
        expect(help.getAttribute('aria-expanded')).toBe('false');
        // pushing the same button again closes it
        legal.click();
        expect(container.querySelector('.oe-questionnaire-popup')).toBeNull();
    });

    test('popups close on Escape (focus returns), outside click, and the close button', () => {
        const { container } = mount({
            item: [{
                linkId: 'q', type: 'string', text: 'Q',
                item: [{ linkId: 'q-help', type: 'display', text: 'Help', extension: [HELP] }],
            }],
        });
        const button = container.querySelector('.oe-questionnaire-help-button');
        button.click();
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        expect(container.querySelector('.oe-questionnaire-popup')).toBeNull();
        expect(document.activeElement).toBe(button);
        expect(button.getAttribute('aria-expanded')).toBe('false');

        button.click();
        container.querySelector('.oe-questionnaire-popup').dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        expect(container.querySelector('.oe-questionnaire-popup')).not.toBeNull();
        document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        expect(container.querySelector('.oe-questionnaire-popup')).toBeNull();

        button.click();
        container.querySelector('.oe-questionnaire-popup-close').click();
        expect(container.querySelector('.oe-questionnaire-popup')).toBeNull();
    });

    test('help text with rendering-markdown renders as markdown; plain text otherwise', () => {
        const { container } = mount({
            item: [{
                linkId: 'q', type: 'string', text: 'Q',
                item: [{
                    linkId: 'q-help', type: 'display', text: 'See the PHQ guide', extension: [HELP],
                    _text: { extension: [{ url: 'http://hl7.org/fhir/StructureDefinition/rendering-markdown', valueMarkdown: 'See the **PHQ** [guide](https://example.org/g)' }] },
                }],
            }],
        });
        container.querySelector('.oe-questionnaire-help-button').click();
        const popup = container.querySelector('.oe-questionnaire-popup');
        expect(popup.querySelector('strong').textContent).toBe('PHQ');
        expect(popup.querySelector('a').getAttribute('href')).toBe('https://example.org/g');
    });

    test('rendering-xhtml is not trusted: the plain text is shown', () => {
        const { container } = mount({
            item: [{
                linkId: 'q', type: 'string', text: 'Q',
                item: [{
                    linkId: 'q-help', type: 'display', text: 'Plain help', extension: [HELP],
                    _text: { extension: [{ url: 'http://hl7.org/fhir/StructureDefinition/rendering-xhtml', valueString: '<div xmlns="http://www.w3.org/1999/xhtml"><img src="x" onerror="alert(1)"/>Rich</div>' }] },
                }],
            }],
        });
        container.querySelector('.oe-questionnaire-help-button').click();
        const popup = container.querySelector('.oe-questionnaire-popup');
        expect(popup.querySelector('img')).toBeNull();
        expect(popup.textContent).toContain('Plain help');
    });

    test('flyover text shows on hover of the label / heading and is not rendered in place', () => {
        const { container } = mount({
            item: [{
                linkId: 'g', type: 'group', text: 'G',
                item: [
                    { linkId: 'g-fly', type: 'display', text: 'Group hover text', extension: [FLYOVER] },
                    {
                        linkId: 'q', type: 'string', text: 'Q',
                        item: [{ linkId: 'q-fly', type: 'display', text: 'Question hover text', extension: [FLYOVER] }],
                    },
                ],
            }],
        });
        const heading = container.querySelector('[data-link-id="g"] h5');
        expect(heading.title).toBe('Group hover text');
        expect(heading.classList.contains('oe-questionnaire-flyover')).toBe(true);
        const label = container.querySelector('[data-link-id="q"] label');
        expect(label.title).toBe('Question hover text');
        expect(container.querySelector('[data-link-id="g-fly"]')).toBeNull();
        expect(container.querySelector('[data-link-id="q-fly"]')).toBeNull();
    });
});

describe('QuestionnaireResponse metadata (LForms parity)', () => {
    test('questionnaire canonical is url|version, or url alone', () => {
        let { runtime } = mount({ url: 'http://example.org/Questionnaire/phq9', version: '2.1' });
        expect(runtime.getQuestionnaireResponse().questionnaire).toBe('http://example.org/Questionnaire/phq9|2.1');
        ({ runtime } = mount({ url: 'http://example.org/Questionnaire/phq9' }));
        expect(runtime.getQuestionnaireResponse().questionnaire).toBe('http://example.org/Questionnaire/phq9');
    });

    test('without a url, a loaded response keeps its questionnaire reference', () => {
        const { runtime } = mount({}, {
            questionnaireResponse: { resourceType: 'QuestionnaireResponse', status: 'completed', questionnaire: 'Questionnaire/abc' },
        });
        expect(runtime.getQuestionnaireResponse().questionnaire).toBe('Questionnaire/abc');
    });

    test('meta gets the base profile and a version tag; Questionnaire.meta is not copied', () => {
        const { runtime } = mount({
            meta: { versionId: '7', lastUpdated: '2024-01-01T00:00:00Z', profile: ['http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire'] },
        }, { options: { openemrVersion: '8.1.0' } });
        const meta = runtime.getQuestionnaireResponse().meta;
        expect(meta.profile).toEqual(['http://hl7.org/fhir/4.0/StructureDefinition/QuestionnaireResponse']);
        expect(meta.tag).toEqual([{ system: 'https://www.open-emr.org/fhir/CodeSystem/openemr-version', code: 'openemrVersion: 8.1.0' }]);
        expect(meta.versionId).toBeUndefined();
        expect(meta.lastUpdated).toBeUndefined();
    });

    test('a loaded response keeps its own meta, profile and other tags; our tag is replaced', () => {
        const { runtime } = mount({}, {
            options: { openemrVersion: '8.2.0' },
            questionnaireResponse: {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
                meta: {
                    versionId: '3',
                    profile: ['http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaireresponse'],
                    tag: [
                        { system: 'https://lhcforms.nlm.nih.gov/cs/lhcforms-versions', code: 'lformsVersion: 33.0.0' },
                        { system: 'https://www.open-emr.org/fhir/CodeSystem/openemr-version', code: 'openemrVersion: 8.1.0' },
                    ],
                },
            },
        });
        const meta = runtime.getQuestionnaireResponse().meta;
        expect(meta.versionId).toBe('3');
        expect(meta.profile).toEqual(['http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaireresponse']);
        expect(meta.tag).toEqual([
            { system: 'https://lhcforms.nlm.nih.gov/cs/lhcforms-versions', code: 'lformsVersion: 33.0.0' },
            { system: 'https://www.open-emr.org/fhir/CodeSystem/openemr-version', code: 'openemrVersion: 8.2.0' },
        ]);
    });

    test('no version tag when no version is known', () => {
        const { runtime } = mount({});
        expect(runtime.getQuestionnaireResponse().meta.tag).toBeUndefined();
    });

    test('the version is read from the host script tag data-openemr-version', () => {
        const script = document.createElement('script');
        script.dataset.openemrVersion = '8.0.1.2';
        const descriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'currentScript');
        Object.defineProperty(document, 'currentScript', { configurable: true, get: () => script });
        try {
            runtimeApi = loadRuntime();
        } finally {
            delete document.currentScript;
            if (descriptor) {
                Object.defineProperty(Document.prototype, 'currentScript', descriptor);
            }
        }
        const { runtime } = mount({});
        expect(runtime.getQuestionnaireResponse().meta.tag).toEqual([
            { system: 'https://www.open-emr.org/fhir/CodeSystem/openemr-version', code: 'openemrVersion: 8.0.1.2' },
        ]);
        // a per-mount option still wins
        const { runtime: overridden } = mount({}, { options: { openemrVersion: '9.0.0' } });
        expect(overridden.getQuestionnaireResponse().meta.tag[0].code).toBe('openemrVersion: 9.0.0');
    });
});
