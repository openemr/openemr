const { populateDemographics } = require('./populate-demographics');
const { TEST_CASES, TODAY } = require('./populate-demographics.mock');

describe('populateDemographics', () => {
    beforeAll(() => {
        jest.useFakeTimers().setSystemTime(new Date(TODAY));
    });

    test.each(TEST_CASES)(
        'should map the input data to a demographics info object',
        (patient, guardian, documentData, result) => {
            expect(
                populateDemographics(patient, guardian, documentData)
            ).toEqual(result);
        }
    );

    afterAll(() => {
        jest.useRealTimers();
    });
});
