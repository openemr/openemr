/**
 * Calendar-safe validation helpers for CQM/AMC report timestamps.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (root, factory) {
    const validator = factory();
    if (typeof module === 'object' && module.exports) {
        module.exports = validator;
    }
    root.CqmDateRangeValidator = validator;
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    function isSupportedTimestamp(value) {
        if (typeof value !== 'string') {
            return false;
        }

        const match = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/.exec(value);
        if (match === null) {
            return false;
        }

        const parts = match.slice(1).map(Number);
        const date = new Date(0);
        date.setUTCHours(parts[3], parts[4], parts[5], 0);
        date.setUTCFullYear(parts[0], parts[1] - 1, parts[2]);

        return date.getUTCFullYear() === parts[0] &&
            date.getUTCMonth() === parts[1] - 1 &&
            date.getUTCDate() === parts[2] &&
            date.getUTCHours() === parts[3] &&
            date.getUTCMinutes() === parts[4] &&
            date.getUTCSeconds() === parts[5];
    }

    return { isSupportedTimestamp };
}));
