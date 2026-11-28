/**
 * Flow Board (interface/patient_tracker/patient_tracker.php):
 * - the Status and Room badges open a menu of the values they can change to, and a choice
 *   is saved through patient_tracker.php (flb_change), then the board is redrawn;
 * - clicking an Appt Date, Appt Time or Order cell (or a repeating appointment's badge)
 *   opens the appointment dialog;
 * - a date or time the row above already shows is hidden, so each appears once;
 * - the To date is kept on or after the From date;
 * - a filter menu is redrawn after each change, so a long choice's "…" doesn't stay.
 *
 * window.flowBoardConfig (templates/patient_tracker/flow_board_script.html.twig):
 * { url, csrfToken, dateFormat, menus: { status: [{id, title}], room: [...] },
 *   text: { menuLabel: { status, room }, failed } }
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* exported flowBoardMarkRepeats, flowBoardDateKey, flowBoardKeepRange, flowBoardRedrawChoice */

/**
 * Hides each Appt Date or Appt Time that is the same as in the shown row above, so a date
 * shows once per day and a time once per time. Rows hidden by the board's filters are skipped.
 */
function flowBoardMarkRepeats(root) {
    if (!root) {
        return;
    }
    var previous = [];
    root.querySelectorAll('tbody tr').forEach(function (row) {
        if (row.style.display === 'none') {
            return;
        }
        var keys = [];
        row.querySelectorAll('[data-flb-repeat]').forEach(function (cell, index) {
            var key = cell.getAttribute('data-flb-repeat');
            cell.classList.toggle('flb-repeat', key === previous[index]);
            keys.push(key);
        });
        previous = keys;
    });
}

/**
 * A date as typed in a date picker, as YYYYMMDD for comparing, or null when unreadable.
 * format is date_display_format: 0 Y-m-d, 1 m/d/Y, 2 d/m/Y.
 */
function flowBoardDateKey(value, format) {
    var parts = String(value || '').trim().split(/[-/.]/);
    if (parts.length !== 3) {
        return null;
    }
    var year, month, day;
    if (Number(format) === 1) {
        month = parts[0]; day = parts[1]; year = parts[2];
    } else if (Number(format) === 2) {
        day = parts[0]; month = parts[1]; year = parts[2];
    } else {
        year = parts[0]; month = parts[1]; day = parts[2];
    }
    if (!/^\d{4}$/.test(year) || !/^\d{1,2}$/.test(month) || !/^\d{1,2}$/.test(day)) {
        return null;
    }
    return year + month.padStart(2, '0') + day.padStart(2, '0');
}

/**
 * When the To date is before the From date, sets it to the From date. Returns whether it changed.
 */
function flowBoardKeepRange(fromInput, toInput, format) {
    var from = flowBoardDateKey(fromInput.value, format);
    var to = flowBoardDateKey(toInput.value, format);
    if (from === null || to === null || to >= from) {
        return false;
    }
    toInput.value = fromInput.value;
    return true;
}

/**
 * Redraws a filter menu's shown choice. Some browsers keep the "…" of a long choice after a
 * shorter one is picked; showing the choice clipped for one frame makes them draw it again.
 */
function flowBoardRedrawChoice(select) {
    select.classList.add('flb-redraw');
    select.getBoundingClientRect();
    window.requestAnimationFrame(function () {
        select.classList.remove('flb-redraw');
    });
}

(function () {
    var openMenu = null;

    function config() {
        return window.flowBoardConfig || { menus: {}, text: { menuLabel: {} } };
    }

    function closeMenu(focusBadge) {
        if (!openMenu) {
            return;
        }
        var badge = openMenu.badge;
        openMenu.element.remove();
        badge.setAttribute('aria-expanded', 'false');
        openMenu = null;
        if (focusBadge && badge.isConnected) {
            badge.focus();
        }
    }

    // The badge is disabled until its save is done, so a second choice can't race the first.
    function save(badge, kind, value) {
        var settings = config();
        var body = new URLSearchParams();
        body.set('flb_change', '1');
        body.set('csrf_token_form', settings.csrfToken);
        body.set('eid', badge.getAttribute('data-flb-eid'));
        body.set(kind, value);
        if (window.top && typeof window.top.restoreSession === 'function') {
            window.top.restoreSession();
        }
        badge.disabled = true;
        return fetch(settings.url, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Flow Board change failed: ' + response.status);
                }
                badge.setAttribute('data-flb-value', value);
                badge.disabled = false;
                if (typeof window.refreshMe === 'function') {
                    window.refreshMe();
                }
            })
            .catch(function () {
                badge.disabled = false;
                window.alert(settings.text.failed);
            });
    }

    function showMenu(badge) {
        var kind = badge.getAttribute('data-flb-choice');
        var current = badge.getAttribute('data-flb-value');
        var settings = config();
        var options = settings.menus[kind] || [];

        var menu = document.createElement('div');
        menu.className = 'dropdown-menu show flb-menu';
        menu.setAttribute('role', 'menu');
        menu.setAttribute('aria-label', settings.text.menuLabel[kind] || '');
        options.forEach(function (option) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'dropdown-item' + (String(option.id) === current ? ' active' : '');
            item.setAttribute('role', 'menuitem');
            item.textContent = option.title;
            item.addEventListener('click', function () {
                closeMenu(true);
                if (String(option.id) !== current) {
                    save(badge, kind, String(option.id));
                }
            });
            menu.appendChild(item);
        });

        // In kiosk mode the table is fullscreen, and only what is inside it shows.
        var host = document.fullscreenElement || document.body;
        var fixed = host !== document.body;
        host.appendChild(menu);
        var rect = badge.getBoundingClientRect();
        menu.style.position = fixed ? 'fixed' : 'absolute';
        menu.style.top = (rect.bottom + (fixed ? 0 : window.scrollY)) + 'px';
        menu.style.left = (rect.left + (fixed ? 0 : window.scrollX)) + 'px';
        badge.setAttribute('aria-expanded', 'true');
        openMenu = { element: menu, badge: badge };
        var first = menu.querySelector('.active') || menu.querySelector('.dropdown-item');
        if (first) {
            first.focus();
        }
    }

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target || (openMenu && openMenu.element.contains(target))) {
            return;
        }
        var badge = target.closest('[data-flb-choice]');
        var wasOpen = openMenu && openMenu.badge === badge;
        closeMenu(false);
        if (badge) {
            if (!wasOpen) {
                showMenu(badge);
            }
            return;
        }
        var cell = target.closest('.flb-appt');
        if (cell && typeof window.calendarpopup === 'function') {
            window.calendarpopup(cell.getAttribute('data-flb-eid'), cell.getAttribute('data-flb-date'));
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!openMenu) {
            return;
        }
        var items = Array.prototype.slice.call(openMenu.element.querySelectorAll('.dropdown-item'));
        var index = items.indexOf(document.activeElement);
        if (event.key === 'Escape') {
            event.preventDefault();
            closeMenu(true);
        } else if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && index !== -1) {
            // Only while focus is in the menu; elsewhere the keys keep their own meaning.
            event.preventDefault();
            var step = event.key === 'ArrowDown' ? 1 : -1;
            var next = items[(index + step + items.length) % items.length];
            if (next) {
                next.focus();
            }
        }
    });

    function onDateChange() {
        var from = document.getElementById('form_from_date');
        var to = document.getElementById('form_to_date');
        if (from && to) {
            flowBoardKeepRange(from, to, config().dateFormat);
        }
    }

    // The date picker reports a pick through jQuery, which native listeners don't see.
    if (window.jQuery) {
        window.jQuery(document).on('change', '#form_from_date, #form_to_date', onDateChange);
    } else {
        document.addEventListener('change', function (event) {
            if (event.target && (event.target.id === 'form_from_date' || event.target.id === 'form_to_date')) {
                onDateChange();
            }
        });
    }

    document.addEventListener('change', function (event) {
        var target = event.target;
        if (target && target.tagName === 'SELECT' && target.closest('.flb-filters')) {
            flowBoardRedrawChoice(target);
        }
    });

    // Called by the board's refineMe() after each redraw or filter change.
    window.flowBoardRedrawn = function (root) {
        // A redraw replaces the badge an open menu belongs to.
        if (openMenu && !openMenu.badge.isConnected) {
            closeMenu(false);
        }
        flowBoardMarkRepeats(root);
    };
})();
