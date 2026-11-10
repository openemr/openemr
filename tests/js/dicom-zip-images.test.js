/**
 * Tests for the DICOM viewer's zip handling (library/js/dwv/dicom_zip_images.js): which zip entries are shown,
 * in which order, and the previous/next navigation (one image at a time, stale reads dropped).
 *
 * Run with: npm run test:js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const zipImages = require('../../library/js/dwv/dicom_zip_images.js');

const entry = (name, dir = false) => ({
    name,
    dir,
    async: jest.fn(() => Promise.resolve(new ArrayBuffer(8))),
});
const filesOf = (...entries) => Object.fromEntries(entries.map((e) => [e.name, e]));
const names = (entries) => entries.map((e) => e.name);
const flush = () => new Promise((resolve) => setImmediate(resolve));

describe('isZipDocumentUrl', () => {
    test.each([
        ['/controller.php?document&retrieve&patient_id=1&document_id=2&type=.zip', true],
        ['/controller.php?document&retrieve&type=.zip&as_file=false', true],
        ['/controller.php?document&retrieve&patient_id=1&document_id=2', false],
        ['/controller.php?document&retrieve&type=.zipx', false],
        ['/controller.php?document&retrieve&mytype=.zip', false],
    ])('%s', (url, expected) => {
        expect(zipImages.isZipDocumentUrl(url)).toBe(expected);
    });
});

describe('selectImageEntries', () => {
    test('skips folders, __MACOSX, dot files and DICOMDIR', () => {
        const files = filesOf(
            entry('study/', true),
            entry('study/1.dcm'),
            entry('__MACOSX/study/._1.dcm'),
            entry('study/.DS_Store'),
            entry('DICOMDIR'),
            entry('cd/dicomdir'),
            entry('study/2.dcm')
        );
        expect(names(zipImages.selectImageEntries(files))).toEqual(['study/1.dcm', 'study/2.dcm']);
    });

    test('sorts by name with numbers in numeric order', () => {
        const files = filesOf(entry('10.dcm'), entry('2.dcm'), entry('1.dcm'));
        expect(names(zipImages.selectImageEntries(files))).toEqual(['1.dcm', '2.dcm', '10.dcm']);
    });

    test('a zip with only skipped entries has no images', () => {
        const files = filesOf(entry('DICOMDIR'), entry('__MACOSX/', true));
        expect(zipImages.selectImageEntries(files)).toEqual([]);
    });
});

describe('dicomFileName', () => {
    test('keeps the base name and adds .dcm only when missing', () => {
        expect(zipImages.dicomFileName('a/b/IM0001')).toBe('IM0001.dcm');
        expect(zipImages.dicomFileName('a/b/IM0001.DCM')).toBe('IM0001.DCM');
    });
});

describe('createNavigator', () => {
    const setup = (count = 3) => {
        const entries = Array.from({length: count}, (_, i) => entry(`${i + 1}.dcm`));
        const ui = {prev: {disabled: true}, next: {disabled: true}, count: {textContent: ''}};
        const loaded = [];
        const nav = zipImages.createNavigator(entries, ui, (buffer, name, isFirst) => loaded.push([name, isFirst]));
        return {entries, ui, loaded, nav};
    };

    test('shows the first image, counts it and keeps the buttons off until the load ended', async () => {
        const {ui, loaded, nav} = setup();
        nav.show(0);
        expect(ui.count.textContent).toBe('1 / 3');
        expect(ui.prev.disabled).toBe(true);
        expect(ui.next.disabled).toBe(true); // busy
        await flush();
        expect(loaded).toEqual([['1.dcm', true]]);
        nav.loadEnded();
        expect(ui.prev.disabled).toBe(true); // first image
        expect(ui.next.disabled).toBe(false);
    });

    test('next and previous move one image and only the first load is flagged first', async () => {
        const {ui, loaded, nav} = setup();
        nav.show(0);
        await flush();
        nav.loadEnded();
        ui.next.onclick();
        await flush();
        nav.loadEnded();
        ui.next.onclick();
        await flush();
        nav.loadEnded();
        expect(loaded).toEqual([['1.dcm', true], ['2.dcm', false], ['3.dcm', false]]);
        expect(ui.count.textContent).toBe('3 / 3');
        expect(ui.next.disabled).toBe(true); // last image
        expect(ui.prev.disabled).toBe(false);
        ui.prev.onclick();
        await flush();
        expect(ui.count.textContent).toBe('2 / 3');
        expect(nav.current()).toBe(1);
    });

    test('clicks are ignored while an image is loading and at the ends', async () => {
        const {ui, loaded, nav} = setup();
        nav.show(0);
        await flush();
        ui.next.onclick(); // still busy: the viewer has not reported the load end
        ui.prev.onclick(); // at the first image anyway
        await flush();
        expect(loaded).toHaveLength(1);
        expect(nav.current()).toBe(0);
    });

    test('a read replaced by a newer one is dropped', async () => {
        const {entries, ui, loaded, nav} = setup();
        let finishFirst;
        entries[0].async.mockImplementationOnce(() => new Promise((resolve) => { finishFirst = resolve; }));
        nav.show(0); // slow read
        nav.show(1); // newer request
        await flush();
        finishFirst(new ArrayBuffer(8));
        await flush();
        expect(loaded).toEqual([['2.dcm', true]]);
        expect(ui.count.textContent).toBe('2 / 3');
    });

    test('a failed read re-enables the buttons and loads nothing', async () => {
        const {entries, ui, loaded, nav} = setup();
        const log = jest.spyOn(console, 'log').mockImplementation(() => {});
        entries[1].async.mockImplementationOnce(() => Promise.reject(new Error('corrupt')));
        nav.show(1);
        await flush();
        expect(loaded).toEqual([]);
        expect(ui.prev.disabled).toBe(false);
        expect(ui.next.disabled).toBe(false);
        log.mockRestore();
    });
});
