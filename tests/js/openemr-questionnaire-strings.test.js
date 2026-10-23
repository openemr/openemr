/**
 * @jest-environment jsdom
 */

/**
 * The runtime's own words: English by default, the host's translation when
 * it supplies one (options.strings, or the include's data-openemr-strings),
 * with {0}, {1} filled in order. The include's table must carry the same
 * keys and English text as the runtime's defaults, or a translation would
 * miss.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-strings.test.js
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
    item: [
        { linkId: 'name', type: 'string', text: 'Nombre', required: true },
        { linkId: 'smoke', type: 'boolean', text: '¿Fuma?' },
        { linkId: 'rows', type: 'group', repeats: true, text: 'Medicinas', item: [{ linkId: 'med', type: 'string', text: 'Medicina' }] },
    ],
};

function setup(options = {}) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire, container, options });
    return { container, runtime };
}

beforeEach(() => {
    document.body.innerHTML = '';
});

test('English by default', () => {
    const { container, runtime } = setup();
    expect(runtime.validate().issues[0].message).toBe('Nombre requires a value');
    expect([...container.querySelectorAll('[data-link-id="smoke"] label')].map((label) => label.textContent)).toEqual(['¿Fuma?', 'Yes', 'No']);
    expect(container.querySelector('.oe-questionnaire-repeat-add').textContent).toBe('+ Add Medicinas');
});

test('the host\'s strings, with placeholders in their own order', () => {
    const { container, runtime } = setup({ strings: { requiresValue: 'Falta un valor: {0}', yes: 'Sí', add: 'Agregar {0}' } });
    expect(runtime.validate().issues[0].message).toBe('Falta un valor: Nombre');
    expect(container.querySelector('[data-link-id="smoke"] label[for$="-0"]').textContent).toBe('Sí');
    expect(container.querySelector('[data-link-id="smoke"] label[for$="-1"]').textContent).toBe('No');
    expect(container.querySelector('.oe-questionnaire-repeat-add').textContent).toBe('Agregar Medicinas');
});

test('the include translates the same keys and English text the runtime uses', () => {
    const php = fs.readFileSync(path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/openemr_questionnaire_components.php'), 'utf8');
    const table = Object.fromEntries([...php.matchAll(/^\s+'(\w+)' => xl\('((?:[^'\\]|\\.)*)'\),$/gm)].map((match) => [match[1], match[2]]));
    expect(Object.keys(table).length).toBeGreaterThan(20);
    expect(table).toEqual({ ...runtimeApi.DEFAULT_STRINGS });
});
