/**
 * Tests for the medication search ranking of the issue form (library/js/issue_medication_search.js).
 *
 * Run with: npm run test:js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const search = require('../../library/js/issue_medication_search.js');

const row = (code, description, codetype = 'RXCUI') =>
    ({DT_RowId: 'CID|' + JSON.stringify({code, description, codetype})});
const descriptions = (items) => items.map((i) => i.description);

describe('parseResult', () => {
    test('reads code, code type and description', () => {
        expect(search.parseResult(row('1191', 'Aspirin'))).toEqual(
            {code: '1191', codeType: 'RXCUI', description: 'Aspirin'}
        );
    });

    test('keeps only the part of the code before a pipe', () => {
        expect(search.parseResult(row('1191|extra', 'Aspirin')).code).toBe('1191');
    });

    test.each([
        ['no row', null],
        ['no DT_RowId', {}],
        ['wrong prefix', {DT_RowId: 'XID|{}'}],
        ['an array payload', {DT_RowId: 'CID|[]'}],
        ['a missing code', row('', 'Aspirin')],
        ['a missing description', row('1191', '')],
        ['a missing code type', row('1191', 'Aspirin', '')],
    ])('%s gives null', (name, input) => {
        expect(search.parseResult(input)).toBeNull();
    });

    test('invalid JSON gives null and is logged', () => {
        const log = jest.spyOn(console, 'error').mockImplementation(() => {});
        expect(search.parseResult({DT_RowId: 'CID|{not json'})).toBeNull();
        expect(log).toHaveBeenCalled();
        log.mockRestore();
    });
});

describe('scoreResult', () => {
    const score = (description, term) => search.scoreResult({description}, term);

    test('exact > starts with > bracket start > word start > contains > none', () => {
        const scores = [
            score('aspirin', 'aspirin'),
            score('aspirin 81 mg', 'aspirin'),
            score('acetaminophen [aspirin]', 'aspirin'),
            score('buffered aspirin', 'aspirin'),
            score('nonaspirin', 'aspirin'),
            score('ibuprofen', 'aspirin'),
        ];
        expect(scores).toEqual([1000, 950, 925, 900, 500, 0]);
    });

    test('ignores case and surrounding blanks of the term', () => {
        expect(score('Aspirin', '  ASPIRIN ')).toBe(1000);
    });

    test('an empty term scores 0', () => {
        expect(score('Aspirin', '   ')).toBe(0);
    });

    test('regular expression characters in the term are matched literally', () => {
        expect(score('vitamin b (12)', '(12')).toBe(900);
        expect(score('vitamin b12', 'b.2')).toBe(0);
    });
});

describe('rankResults', () => {
    test('puts the exact match first even when it comes late in the rows', () => {
        const rows = [
            row('1', 'Acetaminophen / aspirin / caffeine'),
            row('2', 'Aspirin 81 MG'),
            row('3', 'Buffered Aspirin'),
            row('4', 'Aspirin'),
        ];
        expect(descriptions(search.rankResults(rows, 'aspirin', 25))).toEqual([
            'Aspirin',
            'Aspirin 81 MG',
            'Acetaminophen / aspirin / caffeine',
            'Buffered Aspirin',
        ]);
    });

    test('equal scores are ordered by description', () => {
        const rows = [row('1', 'Aspirin 325 MG'), row('2', 'Aspirin 100 MG')];
        expect(descriptions(search.rankResults(rows, 'aspirin', 25))).toEqual(['Aspirin 100 MG', 'Aspirin 325 MG']);
    });

    test('drops duplicates of the same code type and code, keeps other code types', () => {
        const rows = [
            row('1191', 'Aspirin'),
            row('1191', 'Aspirin'),
            row('1191', 'Aspirin', 'RXNORM'),
        ];
        expect(search.rankResults(rows, 'aspirin', 25)).toHaveLength(2);
    });

    test('skips unreadable rows and keeps only the best `limit`', () => {
        const rows = [{}, row('1', 'Aspirin'), row('2', 'Aspirin 81'), row('3', 'Aspirin 325')];
        const ranked = search.rankResults(rows, 'aspirin', 2);
        expect(descriptions(ranked)).toEqual(['Aspirin', 'Aspirin 325']);
    });
});
