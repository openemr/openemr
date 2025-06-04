const cleanCode = require('./clean-code').cleanCode;
const NULL_FLAVOR = require('../constants').NULL_FLAVOR;

describe('cleanCode', () => {
    it('should return NULL_FLAVOR for undefined input', () => {
        expect(cleanCode()).toEqual(NULL_FLAVOR);
    });

    it('should return NULL_FLAVOR for input with length < 1', () => {
        expect(cleanCode('')).toEqual(NULL_FLAVOR);
    });

    it('should remove . and # characters from input for input with length > 1',  () => {
        expect(cleanCode('.A')).toEqual('A');
        expect(cleanCode('A.')).toEqual('A');
        expect(cleanCode('A.B')).toEqual('AB');
        expect(cleanCode('A..B')).toEqual('A.B');
        expect(cleanCode('#A')).toEqual('A');
        expect(cleanCode('A#')).toEqual('A');
        expect(cleanCode('A#B')).toEqual('AB');
        expect(cleanCode('A##B')).toEqual('A#B');
    });
});
