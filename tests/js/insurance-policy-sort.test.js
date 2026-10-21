/**
 * Tests for the insurance editor's policy ordering (InsurancePolicyService.sortInsurancesByDate).
 *
 * The editor opens on the first policy of each type, so the order must match the server's
 * (`InsuranceService`): active policies first, then most recent start date, with a policy
 * that has no start date last, as MySQL and MariaDB order NULLs in a DESC sort.
 *
 * Run with: npm run test:js
 */

const fs = require('fs');
const path = require('path');

// The module loads its model and error classes with top-level `await import(...)`
// (cache-busted by the asset version); the sort needs neither, so drop those two
// lines and evaluate the class without a bundler.
const src = fs.readFileSync(
    path.resolve(__dirname, '../../library/js/oeUI/insurance/InsurancePolicyService.js'),
    'utf8'
)
    .split('\n')
    .filter((line) => !line.includes('await import('))
    .join('\n')
    .replace('export class InsurancePolicyService', 'class InsurancePolicyService');
const InsurancePolicyService = new Function(src + '\nreturn InsurancePolicyService;')();

const day = (iso) => new Date(iso + 'T00:00:00');
const policy = (policy_number, date, date_end = null) => ({ policy_number, date, date_end });

function sorted(policies) {
    const service = new InsurancePolicyService('token', '/apis', [], ['primary'], 'puuid');
    const copy = policies.slice();
    service.sortInsurancesByDate(copy);
    return copy.map((p) => p.policy_number);
}

describe('compareDatesDesc', () => {
    test('most recent first', () => {
        expect(InsurancePolicyService.compareDatesDesc(day('2024-01-01'), day('2020-01-01'))).toBeLessThan(0);
        expect(InsurancePolicyService.compareDatesDesc(day('2020-01-01'), day('2024-01-01'))).toBeGreaterThan(0);
    });

    test('a missing date sorts last, and two missing dates are equal', () => {
        expect(InsurancePolicyService.compareDatesDesc(null, day('2020-01-01'))).toBeGreaterThan(0);
        expect(InsurancePolicyService.compareDatesDesc(day('2020-01-01'), null)).toBeLessThan(0);
        expect(InsurancePolicyService.compareDatesDesc(null, null)).toBe(0);
    });

    test('equal dates are equal', () => {
        expect(InsurancePolicyService.compareDatesDesc(day('2020-01-01'), day('2020-01-01'))).toBe(0);
    });
});

describe('sortInsurancesByDate', () => {
    test('the policy with a start date comes before one that never got a start date', () => {
        const old = policy('OLD-IMPORT', null);
        const current = policy('CURRENT', day('2024-03-01'));
        expect(sorted([old, current])).toEqual(['CURRENT', 'OLD-IMPORT']);
        expect(sorted([current, old])).toEqual(['CURRENT', 'OLD-IMPORT']);
    });

    test('among active policies the most recent start comes first', () => {
        expect(sorted([
            policy('B', day('2022-01-01')),
            policy('C', day('2024-06-01')),
            policy('A', day('2019-01-01')),
        ])).toEqual(['C', 'B', 'A']);
    });

    test('an ended policy comes after active ones, whatever its start date', () => {
        expect(sorted([
            policy('ENDED', day('2025-01-01'), day('2025-06-30')),
            policy('ACTIVE', day('2020-01-01')),
        ])).toEqual(['ACTIVE', 'ENDED']);
    });

    test('ties fall through to the policy number', () => {
        expect(sorted([
            policy('B', null),
            policy('A', null),
        ])).toEqual(['A', 'B']);
    });
});
