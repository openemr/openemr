/**
 * @jest-environment jsdom
 */

/**
 * Closing a tab with its close button asks first when the page in it, or a
 * frame inside it, has unsaved changes. Removing the tab's iframe fires no
 * beforeunload prompt, so without this the edits are lost silently.
 *
 * Run with: npm run test:js -- tests/js/tabs-unsaved-changes.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../interface/main/tabs/js/tabs_view_model.js'),
    'utf8'
);

// The script declares its functions at the top level; hand back the ones under test.
const { patientChartCanClose, tabClose, tabCloseByName, tabHasUnsavedChanges } = new Function(
    source + '\nreturn { patientChartCanClose, tabClose, tabCloseByName, tabHasUnsavedChanges };'
)();

const clickEvent = { type: 'click' };

function fakeTab(name, win) {
    return {
        name: () => name,
        visible: () => false,
        window: win,
    };
}

function frameWindow({ somethingChanged, frames = [] } = {}) {
    const win = { frames };
    if (somethingChanged !== undefined) {
        win.somethingChanged = somethingChanged;
    }
    return win;
}

let tabs;

beforeEach(() => {
    tabs = [];
    window.app_view_model = {
        application_data: {
            tabs: {
                tabsList: Object.assign(() => tabs, {
                    remove: (tab) => {
                        tabs = tabs.filter((t) => t !== tab);
                    },
                }),
            },
        },
    };
    window.xl = (s) => s;
    window.timed_out = false;
    window.confirm = jest.fn();
});

describe('tabHasUnsavedChanges', () => {
    test('is false without a window', () => {
        expect(tabHasUnsavedChanges(null)).toBe(false);
    });

    test('is false when the page does not track changes', () => {
        expect(tabHasUnsavedChanges(frameWindow())).toBe(false);
    });

    test('reads the page flag', () => {
        expect(tabHasUnsavedChanges(frameWindow({ somethingChanged: false }))).toBe(false);
        expect(tabHasUnsavedChanges(frameWindow({ somethingChanged: true }))).toBe(true);
    });

    test('finds changes in a nested frame, such as an encounter form', () => {
        const form = frameWindow({ somethingChanged: true });
        const encounter = frameWindow({ frames: [frameWindow(), form] });
        expect(tabHasUnsavedChanges(frameWindow({ frames: [encounter] }))).toBe(true);
    });

    test('skips a frame it cannot read', () => {
        const crossOrigin = {};
        Object.defineProperty(crossOrigin, 'somethingChanged', {
            get() {
                throw new DOMException('Blocked a frame', 'SecurityError');
            },
        });
        expect(tabHasUnsavedChanges(frameWindow({ frames: [crossOrigin] }))).toBe(false);
    });
});

describe('tabClose', () => {
    test('closes a tab with no unsaved changes without asking', () => {
        const tab = fakeTab('pat', frameWindow({ somethingChanged: false }));
        tabs = [tab];
        tabClose(tab, clickEvent);
        expect(window.confirm).not.toHaveBeenCalled();
        expect(tabs).toEqual([]);
    });

    test('keeps the tab open when the user cancels', () => {
        const tab = fakeTab('pat', frameWindow({ somethingChanged: true }));
        tabs = [tab];
        window.confirm.mockReturnValue(false);
        tabClose(tab, clickEvent);
        expect(window.confirm).toHaveBeenCalledTimes(1);
        expect(tabs).toEqual([tab]);
    });

    test('closes the tab when the user confirms', () => {
        const tab = fakeTab('pat', frameWindow({ somethingChanged: true }));
        tabs = [tab];
        window.confirm.mockReturnValue(true);
        tabClose(tab, clickEvent);
        expect(window.confirm).toHaveBeenCalledTimes(1);
        expect(tabs).toEqual([]);
    });

    test('does not ask after the session timed out', () => {
        const tab = fakeTab('pat', frameWindow({ somethingChanged: true }));
        tabs = [tab];
        window.timed_out = true;
        tabClose(tab, clickEvent);
        expect(window.confirm).not.toHaveBeenCalled();
        expect(tabs).toEqual([]);
    });

    test('does not ask when a page closes its own tab by name', () => {
        const tab = fakeTab('enc', frameWindow({ somethingChanged: true }));
        tabs = [tab];
        tabCloseByName('enc');
        expect(window.confirm).not.toHaveBeenCalled();
        expect(tabs).toEqual([]);
    });
});

describe('patientChartCanClose', () => {
    test('closes without asking when no chart tab has unsaved changes', () => {
        tabs = [fakeTab('pat', frameWindow({ somethingChanged: false })), fakeTab('enc', frameWindow())];
        expect(patientChartCanClose()).toBe(true);
        expect(window.confirm).not.toHaveBeenCalled();
    });

    test('ignores unsaved changes in a tab that is not part of the chart', () => {
        tabs = [fakeTab('adm', frameWindow({ somethingChanged: true }))];
        expect(patientChartCanClose()).toBe(true);
        expect(window.confirm).not.toHaveBeenCalled();
    });

    test('asks once when a chart tab has unsaved changes, and honors cancel', () => {
        tabs = [
            fakeTab('pat', frameWindow({ somethingChanged: true })),
            fakeTab('enc', frameWindow({ frames: [frameWindow({ somethingChanged: true })] })),
        ];
        window.confirm.mockReturnValue(false);
        expect(patientChartCanClose()).toBe(false);
        expect(window.confirm).toHaveBeenCalledTimes(1);
    });

    test('closes when the user confirms', () => {
        tabs = [fakeTab('enc', frameWindow({ somethingChanged: true }))];
        window.confirm.mockReturnValue(true);
        expect(patientChartCanClose()).toBe(true);
    });

    test('does not ask after the session timed out', () => {
        tabs = [fakeTab('pat', frameWindow({ somethingChanged: true }))];
        window.timed_out = true;
        expect(patientChartCanClose()).toBe(true);
        expect(window.confirm).not.toHaveBeenCalled();
    });
});
