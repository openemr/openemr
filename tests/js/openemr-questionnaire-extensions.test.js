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
