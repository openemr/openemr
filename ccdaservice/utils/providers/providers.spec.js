const { populateProvider } = require('./providers');
const { PROVIDER_TEST_DATA, TODAY } = require('./providers.mock');

describe('populateProvider', () => {
    beforeAll(() => {
        jest.useFakeTimers().setSystemTime(new Date(TODAY));
    });

    test.each(PROVIDER_TEST_DATA)(
        'should map the input data to provider data object',
        (provider, documentData, result) => {
            expect(populateProvider(provider, documentData)).toEqual(result);
        }
    );

    afterAll(() => {
        jest.useRealTimers();
    });
});
