const safeTrim = require('./safe-trim').safeTrim;

describe('safeTrim', () => {
    it('should trim the input if the input is a string', () => {
        expect(safeTrim('   ABC ')).toEqual('ABC');
    });

    test.each([null, undefined, 10, {}, true])(
        `for input of same type as %p, it should return the input without modifications`,
        (input) => {
            expect(safeTrim(input)).toEqual(input);
        }
    );
});
