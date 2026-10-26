/**
 * Click-to-sort headers for the invoice list on EOB Posting - Search
 * (sl_eob_search.php). A header with data-sort ("text", "number" or "date")
 * becomes a button; clicking it sorts the rows by that column, ascending
 * first, then descending. A cell's data-sort-value, when present, is what
 * sorts (dates as YYYY-MM-DD, amounts unformatted, the invoice as pid:encounter);
 * otherwise its text. Text compares runs of digits as numbers.
 * Rows are moved, not rebuilt, so their checkboxes keep their state.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function () {
    'use strict';

    const table = document.getElementById('invoice-list');
    if (!table || !table.tHead) {
        return;
    }

    const sortValue = function (row, index) {
        const cell = row.cells[index];
        if (!cell) {
            return '';
        }
        return cell.dataset.sortValue ?? cell.textContent.trim();
    };

    const compare = function (type, a, b) {
        if (type === 'number') {
            return (parseFloat(a) || 0) - (parseFloat(b) || 0);
        }
        if (type === 'date') {
            // YYYY-MM-DD compares as text; no date (never sent) sorts first.
            return a < b ? -1 : (a > b ? 1 : 0);
        }
        return a.localeCompare(b, undefined, {sensitivity: 'base', numeric: true});
    };

    const headers = Array.from(table.tHead.rows[0].cells);
    headers.forEach(function (th, index) {
        const type = th.dataset.sort;
        if (!type) {
            return;
        }
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm p-0 font-weight-bold text-reset';
        button.textContent = th.textContent.trim();
        th.textContent = '';
        th.appendChild(button);

        button.addEventListener('click', function () {
            const body = table.tBodies[0];
            if (!body) {
                return;
            }
            const descending = th.getAttribute('aria-sort') === 'ascending';
            const rows = Array.from(body.rows);
            rows.sort(function (a, b) {
                const order = compare(type, sortValue(a, index), sortValue(b, index));
                return descending ? -order : order;
            });
            rows.forEach(function (row) {
                body.appendChild(row);
            });
            headers.forEach(function (other) {
                other.removeAttribute('aria-sort');
            });
            th.setAttribute('aria-sort', descending ? 'descending' : 'ascending');
        });
    });
})();
