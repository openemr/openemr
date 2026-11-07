/**
 * @jest-environment jsdom
 */

/**
 * SDC 4.0.0 Advanced Rendering: rendering-markdown on item text, prefixes
 * and answer options; itemMedia and itemAnswerMedia; collapsible groups;
 * supportHyperlink; columnCount; and the header, footer, slider and
 * autocomplete item controls.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-advanced-rendering.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js'),
    'utf8'
))(global.window, global.document, global.window.HTMLElement, global.window.HTMLInputElement, global.window.CustomEvent);

const runtimeApi = global.window.OpenEMRQuestionnaire;
const SDC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/';
const CORE = 'http://hl7.org/fhir/StructureDefinition/';
const markdown = (value) => ({ extension: [{ url: CORE + 'rendering-markdown', valueMarkdown: value }] });
const control = (code) => ({
    url: CORE + 'questionnaire-itemControl',
    valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code }] },
});

function setup(items) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items }, container });
    return { container, runtime };
}

const item = (root, linkId) => root.querySelector('[data-link-id="' + linkId + '"]');

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('rendering-markdown', () => {
    test('on a question, a display item, a group title and an answer option', () => {
        const { container } = setup([
            { linkId: 'q', type: 'string', text: 'Use only if told to', _text: markdown('Use **only** if told to') },
            { linkId: 'd', type: 'display', text: 'Read this first', _text: markdown('Read *this* first') },
            { linkId: 'g', type: 'group', text: 'Part one', _text: markdown('Part **one**'), item: [{ linkId: 'g1', type: 'string', text: 'x' }] },
            { linkId: 'c', type: 'choice', text: 'Pick', answerOption: [{ valueString: 'Plain' }, { valueString: 'Bold one', _valueString: markdown('**Bold** one') }] },
        ]);
        expect(item(container, 'q').querySelector('.oe-questionnaire-label strong').textContent).toBe('only');
        expect(item(container, 'd').querySelector('em').textContent).toBe('this');
        expect(item(container, 'g').querySelector('.oe-questionnaire-group-title strong').textContent).toBe('one');
        const labels = item(container, 'c').querySelectorAll('.oe-questionnaire-choice-label');
        expect(labels[0].querySelector('strong')).toBeNull();
        expect(labels[1].querySelector('strong').textContent).toBe('Bold');
    });

    test('without the extension, text is shown exactly as written', () => {
        const { container } = setup([{ linkId: 'q', type: 'string', text: 'Score *each* item 0_3' }]);
        expect(item(container, 'q').querySelector('.oe-questionnaire-label').textContent).toBe('Score *each* item 0_3');
    });
});

describe('media', () => {
    test('itemMedia shows an image with its alt text; unsafe sources are dropped', () => {
        const { container } = setup([
            { linkId: 'a', type: 'string', text: 'Where is the pain?', extension: [{ url: SDC + 'sdc-questionnaire-itemMedia', valueAttachment: { contentType: 'image/png', url: 'https://example.org/body.png', title: 'Body diagram' } }] },
            { linkId: 'b', type: 'string', text: 'B', extension: [{ url: SDC + 'sdc-questionnaire-itemMedia', valueAttachment: { contentType: 'image/png', url: 'javascript:alert(1)' } }] },
        ]);
        const image = item(container, 'a').querySelector('img.oe-questionnaire-media');
        expect(image.src).toBe('https://example.org/body.png');
        expect(image.alt).toBe('Body diagram');
        expect(item(container, 'b').querySelector('img')).toBeNull();
    });

    test('itemAnswerMedia shows an image in its option', () => {
        const { container } = setup([{
            linkId: 'face', type: 'choice', text: 'How do you feel?', answerOption: [
                { valueString: 'Happy', extension: [{ url: SDC + 'sdc-questionnaire-itemAnswerMedia', valueAttachment: { contentType: 'image/png', data: 'iVBORw0KGgo=' } }] },
                { valueString: 'Sad' },
            ],
        }]);
        const image = item(container, 'face').querySelector('.oe-questionnaire-choice-label img');
        expect(image.src).toBe('data:image/png;base64,iVBORw0KGgo=');
        expect(image.alt).toBe('Happy');
    });
});

describe('groups', () => {
    test('collapsible default-closed starts closed and toggles', () => {
        const { container } = setup([{ linkId: 'g', type: 'group', text: 'Details', extension: [{ url: SDC + 'sdc-questionnaire-collapsible', valueCode: 'default-closed' }], item: [{ linkId: 'g1', type: 'string', text: 'x' }] }]);
        const toggle = item(container, 'g').querySelector('.oe-questionnaire-collapse-toggle');
        const body = document.getElementById(toggle.getAttribute('aria-controls'));
        expect(toggle.getAttribute('aria-expanded')).toBe('false');
        expect(body.classList.contains('d-none')).toBe(true);
        toggle.click();
        expect(toggle.getAttribute('aria-expanded')).toBe('true');
        expect(body.classList.contains('d-none')).toBe(false);
    });

    test('header and footer item controls keep a group in view', () => {
        const { container } = setup([
            { linkId: 'h', type: 'group', text: 'Patient', extension: [control('header')], item: [{ linkId: 'h1', type: 'string', text: 'x' }] },
            { linkId: 'f', type: 'group', text: 'Totals', extension: [control('footer')], item: [{ linkId: 'f1', type: 'string', text: 'y' }] },
        ]);
        expect(item(container, 'h').classList.contains('oe-questionnaire-sticky-header')).toBe(true);
        expect(item(container, 'f').classList.contains('oe-questionnaire-sticky-footer')).toBe(true);
    });
});

describe('links and columns', () => {
    test('supportHyperlink adds a link that opens in a new tab; only http(s)', () => {
        const { container } = setup([{ linkId: 'q', type: 'string', text: 'Q', extension: [
            { url: CORE + 'questionnaire-supportHyperlink', valueUri: 'https://example.org/help' },
            { url: CORE + 'questionnaire-supportHyperlink', valueUri: 'javascript:alert(1)' },
        ] }]);
        const links = item(container, 'q').querySelectorAll('.oe-questionnaire-support-link');
        expect(links).toHaveLength(1);
        expect(links[0].href).toBe('https://example.org/help');
        expect(links[0].rel).toBe('noopener noreferrer');
        expect(links[0].textContent).toBe('More information');
    });

    test('columnCount lays the answers out in columns', () => {
        const { container } = setup([{ linkId: 'c', type: 'choice', repeats: true, text: 'Which?', extension: [{ url: SDC + 'sdc-questionnaire-columnCount', valueInteger: 2 }], answerOption: ['a', 'b', 'c', 'd'].map((value) => ({ valueString: value })) }]);
        expect(item(container, 'c').querySelector(':scope > .oe-questionnaire-control').style.columnCount).toBe('2');
    });
});

describe('slider and autocomplete', () => {
    const range = { linkId: 'pain', type: 'integer', text: 'Pain, 0 to 10', extension: [
        control('slider'),
        { url: CORE + 'minValue', valueInteger: 0 },
        { url: CORE + 'maxValue', valueInteger: 10 },
        { url: CORE + 'questionnaire-sliderStepValue', valueInteger: 2 },
    ] };

    test('slider is a range between minValue and maxValue, stepped, unanswered until moved', () => {
        const { container, runtime } = setup([range]);
        const slider = item(container, 'pain').querySelector('input[type="range"]');
        expect([slider.min, slider.max, slider.step]).toEqual(['0', '10', '2']);
        expect(runtime.responseStore.getAnswers('pain')).toEqual([]);
        slider.value = '6';
        slider.dispatchEvent(new Event('change', { bubbles: true }));
        expect(runtime.responseStore.getAnswers('pain')).toEqual([{ valueKey: 'valueInteger', value: 6 }]);
        expect(item(container, 'pain').querySelector('output').textContent).toBe('6');
    });

    test('a slider without bounds stays a number input', () => {
        const { container } = setup([{ linkId: 'n', type: 'integer', text: 'N', extension: [control('slider')] }]);
        expect(item(container, 'n').querySelector('input[type="number"]')).not.toBeNull();
    });

    test('autocomplete matches typed text to an option; an open choice keeps other text', () => {
        const languages = ['English', 'Spanish', 'Tagalog'].map((display, index) => ({ valueCoding: { system: 'urn:ietf:bcp:47', code: ['en', 'es', 'tl'][index], display } }));
        const { container, runtime } = setup([
            { linkId: 'lang', type: 'choice', text: 'Language', extension: [control('autocomplete')], answerOption: languages },
            { linkId: 'other', type: 'open-choice', text: 'Language (other)', extension: [control('autocomplete')], answerOption: languages },
        ]);
        const type = (linkId, value) => {
            const input = item(container, linkId).querySelector('input.oe-questionnaire-autocomplete');
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        expect(item(container, 'lang').querySelectorAll('datalist option')).toHaveLength(3);
        type('lang', 'spanish');
        expect(runtime.responseStore.getAnswers('lang')[0].value.code).toBe('es');
        type('lang', 'Klingon');
        expect(runtime.responseStore.getAnswers('lang')).toEqual([]);
        type('other', 'Klingon');
        expect(runtime.responseStore.getAnswers('other')).toEqual([{ valueKey: 'valueString', value: 'Klingon' }]);
    });
});
