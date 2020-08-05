/**
 * report_helper.js
 *
 * JavaScript functions to enhance reports.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

let oeFixedHeaderSrc; // the report's main table
let oeFixedHeaderNew; // our clone of the table with just its thead
let oeFixedHeaderPad = 0; // fudge factor for cell padding

// This is invoked whenever the window or frame resizes or scrolls.  It sets the table
// and column widths and horizontal position of the clone table to match the original table,
// and makes it visible or not depending on scroll position.
//
function oeFixedHeaderRepos() {
    const toppos = oeFixedHeaderSrc.offsetTop - $(window).scrollTop();
    if (toppos < 0) {
        oeFixedHeaderNew.style.left = oeFixedHeaderSrc.offsetLeft - $(window).scrollLeft();
        oeFixedHeaderNew.style.width = `${oeFixedHeaderSrc.offsetWidth}px`;
        // Set cell widths to match. In the case where there is more than one header row, we
        // copy widths from the last one to the first one in the target row. See above comments.
        const hrow = oeFixedHeaderSrc.rows[oeFixedHeaderNew.rows.length - 1];
        for (let i = 0; i < hrow.cells.length; i += 1) {
            const width = hrow.cells[i].offsetWidth;
            const newcell = oeFixedHeaderNew.rows[0].cells[i];
            newcell.style.width = `${(width - oeFixedHeaderPad)}px`;
            // offsetWidth includes some padding that style.width does not include,
            // so oeFixedHeaderPad is to compensate for the difference.
            const tmp = newcell.offsetWidth - width;
            if (tmp !== 0) {
                oeFixedHeaderPad += tmp;
                newcell.style.width = `${(width - oeFixedHeaderPad)}px`;
            }
            // oeFixedHeaderNew.rows[0].cells[i].innerHTML = oeFixedHeaderPad; // debugging
        }
        oeFixedHeaderNew.style.visibility = 'visible';
    } else {
        oeFixedHeaderNew.style.visibility = 'hidden';
    }
}

// Call this to manage a table that has a <thead> section, so that the header will
// appear to be fixed at the top of the window while the rest of the table scrolls.
//
function oeFixedHeaderSetup(tbl) {
    if (!tbl) return;
    // Here we make a clone of the original table that contains only a copy of its thead
    // and all the contents of that thead.  It is initially invisible and always fixed in
    // position at the top of the window.
    oeFixedHeaderNew = tbl.cloneNode(false);
    oeFixedHeaderNew.id = ''; // remove id because duplicates not allowed
    oeFixedHeaderNew.style.tableLayout = 'fixed'; // this improves matching of column widths
    oeFixedHeaderNew.style.position = 'fixed';
    oeFixedHeaderNew.style.visibility = 'hidden';
    oeFixedHeaderNew.appendChild(tbl.tHead.cloneNode(true));
    // If there is more than one header row then create a dummy empty clone of the last at the top.
    // This is a kludge because it does not work to set widths for any rows other than the first.
    // We assume the last row is the most "detailed" and so setting its cell widths is enough.
    const nrows = oeFixedHeaderNew.rows.length;
    if (nrows > 1) {
        const zrow = oeFixedHeaderNew.rows[nrows - 1].cloneNode(true);
        for (let c = 0; c < zrow.cells.length; c += 1) {
            zrow.cells[c].innerHTML = '';
            zrow.cells[c].style.height = '0px';
            zrow.cells[c].style.padding = '0px';
            zrow.cells[c].style.borderWidth = '0px';
            zrow.cells[c].style.borderStyle = 'none';
        }
        zrow.style.height = '0px';
        zrow.style.borderWidth = '0px';
        zrow.style.borderStyle = 'none';
        oeFixedHeaderNew.rows[0].parentNode.insertBefore(zrow, oeFixedHeaderNew.rows[0]);
    }
    // Position this new table after the original table in the DOM so it will appear on top.
    tbl.parentNode.appendChild(oeFixedHeaderNew);
    oeFixedHeaderNew.style.marginTop = '0px';
    oeFixedHeaderNew.style.top = '0px';
    oeFixedHeaderSrc = tbl;
    window.onresize = oeFixedHeaderRepos;
    window.onscroll = oeFixedHeaderRepos;
}
