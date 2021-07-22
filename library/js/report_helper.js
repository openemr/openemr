/**
 * report_helper.js
 *
 * JavaScript functions to enhance reports.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2021 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var oeFixedHeaderSrc;     // the report's main table
var oeFixedHeaderNew;     // our clone of the table with just its thead
var oeFixedHeaderPad = 0; // fudge factor for cell padding

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
  var nrows = oeFixedHeaderNew.rows.length;
  if (nrows > 1) {
    var zrow = oeFixedHeaderNew.rows[nrows - 1].cloneNode(true);
    for (var c = 0; c < zrow.cells.length; ++c) {
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

// This is invoked whenever the window or frame resizes or scrolls.  It sets the table
// and column widths and horizontal position of the clone table to match the original table,
// and makes it visible or not depending on scroll position.
//
function oeFixedHeaderRepos() {
  var toppos = oeFixedHeaderSrc.offsetTop - $(window).scrollTop();
  if (toppos < 0) {
    oeFixedHeaderNew.style.left  = oeFixedHeaderSrc.offsetLeft - $(window).scrollLeft();
    oeFixedHeaderNew.style.width = oeFixedHeaderSrc.offsetWidth + 'px';
    // Set cell widths to match. In the case where there is more than one header row, we
    // copy widths from the last one to the first one in the target row. See above comments.
    var hrow = oeFixedHeaderSrc.rows[oeFixedHeaderNew.rows.length - 1];
    for (var i = 0; i < hrow.cells.length; ++i) {
      var width = hrow.cells[i].offsetWidth;
      var newcell = oeFixedHeaderNew.rows[0].cells[i];
      newcell.style.width = (width - oeFixedHeaderPad) + 'px';
      // offsetWidth includes some padding that style.width does not include,
      // so oeFixedHeaderPad is to compensate for the difference.
      var tmp = newcell.offsetWidth - width;
      if (tmp != 0) {
        oeFixedHeaderPad += tmp;
        newcell.style.width = (width - oeFixedHeaderPad) + 'px';
      }
      // oeFixedHeaderNew.rows[0].cells[i].innerHTML = oeFixedHeaderPad; // debugging
    }
    oeFixedHeaderNew.style.visibility = 'visible';
  }
  else {
    oeFixedHeaderNew.style.visibility = 'hidden';
  }
}
