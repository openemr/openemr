const fetchPreviousAddresses =
    require('./previous-addresses').fetchPreviousAddresses;
const { TEST_CASES, TODAY } = require('./previous-addresses.mock');

describe('fetchPreviousAddresses', () => {
    beforeAll(() => {
        jest.useFakeTimers().setSystemTime(new Date(TODAY));
    });

    test.each(TEST_CASES)(
        'should return an array containing the addresses in the input object',
        (input, result) => {
            expect(fetchPreviousAddresses(input)).toEqual(result);
        }
    );

    afterAll(() => {
        jest.useRealTimers();
    });
});
