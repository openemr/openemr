/**
 * @jest-environment jsdom
 */

/**
 * FHIRPath 2.0.0 functions that the other suites leave unexercised: set and
 * boolean aggregates, type tests, subsetting, the remaining string, math and
 * conversion functions, and ordering of mixed values by min()/max().
 * Expected results follow the FHIRPath 2.0.0 specification text.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-fhirpath-collections.test.js
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

const response = {
    resourceType: 'QuestionnaireResponse',
    item: [
        { linkId: 'n', answer: [{ valueInteger: 1 }, { valueInteger: 2 }, { valueInteger: 2 }, { valueInteger: 3 }] },
        { linkId: 'flags', answer: [{ valueBoolean: true }, { valueBoolean: false }] },
        { linkId: 'c', answer: [{ valueCoding: { system: 'http://loinc.org', code: 'LA33-6' } }] },
    ],
};

function evaluate(expression) {
    const ast = new runtimeApi.FhirPathParser().parse(expression);
    const environment = { resource: response, rootResource: response, context: response };
    return new runtimeApi.FhirPathEvaluator().evaluate(ast, environment, response);
}

const values = (linkId) => "%resource.item.where(linkId = '" + linkId + "').answer.value";

describe('set functions', () => {
    test.each([
        // union removes duplicates; combine keeps them (FHIRPath 2.0.0, 5.4)
        ['(1 | 2).union(2 | 3)', [1, 2, 3]],
        ['(1 | 2).combine(2 | 3)', [1, 2, 2, 3]],
        // intersect removes duplicates; exclude does not
        [values('n') + '.intersect(2 | 3 | 4)', [2, 3]],
        [values('n') + '.exclude(1)', [2, 2, 3]],
        [values('n') + '.isDistinct()', [false]],
        ['(1 | 2).isDistinct()', [true]],
        ['(1 | 2).subsetOf(1 | 2 | 3)', [true]],
        ['(1 | 4).subsetOf(1 | 2 | 3)', [false]],
        ['(1 | 2 | 3).supersetOf(1 | 3)', [true]],
        ['(1 | 2).supersetOf(1 | 5)', [false]],
        // an empty collection is a subset of anything
        ['{}.subsetOf(1 | 2)', [true]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });
});

describe('boolean aggregates', () => {
    test.each([
        [values('flags') + '.allTrue()', [false]],
        [values('flags') + '.anyTrue()', [true]],
        [values('flags') + '.allFalse()', [false]],
        [values('flags') + '.anyFalse()', [true]],
        // the empty collection: allTrue/allFalse are true, anyTrue/anyFalse false
        ['{}.allTrue()', [true]],
        ['{}.anyTrue()', [false]],
        ['{}.allFalse()', [true]],
        ['{}.anyFalse()', [false]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });
});

describe('subsetting and type tests', () => {
    test.each([
        [values('n') + '.tail()', [2, 2, 3]],
        [values('n') + '.skip(2)', [2, 3]],
        [values('n') + '.skip(-1)', [1, 2, 2, 3]],
        [values('n') + '.take(2)', [1, 2]],
        [values('n') + '.take(0)', []],
        ['5.single()', [5]],
        ['{}.single()', []],
        ["'a'.hasValue()", [true]],
        ['{}.hasValue()', [false]],
        [values('c') + '.hasValue()', [false]],
        // ofType keeps matching items; is/as on a single item
        ["(1 | 'a' | 2.5).ofType(Integer)", [1]],
        ["(1 | 'a').ofType(String)", ['a']],
        ['5.is(Integer)', [true]],
        ["'5'.is(Integer)", [false]],
        ["'x'.as(String)", ['x']],
        ['5.as(String)', []],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('single() over more than one item is an error', () => {
        expect(() => evaluate(values('n') + '.single()')).toThrow(/single item/);
    });

    test('a type function without a type name is an error', () => {
        expect(() => evaluate("5.is('Integer')")).toThrow(/type name/);
    });
});

describe('strings', () => {
    test.each([
        ["'AbC'.lower()", ['abc']],
        ["'  pad  '.trim()", ['pad']],
        ["'banana'.indexOf('an')", [1]],
        ["'banana'.indexOf('x')", [-1]],
        ["'banana'.indexOf('')", [0]],
        ["'banana'.startsWith('ban')", [true]],
        ["'banana'.endsWith('na')", [true]],
        ["'banana'.endsWith('x')", [false]],
        ["'a1b22c'.replaceMatches('[0-9]+', '#')", ['a#b#c']],
        ["'2026-10-25'.matches('^\\\\d{4}-\\\\d{2}-\\\\d{2}$')", [true]],
        ["'a,b,c'.split(',')", ['a', 'b', 'c']],
        ["('a' | 'b' | 'c').join('-')", ['a-b-c']],
        ["('a' | 'b').join()", ['ab']],
        ["'hey'.toChars()", ['h', 'e', 'y']],
        ["'banana'.substring(1, 3)", ['ana']],
        ["'banana'.substring(10)", []],
        // a function on a non-string input gives empty
        ['5.lower()', []],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });
});

describe('math', () => {
    test.each([
        ['1.2.ceiling()', [2]],
        ['(-1.2).ceiling()', [-1]],
        ['1.8.floor()', [1]],
        ['(-1.8).truncate()', [-1]],
        ['16.sqrt()', [4]],
        ['(-1).sqrt()', []],
        ['1.ln()', [0]],
        ['0.ln()', []],
        ['100.log(10)', [2]],
        ['8.log(1)', []],
        ['2.power(10)', [1024]],
        ['(-8).power(0.5)', []],
        ['0.exp()', [1]],
        ['3.14159.round(2)', [3.14]],
        ['2.5.round()', [3]],
        ['1.5.round(-1)', []],
        ['(1 | 2 | 4).avg()', [7 / 3]],
        ["('a' | 'b').avg()", []],
    ])('%s', (expression, expected) => {
        const result = evaluate(expression);
        expect(result).toHaveLength(expected.length);
        result.forEach((value, index) => expect(value).toBeCloseTo(expected[index], 10));
    });
});

describe('conversions', () => {
    test.each([
        ["'12'.convertsToInteger()", [true]],
        ["'1.5'.convertsToInteger()", [false]],
        ["'1.5'.convertsToDecimal()", [true]],
        ["'yes'.convertsToBoolean()", [true]],
        ["'maybe'.convertsToBoolean()", [false]],
        ['5.convertsToString()', [true]],
        ['{}.convertsToInteger()', []],
        ["'t'.toBoolean()", [true]],
        ["'0.0'.toBoolean()", [false]],
        ['true.toInteger()', [1]],
        ['false.toDecimal()', [0]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });
});

describe('ordering', () => {
    test.each([
        ["('pear' | 'apple' | 'fig').min()", ['apple']],
        ["('pear' | 'apple' | 'fig').max()", ['pear']],
        [values('n') + '.max()', [3]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });
});
