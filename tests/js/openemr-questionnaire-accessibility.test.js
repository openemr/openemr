/**
 * @jest-environment jsdom
 */

/**
 * What assistive technology gets from the runtime (WCAG 2.1): answer groups
 * named by their question (1.3.1, 4.1.2), required state that isn't visual
 * only (1.3.1, 3.3.2), errors identified in text at the question (3.3.1),
 * and the form's own language (3.1.2).
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-accessibility.test.js
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

const questionnaire = {
    resourceType: 'Questionnaire',
    status: 'active',
    language: 'es',
    item: [
        { linkId: 'name', type: 'string', text: 'Nombre', required: true },
        { linkId: 'pick', type: 'choice', text: 'Elija una', required: true, answerOption: [{ valueString: 'Sí' }, { valueString: 'No' }] },
        { linkId: 'many', type: 'choice', text: 'Todas las que apliquen', repeats: true, answerOption: [{ valueString: 'Uno' }, { valueString: 'Dos' }] },
        { linkId: 'yes', type: 'boolean', text: '¿Fuma?' },
        { linkId: 'list', type: 'choice', text: 'Lista', answerOption: [1, 2, 3, 4, 5, 6, 7].map((n) => ({ valueString: 'Opción ' + n })) },
    ],
};

function setup(q = questionnaire) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: q, container });
    return { container, runtime };
}

const item = (root, linkId) => root.querySelector('[data-link-id="' + linkId + '"]');
const label = (root, linkId) => item(root, linkId).querySelector('.oe-questionnaire-label');
const control = (root, linkId) => item(root, linkId).querySelector(':scope > .oe-questionnaire-control');

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('answer groups are named by their question', () => {
    test('radios form a radiogroup labelled by the question', () => {
        const { container } = setup();
        const group = control(container, 'pick');
        expect(group.getAttribute('role')).toBe('radiogroup');
        expect(document.getElementById(group.getAttribute('aria-labelledby'))).toBe(label(container, 'pick'));
    });

    test('checkboxes form a group, and a boolean\'s Yes/No a radiogroup', () => {
        const { container } = setup();
        expect(control(container, 'many').getAttribute('role')).toBe('group');
        expect(control(container, 'yes').getAttribute('role')).toBe('radiogroup');
    });

    test('a drop-down keeps its own label, with no group role', () => {
        const { container } = setup();
        const select = control(container, 'list').querySelector('select');
        expect(label(container, 'list').htmlFor).toBe(select.id);
        expect(control(container, 'list').getAttribute('role')).toBeNull();
    });
});

describe('required state', () => {
    test('a required input and a required radiogroup carry aria-required; the asterisk is hidden from speech', () => {
        const { container } = setup();
        expect(control(container, 'name').querySelector('input').getAttribute('aria-required')).toBe('true');
        expect(control(container, 'pick').getAttribute('aria-required')).toBe('true');
        expect(label(container, 'name').querySelector('span').getAttribute('aria-hidden')).toBe('true');
        expect(control(container, 'many').getAttribute('aria-required')).toBeNull();
    });
});

describe('errors at the question', () => {
    test('validate() shows each message at its question, tied to its control', () => {
        const { container, runtime } = setup();
        const result = runtime.validate();
        expect(result.valid).toBe(false);

        const input = control(container, 'name').querySelector('input');
        expect(input.getAttribute('aria-invalid')).toBe('true');
        const message = document.getElementById(input.getAttribute('aria-describedby'));
        expect(message.textContent).toBe('Nombre requires a value');
        expect(message.previousElementSibling).toBe(control(container, 'name'));

        const group = control(container, 'pick');
        expect(group.getAttribute('aria-invalid')).toBe('true');
        expect(document.getElementById(group.getAttribute('aria-describedby')).textContent).toBe('Elija una requires a value');
    });

    test('answering clears the messages and the invalid state', () => {
        const { container, runtime } = setup();
        runtime.validate();
        runtime.setAnswers('name', [{ valueKey: 'valueString', value: 'Ana' }]);
        expect(container.querySelectorAll('.oe-questionnaire-error')).toHaveLength(0);
        expect(container.querySelectorAll('[aria-invalid]')).toHaveLength(0);
    });

    test('a valid form shows no messages', () => {
        const { container, runtime } = setup();
        runtime.setAnswers('name', [{ valueKey: 'valueString', value: 'Ana' }]);
        runtime.setAnswers('pick', [{ valueKey: 'valueString', value: 'Sí' }]);
        expect(runtime.validate().valid).toBe(true);
        expect(container.querySelectorAll('.oe-questionnaire-error')).toHaveLength(0);
    });
});

describe('language', () => {
    test('Questionnaire.language sets lang on the form', () => {
        const { container } = setup();
        expect(container.lang).toBe('es');
    });

    test('no language, no lang attribute', () => {
        const { container } = setup({ ...questionnaire, language: undefined });
        expect(container.hasAttribute('lang')).toBe(false);
    });
});
