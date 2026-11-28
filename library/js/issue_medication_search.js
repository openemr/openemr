/**
 * Ranking of the medication terminology search on the issue form (interface/patient_file/summary/add_edit_issue.php).
 *
 * The form asks find_code_dynamic_ajax.php for the rows that contain the search term; this reads those rows,
 * drops duplicates and puts the best matches first: exact, then starts with, then a word or bracketed start,
 * then anywhere. The logic needs no DOM so that it can be tested (tests/js/issue-medication-search.test.js).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var issueMedicationSearch = (function () {
    /**
     * One row of the DataTables reply of find_code_dynamic_ajax.php to {code, codeType, description}, or null.
     */
    const parseResult = function (row) {
        if (!row || typeof row.DT_RowId !== 'string' || row.DT_RowId.indexOf('CID|') !== 0) {
            return null;
        }

        let payload = null;
        try {
            payload = JSON.parse(row.DT_RowId.substring(4));
        } catch (error) {
            console.error('Unable to parse OpenEMR code-search result', error);
            return null;
        }

        if (payload === null || typeof payload !== 'object' || Array.isArray(payload)) {
            return null;
        }

        const code = payload.code !== undefined ? String(payload.code).split('|')[0] : '';
        const description = payload.description !== undefined ? String(payload.description) : '';
        const codeType = payload.codetype ? String(payload.codetype) : '';

        if (!code || !description || !codeType) {
            return null;
        }

        return {
            code: code,
            codeType: codeType,
            description: description
        };
    };

    /**
     * How well a result matches the term: higher is better, 0 = no match.
     */
    const scoreResult = function (item, term) {
        const description = item.description.toLowerCase();
        const query = term.trim().toLowerCase();

        if (!query) {
            return 0;
        }
        if (description === query) {
            return 1000;
        }
        if (description.startsWith(query)) {
            return 950;
        }
        if (description.includes('[' + query) || description.includes('(' + query)) {
            return 925;
        }

        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const boundary = new RegExp('(^|[^a-z0-9])' + escaped, 'i');
        if (boundary.test(description)) {
            return 900;
        }
        if (description.includes(query)) {
            return 500;
        }
        return 0;
    };

    /**
     * Parse, drop duplicate code type + code pairs, order by score then description, and keep the best `limit`.
     */
    const rankResults = function (rows, term, limit) {
        const seen = new Set();
        const items = rows
            .map(parseResult)
            .filter((item) => item !== null)
            .filter(function (item) {
                const key = item.codeType + ':' + item.code;
                if (seen.has(key)) {
                    return false;
                }
                seen.add(key);
                return true;
            });

        items.sort(function (a, b) {
            const scoreDifference = scoreResult(b, term) - scoreResult(a, term);
            if (scoreDifference !== 0) {
                return scoreDifference;
            }
            return a.description.localeCompare(b.description);
        });

        return items.slice(0, limit);
    };

    return {
        parseResult: parseResult,
        scoreResult: scoreResult,
        rankResults: rankResults
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = issueMedicationSearch;
}
