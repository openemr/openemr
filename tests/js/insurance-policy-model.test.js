/**
 * Tests for the insurance editor's policy model (InsurancePolicyModel).
 *
 * The editor hides the Secondary Medicare Type field on a primary policy: the 837 sends
 * that code (SBR05) only when Medicare is a payer other than primary.
 *
 * Run with: npm run test:js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

// The model is an ES module; evaluate the class without a bundler.
const src = fs.readFileSync(
    path.resolve(__dirname, '../../library/js/oeUI/insurance/InsurancePolicyModel.js'),
    'utf8'
).replace('export class InsurancePolicyModel', 'class InsurancePolicyModel');
const InsurancePolicyModel = new Function(src + '\nreturn InsurancePolicyModel;')();

function policyOfType(type) {
    const policy = new InsurancePolicyModel();
    policy.type = type;
    return policy;
}

describe('usesSecondaryMedicareType', () => {
    test('a new policy is primary and has no use for the field', () => {
        expect(new InsurancePolicyModel().usesSecondaryMedicareType()).toBe(false);
    });

    test('a primary policy has no use for the field', () => {
        expect(policyOfType('primary').usesSecondaryMedicareType()).toBe(false);
    });

    test.each(['secondary', 'tertiary'])('a %s policy keeps the field', (type) => {
        expect(policyOfType(type).usesSecondaryMedicareType()).toBe(true);
    });
});
