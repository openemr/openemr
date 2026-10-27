/**
 * @jest-environment jsdom
 */

/**
 * Paths the other suites leave unexercised: drawing a signature on the pad
 * (canvas stubbed, jsdom has none), redrawing a loaded image signature,
 * removing a row from a gtable repeating group, the answer type a calculated
 * choice/open-choice value is stored under, and parity between the PHP
 * translation table and the runtime's built-in strings.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-review-gaps.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const RUNTIME = path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js');
const COMPONENTS = path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/openemr_questionnaire_components.php');

new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', fs.readFileSync(RUNTIME, 'utf8'))(
    global.window, global.document, global.window.HTMLElement, global.window.HTMLInputElement, global.window.CustomEvent
);

const runtimeApi = global.window.OpenEMRQuestionnaire;
const CORE = 'http://hl7.org/fhir/StructureDefinition/';
const SIGNATURE = CORE + 'questionnaireresponse-signature';
const CALC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';
const required = {
    url: CORE + 'questionnaire-signatureRequired',
    valueCodeableConcept: { coding: [{ system: 'urn:iso-astm:E1762-95:2013', code: '1.2.840.10065.1.12.1.7', display: 'Consent Signature' }] },
};
const control = (code) => ({
    url: CORE + 'questionnaire-itemControl',
    valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code }] },
});

function setup(questionnaire, { questionnaireResponse = null, options = {} } = {}) {
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

function formSignature(runtime) {
    return (runtime.getQuestionnaireResponse().extension ?? []).find((extension) => extension.url === SIGNATURE)?.valueSignature;
}

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('signature pad drawing', () => {
    const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
    let context;
    let restore;

    beforeEach(() => {
        context = {
            beginPath: jest.fn(), moveTo: jest.fn(), lineTo: jest.fn(), stroke: jest.fn(),
            clearRect: jest.fn(), drawImage: jest.fn(),
        };
        const proto = global.window.HTMLCanvasElement.prototype;
        const original = { getContext: proto.getContext, toDataURL: proto.toDataURL };
        proto.getContext = jest.fn(() => context);
        proto.toDataURL = jest.fn(() => 'data:image/png;base64,' + PNG);
        restore = () => Object.assign(proto, original);
    });

    afterEach(() => restore());

    const pointer = (canvas, type, x, y) => {
        const event = new global.window.MouseEvent(type, { bubbles: true, clientX: x, clientY: y });
        canvas.dispatchEvent(event);
    };

    test('a stroke on the pad signs the form with a PNG and a timestamp', () => {
        const { container, runtime } = setup({ extension: [required], item: [{ linkId: 'q', type: 'string', text: 'Q' }] }, {
            options: { signer: { reference: 'Patient/1' } },
        });
        const canvas = container.querySelector('canvas.oe-questionnaire-signature-pad');
        expect(canvas.classList.contains('d-none')).toBe(false);
        expect(runtime.validate().valid).toBe(false);

        pointer(canvas, 'pointerdown', 10, 10);
        pointer(canvas, 'pointermove', 40, 30);
        pointer(canvas, 'pointerup', 40, 30);

        expect(context.beginPath).toHaveBeenCalledTimes(1);
        expect(context.lineTo).toHaveBeenCalledTimes(1);
        const signature = formSignature(runtime);
        expect(signature).toMatchObject({ sigFormat: 'image/png', data: PNG, who: { reference: 'Patient/1' } });
        expect(signature.when).toMatch(/^\d{4}-\d{2}-\d{2}T/);
        expect(runtime.validate().valid).toBe(true);
    });

    test('moving without pressing does not draw, and leaving without a stroke does not sign', () => {
        const { container, runtime } = setup({ extension: [required] });
        const canvas = container.querySelector('canvas.oe-questionnaire-signature-pad');
        pointer(canvas, 'pointermove', 5, 5);
        pointer(canvas, 'pointerleave', 5, 5);
        expect(context.lineTo).not.toHaveBeenCalled();
        expect(formSignature(runtime)).toBeUndefined();
    });

    test('drawing replaces a typed name, and Clear removes the signature', () => {
        const { container, runtime } = setup({ extension: [required] });
        const typed = container.querySelector('.oe-questionnaire-signature input[type="text"]');
        typed.value = 'Ana Rivera';
        typed.dispatchEvent(new Event('change', { bubbles: true }));
        expect(formSignature(runtime).sigFormat).toBe('text/plain');

        const canvas = container.querySelector('canvas.oe-questionnaire-signature-pad');
        pointer(canvas, 'pointerdown', 1, 1);
        pointer(canvas, 'pointerleave', 2, 2);
        expect(typed.value).toBe('');
        expect(formSignature(runtime).sigFormat).toBe('image/png');

        container.querySelector('.oe-questionnaire-signature button').click();
        expect(context.clearRect).toHaveBeenCalled();
        expect(formSignature(runtime)).toBeUndefined();
    });

    test('a loaded image signature is drawn back onto the pad and kept unchanged', () => {
        const loaded = { type: required.valueCodeableConcept.coding, when: '2026-10-01T12:00:00Z', who: { reference: 'Patient/1' }, sigFormat: 'image/png', data: PNG };
        const OriginalImage = global.window.Image;
        let created = null;
        global.window.Image = class {
            set src(value) {
                this.loadedFrom = value;
                created = this;
            }
        };
        global.Image = global.window.Image;
        try {
            const { runtime } = setup({ extension: [required] }, {
                questionnaireResponse: { resourceType: 'QuestionnaireResponse', status: 'completed', extension: [{ url: SIGNATURE, valueSignature: loaded }] },
            });
            expect(created.loadedFrom).toBe('data:image/png;base64,' + PNG);
            created.onload();
            expect(context.drawImage).toHaveBeenCalledWith(created, 0, 0);
            expect(formSignature(runtime)).toEqual(loaded);
        } finally {
            global.window.Image = OriginalImage;
            global.Image = OriginalImage;
        }
    });
});

describe('gtable repeating group', () => {
    const medications = {
        linkId: 'meds', type: 'group', text: 'Medications', repeats: true, extension: [control('gtable')], item: [
            { linkId: 'name', type: 'string', text: 'Name' },
            { linkId: 'dose', type: 'string', text: 'Dose' },
        ],
    };

    test('removing a row shifts later rows up and keeps their answers', () => {
        const { container, runtime } = setup({ item: [medications] }, {
            questionnaireResponse: {
                resourceType: 'QuestionnaireResponse', status: 'in-progress', item: [
                    { linkId: 'meds', item: [{ linkId: 'name', answer: [{ valueString: 'Aspirin' }] }] },
                    { linkId: 'meds', item: [{ linkId: 'name', answer: [{ valueString: 'Metformin' }] }] },
                    { linkId: 'meds', item: [{ linkId: 'name', answer: [{ valueString: 'Lisinopril' }] }] },
                ],
            },
        });
        const rows = () => [...container.querySelectorAll('table.oe-questionnaire-grid tbody tr')];
        expect(rows()).toHaveLength(3);
        rows()[1].querySelector('.oe-questionnaire-repeat-remove').click();
        expect(rows()).toHaveLength(2);
        const names = runtime.getQuestionnaireResponse().item
            .filter((entry) => entry.linkId === 'meds')
            .map((entry) => entry.item.find((child) => child.linkId === 'name').answer[0].valueString);
        expect(names).toEqual(['Aspirin', 'Lisinopril']);
    });

    test('the last row (minOccurs) has no remove button', () => {
        const { container } = setup({ item: [medications] });
        expect(container.querySelectorAll('table.oe-questionnaire-grid tbody tr')).toHaveLength(1);
        expect(container.querySelector('.oe-questionnaire-repeat-remove')).toBeNull();
    });
});

describe('calculated choice answers keep their value type', () => {
    const calculatedChoice = (expression, type = 'choice') => ({
        linkId: 'out', type, text: 'Out', readOnly: true,
        extension: [{ url: CALC, valueExpression: { language: 'text/fhirpath', expression } }],
    });
    const answerOf = (runtime) => runtime.getQuestionnaireResponse().item?.find((entry) => entry.linkId === 'out')?.answer;

    test.each([
        ['2', { valueInteger: 2 }],
        ['2.5', { valueDecimal: 2.5 }],
        ["'yes'", { valueString: 'yes' }],
        ['@2026-10-25', { valueDate: '2026-10-25' }],
        ['@T10:30:00', { valueTime: '10:30:00' }],
    ])('%s', (expression, expected) => {
        const { runtime } = setup({ item: [calculatedChoice(expression)] });
        expect(answerOf(runtime)).toEqual([expected]);
    });

    test('an open-choice Coding is stored as valueCoding', () => {
        const { runtime } = setup({
            item: [
                { linkId: 'src', type: 'choice', text: 'Src', answerOption: [{ valueCoding: { code: 'a', display: 'A' } }], initial: [{ valueCoding: { code: 'a', display: 'A' } }] },
                calculatedChoice("%resource.item.where(linkId = 'src').answer.value", 'open-choice'),
            ],
        });
        expect(answerOf(runtime)).toEqual([{ valueCoding: { code: 'a', display: 'A' } }]);
    });
});

describe('translation table parity', () => {
    // The host passes translated strings through openemr_questionnaire_components.php;
    // a key or English text that drifts from DEFAULT_STRINGS silently falls back to
    // English, or leaves a string untranslatable.
    const php = fs.readFileSync(COMPONENTS, 'utf8');
    const phpStrings = Object.fromEntries([...php.matchAll(/'([A-Za-z]+)'\s*=>\s*xl\('((?:[^'\\]|\\.)*)'\)/g)]
        .map(([, key, text]) => [key, text.replace(/\\'/g, "'")]));

    test('every runtime string has a PHP translation entry with the same English text', () => {
        expect(Object.keys(phpStrings).sort()).toEqual(Object.keys(runtimeApi.DEFAULT_STRINGS).sort());
        for (const [key, text] of Object.entries(runtimeApi.DEFAULT_STRINGS)) {
            expect([key, phpStrings[key]]).toEqual([key, text]);
        }
    });

    test('placeholders survive translation and unfilled ones are left visible', () => {
        const { container, runtime } = setup({ item: [] }, { options: { strings: { requiresValue: '{0} es obligatorio' } } });
        expect(container).not.toBeNull();
        expect(runtime.t('requiresValue', 'Edad')).toBe('Edad es obligatorio');
        expect(runtime.t('requiresAtLeast', 'Q')).toBe('Q requires at least {1} answers');
        expect(runtime.t('noSuchKey')).toBe('noSuchKey');
    });
});
