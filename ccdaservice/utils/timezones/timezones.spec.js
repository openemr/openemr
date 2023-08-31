const {
    populateTimezones,
    TIMEZONE_PRECISION,
    DEPTH_LIMIT,
} = require('./timezones');

describe('populateTimezones', () => {
    const timezoneOffset = 5;

    test.each([null, undefined, 10, false, 'OpenEMR'])(
        'should return the node input if the node input is not an object',
        (input) => {
            expect(populateTimezones(input)).toEqual(input);
        }
    );

    it(`should set timezoneOffset if node input has precision equals ${TIMEZONE_PRECISION}`, () => {
        expect(
            populateTimezones({ precision: TIMEZONE_PRECISION }, timezoneOffset)
        ).toEqual({
            precision: TIMEZONE_PRECISION,
            timezoneOffset,
        });
    });

    it('should set timezoneOffset for nested nodes', () => {
        const nodes = {
            a: {
                b: {
                    c: {
                        precision: TIMEZONE_PRECISION,
                    },
                },
            },
        };
        expect(populateTimezones(nodes, timezoneOffset)).toEqual({
            a: {
                b: {
                    c: {
                        precision: TIMEZONE_PRECISION,
                        timezoneOffset,
                    },
                },
            },
        });
    });

    it(`should abort recursion and return node if it hits depth ${DEPTH_LIMIT}`, () => {
        // keeping logs clean
        jest.spyOn(console, 'error').mockImplementationOnce(() => {});
        const node = getDeepNode();
        expect(populateTimezones(node, timezoneOffset, 0)).toEqual(node);
        expect(console.error).toHaveBeenCalledTimes(1);

        function getDeepNode() {
            const node = {};
            let latest = node;
            for (let i = 0; i < DEPTH_LIMIT + 1; i++) {
                latest[0] = {};
                latest = latest[0];
            }
            return node;
        }
    });
});
