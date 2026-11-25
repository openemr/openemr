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

    test('skips the non-image files of an imaging-CD export but keeps pictures and extensionless images', () => {
        const files = filesOf(
            entry('README.TXT'),
            entry('AUTORUN.INF'),
            entry('viewer/VIEWER.EXE'),
            entry('index.html'),
            entry('IM0001'),
            entry('preview.JPG'),
            entry('IM0002.dcm')
        );
        expect(names(zipImages.selectImageEntries(files))).toEqual(['IM0001', 'IM0002.dcm', 'preview.JPG']);
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

    test('a picture keeps its own extension', () => {
        expect(zipImages.dicomFileName('a/preview.jpg')).toBe('preview.jpg');
        expect(zipImages.dicomFileName('a/preview.PNG')).toBe('preview.PNG');
    });
});

describe('createNavigator', () => {
    const setup = (count = 3) => {
        const entries = Array.from({length: count}, (_, i) => entry(`${i + 1}.dcm`));
        const ui = {prev: {disabled: true}, next: {disabled: true}, count: {textContent: ''}};
        const loaded = [];
        const errors = [];
        const nav = zipImages.createNavigator(
            entries,
            ui,
            (buffer, name, isFirst) => loaded.push([name, isFirst]),
            (error) => errors.push(String(error))
        );
        return {entries, ui, loaded, errors, nav};
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

    test('the counter starts at the first image', () => {
        const {ui} = setup();
        expect(ui.count.textContent).toBe('1 / 3');
    });

    test('a first image that cannot be read rejects, so the caller can fall back', async () => {
        const {entries, ui, loaded, nav} = setup();
        entries[0].async.mockImplementationOnce(() => Promise.reject(new Error('corrupt')));
        await expect(nav.show(0)).rejects.toThrow('corrupt');
        expect(loaded).toEqual([]);
        expect(ui.next.disabled).toBe(false);
    });

    test('a later image that cannot be read leaves the counter and the image as they were', async () => {
        const {entries, ui, loaded, errors, nav} = setup();
        const log = jest.spyOn(console, 'log').mockImplementation(() => {});
        await nav.show(0);
        nav.loadEnded();
        entries[1].async.mockImplementationOnce(() => Promise.reject(new Error('corrupt')));
        ui.next.onclick();
        await flush();
        expect(loaded).toEqual([['1.dcm', true]]);
        expect(ui.count.textContent).toBe('1 / 3');
        expect(nav.current()).toBe(0);
        expect(ui.next.disabled).toBe(false);
        expect(errors).toHaveLength(1);
        log.mockRestore();
    });

    test('the last two images read are not inflated again', async () => {
        const {entries, ui, nav} = setup();
        await nav.show(0);
        nav.loadEnded();
        ui.next.onclick();
        await flush();
        nav.loadEnded();
        ui.prev.onclick();
        await flush();
        nav.loadEnded();
        expect(entries[0].async).toHaveBeenCalledTimes(1);
        expect(entries[1].async).toHaveBeenCalledTimes(1);
    });

    test('a load end the navigator did not start does not re-enable the buttons', async () => {
        const {ui, nav} = setup();
        await nav.show(0);
        nav.loadEnded();
        ui.next.onclick(); // read in flight, buttons off
        expect(ui.next.disabled).toBe(true);
        nav.loadEnded(); // for some other load (for example a restored state)
        expect(ui.next.disabled).toBe(true);
        await flush();
        nav.loadEnded();
        expect(ui.next.disabled).toBe(false);
    });
});

describe('fileType', () => {
    test.each([
        ['IM0001.dcm', 'application/dicom'],
        ['IM0001.DCM', 'application/dicom'],
        ['preview.jpg', 'image/jpeg'],
        ['preview.JPEG', 'image/jpeg'],
        ['scan.png', 'image/png'],
        ['scan.gif', 'image/gif'],
        ['scan.bmp', 'image/bmp'],
        ['unknown.xyz', ''],
        ['noextension', ''],
        ['constructor', ''],
    ])('%s', (name, expected) => {
        expect(zipImages.fileType(name)).toBe(expected);
    });

    test('a name made by dicomFileName gets a type dwv can load', () => {
        expect(zipImages.fileType(zipImages.dicomFileName('series/IM0001'))).toBe('application/dicom');
        expect(zipImages.fileType(zipImages.dicomFileName('series/preview.jpg'))).toBe('image/jpeg');
    });
});

describe('readResponse', () => {
    const streamResponse = (chunks, contentLength) => {
        let index = 0;
        return {
            headers: {get: (name) => (name === 'Content-Length' && contentLength !== undefined ? String(contentLength) : null)},
            arrayBuffer: jest.fn(() => Promise.resolve(new ArrayBuffer(0))),
            body: {
                getReader: () => ({
                    read: () => Promise.resolve(index < chunks.length
                        ? {done: false, value: chunks[index++]}
                        : {done: true, value: undefined}),
                }),
            },
        };
    };

    test('joins the chunks in order and reports the progress after each chunk', async () => {
        const response = streamResponse([Uint8Array.of(1, 2, 3), Uint8Array.of(4, 5)], 5);
        const progress = jest.fn();
        const buffer = await zipImages.readResponse(response, progress);
        expect(Array.from(new Uint8Array(buffer))).toEqual([1, 2, 3, 4, 5]);
        expect(progress.mock.calls).toEqual([[3, 5], [5, 5]]);
        expect(response.arrayBuffer).not.toHaveBeenCalled();
    });

    test('reads the body in one go when the size is unknown', async () => {
        const response = streamResponse([Uint8Array.of(1)], undefined);
        const progress = jest.fn();
        await zipImages.readResponse(response, progress);
        expect(response.arrayBuffer).toHaveBeenCalledTimes(1);
        expect(progress).not.toHaveBeenCalled();
    });

    test('reads the body in one go when it cannot be streamed', async () => {
        const response = streamResponse([], 10);
        response.body = null;
        await zipImages.readResponse(response, jest.fn());
        expect(response.arrayBuffer).toHaveBeenCalledTimes(1);
    });

    test('a read that fails (for example a cancelled download) rejects', async () => {
        const response = streamResponse([], 10);
        const abort = Object.assign(new Error('aborted'), {name: 'AbortError'});
        response.body = {getReader: () => ({read: () => Promise.reject(abort)})};
        await expect(zipImages.readResponse(response, jest.fn())).rejects.toBe(abort);
    });
});

describe('isAbortError', () => {
    test('only an AbortError counts as a cancel', () => {
        expect(zipImages.isAbortError(Object.assign(new Error('x'), {name: 'AbortError'}))).toBe(true);
        expect(zipImages.isAbortError(new Error('HTTP 500'))).toBe(false);
        expect(zipImages.isAbortError(null)).toBe(false);
        expect(zipImages.isAbortError(undefined)).toBe(false);
    });
});

describe('createNavigator when the viewer refuses an image', () => {
    const build = (loadFile) => {
        const entries = [entry('1.dcm'), entry('2.dcm'), entry('3.dcm')];
        const ui = {prev: {disabled: true}, next: {disabled: true}, count: {textContent: ''}};
        const errors = [];
        const nav = zipImages.createNavigator(entries, ui, loadFile, (error) => errors.push(String(error)));
        return {ui, errors, nav};
    };

    test('the first image that the viewer refuses rejects, so the caller can fall back', async () => {
        const {ui, nav} = build(() => {
            throw new Error('No loader found for file');
        });
        await expect(nav.show(0)).rejects.toThrow('No loader found for file');
        expect(ui.next.disabled).toBe(false); // not stuck busy
    });

    test('a later image that the viewer refuses is reported and the buttons work again', async () => {
        let calls = 0;
        const {ui, errors, nav} = build(() => {
            calls += 1;
            if (calls === 2) {
                throw new Error('No loader found for file');
            }
        });
        await nav.show(0);
        nav.loadEnded();
        ui.next.onclick();
        await flush();
        expect(errors).toEqual(['Error: No loader found for file']);
        expect(ui.next.disabled).toBe(false);
        expect(ui.prev.disabled).toBe(false);
    });
});
