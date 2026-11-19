/**
 * Calendar Visit Lists: fit a list's patients into its block. A block keeps the height of
 * its time range (day view, week view, day print). When its patients don't all fit, the
 * last ones are hidden and a marker takes their place: "+N more" on screen, which opens the
 * whole list over the grid, or a note in print that the list continues below the schedule.
 *
 * Row heights depend on the theme and font, so the fitting is measured in the browser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* exported pcFitVisitList, pcSetUpVisitLists, pcSetUpVisitListsForPrint */

// A label with %s replaced by a number, so translators place the number themselves.
function pcVisitListLabel(phrase, count) {
    return phrase.replace('%s', String(count));
}

function pcVisitListOverflows(block) {
    return block.scrollHeight > block.clientHeight;
}

/**
 * Hides the block's last rows until the rest fit beside the marker, and returns how many it
 * hid. label(hidden) gives the marker's text, set before each check so it is measured at its
 * final size. When not even one row fits beside the marker, the marker's content moves into
 * the header, before the + button. A block that then fits gets class visit-list-fitted,
 * which hides its scrollbar; one too short for even its header keeps the scrollbar, so
 * nothing in it is out of reach.
 */
function pcFitVisitList(block, marker, label) {
    var list = block.querySelector('.visit-list-entries');
    if (list === null || !pcVisitListOverflows(block)) {
        return 0;
    }
    var rows = list.querySelectorAll('.visit-list-entry');
    var labelElement = marker.querySelector('.visit-list-more-label');
    var hidden = 0;
    list.appendChild(marker);
    labelElement.textContent = label(hidden);
    for (var i = rows.length - 1; i >= 0 && pcVisitListOverflows(block); i--) {
        rows[i].classList.add('visit-list-overflow');
        hidden++;
        labelElement.textContent = label(hidden);
    }
    var header = block.querySelector('.visit-list-header');
    if (pcVisitListOverflows(block) && header !== null) {
        var content = marker.firstElementChild;
        content.classList.add('visit-list-more-in-header');
        header.insertBefore(content, header.querySelector('.visit-list-add'));
        marker.remove();
    }
    if (!pcVisitListOverflows(block)) {
        block.classList.add('visit-list-fitted');
    }
    return hidden;
}

// Runs callback once the block has a size: now, or when it first gets one (a calendar
// loaded in a hidden tab has none until the tab is shown).
function pcWhenVisitListShown(block, callback) {
    if (block.clientHeight > 0) {
        callback();
        return;
    }
    if (typeof ResizeObserver === 'undefined') {
        return;
    }
    var observer = new ResizeObserver(function () {
        if (block.clientHeight > 0) {
            observer.disconnect();
            callback();
        }
    });
    observer.observe(block);
}

function pcCloseVisitList(block) {
    if (!block.classList.contains('visit-list-expanded')) {
        return;
    }
    block.classList.remove('visit-list-expanded');
    block.style.height = block.dataset.fittedHeight || '';
    block.style.top = block.dataset.fittedTop || '';
    block.style.maxHeight = '';
    var button = block.querySelector('.visit-list-more-button');
    button.setAttribute('aria-expanded', 'false');
    button.querySelector('.visit-list-more-label').textContent = block.dataset.moreLabel || '';
}

// Opens the whole list over the grid. Where it would run past the bottom of the visible
// grid (container), it moves up; where it is taller than the grid, it scrolls inside.
function pcOpenVisitList(block, container, fewerLabel) {
    var doc = block.ownerDocument;
    doc.querySelectorAll('.visit-list-expanded').forEach(pcCloseVisitList);
    block.dataset.fittedHeight = block.style.height;
    block.dataset.fittedTop = block.style.top;
    block.classList.add('visit-list-expanded');
    block.style.height = 'auto';

    var room = container.getBoundingClientRect();
    var rect = block.getBoundingClientRect();
    var below = rect.bottom - room.bottom;
    if (below > 0) {
        var shift = Math.min(below, Math.max(0, rect.top - room.top));
        block.style.top = (block.offsetTop - shift) + 'px';
        rect = block.getBoundingClientRect();
    }
    if (rect.bottom > room.bottom) {
        block.style.maxHeight = Math.max(0, room.bottom - rect.top) + 'px';
    }

    var button = block.querySelector('.visit-list-more-button');
    button.setAttribute('aria-expanded', 'true');
    button.querySelector('.visit-list-more-label').textContent = fewerLabel;
}

/**
 * Fits every Visit List block in root (the on-screen views) and wires up "+N more".
 * labels.more is a phrase with %s for the number of hidden patients; labels.fewer closes
 * an opened list. A click elsewhere or Escape also closes it.
 */
function pcSetUpVisitLists(root, labels) {
    var doc = root.ownerDocument || root;
    root.querySelectorAll('.event_visit_list').forEach(function (block) {
        pcWhenVisitListShown(block, function () {
            var marker = doc.createElement('li');
            marker.className = 'visit-list-more';
            marker.innerHTML = '<button type="button" class="visit-list-more-button" aria-expanded="false">' +
                '<span class="visit-list-more-label"></span></button>';
            var hidden = pcFitVisitList(block, marker, function (count) {
                return pcVisitListLabel(labels.more, count);
            });
            if (hidden === 0) {
                return;
            }
            var button = block.querySelector('.visit-list-more-button');
            block.dataset.moreLabel = button.querySelector('.visit-list-more-label').textContent;
            button.addEventListener('click', function (e) {
                e.stopPropagation();
                if (block.classList.contains('visit-list-expanded')) {
                    pcCloseVisitList(block);
                } else {
                    pcOpenVisitList(block, block.closest('#bigCal') || doc.body, labels.fewer);
                }
            });
            button.addEventListener('dblclick', function (e) {
                e.stopPropagation();
            });
        });
    });
    doc.addEventListener('click', function (e) {
        doc.querySelectorAll('.visit-list-expanded').forEach(function (block) {
            if (!block.contains(e.target)) {
                pcCloseVisitList(block);
            }
        });
    });
    doc.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') {
            return;
        }
        doc.querySelectorAll('.visit-list-expanded').forEach(function (block) {
            var hadFocus = block.contains(doc.activeElement);
            pcCloseVisitList(block);
            if (hadFocus) {
                block.querySelector('.visit-list-more-button').focus();
            }
        });
    });
}

/**
 * Day print: fits each block on the grid and notes that its list continues below the
 * schedule, where the template printed every list in full. Keeps the full copy only of the
 * lists that ran out of room, and drops the section when none did. continuedLabel is a
 * phrase with %s for the number of patients not shown on the grid.
 */
function pcSetUpVisitListsForPrint(root, continuedLabel) {
    var doc = root.ownerDocument || root;
    var shown = 0;
    root.querySelectorAll('#bigCal .event_visit_list:not(.visit-list-full)').forEach(function (block) {
        var marker = doc.createElement('li');
        marker.className = 'visit-list-more';
        marker.innerHTML = '<span class="visit-list-more-label"></span>';
        var hidden = pcFitVisitList(block, marker, function (count) {
            return pcVisitListLabel(continuedLabel, count);
        });
        root.querySelectorAll('.visit-list-full').forEach(function (full) {
            if (full.dataset.eid !== block.dataset.eid) {
                return;
            }
            if (hidden === 0) {
                full.remove();
            } else {
                shown++;
            }
        });
    });
    var section = root.querySelector('.visit-list-continued');
    if (shown === 0 && section !== null) {
        section.remove();
    }
}
