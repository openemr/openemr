const {
    getNpiFacility,
    populateDemographics,
} = require('./populate-demographics');
const { NOT_INFORMED } = require('../constants');
const { TEST_CASES, TODAY } = require('./populate-demographics.mock');

describe('getNpiFacility', () => {
    describe('input with facility_npi value', () => {
        it('should return the facility_npi value', () => {
            const facility_npi = 'mock_npi';
            expect(
                getNpiFacility({ encounter_provider: { facility_npi } })
            ).toEqual(facility_npi);
        });
    });

    describe('input wihout facility_npi value', () => {
        it('should return undefined for structured documents', () => {
            expect(getNpiFacility({ encounter_provider: {} }, false)).toEqual(
                undefined
            );
        });

        it(`should return ${NOT_INFORMED} for unstructured documents`, () => {
            expect(getNpiFacility({ encounter_provider: {} }, true)).toEqual(
                NOT_INFORMED
            );
        });
    });
});

describe('populateDemographics', () => {
    beforeAll(() => {
        jest.useFakeTimers().setSystemTime(new Date(TODAY));
    });

    test.each(TEST_CASES)(
        'should map the input data to a demographics info object',
        (documentData, result) => {
            const npiFacility = getNpiFacility(documentData, true);
            expect(populateDemographics(documentData, npiFacility)).toEqual(
                result
            );
        }
    );

    afterAll(() => {
        jest.useRealTimers();
    });
});
