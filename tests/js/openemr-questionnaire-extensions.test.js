/**
 * @jest-environment jsdom
 */

/**
 * SDC 4.0.0's extension lists: rendering-criticalExtension stops a form the
 * runtime can't support; other listed extensions it doesn't act on are
 * reported; and minLength, mimeType, maxSize and optionExclusive (Advanced
 * Behavior must-support) take effect.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-extensions.test.js
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
const critical = (url) => ({ url: SDC + 'sdc-rendering-criticalExtension', valueCanonical: url });

function setup(questionnaire) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', ...questionnaire }, container });
    return { container, runtime };
}

const codes = (runtime, code) => runtime.collectWarnings().filter((warning) => warning.code === code);

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('rendering-criticalExtension', () => {
    test('a critical extension the runtime lacks stops the form, and says why', () => {
        const { container, runtime } = setup({
            modifierExtension: [critical(SDC + 'sdc-questionnaire-lookupQuestionnaire')],
            item: [{ linkId: 'q', type: 'string', text: 'Q' }],
        });
        expect(container.querySelector('[data-link-id="q"]')).toBeNull();
        expect(codes(runtime, 'critical-extension-unsupported')).toHaveLength(1);
        expect(container.querySelector('.oe-questionnaire-warnings').textContent).toContain('sdc-questionnaire-lookupQuestionnaire');
    });

    test('a critical extension it supports renders as usual', () => {
        const { container, runtime } = setup({
            extension: [critical(SDC + 'sdc-questionnaire-enableWhenExpression|4.0.0')],
            item: [{ linkId: 'q', type: 'string', text: 'Q' }],
        });
        expect(container.querySelector('[data-link-id="q"]')).not.toBeNull();
        expect(codes(runtime, 'critical-extension-unsupported')).toHaveLength(0);
    });
});

describe('modifierExtension', () => {
    // R4: "Applications processing a resource are required to check for modifier extensions."
    const unknown = { url: 'http://example.org/fhir/StructureDefinition/only-if-pregnant', valueBoolean: true };

    test.each([
        ['on the Questionnaire', { modifierExtension: [unknown], item: [{ linkId: 'q', type: 'string', text: 'Q' }] }],
        ['on an item', { item: [{ linkId: 'q', type: 'string', text: 'Q', modifierExtension: [unknown] }] }],
        ['on an answer option', { item: [{ linkId: 'q', type: 'choice', text: 'Q', answerOption: [{ valueString: 'A', modifierExtension: [unknown] }] }] }],
    ])('one it does not understand, %s, stops the form and says why', (label, questionnaire) => {
        const { container, runtime } = setup(questionnaire);
        expect(container.querySelector('[data-link-id="q"]')).toBeNull();
        expect(codes(runtime, 'modifier-extension-unsupported')).toHaveLength(1);
        expect(container.querySelector('.oe-questionnaire-warnings').textContent).toContain('only-if-pregnant');
    });

    test('one inside a contained resource is left to whoever uses it', () => {
        const { container, runtime } = setup({
            contained: [{ resourceType: 'ValueSet', id: 'vs', status: 'active', modifierExtension: [unknown] }],
            item: [{ linkId: 'q', type: 'string', text: 'Q' }],
        });
        expect(container.querySelector('[data-link-id="q"]')).not.toBeNull();
        expect(codes(runtime, 'modifier-extension-unsupported')).toHaveLength(0);
    });
});

describe('extensions not acted on', () => {
    test('one that changes what is captured is a visible warning, once, with its first item', () => {
        const candidate = { url: SDC + 'sdc-questionnaire-candidateExpression', valueExpression: { language: 'text/fhirpath', expression: "'x'" } };
        const { runtime } = setup({
            item: [
                { linkId: 'a', type: 'string', text: 'A', extension: [candidate] },
                { linkId: 'b', type: 'string', text: 'B', extension: [candidate] },
            ],
        });
        const warnings = codes(runtime, 'extension-unsupported');
        expect(warnings).toHaveLength(1);
        expect(warnings[0]).toMatchObject({ linkId: 'a', severity: 'warning' });
    });

    test('a presentation-only one is a console note, and a repeats expression is found under _repeats', () => {
        const { runtime } = setup({
            item: [
                { linkId: 'a', type: 'string', text: 'A', extension: [{ url: SDC + 'sdc-questionnaire-choiceColumn', extension: [{ url: 'path', valueString: 'display' }] }] },
                { linkId: 'b', type: 'string', text: 'B', _repeats: { extension: [{ url: CORE + 'cqf-expression', valueExpression: { language: 'text/fhirpath', expression: 'true' } }] } },
            ],
        });
        const bySeverity = Object.fromEntries(codes(runtime, 'extension-unsupported').map((warning) => [warning.linkId, warning.severity]));
        expect(bySeverity).toEqual({ a: 'info', b: 'warning' });
    });

    test('extraction and population extensions are not reported', () => {
        const { runtime } = setup({
            item: [{ linkId: 'a', type: 'integer', text: 'A', extension: [{ url: SDC + 'sdc-questionnaire-observationExtract', valueBoolean: true }] }],
        });
        expect(codes(runtime, 'extension-unsupported')).toHaveLength(0);
    });
});

describe('minLength, mimeType, maxSize', () => {
    test('minLength is checked', () => {
        const { runtime } = setup({ item: [{ linkId: 'z', type: 'string', text: 'ZIP', extension: [{ url: CORE + 'minLength', valueInteger: 5 }] }] });
        runtime.setAnswers('z', [{ valueKey: 'valueString', value: '123' }]);
        expect(runtime.validate().issues.map((issue) => issue.code)).toEqual(['min-length']);
        runtime.setAnswers('z', [{ valueKey: 'valueString', value: '12345' }]);
        expect(runtime.validate().valid).toBe(true);
    });

    test('mimeType limits the picker and the answer; maxSize the answer', () => {
        const { container, runtime } = setup({
            item: [{
                linkId: 'f', type: 'attachment', text: 'Scan', extension: [
                    { url: CORE + 'mimeType', valueCode: 'application/pdf' },
                    { url: CORE + 'mimeType', valueCode: 'image/png' },
                    { url: CORE + 'maxSize', valueDecimal: 1000 },
                ],
            }],
        });
        expect(container.querySelector('input[type="file"]').accept).toBe('application/pdf,image/png');
        runtime.setAnswers('f', [{ valueKey: 'valueAttachment', value: { contentType: 'text/plain', size: 5000, title: 'x.txt' } }]);
        expect(runtime.validate().issues.map((issue) => issue.code).sort()).toEqual(['max-size', 'mime-type']);
        runtime.setAnswers('f', [{ valueKey: 'valueAttachment', value: { contentType: 'image/png', size: 900, title: 'x.png' } }]);
        expect(runtime.validate().valid).toBe(true);
    });
});

describe('optionExclusive', () => {
    const options = [
        { valueString: 'Food' },
        { valueString: 'Housing' },
        { valueString: 'None of these', extension: [{ url: CORE + 'questionnaire-optionExclusive', valueBoolean: true }] },
    ];
    const tick = (container, index) => {
        const box = container.querySelectorAll('input[type="checkbox"]')[index];
        box.checked = true;
        box.dispatchEvent(new Event('change', { bubbles: true }));
    };
    const answers = (runtime) => runtime.responseStore.getAnswers('needs').map((answer) => answer.value);

    test('the exclusive answer clears the others, and another answer clears it', () => {
        const { container, runtime } = setup({ item: [{ linkId: 'needs', type: 'choice', repeats: true, text: 'Needs', answerOption: options }] });
        tick(container, 0);
        tick(container, 1);
        expect(answers(runtime)).toEqual(['Food', 'Housing']);
        tick(container, 2);
        expect(answers(runtime)).toEqual(['None of these']);
        expect([...container.querySelectorAll('input[type="checkbox"]')].map((box) => box.checked)).toEqual([false, false, true]);
        tick(container, 0);
        expect(answers(runtime)).toEqual(['Food']);
    });
});

// Six extensions that worked with no test of their own.
describe('hidden, openLabel, displayCategory, choiceOrientation, minOccurs and maxOccurs', () => {
    const ext = (name, value) => ({ url: (name === 'openLabel' ? SDC + 'sdc-questionnaire-' : CORE + 'questionnaire-') + name, ...value });

    test('questionnaire-hidden: not shown, not validated, still saved', () => {
        const { container, runtime } = setup({
            item: [{ linkId: 'h', type: 'string', text: 'H', required: true, initial: [{ valueString: 'kept' }], extension: [ext('hidden', { valueBoolean: true })] },
                { linkId: 'gone', type: 'string', text: 'Gone', required: true, extension: [ext('hidden', { valueBoolean: true })] }],
        });
        expect(container.querySelector('[data-link-id="h"]').closest('.d-none')).not.toBeNull();
        expect(runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'h').answer).toEqual([{ valueString: 'kept' }]);
        expect(runtime.validate().issues.filter((issue) => issue.linkId === 'gone')).toEqual([]);
    });

    test('sdc-questionnaire-openLabel labels the free-text answer of an open choice', () => {
        const { container } = setup({
            item: [{ linkId: 'o', type: 'open-choice', text: 'O', answerOption: [{ valueString: 'A' }], extension: [ext('openLabel', { valueString: 'Something else' })] }],
        });
        expect(container.querySelector('[data-link-id="o"] .oe-questionnaire-open-label').textContent).toBe('Something else');
        expect(container.querySelector('[data-link-id="o"] input[type="text"]').placeholder).toBe('Something else');
    });

    test.each([
        ['instructions', 'alert-info'],
        ['security', 'alert-warning'],
    ])('questionnaire-displayCategory %s styles a display item as %s', (code, className) => {
        const { container } = setup({
            item: [{ linkId: 'd', type: 'display', text: 'Note', extension: [ext('displayCategory', { valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-display-category', code }] } })] }],
        });
        const alert = container.querySelector('[data-link-id="d"] .alert');
        expect(alert.classList.contains(className)).toBe(true);
        expect(alert.dataset.displayCategory).toBe(code);
    });

    test.each([
        ['horizontal', true],
        ['vertical', false],
    ])('questionnaire-choiceOrientation %s lays the choices out inline: %s', (orientation, inline) => {
        const { container } = setup({
            item: [{ linkId: 'c', type: 'choice', text: 'C', answerOption: [{ valueString: 'A' }, { valueString: 'B' }], extension: [ext('choiceOrientation', { valueCode: orientation })] }],
        });
        const rows = [...container.querySelectorAll('[data-link-id="c"] input[type="radio"]')].map((input) => input.parentElement);
        expect(rows).toHaveLength(2);
        expect(rows.every((row) => row.classList.contains('custom-control-inline'))).toBe(inline);
    });

    test('questionnaire-minOccurs: a repeating group starts with that many repetitions, and a question needs that many answers', () => {
        const { container, runtime } = setup({
            item: [
                { linkId: 'g', type: 'group', text: 'G', repeats: true, extension: [ext('minOccurs', { valueInteger: 2 })], item: [{ linkId: 'x', type: 'string', text: 'X' }] },
                { linkId: 'names', type: 'string', text: 'Names', repeats: true, extension: [ext('minOccurs', { valueInteger: 2 })] },
            ],
        });
        expect(container.querySelectorAll('[data-link-id="g"] > .card-body > .oe-questionnaire-group-instance')).toHaveLength(2);
        runtime.setAnswers('names', [{ valueKey: 'valueString', value: 'Ana' }]);
        expect(runtime.validate().issues.filter((issue) => issue.linkId === 'names').map((issue) => issue.code)).toEqual(['min-occurs']);
    });

    test('questionnaire-maxOccurs: a repeating group stops adding at that many, and a question allows no more answers', () => {
        const { container, runtime } = setup({
            item: [
                { linkId: 'g', type: 'group', text: 'G', repeats: true, extension: [ext('maxOccurs', { valueInteger: 2 })], item: [{ linkId: 'x', type: 'string', text: 'X' }] },
                { linkId: 'names', type: 'string', text: 'Names', repeats: true, extension: [ext('maxOccurs', { valueInteger: 1 })] },
            ],
        });
        const add = () => container.querySelector('[data-link-id="g"] > .card-body > .oe-questionnaire-repeat-add');
        expect(add().disabled).toBe(false);
        add().click();
        expect(add().disabled).toBe(true);
        runtime.setAnswers('names', [{ valueKey: 'valueString', value: 'Ana' }, { valueKey: 'valueString', value: 'Bo' }]);
        expect(runtime.validate().issues.filter((issue) => issue.linkId === 'names').map((issue) => issue.code)).toEqual(['max-occurs']);
    });
});
