const { countEntities } = require('./count-entities');

describe('countEntities', () => {
    test.each([
        { description: 'I only have one value!' },
        { npi: '2.16.840.1.113883.4.6', other: true },
        { code: '409073007', other: false },
        { extension: '45665', other: 10 },
        { id: '47', other: null },
        { date: '2023-09-17', other: {} },
        { use: 'work place', other: [] },
        { type: 'primary home', other: () => true },
    ])('should return 1 if the input represents a single entity', (input) => {
        expect(countEntities(input)).toEqual(1);
    });

    test.each([
        [[{ a: 1 }, { b: 2 }, { c: 3 }], 3],
        [
            {
                description: `I have multiple values inside, but none has an entity key!`,
                explode: `Why not?`,
            },
            2,
        ],
    ])(
        'should return the number of enumerable keys in the object if the input does not represent a single entity',
        (input, result) => {
            expect(countEntities(input)).toEqual(result);
        }
    );

    test.each([null, undefined, 10, true, 'OpenEMR', () => {}])(
        'should return zero if the input is %p',
        (input) => {
            expect(countEntities(input)).toEqual(0);
        }
    );

    it('should return zero if the input is an empty array', () => {
        expect(countEntities([])).toEqual(0);
    });
});
