const { fDate, templateDate } = require('./date');

describe('fdate', () => {
    const today = new Date('2023-08-25T09:18:57.268Z');

    beforeAll(() => {
        jest.useFakeTimers().setSystemTime(today);
    });

    test.each([
        ['', today.toISOString()],
        ['0', today.toISOString()],
        ['0000-00-00', today.toISOString()],
        ['20230514', '2023-05-14'],
        ['20230514XXXX00', '2023-05-14'],
        ['12-21-2023', '2023-12-21'],
        ['12/21/2023', '2023-12-21'],
        ['20230517 09:18:57', '2023-05-17 09:18:57'],
        ['20230517091857-ZONE', '2023-05-17 09:18:57-ZONE'],
        ['2023-08-25T09:18:57.268Z', '2023-08-25T09:18:57.268Z']
    ])(
        `format date input: %p`,
        (date, formatted) => {
            expect(fDate(date)).toEqual(formatted);
        }
    );

    describe('lim8', () => {
        it('should format date returning the first 8 characters', () => {
            expect(fDate('14-05-23 09:57', true)).toEqual('14-05-23');
        });
    });

    afterAll(() => {
        jest.useRealTimers();
    });
});

describe('templateDate', () => {
    it('should return an object containing the formatted date and precision', () => {
        expect(templateDate('20230514', 'day')).toEqual({ date: '2023-05-14', precision: 'day' });
    });
});
