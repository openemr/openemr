/**
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
const validator = require('../../library/js/CqmDateRangeValidator');

describe('CQM date range timestamp validation', () => {
    test.each([
        '2024-02-29 23:59:59',
        '2025-01-01 00:00:00',
        '0001-01-01 00:00:00',
    ])('accepts exact normalized calendar-valid timestamp %s', (value) => {
        expect(validator.isSupportedTimestamp(value)).toBe(true);
    });

    test.each([
        undefined,
        '',
        '2025-02-29 00:00:00',
        '2025-04-31 00:00:00',
        '2025-01-01 24:00:00',
        '2025-1-01 00:00:00',
        '2025-01-01T00:00:00',
    ])('ignores unsupported timestamp %s', (value) => {
        expect(validator.isSupportedTimestamp(value)).toBe(false);
    });
});
