/**
 * @jest-environment jsdom
 */

/**
 * SDC launchContext (the host's resources as %patient, %encounter ...) and
 * R4's signatureRequired, signed by drawing or by a typed name and saved as
 * questionnaireresponse-signature.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-launch-signature.test.js
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
const SIGNATURE = CORE + 'questionnaireresponse-signature';
const launch = (name, type) => ({
    url: SDC + 'sdc-questionnaire-launchContext',
    extension: [{ url: 'name', valueCoding: { system: 'http://hl7.org/fhir/uv/sdc/CodeSystem/launchContext', code: name } }, { url: 'type', valueCode: type }],
});
const initial = (expression) => [{ url: SDC + 'sdc-questionnaire-initialExpression', valueExpression: { language: 'text/fhirpath', expression } }];

function setup(questionnaire, options = {}, questionnaireResponse = null) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', ...questionnaire }, questionnaireResponse, container, options });
    return { container, runtime };
}

const answerOf = (runtime, linkId) => (runtime.getQuestionnaireResponse().item ?? []).find((item) => item.linkId === linkId)?.answer?.[0];

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('launchContext', () => {
    const questionnaire = {
        extension: [launch('patient', 'Patient'), launch('user', 'Practitioner')],
        item: [
            { linkId: 'family', type: 'string', text: 'Family name', extension: initial('%patient.name.first().family') },
            { linkId: 'dob', type: 'date', text: 'Date of birth', extension: initial('%patient.birthDate') },
            { linkId: 'by', type: 'string', text: 'Completed by', extension: initial('%user.name.first().family') },
        ],
    };
    const patient = { resourceType: 'Patient', name: [{ family: 'Rivera', given: ['Ana'] }], birthDate: '1980-02-03' };
    const user = { resourceType: 'Practitioner', name: [{ family: 'Okafor', given: ['Chidi'] }] };

    test('the host\'s resources prefill through initialExpression', () => {
        const { runtime } = setup(questionnaire, { launchContext: { patient, user } });
        expect(answerOf(runtime, 'family')).toEqual({ valueString: 'Rivera' });
        expect(answerOf(runtime, 'dob')).toEqual({ valueDate: '1980-02-03' });
        expect(answerOf(runtime, 'by')).toEqual({ valueString: 'Okafor' });
    });

    test('a declared context the host didn\'t supply is noted, and its items stay empty', () => {
        const { runtime } = setup(questionnaire, { launchContext: { patient } });
        expect(runtime.collectWarnings().filter((warning) => warning.code === 'launch-context-missing').map((warning) => warning.message)).toEqual([
            expect.stringContaining('%user'),
        ]);
        expect(answerOf(runtime, 'by')).toBeUndefined();
    });

    test('a resource the form didn\'t declare isn\'t exposed', () => {
        const { runtime } = setup({ item: [questionnaire.item[0]] }, { launchContext: { patient } });
        expect(answerOf(runtime, 'family')).toBeUndefined();
    });
});

describe('signatureRequired', () => {
    const required = { url: CORE + 'questionnaire-signatureRequired', valueCodeableConcept: { coding: [{ system: 'urn:iso-astm:E1762-95:2013', code: '1.2.840.10065.1.12.1.7', display: 'Consent Signature' }] } };
    const consent = { title: 'Consent to treat', extension: [required], item: [{ linkId: 'agree', type: 'boolean', text: 'I agree' }] };
    const typeName = (container, name) => {
        const input = container.querySelector('.oe-questionnaire-signature input[type="text"]');
        input.value = name;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    test('the form needs a signature before it validates', () => {
        const { container, runtime } = setup(consent);
        expect(container.querySelector('.oe-questionnaire-signature')).not.toBeNull();
        expect(runtime.validate().issues.map((issue) => issue.message)).toEqual(['Consent to treat needs a signature']);
        expect(container.querySelector('.oe-questionnaire-signature .oe-questionnaire-error').textContent).toBe('Consent to treat needs a signature');
    });

    test('a typed name signs it, and the response carries the signature', () => {
        const { container, runtime } = setup(consent, { signer: { reference: 'Patient/1', display: 'Ana Rivera' } });
        typeName(container, 'Ana Rivera');
        expect(runtime.validate().valid).toBe(true);
        const signature = runtime.getQuestionnaireResponse().extension.find((extension) => extension.url === SIGNATURE).valueSignature;
        expect(signature).toMatchObject({ sigFormat: 'text/plain', who: { reference: 'Patient/1' }, type: required.valueCodeableConcept.coding });
        expect(Buffer.from(signature.data, 'base64').toString('utf8')).toBe('Ana Rivera');
        expect(signature.when).toMatch(/^\d{4}-\d{2}-\d{2}T/);
    });

    test('Clear removes it', () => {
        const { container, runtime } = setup(consent);
        typeName(container, 'Ana');
        container.querySelector('.oe-questionnaire-signature button').click();
        expect(runtime.getQuestionnaireResponse().extension ?? []).toEqual([]);
        expect(runtime.validate().valid).toBe(false);
    });

    test('an item\'s own signature sits with the item and goes on its response item', () => {
        const { container, runtime } = setup({ item: [{ linkId: 'witness', type: 'string', text: 'Witness', extension: [required] }] });
        expect(container.querySelector('[data-link-id="witness"] .oe-questionnaire-signature')).not.toBeNull();
        typeName(container, 'Sam Lee');
        const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'witness');
        expect(item.extension[0].url).toBe(SIGNATURE);
    });

    test('a signed response loads back signed', () => {
        const first = setup(consent);
        typeName(first.container, 'Ana Rivera');
        const saved = first.runtime.getQuestionnaireResponse();
        document.body.innerHTML = '';
        const { container, runtime } = setup(consent, {}, saved);
        expect(runtime.validate().valid).toBe(true);
        expect(container.querySelector('.oe-questionnaire-signature input[type="text"]').value).toBe('Ana Rivera');
    });
});
