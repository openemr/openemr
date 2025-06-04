const DataStack = require('./data-stack.js').DataStack;

describe('DataStack', () => {
    const delimiter = ';'
    let stack;

    beforeEach(() => {
        stack = new DataStack(delimiter);
    });

    it('should create a new stack', () => {
        expect(stack).toBeTruthy();
    });

    it('should store and return data', () => {
        expect(stack.returnData()).toEqual(null);
        const hello = 'Hello';
        const world = 'World!';
        stack.push(hello + delimiter);
        stack.push(world + delimiter);
        expect(stack.returnData()).toEqual(hello);
        expect(stack.returnData()).toEqual(world);
    });

    describe('endOfCcda', () => {
        it('should return true if there is no data in the stack', () => {
            expect(stack.endOfCcda()).toEqual(true);
        });

        it('should return true if the delimiter is not part of the data', () => {
            stack.push('Undelimited data');
            expect(stack.endOfCcda()).toEqual(true);
        });

        it('should return false otherwise', () => {
            stack.push(`OpenEMR${delimiter}`);
            expect(stack.endOfCcda()).toEqual(false);
        });
    });

    describe('clear', () => {
        it('should remove all data from the stack', () => {
            stack.push(`OpenEMR${delimiter}`);
            stack.clear();
            expect(stack.endOfCcda()).toEqual(true);
        });
    });

    describe('readStackByDelimiter', () => {
        beforeEach(() => stack.push(`OpenEMR!The best app${delimiter}`));

        it('should be able to read the stack using a different delimiter', () => {
            expect(stack.readStackByDelimiter('!')).toEqual('OpenEMR');
        });

        it('should preserve the original delimiter for future readings', () => {
            stack.readStackByDelimiter('!');
            expect(stack.returnData()).toEqual('The best app');
        });
    });
});
